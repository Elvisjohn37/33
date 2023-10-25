/**
 * This will serve as model for our frontend. 
 * All ajax request must pass through here.
 * Note: 1. Only independent modules should be included as dependency of this module.
 *          This is to avoid conflict and circular pattern.
 *       2. All value placeholder added to ajax, etc. that is used only for this module should have 'psm_',
 *          This is to avoid conflict when we set ajax_setup outside this module.
 *       3. If ajax request needs to retry, complete callback will trigger later after retry.
 *                  
 * @author PS Team
 */

define('ps_model', ['jquery', 'ps_helper', 'ps_gconfig', 'ps_store', 'ps_localstorage','ps_date'], function() {

    'use strict';

    var $               = arguments[0];
    var ps_helper       = arguments[1];
    var ps_gconfig      = arguments[2];
    var ps_store        = arguments[3];
    var ps_localstorage = arguments[4];
    var ps_date         = arguments[5];
    
    var globals = {
                    debug    : false,

                    // store for all data comming from ps_model
                    // we need to register all here so everyone knows that the store exists
                    store   : new ps_store('ps_model', {
                                compatibility: {
                                                recommended: true,
                                                is_notified: ps_localstorage.get('compatibility_is_notified') || false
                                            }
                            }),

                    // sharable stores
                    sharable: ['lang', 'lang_config', 'user', 'rso', 'site', 'compatibility'],

                    // rso store config
                    rso     : {
                                is_served: false
                            },

                    // view_data store config
                    view_data: {
                                payload   : ps_gconfig.payload,
                                is_served : false
                            },

                    // plugin store config
                    plugin   : {
                                is_served : false
                            },

                    csrf     : {
                                ongoing   : null,
                                token     : ps_gconfig.token,
                                max_retry : 3,
                                pending   : {},
                                queue     : []
                            },

                    blocking : { 
                                ongoing     : {},
                                default_max : 1,
                                pendings    : {}
                            },

                    // all ps_model ajax callbacks that can be subscribed
                    subscriptions: {
                                        precomplete: {},
                                        success    : {},
                                        error      : {},
                                        fail       : {},
                                        complete   : {},
                                        progress   : {},
                                        retry      : {}
                                    },
                    chat: {
                            // compatible with countdown timer
                            block_time_format: 'yy-mm-dd HHH:iii:sss'
                        }
                };

    /**
     |------------------------------------------------------------------------------------------------------------------
     | Private Methods
     |------------------------------------------------------------------------------------------------------------------
     */
    var callables = {

        /**
         * Extend helper debug using local configuration
         */
        debug: ps_helper.debug(globals.debug),

        /**
         * This will finlize ajax setups before passing to $.ajax
         * NOTE: we always check psm_abort_success for each process if condition,
         *       because other processes above them might appended abort_success to ajax
         * @param  object   ajax_setup     
         * @param  object   internal_setup 
         * @return void         
         */
        ajax: function(ajax_setup, internal_setup) {

            ajax_setup             = ajax_setup || {}
            internal_setup         = internal_setup || {}
            ajax_setup.request_key = internal_setup.request_key || ps_helper.uniqid();

            var data_object        = ajax_setup.data;
            if (!ps_helper.empty(data_object) && !$.isPlainObject(data_object) && !data_object instanceof FormData) {
                throw 'ps_model.ajax accepts only object data option';
            }

            // blocking
            if (ajax_setup.psm_abort_success != true && $.isPlainObject(internal_setup.block)) {
                if (callables.block_ongoing(internal_setup.block, ajax_setup, internal_setup)) {
                    return false;
                }
            }

            // csrf queueing
            if (ajax_setup.psm_abort_success != true && callables.csrf_queue(ajax_setup, internal_setup)) {
                return false;
            }

            ajax_setup.psm_orig_data = ajax_setup.data;

            // first time ajax call, not yet retried
            if (ajax_setup.psm_has_main != true) {

                if (!ps_helper.is_absolute_url(ajax_setup.url)) {
                    ajax_setup.url = ps_gconfig.root_url + '/' + ajax_setup.url;
                }

                ajax_setup.psm_has_main = true;
                ajax_setup.dataFilter   = callables.ajax_dataFilter(ajax_setup.dataFilter, ajax_setup);
                ajax_setup.xhr          = callables.ajax_xhr(ajax_setup.xhr, ajax_setup);
                ajax_setup.precomplete  = callables.ajax_precomplete(ajax_setup.precomplete, ajax_setup);
                ajax_setup.fail         = callables.ajax_fail(ajax_setup.fail, ajax_setup);
                ajax_setup.retry        = callables.ajax_retry(ajax_setup.retry, ajax_setup, internal_setup);
                ajax_setup.success      = callables.ajax_success(ajax_setup.success, ajax_setup);
                ajax_setup.error        = callables.ajax_error(ajax_setup.error, ajax_setup);
                ajax_setup.progress     = callables.ajax_progress(ajax_setup.progress, ajax_setup);

                // from here onwards we will use psm_complete instead of complete
                // this will be manually triggered on ajax success, fail, anad error
                ajax_setup.psm_complete = callables.ajax_complete(ajax_setup.complete, ajax_setup);
                delete ajax_setup.complete;
            }

            $.ajax(ajax_setup);

        },

        /**
         * This will check if there's no ongoing request with csrf token
         * If there is, this will pending the current request until tken is available again
         * @param  object  ajax_setup     
         * @param  object  internal_setup 
         * @return boolean
         */
        csrf_queue: function(ajax_setup, internal_setup) {

            ajax_setup.psm_has_csrf = ($.isPlainObject(ajax_setup.data) &&  ajax_setup.data.hasOwnProperty('_token'));

            if (ajax_setup.psm_has_csrf) {

                ajax_setup.psm_queue_id = ajax_setup.psm_queue_id || ps_helper.uniqid();
                if (globals.csrf.ongoing == ajax_setup.psm_queue_id || globals.csrf.ongoing == null) {

                    // there's no other pending tokenized request
                    globals.csrf.ongoing = ajax_setup.psm_queue_id;

                    // let's put latest token to the request
                    ajax_setup.data        = ajax_setup.data || {};
                    ajax_setup.data._token = globals.csrf.token;

                    return false;

                } else {

                    // pending the request until token is released
                    globals.csrf.pending[ajax_setup.psm_queue_id] = [ajax_setup,internal_setup];

                    // we have to add queue array to maintain the request order
                    globals.csrf.queue.push(ajax_setup.psm_queue_id);

                    callables.debug('A request is pending: ' + ajax_setup.url);

                    return true;

                }

            } else {

                return false;

            }

        },

        /** 
         * This will release ongoing token and trigger the next ajax that was pending becuase of token
         * @return void
         */
        release_token: function(ajax_setup) {
            if (!ps_helper.empty(globals.csrf.pending)) {

                globals.csrf.ongoing = globals.csrf.queue[0];
                var next_request     = globals.csrf.pending[globals.csrf.ongoing];

                if (!ps_helper.empty(globals.csrf.pending[globals.csrf.ongoing])) {
                    delete globals.csrf.pending[globals.csrf.ongoing];
                }

                if (ps_helper.in_array(globals.csrf.ongoing, globals.csrf.queue)) {
                    globals.csrf.queue.splice(globals.csrf.queue.indexOf(globals.csrf.ongoing), 1);
                }

                if (!ps_helper.empty(next_request)) {
                    callables.ajax.apply(this, next_request);
                } 

            }  else {

                globals.csrf.ongoing = null;

            }
        },

        /**
         * This will update all model data according to header informations set on backend
         * @param  object jqXHR 
         * @return void
         */
        update_global_headers: function(jqXHR) {
            var ps_token = jqXHR.getResponseHeader('PS-Token');
            if (!ps_helper.empty(ps_token)) {
                globals.csrf.token = ps_token;
            }

            // update member status
            if (globals.store.store_exists('user')) {
                if (!ps_helper.empty(jqXHR.getResponseHeader('PS-Member-Status'))) {
                    globals.store.store_update('user', {
                        derived_status_id: jqXHR.getResponseHeader('PS-Member-Status')
                    });
                }

                if (!ps_helper.empty(jqXHR.getResponseHeader('PS-Member-Transactable'))) {
                    globals.store.store_update('user', {
                        derived_is_transactable: parseInt(jqXHR.getResponseHeader('PS-Member-Transactable'))
                    });
                }
            }
        },

        /**
         * Wrapper of $.ajax dataFilter
         * @param  function orig_dataFilter 
         * @param  object   ajax_setup    
         * @return function
         */
        ajax_dataFilter: function(orig_dataFilter, ajax_setup) {
            return function(data) {

                // replace shared directives with shared view_data
                var shared = callables.shared();

                if (shared) {
                    data = ps_helper.render_directives(shared, data);
                }

                if ($.isFunction(orig_dataFilter)) {
                    data = orig_dataFilter.apply(this, arguments);
                }
                
                return data;

            };
        },

        /**
         * Wrapper of $.ajax xhr
         * NOTE: progress event here will stop at 99%, because wwe'll trigger 100 at complete
         * @param  object orig_xhr 
         * @param  object ajax_setup
         * @return function                
         */
        ajax_xhr: function (orig_xhr, ajax_setup) {

            return function() {
                var callback_args = [ajax_setup];

                if ($.isFunction(orig_xhr)) {

                    var xhr = orig_xhr.apply(this, callback_args);

                } else {

                    var xhr = $.ajaxSettings.xhr(callback_args);

                }

                if ($.isFunction(ajax_setup.progress)) {

                    ajax_setup.psm_progress_percent = {download:0, upload: 0};

                    // For downloads
                    xhr.onprogress = function (e) {

                        if (e.lengthComputable) {
                            ajax_setup.psm_progress_percent.download = ps_helper.get_percentage(e.total,e.loaded);

                            var overall_progress = ps_helper.get_percent_average(ajax_setup.psm_progress_percent);
                            overall_progress     = (overall_progress>99) ? overall_progress-1 : overall_progress;

                            // event
                            ajax_setup.progress.call(this,overall_progress,ajax_setup);
                        }

                    }.bind(this);

                    // For uploads
                    xhr.upload.onprogress = function (e) {

                        if (e.lengthComputable) {
                            ajax_setup.psm_progress_percent.upload = ps_helper.get_percentage(e.total,e.loaded);
                            var overall_progress = ps_helper.get_percent_average(ajax_setup.psm_progress_percent);
                            overall_progress     = (overall_progress > 99) ? overall_progress-1 : overall_progress;

                            // event
                            ajax_setup.progress.call(this,overall_progress,ajax_setup);
                        }

                    }.bind(this);

                }

                return xhr;
            };

        },

        /**
         * Wrapper of progress custom callback
         * @param  function orig_progress 
         * @param  object   ajax_setup 
         * @return function
         */
        ajax_progress: function (orig_progress, ajax_setup) {

            return function(percent) {
                var callback_args = [percent, ajax_setup];

                if ($.isFunction(orig_progress)) {
                    orig_progress.apply(this, callback_args);
                }
                callables.trigger_subscribed_events('progress', this, callback_args);

            };

        },

        /**
         * Wrapper of precomplete custom callback
         * @param  function orig_precomplete 
         * @return function
         */
        ajax_precomplete: function (orig_precomplete, ajax_setup) {

            return function(jqXHR, status) {
                var callback_args = [jqXHR, status, ajax_setup];

                // update request token
                callables.update_global_headers(jqXHR);
                if ($.isFunction(orig_precomplete)) {
                    orig_precomplete.apply(this, callback_args);
                }
                callables.trigger_subscribed_events('precomplete', this, callback_args);

            };

        },

        /**
         * Wrapper of $.ajax success
         * @param  function orig_success 
         * @param  object   ajax_setup    
         * @return function
         */
        ajax_success: function (orig_success, ajax_setup) {
            return function(response, status, jqXHR) {
                var callback_args = [response, status, jqXHR, ajax_setup];

                if ($.isFunction(ajax_setup.precomplete)) {
                    ajax_setup.precomplete.call(this, jqXHR, status);
                }

                var false_positive = ($.isPlainObject(response) && response.result == false);

                if (false_positive) {

                    if ($.isFunction(ajax_setup.fail)) {
                        ajax_setup.fail.apply(this, callback_args);
                    } 

                } else {

                    if ($.isFunction(orig_success)) {
                        orig_success.apply(this, callback_args);
                    } 
                    callables.trigger_subscribed_events('success', this, callback_args);

                    if ($.isFunction(ajax_setup.progress)) {
                        ajax_setup.progress.call(this, 100);
                    }

                    if ($.isFunction(ajax_setup.psm_complete)) {
                        ajax_setup.psm_complete.call(this, jqXHR, status);
                    }

                }
            };
        },

        /**
         * Wrapper of fail custom callback
         * @param  function orig_fail 
         * @param  object   ajax_setup    
         * @return function
         */
        ajax_fail: function(orig_fail, ajax_setup) {

            return function(response, status, jqXHR) {
                var callback_args = [response, status, jqXHR, ajax_setup];

                switch (response.err_code) {

                    case '-4': 

                        // abort fail operation if still retrying
                        if (ajax_setup.retry.call(this)) {
                            return false;
                        }

                        break;

                }

                if ($.isFunction(orig_fail)) {
                    orig_fail.apply(this, callback_args);
                } 
                callables.trigger_subscribed_events('fail', this, callback_args);

                if ($.isFunction(ajax_setup.progress)) {
                    ajax_setup.progress.call(this, -1);
                }

                if ($.isFunction(ajax_setup.psm_complete)) {
                    ajax_setup.psm_complete.call(this, jqXHR, status);
                }

            };

        },

        /**
         * Wrapper for retry call
         * @param  function orig_retry      original retry callback 
         * @param  object   ajax_setup      our ajax_setup object
         * @param  object   internal_setup  our internal_setup object
         * @return function
         */
        ajax_retry: function(orig_retry, ajax_setup, internal_setup) {

            return function() {
                var callback_args = [ajax_setup];

                if (ps_helper.empty(ajax_setup.psm_retry)) {

                    ajax_setup.psm_retry = 1;

                } else {

                    ajax_setup.psm_retry++;

                }

                if (ajax_setup.psm_retry < globals.csrf.max_retry) {

                    if ($.isFunction(orig_retry)) {
                        orig_retry.apply(this, callback_args);
                    }

                    callables.trigger_subscribed_events('retry', this, callback_args);
                    callables.ajax(ajax_setup, internal_setup);

                    return true;

                } else {

                    return false;

                }

            };

        },

        /**
         * Wrapper of $.ajax error
         * @param  function orig_error 
         * @param  object   ajax_setup    
         * @return function
         */
        ajax_error: function (orig_error, ajax_setup) {

            return function(jqXHR, status, error) {
                var callback_args = [jqXHR, status, error, ajax_setup];

                if ($.isFunction(ajax_setup.precomplete)) {
                    ajax_setup.precomplete.call(this, jqXHR, status);
                }

                if ($.isFunction(orig_error)) {
                    orig_error.apply(this, callback_args);
                }
                callables.trigger_subscribed_events('error', this, callback_args);

                if ($.isFunction(ajax_setup.progress)) {
                    ajax_setup.progress.call(this, -1);
                }

                if ($.isFunction(ajax_setup.psm_complete)) {
                    ajax_setup.psm_complete.call(this, jqXHR, status);
                }
            };
        },

        /**
         * Wrapper of $.ajax complete
         * @param  function orig_complete 
         * @param  object   ajax_setup    
         * @return function
         */
        ajax_complete: function(orig_complete, ajax_setup) {

            return function(jqXHR, status) {
                var callback_args = [jqXHR, status, ajax_setup];

                if ($.isFunction(orig_complete)) {
                    orig_complete.apply(this, callback_args);
                }
                callables.trigger_subscribed_events('complete', this, callback_args);

                if (ajax_setup.psm_has_csrf) {
                    callables.release_token(ajax_setup);
                }

            };

        },

        /**
         * This will block a request if the same block.key ongoing request is detected
         * If block_options.pending_events is true blocked request callbacks will still be executed 
         * along with ongoing the request
         * @param  object   block_options { max_request, pending_events }
         * @param  object   ajax_setup    
         * @param  object   internal_setup    
         * @return boolean
         */
        block_ongoing: function(block_options, ajax_setup, internal_setup) {

            block_options                         = block_options || {};
            var request_key                       = internal_setup.request_key;
            globals.blocking.ongoing[request_key] = globals.blocking.ongoing[request_key] || {};
            var block_id                          = ajax_setup.psm_block_id || ps_helper.uniqid();

            var max_request   = block_options.max_request || globals.blocking.default_max;
            var ongoing_count = Object.keys(globals.blocking.ongoing[request_key]).length;

            // We also check if block_id has been added before
            // Some request might come back here because of retry feature and therefore shouldn't be blocked
            if (ongoing_count >= max_request && !globals.blocking.ongoing[request_key].hasOwnProperty(block_id)) {

                callables.debug(
                    request_key  + ' has been blocked because there are still ' + ongoing_count + ' ongoing requests.'
                );

                if ($.isFunction(block_options.fallback)) {
                    block_options.fallback();
                }

                // if set as pending, we will add it to pending callback queue
                if (block_options.pending_events) {

                    callables.debug(request_key +' has new pending callbacks.');
                    globals.blocking.pendings[request_key] = globals.blocking.pendings[request_key] || [];
                    globals.blocking.pendings[request_key].push(ajax_setup);

                }

                return true;

            } else {

                if (ajax_setup.psm_has_block != true) {

                    ajax_setup.psm_has_block = true;
                    ajax_setup.psm_block_id  = block_id;
                    globals.blocking.ongoing[request_key][block_id] = block_id;

                    // we use subscription callback events list as reference only on what events should be overriden
                    var complete_cb_key         = ps_helper.empty(ajax_setup.psm_complete) ? 'complete':'psm_complete';
                    var subscriptions_callbacks = Object.keys(globals.subscriptions);
                    subscriptions_callbacks.forEach(function(cb_name) {
                        var real_cb_name         = (cb_name=='complete') ? complete_cb_key : cb_name;
                        ajax_setup[real_cb_name] = (function(orig_callback, request_key, block_id, cb_name) {
                                                        return function() {
                                                            var args = arguments;

                                                            if ($.isFunction(orig_callback)) {
                                                                orig_callback.apply(this, args);
                                                            }

                                                            var pending_cbs = globals.blocking.pendings[request_key];
                                                            
                                                            if (!ps_helper.empty(pending_cbs)) {
                                                                pending_cbs.forEach(function(pending_cb) {
                                                                    if ($.isFunction(pending_cb[cb_name])) {
                                                                        var callback = pending_cb[cb_name];

                                                                        // avoid multiple execution
                                                                        delete pending_cb[cb_name];

                                                                        callback.apply(pending_cb, args);
                                                                    }
                                                                });
                                                            }

                                                            // delete pendings after
                                                            if (cb_name == 'complete') {
                                                                if (!ps_helper.empty(pending_cbs)) {
                                                                    delete globals.blocking.pendings[request_key];
                                                                }

                                                                delete globals.blocking.ongoing[request_key][block_id];
                                                            }
                                                        };

                                                }(ajax_setup[real_cb_name], request_key, block_id, cb_name));

                    });
                }

                return false;
            }

        },

        /**
         * This will add abort + success callback in beforeSend of ajax_setup
         * @param  object     ajax_setup   
         * @param  function   success_data 
         * @param  function   callback   
         * @return void
         */
        abort_success: function(ajax_setup, success_data, callback) {

            // prevent double binding
            if (ajax_setup.psm_abort_success != true) {

                ajax_setup.psm_abort_success = true;

                ajax_setup.beforeSend = (function(orig_beforeSend, ajax_setup, success_info) {
                                            return function(jqXHR, options) {
                                                if ($.isFunction(orig_beforeSend)) {
                                                    orig_beforeSend.apply(this, arguments);
                                                }

                                                ajax_setup.success.call(this, success_info.data, 200, jqXHR);

                                                jqXHR.abort();

                                                if ($.isFunction(success_info.callback)) {
                                                    success_info.callback.apply(this, ajax_setup);
                                                }
                                            };
                                        }(ajax_setup.beforeSend, ajax_setup, { data:success_data, callback:callback }));


            } else {

                // Disallowed, to prevent triggering success callback multiple times
                callables.debug('Multiple abort success has been detected, only the first one will be executed!');

            }

        },

        /**
         * This will trigger subscribed events to ps ajax
         * @param  string   event_name 
         * @param  mixed    context       
         * @param  array    params       
         * @return void
         */
        trigger_subscribed_events: function(event_name, context, params) {
            if ($.isPlainObject(globals.subscriptions[event_name])) {

                var ctr = 0;
                for (var event_id in globals.subscriptions[event_name]) {

                    (function(callback) {
                        setTimeout(function(){
                            callback.apply(context, params);
                        }, 0);
                    }(globals.subscriptions[event_name][event_id]));

                    ctr++;
                }

                if (ctr > 0) { 
                    callables.debug(ctr + ' ' + event_name + ' subcribed callbacks has been executed!'); 
                }

            } else {
                callables.debug('Unknown callback subscription '+ event_name);
            }
        },

        /**
         * This will update view_data store only if the store key is not yet existing
         * @param  object view_data
         * @return void
         */
        view_data_update: function (view_data) {
            // first time setup operations
            if (globals.view_data.is_served == false) {

                globals.view_data.is_served = true;

                globals.sharable.forEach(function(sharable) {
                    if (!view_data.hasOwnProperty(sharable)) {
                        callables.debug('ps_model.store['+sharable+'] global sharable data,'
                                        + ' is not in view_data.');
                    }
                });

                var shared = ps_helper.object_only(view_data, globals.sharable);
                view_data   = ps_helper.render_directives(shared, view_data);  
            }

            // update store
            for (var store_key in view_data) {

                if (!globals.store.store_exists(store_key)) {

                    // pre formatting
                    switch(store_key) {
                        case 'navigation': 
                            view_data[store_key] = callables.format_navigation(view_data[store_key], shared); 
                            break;
                    }

                    globals.store.store_update(store_key, view_data[store_key]);

                } else {

                    callables.debug('ps_model.store['+store_key+'] already exists, '
                                   +'ps_model.view_data_update will not reinitialize already existing store.');

                }
            }
        },

        /**
         * Get data that was fetched fron ps_model.init, or deffered from backend
         * @param  object callbacks
         * @param  array  items      (Optional)If null this will get all items 
         * @return object
         */
        view_data: function(ajax_setup, items) {
            ajax_setup = ajax_setup || {};

            // view_data ajax settings
            // view_data is not realy csrf protected in frontend
            // _token here is just to pending the request incase another view_data query is still running
            var orig_success = ajax_setup.success;
            ps_helper.assoc_merge(ajax_setup, {
                url     : 'view_data',
                method  : 'post',
                data    : { _token: globals.csrf.token, payload: globals.view_data.payload },
                success : function(response, status, jqXHR) {
                            callables.view_data_update(response);
                            if ($.isFunction(orig_success)) {
                                orig_success.call(this, globals.store.store_multi_fetch(items).existing, status, jqXHR);
                            }
                        }
            });

            // if not yet served check if its in global config
            if (!globals.view_data.is_served) {
                if ($.isPlainObject(ps_gconfig.view_data) && !ps_helper.empty(ps_gconfig.view_data)) {
                    callables.view_data_update(ps_gconfig.view_data);
                    callables.debug('view_data initial data was already set in global config.');
                }
            }

            // before send check if already been served
            var original_beforeSend = ajax_setup.beforeSend;
            ajax_setup.beforeSend   = function (jqXHR) {
                                        if (globals.view_data.is_served) {

                                            ajax_setup.beforeSend = original_beforeSend;
                                            jqXHR.abort();

                                            var store_items = globals.store.store_multi_fetch(items);

                                            if ($.isArray(store_items.missing) && store_items.missing.length > 0) {

                                                ajax_setup.data.items = JSON.stringify(store_items.missing);

                                            } else { 

                                                callables.abort_success(
                                                    ajax_setup, 
                                                    globals.store.store_multi_fetch(items).existing, 
                                                    function() {
                                                        callables.debug('view_data has been served already,'
                                                                       +' fetched from store.');
                                                    }
                                                );
                                                
                                            }
                                            
                                            callables.ajax(ajax_setup);
                                        }
                                    };

            callables.ajax(ajax_setup);
        },

        /**
         * This will update rso store if not yet existing
         * @return object
         */
        rso_store: function() {
            if (globals.rso.is_served == false) {
                globals.rso.is_served = true;
                if (parseInt(ps_helper.get_cookie('ps_is_rso'))) {
                    globals.store.store_update('rso', ps_gconfig.rso.urls.original);
                } else {
                    globals.store.store_update('rso', ps_gconfig.rso.urls.fallback);
                }

                // add self_hosted assets path
                globals.store.store_update('rso', 'self_hosted', ps_gconfig.rso.self_hosted);
            }

            return globals.store.store_fetch('rso');
        },

        /**
         * This will get rso from global config
         * @param  string   url      
         * @param  string   rso_path (Optional) default = assets
         * @return string
         */
        rso: function(url, rso_path) {
            rso_path = ps_helper.set_default(rso_path, 'assets');
            return callables.rso_store()[rso_path] + url;
        },


        /**
         * Get all existing sharable data from store
         * @return void
         */
        shared: function() {
            // make sure rso store is updated 
            callables.rso_store();
            
            var shared = globals.store.store_multi_fetch(globals.sharable);
            if (ps_helper.empty(shared.existing)) {
                callables.debug('No shared data found.');
            }

            return shared.existing;

        },

        /**
         * This will format our navigation data before inserting it to store
         * @param  object nav_data 
         * @param  object shared    Raw shared view_data object 
         * @return object
         */
        format_navigation: function(nav_data, shared) {

            // additional data
            ps_helper.assoc_merge(nav_data, {
                actives : { 
                            sidebar: null, 
                            menu: null, 
                            floating_sidebar: null, 
                            floating: null, 
                            page: null, 
                            external : null 
                        },
                hashes  : {},
                pages   : {}
            });

            var add_hash = function(hash, info, type) {
                            if (ps_helper.empty(nav_data.hashes[hash])) {
                                nav_data.hashes[hash] = info;
                                return true;
                            } else {
                                callables.debug(hash + ' has multiple config, only the first one will be set.'
                                                     + ' Add just_link to non-primary item to avoid wrong setting.');
                                callables.debug(info);
                                return false;
                            }
                        };
            // menu
            var has_unfloating = false;
            for (var menu_type in nav_data.menu) {
                var menu_list        = nav_data.menu[menu_type];
                var menu_list_length = menu_list.length;

                for (var i = 0; i < menu_list_length; i++) {
                    if (!menu_list[i].floating) {
                        has_unfloating = true;
                        break;
                    }
                }

                if (has_unfloating) {
                    break;
                }
            }

            var sidebar_menu_reference = {};
            for (var menu_type in nav_data.menu) {
                nav_data.menu[menu_type].forEach(function(menu) {
                    if(menu.external !== true) {
                        menu.floating = has_unfloating ? menu.floating : false;
                        menu.hash     = callables.menu_hash_pattern(menu);
                        menu.type     = menu.floating ? 'floating' : 'menu';

                        if (menu.type == 'menu') {
                            if (ps_helper.empty(nav_data.pages[menu.page])) {
                                nav_data.pages[menu.page] = { 
                                                                is_rendering    : true, 
                                                                menu_hashes     : [menu.hash],
                                                                active_main_hash:'' 
                                                            };
                            } else {
                                nav_data.pages[menu.page].menu_hashes.push(menu.hash);
                            }
                        } 

                        // menu hash map
                        if (menu.just_link != true && add_hash(menu.hash, menu)) {
                            sidebar_menu_reference[menu.productID] = menu.hash;
                        }
                    }
                });
            }

            // sidebar
            var sidebar_tree = function(sidebar, parents, menu_hash) {
                                    sidebar.active= sidebar.active||ps_helper.empty(sidebar.children)||sidebar.external;
                                    sidebar.text  = callables.sidebar_text(sidebar, shared);

                                    // add  to hash list tag as active sidebar
                                    if (sidebar.active) {
                                        var menu_hash_info = nav_data.hashes[menu_hash];

                                        sidebar.menu_hash = menu_hash;
                                        sidebar.parents   = parents;
                                        if (menu_hash_info.floating) {
                                            sidebar.type = 'floating sidebar';
                                        } else {
                                            sidebar.type = 'sidebar';
                                        }
                                        var sidebar_hash  = callables.sidebar_hash_pattern(sidebar);
                                        sidebar.hash      = sidebar_hash;
                                        add_hash(sidebar_hash, sidebar);
                                        menu_hash_info.sidebars = menu_hash_info.sidebars || [];
                                        menu_hash_info.sidebars.push(sidebar_hash);
                                    }

                                    if (!ps_helper.empty(sidebar.children)) {

                                        var child_parent = ps_helper.array_shallow_clone(parents);

                                        // parent infos that are needed by our sidebar childrens
                                        child_parent.push({ 
                                                            id      : sidebar.id, 
                                                            hash    : sidebar.hash
                                                        });
                                        
                                        sidebar.children.forEach(function(child) {
                                            sidebar_tree(child, child_parent, menu_hash);
                                        });
                                    }
                            };

            // sidebars
            for (var productID in nav_data.sidebars) {
                nav_data.sidebars[productID].forEach(function(sidebar) {
                    sidebar_tree(sidebar, [], sidebar_menu_reference[productID]);
                });
            }
            
            return nav_data;
        },

        /**
         * This will give the final text of a sidebar
         * @param  object sidebar
         * @param  object shared  Raw shared view_data object 
         * @return string
         */
        sidebar_text: function(sidebar, shared) {
            if (!ps_helper.empty(sidebar.text)) {
                return sidebar.text;
            } else if ($.isPlainObject(shared.lang) && (!ps_helper.empty(shared.lang.language[sidebar.id]))) {
                return shared.lang.language[sidebar.id];
            } else {
                return ps_helper.ucwords(ps_helper.replace_all(sidebar.id,'_',' '));
            }
        },

        /**
         * Menu hash pattern
         * @param  object menu
         * @return string
         */
        menu_hash_pattern: function(menu) {
            return '#'+ps_helper.snake_case(menu.id);
        },

        /**
         * Sidebar hash pattern
         * @param  object sidebar
         * @return string
         */
        sidebar_hash_pattern: function(sidebar) {
            var sidebar_hash = '#';

            if (!ps_helper.empty(sidebar.parents) && sidebar.id != sidebar.parents[0].id) {
                sidebar_hash += ps_helper.snake_case(sidebar.parents[0].id)+'_';
            } 

            return sidebar_hash + ps_helper.snake_case(sidebar.id);
        },

        /**
         * This will add formatted message to chat_message store, this will automatically add 'order' attibute also
         * @param object  message_object
         * @param boolean from_history
         * return void
         */
        add_message: function(message_object, from_history) {

            var chat_status = globals.store.store_fetch('chat_status');

            if ($.isPlainObject(message_object)) {

                var chat_status_update = {};

                // add message only if chatbox is already loaded
                if (!chat_status.is_loading) {
                    // check if send_id is not yet existing
                    if (message_object.hasOwnProperty('send_id')) {
                        if (!ps_helper.in_array(message_object.send_id, chat_status.send_ids)) {

                            globals.store.store_list_push('chat_status','send_ids', message_object.send_id);

                        } else {

                            globals.store.store_list_update('chat_messages', function(message) {

                                return (message.send_id == message_object.send_id);

                            }, message_object);

                            callables.debug('Chat with send_id '+message_object.send_id+' is updated!');
                            return;
                        }
                    }

                    var msg_list            = globals.store.store_fetch('chat_messages').list;
                    var msg_length          = msg_list.length;
                    message_object.order    = msg_length;
                    message_object.messages = ps_helper.nl2br(ps_helper.no_tag(message_object.messages));

                    if (!message_object.hasOwnProperty('dateTime')) {
                        // add temporary dateTime
                        message_object.dateTime = false;
                    }

                    // Prevent same date to be shown by disabling the old same date display
                    if (message_object.showDate) {

                        globals.store.store_list_update('chat_messages', function(message) {

                            return (message.showDate && message.displayDate == message_object.displayDate);

                        }, { showDate: false });

                    }

                    globals.store.store_list_push('chat_messages', message_object);
                }

                // update unread count
                if (!message_object.is_you && !from_history) {
                    chat_status_update.unread = chat_status.unread + 1;
                }

                globals.store.store_update('chat_status', chat_status_update);

            } else {

                callables.debug('ps_modal.add_message 1st argument should be an object.');

            }
        },

        /**
         * This will block chat sending for period of time
         * @param  int remaining 
         * @return void
         */
        chat_block_time: function(remaining) {
            var chat_status = globals.store.store_fetch('chat_status');

            if (chat_status.block_until === null) {
                // we add 1 second to what backend has given 
                // because it will still block the request at exact remaining time
                globals.store.store_update(
                    'chat_status',
                    'block_until',
                    ps_date.add_seconds(new Date(), (remaining + 1), globals.chat.block_time_format)
                ); 
            }
        },

        /**
         * This will block chat sending for period of time
         * @param  int remaining 
         * @return void
         */
        chat_block_message: function(send_id) {

            globals.store.store_list_update('chat_messages', function(value) {
                return value.send_id == send_id;
            }, { sending: false,  failed: true, blocked: true });

        },

        /**
         * This will check if sending message still blocked
         * @return boolean
         */
        chat_blocked_status: function() {
            var chat_status = globals.store.store_fetch('chat_status');
            if (chat_status.block_until===null) {

                return true;

            } else {

                var remaining   = ps_date.diff_date(
                                    chat_status.block_until, 
                                    ps_date.get_current_date(globals.chat.block_time_format), 
                                    ['seconds']
                                );

                if (parseFloat(remaining.seconds) > 0) {

                    return false;

                } else {

                    globals.store.store_update('chat_status','block_until',null); 

                    return true;
                }

            }
        },

        /**
         * Send message via ajax
         * @param  string message 
         * @param  string send_id
         * @return void
         */
        send_message_ajax: function(message, send_id) {
            if (callables.chat_blocked_status()) {

                callables.ajax({
                    url    : 'send_msg',
                    method : 'post',
                    data   : { _token: globals.csrf.token, msg: message, send_id: send_id },
                    success: function(response) {
                                globals.store.store_list_update('chat_messages', function(value) {
                                    return value.send_id == send_id;
                                }, { sending: false, failed: false, blocked: false });
                            },
                    fail   : function(response) {
                                if (response.err_code == 'ERR_00115') {

                                    callables.chat_block_time(response.remaining);
                                    callables.chat_block_message(send_id);

                                } else{

                                    globals.store.store_list_update('chat_messages', function(value) {
                                        return value.send_id == send_id;
                                    }, { sending: false,  failed: true });
                                }
                            },
                    error  : function() {
                                globals.store.store_list_update('chat_messages', function(value) {
                                    return value.send_id == send_id;
                                }, { sending: false, failed: true  });
                            }
                });  

            } else {

                callables.chat_block_message(send_id);

            }

        },

        /**
         * Updates unread messages of chatbox
         * @param  int unread 
         * @return void
         */
        update_unread_messages: function(unread) {
            unread = unread || 0;
            globals.store.store_update('chat_status', 'unread', unread);
        },

        /**
         * This will add new transfer type to transaction object list store
         * @param  string type     
         * @param  object transaction 
         * @return void
         */
        update_transactions: function(type, transaction) {
            if (globals.store.store_exists('transactions')) {

                if (ps_helper.empty(transaction.product)) {
                    transaction.product = type;
                }

                globals.store.store_list_unshift('transactions', type, transaction);
            }
        },

        /**
         * This will resend listed messages in array
         * @param  array message_array 
         * @return void
         */
        resend_message_array: function(message_array) {

            // resend
            message_array.forEach(function(message_object) {

                if (message_object.failed === true && message_object.sending === false) {

                    globals.store.store_list_update('chat_messages', function(value) {
                        return value.send_id == message_object.send_id;
                    }, { sending: true, failed: false });

                    callables.send_message_ajax(message_object.messages, message_object.send_id);

                }

            });

        },

        /**
         * This will update captcha image
         * @param  string image 
         * @return string       captcha store
         */
        update_captcha_image: function(image) {
            globals.store.store_update('captcha', { image: image });
            return globals.store.store_fetch('captcha');
        }
    };


    /**
     |------------------------------------------------------------------------------------------------------------------
     | Exports
     |------------------------------------------------------------------------------------------------------------------
     */
    return {
        view_data              : callables.view_data,
        shared                 : callables.shared,      
        rso                    : callables.rso,
        add_message            : callables.add_message,   
        update_unread_messages : callables.update_unread_messages, 
        update_transactions    : callables.update_transactions,  
        update_captcha_image   : callables.update_captcha_image,  
        chat_block_time        : globals.chat.block_time_format,  

        /**
         * This will add event subcription
         * @param  string   event_name   
         * @param  function event_function
         * @param  string   event_id
         * @return void
         */
        subscribe: function(event_name, event_function, event_id) {
            if (globals.subscriptions.hasOwnProperty(event_name)) {

                if ($.isFunction(event_function)) {

                    if (ps_helper.empty(event_id)) {
                        event_id = ps_helper.uniqid();
                    }

                    if ($.isFunction(globals.subscriptions[event_name][event_id])) {
                        callables.debug(event_name + ' event subscription with id '+event_id+ ' will be overwritten!');
                    }

                    globals.subscriptions[event_name][event_id] = event_function;

                } else {

                    throw 'ps_model.subscribe 2nd argument must be a function';

                }

            } else {

                callables.debug('Unknown callback subscription '+ event_name);

            }
        },

        /**
         * This will delete an event subscription
         * @param  string   event_name   
         * @param  string   event_id
         * @return void
         */
        unsubscribe: function(event_name, event_id) {
            delete globals.subscriptions[event_name][event_id];
        },

        /**
         * This will get rso css file and pass it to success call
         * @param  string   file   
         * @return void
         */
        rso_css: function(file) {
            return callables.rso(ps_gconfig.rso.script_paths['css'] + file);
        },      

        /**
         * This will get js file rso and pass it to success call
         * @param  string   file   
         * @return void
         */
        rso_js: function(file) {
            return callables.rso(ps_gconfig.rso.script_paths['js'] + file);
        },

        /**
         * This will get balances from backend
         * @param  object options 
         * @return 
         */
        get_balance: function(ajax_setup) {
            ajax_setup = ajax_setup || {};

            ps_helper.assoc_merge(ajax_setup, {
                url     : 'get',
                method  : 'post',
                data    : { _token: globals.csrf.token, g: 'balance' },

                // wrap success
                success : (function(orig_success) {
                            return function(response, status, jqXHR) {

                                globals.store.store_multi_update({
                                    user       : ps_helper.object_only(response,['availableBalance','playableBalance']),
                                    usedBalance: response.usedBalance
                                });

                                if ($.isFunction(orig_success)) {
                                    orig_success.call(
                                        this, 
                                        globals.store.store_multi_fetch(['user','usedBalance']).existing,
                                        status,
                                        jqXHR
                                    );
                                }
                            };

                        }(ajax_setup.success))
            });

            callables.ajax(ajax_setup,{ request_key:'get_balance', block:{ pending_events: true }});
        },

        /**
         * Update current active menu in our store
         * @param  string hash
         * @return void
         */
        update_nav_menu: function(hash) {
            globals.store.store_update('navigation', 'actives.menu', hash);
        },

        /**
         * Update current active floating in our store
         * @param  string hash
         * @return void
         */
        update_nav_floating: function(hash) {
            globals.store.store_update('navigation', 'actives.floating', hash);
        },

        /**
         * Update current active sidebar in our store
         * @param  string hash
         * @return void
         */
        update_nav_sidebar: function(hash) {
            globals.store.store_update('navigation', 'actives.sidebar', hash);
        },

        /**
         * Update current active floating sidebar in our store
         * @param  string hash
         * @return void
         */
        update_floating_sidebar: function(hash) {
            globals.store.store_update('navigation', 'actives.floating_sidebar', hash);
        },

        /**
         * Update current active page in our store
         * @param  string page
         * @return void
         */
        update_nav_page: function(page) {
            globals.store.store_update('navigation', 'actives.page', page);
        },

        /**
         * This will update page status to rendered
         * @param  string page
         * @return void
         */
        update_page_rendered: function(page) {
            globals.store.store_update('navigation', 'pages.' + page + '.is_rendering', false);
        },

        /**
         * This will update page status to rendered
         * @param  string page
         * @return void
         */
        update_main_hash: function(page, hash) {
            globals.store.store_update('navigation', 'pages.' + page + '.active_main_hash', hash);
        },

        /**
         * This will get current active sidebar hash if there is, else get current active menu hash
         * @return string
         */
        active_main_hash: function() {
            var navigation_store = globals.store.store_fetch('navigation');
            if (ps_helper.empty(navigation_store.actives.sidebar)) {
                return navigation_store.actives.menu;
            } else {
                return navigation_store.actives.sidebar;
            }
        },

        /**
         * This will get the first sidebar if menu have sidebar else return the menu only itself
         * @return string
         */
        hash_final_form : function(hash) {
            var navigation_store = globals.store.store_fetch('navigation');
            var hash_info        = navigation_store.hashes[hash];

            if (hash_info.type === 'menu') {
                if (hash_info.first_sidebar!==false && $.isArray(hash_info.sidebars) && hash_info.sidebars.length > 0) {
                    return hash_info.sidebars[0];
                } else {
                    return hash_info.hash;
                }
            } else {
                return hash;
            }
        },

        /**
         * This will check if two hashes is related
         * @param  string  first_hash 
         * @param  string  second_hash 
         * @return boolean
         */
        is_hash_related: function(first_hash, second_hash) {
            var navigation_store = globals.store.store_fetch('navigation');
            var hash_info        = navigation_store.hashes[first_hash];
            
            if (first_hash === second_hash) {
                return true;
            }

            if ($.isArray(hash_info.sidebars)) {
                if (ps_helper.in_array(second_hash, hash_info.sidebars)) {
                    return true;
                }
            }

            if (!ps_helper.empty(hash_info.menu_hash)) {
                if (hash_info.menu_hash == second_hash) {
                    return true;
                }
            }

            if ($.isArray(hash_info.parents)) {
                var parents_length = hash_info.parents.length;

                for (var i = 0; i < parents_length; i++) {
                    if (hash_info.parents[i].hash === second_hash) {
                        return true;
                    }
                }
            }

            return false;
        },

        /** 
         * This will get data for our plugins
         * @param  object   ajax_setup 
         * @param  string   item      
         * @return void
         */
        plugin: function(ajax_setup, item) {
            ajax_setup = ajax_setup || {};

            var orig_success = ajax_setup.success;

            ps_helper.assoc_merge(ajax_setup, {
                url     : 'plugin',
                method  : 'post'  ,
                success : function(response, status, jqXHR) {

                            if (globals.plugin.is_served == false) {
                                globals.plugin.is_served = true;

                                for (var store_key in response.content) {
                                    globals.store.store_update(store_key, response.content[store_key]);
                                }
                            }

                            if ($.isFunction(orig_success)) {
                                orig_success.call(this, globals.store.store_fetch(item), status, jqXHR);
                            }
                        }    
            });

            if (globals.plugin.is_served) {

                callables.abort_success(ajax_setup, globals.store.store_multi_fetch(item).existing, function() {
                    callables.debug('plugin has been served already, fetched from store.');
                });

            }

            callables.ajax(ajax_setup,{ request_key:'plugin', block:{ pending_events: true }});
        },

        /**
         * This is just a wrapper of update_transactions but specific for winners type only
         * @param  string type     
         * @param  object transaction 
         * @return void
         */
        update_winners   : function(type, transaction) {
            if (globals.store.store_exists('transactions')) {
                var cur_transactions = globals.store.store_fetch('transactions');
                
                if ($.isArray(cur_transactions[type]) && cur_transactions[type].length > 0) {
                    transaction.product = cur_transactions[type][0].product;
                }

                callables.update_transactions(type, transaction);
            }
        },

        /**
         * This will get item from news store
         * @param  int    index 
         * @return object
         */
        get_news: function(index) {
            var news = globals.store.store_fetch('news');

            if ($.isPlainObject(news) && !ps_helper.empty(news.list)) {
                return news.list[index];
            } 
        },

        /**
         * This will verify parent status and chatStatus
         * @return void
         */
        verify_parent: function(ajax_setup) {
            // initial chat status
            if (!globals.store.store_exists('chat_status')) {
                globals.store.store_update('chat_status', {
                    is_loading : true,
                    has_message: true,
                    block_until: null,
                    unread     : 0,
                    date       : '',
                    send_ids   : []
                });
            }

            // get history from backend]
            ajax_setup       = ajax_setup || {};
            var orig_success = ajax_setup.success;

            ps_helper.assoc_merge(ajax_setup, {
                url     : 'verify_parent',
                method  : 'post',
                success : function(response, status, jqXHR) {
                            globals.store.store_update('chat_status', response);

                            if ($.isFunction(orig_success)) {
                                orig_success.call(this, globals.store.store_fetch('chat_status'), status, jqXHR);
                            }
                        }    
            });

            callables.ajax(ajax_setup, { request_key:'verify_parent', block:{ pending_events: true }});
        },

        /**
         * This will fetch chat history via ajax
         * @param  object   ajax_setup 
         * @return void
         */
        chat_history: function(ajax_setup) {
            ajax_setup       = ajax_setup || {};
            var orig_success = ajax_setup.success;
            var last_chatID  = null;

            if (globals.store.store_exists('chat_status')) {
                var chat_status = globals.store.store_fetch('chat_status');
                last_chatID     = chat_status.last;
                if (!chat_status.has_message) {
                    callables.debug('ps_model.chat_history will not proceed, all messages has been fetched.');
                    return false;
                }

                globals.store.store_update('chat_status', 'is_loading', true);
            }

            ps_helper.assoc_merge(ajax_setup, { 
                url    : 'chat_history',
                method : 'post',
                data   : { 
                            _token     : globals.csrf.token,
                            last_chatID: last_chatID
                        },
                success: function(response, status, jqXHR) {
                            response.msg       = response.msg || [];
                            var message_length = response.msg.length;

                            globals.store.store_update('chat_status'  , ps_helper.object_except(response,'msg'));
                            globals.store.store_update('chat_status'  , 'is_loading', false);

                            if (!globals.store.store_exists('chat_messages')) {
                                globals.store.store_update('chat_messages', []);
                            }

                            // backend returns messages in descending order 
                            // we need to add from last item
                            for (var i = message_length - 1; i >= 0; i--) {
                                callables.add_message(response.msg[i], true);
                            }

                            if ($.isFunction(orig_success)) {
                                orig_success.call(this, globals.store.store_fetch('chat_messages'),status,jqXHR);
                            }
                        }
            });

            callables.ajax(ajax_setup, { request_key:'chat_history', block:{} });
        },

        /**
         * This will send chat message
         * @param  string message 
         * @return boolean
         */
        send_message: function(message) {
            if (!ps_helper.empty(message.trim()) && $.type(message) === 'string') {
                callables.view_data({
                    success: function(view_data) {
                                // add to message object
                                var message_object = {  
                                                        send_id  : ps_helper.uniqid(),
                                                        failed   : false,
                                                        blocked  : false,
                                                        sending  : true,
                                                        messages : message,
                                                        sender   : view_data.user.username,
                                                        reciever : view_data.user.parent.username,
                                                        is_you   : true
                                                    };

                                callables.add_message(message_object);
                                callables.send_message_ajax(message, message_object.send_id);
                            }
                }, ['user']);

                return true;

            } else {

                return false;

            }
        },

        /**
         * This retry to send message again
         * @param  string send_id
         * @return void
         */
        retry_message: function(send_id) {

            var message_array = globals.store.store_list_fetch('chat_messages', function(value) {
                                    return value.send_id == send_id;
                                });

            if (!ps_helper.empty(message_array) && $.isArray(message_array)) {

                callables.resend_message_array(message_array);

            } else {

                callables.debug("ps_model.retry_message Couldn't find mesage with send_id " + send_id);

            }

        },

        /**
         * This will seen chat messages
         * @return void
         */
        seen_message: function(ajax_setup) {
            ajax_setup = ajax_setup || {};

            callables.update_unread_messages(0);

            ps_helper.assoc_merge(ajax_setup, { 
                url    : 'seen',
                method : 'post',
                data   : { _token: globals.csrf.token }
            });

            callables.ajax(ajax_setup, { request_key:'seen_message', block:{} });
        },

        /**
         * validate WS connection
         * @return void
         */ 
        validate_ws: function(socket_id, ajax_setup) {


            ajax_setup = ajax_setup || {};

            ps_helper.assoc_merge(ajax_setup, { 
                url    : 'validate_ws',
                method : 'post',
                data   : { socket_id: socket_id, payload: globals.view_data.payload }
            });

            callables.ajax(ajax_setup, { request_key:'validate_ws', block:{} });
        },

        /**
         * Updates online and offline chat status
         * @param  string status
         * @return void
         */
        update_chat_status: function(message) {
            
            globals.store.store_update('chat_status',message);
        },

        /**
         * Updates hide and show chat visibility
         * @param  string visibility
         * @return void
         */
        update_chat_visibility: function(visibility) {
            globals.store.store_update('chat_status', 'chatStatus', visibility);
        },

        /**
         * This will update online chat operational
         * @param  object online_list 
         * @return void
         */
        update_chat_operational: function(online_list) {
            var online_list = JSON.parse(online_list);

            globals.store.store_list_update('chat operational', function() {
                return true
            }, { status: false });
            globals.store.store_list_update('chat operational', function(value) {
                if ($.isArray(online_list[value.application])) {
                    return ps_helper.in_array(value.whiteLabelChatAppID, online_list[value.application]);
                } else {
                    return false;
                }
            }, { status: true });
        },

        /**
         * Get 'lang' store from model
         * @return object
         */
        lang_store: function() {
            return globals.store.store_fetch('lang');
        },

       /**
        * Login user
        * @param  object form_data  
        * @param  string window_id  
        * @param  object ajax_setup 
        * @return void
        */
        login: function(form_data, window_id, ajax_setup) {
            ajax_setup = ajax_setup || {};
            form_data  = form_data  || {};
            
            form_data.window_id  = window_id;
            var original_success = ajax_setup.success;
            ps_helper.assoc_merge(ajax_setup, { 
                url    : 'authenticate',
                method : 'post',
                data   : form_data,
                success: function() {
                            ps_localstorage.remove_all();

                            if ($.isFunction(original_success)) {
                                original_success.apply(this, arguments);
                            }
                        }
            });
            
            callables.ajax(ajax_setup, { request_key:'login', block:{} });
        },

        /**
         * Logout user
         * @return void
         */
        logout: function(ajax_setup) {
            ajax_setup = ajax_setup || {};

            callables.view_data({
                success: function(response) {
                            if (response.user.is_auth) {
                                var active_page  = response.navigation.actives.page;
                                var lastPage    = 0;

                                var primary_menu = response.navigation.menu.primary
                                if ($.isArray(primary_menu)) {
                                    var primary_menu_length = primary_menu.length;
                                    for (var i = 0; i < primary_menu_length; i++) {
                                        if (primary_menu[i].page === active_page) {
                                            lastPage = i;
                                            break;
                                        }
                                    }
                                }

                                var original_success = ajax_setup.success;
                                ps_helper.assoc_merge(ajax_setup, { 
                                    url    : 'logout',
                                    method : 'post',
                                    data   : { lastPage: lastPage },
                                    success: function() {
                                                ps_localstorage.remove_all();

                                                if ($.isFunction(original_success)) {
                                                    original_success.apply(this, arguments);
                                                }
                                            }
                                });

                                callables.ajax(ajax_setup, { request_key:'logout', block:{} });

                            } else {
                                callables.debug('ps_model.logout is not allowed.');
                            }
                        }   
            }, ['user','navigation']);
        },

        /**
         * get new captcha
         * @param  object ajax_setup
         * @return void
         */
        get_captcha: function(ajax_setup) {
            ajax_setup = ajax_setup || {};

            var orig_success = ajax_setup.success;
            ps_helper.assoc_merge(ajax_setup, { 
                url    : 'v_get',
                method : 'post',
                data   : { g: 'captcha', _token: globals.csrf.token },
                success: function(response, status, jqXHR) {
                            var captcha_store = callables.update_captcha_image(response.path);

                            if ($.isFunction(orig_success)) {
                                orig_success.call(this, captcha_store, status, jqXHR);
                            }
                        }
            });

            callables.ajax(ajax_setup, { request_key:'get_captcha', block:{} });
        },

        /**
         * Submit login captcha
         * @param  object form_data 
         * @param  object ajax_setup 
         * @return void
         */
        login_captcha_submit: function(form_data, ajax_setup) {
            form_data  = form_data  || {};
            ajax_setup = ajax_setup || {};

            ps_helper.assoc_merge(ajax_setup, { 
                url    : 'process',
                method : 'post',
                data   : ps_helper.assoc_merge(form_data, {
                            'ps_form-process': 'CAPTCHA',
                            _token: globals.csrf.token
                        })
            });

            callables.ajax(ajax_setup, { request_key:'login_captcha_submit', block:{} });
        },

        /**
         * This will resend email verification to recently failed login acount
         * @return void
         */
        resend_verification_email: function() {
            callables.ajax({
                url    : 'resend_email',
                method : 'post',
                data   :  {_token: globals.csrf.token }
            }, { request_key:'resend_verification_email', block:{} });
        },

        /**
         * This will get security question of a given credential
         * @param  object form_data  
         * @param  object ajax_setup 
         * @return void
         */
        get_securityQuestion: function(form_data, ajax_setup) {
            form_data  = form_data  || {};
            ajax_setup = ajax_setup || {};

            ps_helper.assoc_merge(ajax_setup, { 
                url    : 'v_get',
                method : 'post',
                data   : ps_helper.assoc_merge(form_data, {g: 'security_question',_token: globals.csrf.token})
            });


            callables.ajax(ajax_setup, { request_key:'get_securityQuestion', block:{} });
        },

        /**
         * This will submit forgot password form
         * @param  object form_data  
         * @param  object ajax_setup 
         * @return void
         */
        forgot_password_submit: function(form_data, ajax_setup) {
            form_data  = form_data  || {};
            ajax_setup = ajax_setup || {};

            ps_helper.assoc_merge(ajax_setup, { 
                url    : 'process',
                method : 'post',
                data   : ps_helper.assoc_merge(form_data, {
                            'ps_form-process': 'LOST_PASSWORD',
                            _token: globals.csrf.token
                        })
            });

            callables.ajax(ajax_setup, { request_key:'forgot_password_submit', block:{} });
        },

        /**
         * This will submit register form
         * @param  object form_data  
         * @param  object ajax_setup 
         * @return void
         */
        register: function(form_data, ajax_setup) {
            form_data  = form_data  || {};
            ajax_setup = ajax_setup || {};

            ps_helper.assoc_merge(ajax_setup, { 
                url    : 'process',
                method : 'post',
                data   : ps_helper.assoc_merge(form_data, {
                            'ps_form-process': 'PLAYER_REGISTRATION',
                            _token: globals.csrf.token
                        })
            });

            callables.ajax(ajax_setup, { request_key:'register', block:{} });
        },

        /**
         * This will get avatars data 
         * @param  object ajax_setup
         * @return void
         */
        avatars: function(ajax_setup) {
            ajax_setup = ajax_setup || {};

            var orig_success = ajax_setup.success;
            ps_helper.assoc_merge(ajax_setup, { 
                url    : 'avatars',
                method : 'post',
                success: function(response, status, jqXHR) {
                            globals.store.store_update('avatars', response);

                            if ($.isFunction(orig_success)) {
                                orig_success.call(this, globals.store.store_fetch('avatars'), status, jqXHR);
                            }
                        }
            });

            callables.ajax(ajax_setup, { request_key:'avatars', block:{} });
        },

        /**
         * This will upload avatar for specified imgOrder
         * @param  int    imgOrder  
         * @param  string base64    
         * @param  object ajax_setup
         * @return void
         */
        avatar_upload: function(imgOrder, base64, ajax_setup) {
            ajax_setup    = ajax_setup || {};
            var blob      = ps_helper.base64_to_blob(base64, 'image/png');
            var form_data = ps_helper.form_data({
                                _token       : globals.csrf.token,
                                ps_img_order : imgOrder,
                                ps_img_base64: blob
                            });

            var orig_success = ajax_setup.success;
            ps_helper.assoc_merge(ajax_setup, { 
                url        : 'up_av',
                method     : 'post',
                contentType: false,
                processData: false,
                data       : form_data,
                success: function(response, status, jqXHR) {
                            globals.store.store_list_update('avatars', function(avatar) {
                                return parseInt(avatar.imgOrder) == parseInt(response.imgOrder);
                            },{ filename:response.filename, status:2 });

                            if ($.isFunction(orig_success)) {
                                orig_success.apply(this, arguments);
                            }
                        }
            });

            callables.ajax(ajax_setup, { request_key:'avatar_upload', block:{} });
        },

        /**
         * This will set imgOrder as primary avatar
         * @param  int    imgOrder 
         * @param  object ajax_setup
         * @return void
         */
        avatar_set_primary: function(imgOrder,ajax_setup) {
            ajax_setup = ajax_setup || {};

            var orig_success = ajax_setup.success;
            ps_helper.assoc_merge(ajax_setup, { 
                url        : 'set_av',
                method     : 'post',
                data       : { _token: globals.csrf.token, imgOrder:imgOrder },
                success: function(response, status, jqXHR) {
                            globals.store.store_list_update('avatars', function(avatar) {
                                return parseInt(avatar.isActive) == 1;
                            },{ isActive:0 });

                            globals.store.store_list_update('avatars', function(avatar) {
                                return parseInt(avatar.imgOrder) == imgOrder;
                            },{ isActive:1 });

                            var set_filename =  globals.store.store_list_fetch('avatars', function(avatar) {
                                                    return parseInt(avatar.imgOrder) == imgOrder;
                                                })[0].filename;

                            globals.store.store_update('user', 'avatar_filename', set_filename);


                            if ($.isFunction(orig_success)) {
                                orig_success.apply(this, arguments);
                            }
                        }
            });

            callables.ajax(ajax_setup, { request_key:'avatar_set_primary', block:{} });
        },


        /**
         * This will upload avatar for specified imgOrder
         * @param  int    displayName  
         * @param  object ajax_setup
         * @return void
         */
        validate_displayName: function(displayName, ajax_setup) {
            ajax_setup    = ajax_setup || {};

            ps_helper.assoc_merge(ajax_setup, { 
                url        : 'validate',
                method     : 'post',
                data       : { field:'ps_change_display_name', value: displayName }
            });

            callables.ajax(ajax_setup, { request_key:'validate_displayName', block:{} });
        },

        /**
         * This will trigger system to generate random displayName
         * @param  object ajax_setup
         * @return void
         */
        generate_displayName: function(ajax_setup) {
            ajax_setup    = ajax_setup || {};
            
            if (globals.store.store_fetch('user').displayNameStatus == 0) {
                var orig_success = ajax_setup.success;

                ps_helper.assoc_merge(ajax_setup, { 
                    url     : 'process',
                    method  : 'post',
                    data    : {  _token: globals.csrf.token, 'ps_form-process':'GENERATE_DISPLAY_NAME' },
                    success : function(response) {
                                globals.store.store_update('user',{
                                    displayName      : response.displayName,
                                    displayNameStatus: 2
                                });

                                if ($.isFunction(orig_success)) {
                                    orig_success.call(this, arguments);
                                }
                            }    
                });

                callables.ajax(ajax_setup, { request_key:'generate_displayName', block:{} });
            } else {
                callables.debug('Cannot generate displayName.')
            }
        },

        /**
         * This will change user displayName
         * @param  object form_data 
         * @param  object ajax_setup 
         * @return void
         */
        change_displayName: function(form_data, ajax_setup) {
            form_data  = form_data  || {};
            ajax_setup = ajax_setup || {};

            if (globals.store.store_fetch('user').displayNameStatus != 1) {

                var orig_success = ajax_setup.success;
                ps_helper.assoc_merge(ajax_setup, { 
                    url    : 'process',
                    method : 'post',
                    data   : ps_helper.assoc_merge(form_data, {
                                'ps_form-process': 'ps_change_display_name',
                                _token: globals.csrf.token
                            }),
                    success : function(response) {
                                globals.store.store_update('user',{
                                    displayName      : response.displayName,
                                    displayNameStatus: 1
                                });

                                if ($.isFunction(orig_success)) {
                                    orig_success.call(this, arguments);
                                }
                            }    
                });

                callables.ajax(ajax_setup, { request_key:'change_displayName', block:{} });
            }
        },

        /**
         * This will submit register friend form
         * @param  object form_data  
         * @param  object ajax_setup 
         * @return void
         */
        register_friend: function(form_data, ajax_setup) {
            form_data  = form_data  || {};
            ajax_setup = ajax_setup || {};

            ps_helper.assoc_merge(ajax_setup, { 
                url    : 'process',
                method : 'post',
                data   : ps_helper.assoc_merge(form_data, {
                            'ps_form-process': 'REGISTER_FRIEND',
                            _token: globals.csrf.token
                        })
            });

            callables.ajax(ajax_setup, { request_key:'register_friend', block:{} });
        },

        /**
         * This will submit deposit confirmation form
         * @param  object form_data  
         * @param  object ajax_setup 
         * @return void
         */
        deposit_confirmation: function(form_data, ajax_setup) {
            form_data  = form_data  || {};
            ajax_setup = ajax_setup || {};

            var orig_success = ajax_setup.success;
            var orig_fail    = ajax_setup.fail;
            ps_helper.assoc_merge(ajax_setup, { 
                url    : 'process',
                method : 'post',
                data   : ps_helper.assoc_merge(form_data, {
                            'ps_form-process': 'DEPOSIT_CONFIRMATION',
                            _token: globals.csrf.token
                        }),
                success: function(response) {
                            if (response.hasOwnProperty('has_captcha')) {
                                globals.store.store_update(
                                    'account',
                                    'deposit_has_captcha', 
                                    response.has_captcha
                                );
                            }

                            if ($.isFunction(orig_success)) {
                                orig_success.apply(this, arguments);
                            }
                        },
                fail    : function(response) {
                            if (response.hasOwnProperty('has_captcha')) {
                                globals.store.store_update(
                                    'account',
                                    'deposit_has_captcha', 
                                    response.has_captcha
                                );
                            }

                            if ($.isFunction(orig_fail)) {
                                orig_success.fail(this, arguments);
                            }
                        }
            });

            callables.ajax(ajax_setup, { request_key:'deposit_confirmation', block:{} });
        },

        /**
         * This will submit withdrawal request form
         * @param  object form_data  
         * @param  object ajax_setup 
         * @return void
         */
        withdrawal_request: function(form_data, ajax_setup) {
            form_data  = form_data  || {};
            ajax_setup = ajax_setup || {};

            var orig_success = ajax_setup.success;
            var orig_fail    = ajax_setup.fail;
            ps_helper.assoc_merge(ajax_setup, { 
                url    : 'process',
                method : 'post',
                data   : ps_helper.assoc_merge(form_data, {
                            'ps_form-process': 'WITHDRAWAL_REQUEST',
                            _token: globals.csrf.token
                        }),
                success: function(response) {
                            globals.store.store_update('user',{
                                availableBalance: response._ab,
                            });

                            if (response.hasOwnProperty('has_captcha')) {
                                globals.store.store_update(
                                    'account',
                                    'withdrawal_has_captcha', 
                                    response.has_captcha
                                );
                            }

                            if ($.isFunction(orig_success)) {
                                orig_success.apply(this, arguments);
                            }
                        },
                fail    : function(response) {
                            if (response.hasOwnProperty('has_captcha')) {
                                globals.store.store_update(
                                    'account',
                                    'withdrawal_has_captcha', 
                                    response.has_captcha
                                );
                            }

                            if ($.isFunction(orig_fail)) {
                                orig_success.fail(this, arguments);
                            }
                        }
            });

            callables.ajax(ajax_setup, { request_key:'withdrawal_request', block:{} });
        },

        /**
         * This will submit change password form
         * @param  object form_data  
         * @param  object ajax_setup 
         * @return void
         */
        change_password: function(form_data, ajax_setup) {
            form_data  = form_data  || {};
            ajax_setup = ajax_setup || {};

            ps_helper.assoc_merge(ajax_setup, { 
                url    : 'process',
                method : 'post',
                data   : ps_helper.assoc_merge(form_data, {
                            'ps_form-process': 'CHANGE_PASSWORD',
                            _token: globals.csrf.token
                        })
            });

            callables.ajax(ajax_setup, { request_key:'change_password', block:{} });
        },

        /**
         * This will get api balances from backend
         * @param  object        options 
         * @param  array/string  companies 
         * @return 
         */
        get_api_balance: function(ajax_setup, companies) {
            ajax_setup = ajax_setup || {};

            var data = { _token: globals.csrf.token, g: 'api_balance' };
            if (!ps_helper.empty(companies)) {
                data.company = companies;
            }

            ps_helper.assoc_merge(ajax_setup, {
                url     : 'get',
                method  : 'post',
                data    : data,

                // wrap success
                success : (function(orig_success) {
                            return function(response, status, jqXHR) {
                                globals.store.store_update('api_balance', response.balance);
                                globals.store.store_update('user', 'availableBalance', response.availableBalance);

                                if ($.isFunction(orig_success)) {
                                    orig_success.call(
                                        this, 
                                        globals.store.store_multi_fetch('api_balance').existing,
                                        status,
                                        jqXHR
                                    );
                                }
                            };

                        }(ajax_setup.success))
            });

            callables.ajax(ajax_setup,{ request_key:'get_api_balance', block:{ pending_events: true }});
        },

        /**
         * This will submit fund transfer form
         * @param  object form_data  
         * @param  object ajax_setup 
         * @return void
         */
        fund_transfer: function(form_data, ajax_setup) {
            form_data  = form_data  || {};
            ajax_setup = ajax_setup || {};

            var orig_success = ajax_setup.success;
            var orig_fail    = ajax_setup.fail;
            ps_helper.assoc_merge(ajax_setup, { 
                url    : 'process',
                method : 'post',
                data   : ps_helper.assoc_merge(form_data, {
                            'ps_form-process': 'FUND_TRANSFER',
                            _token: globals.csrf.token
                        }),
                success: function(response) {

                            if (response.hasOwnProperty('has_captcha')) {
                                globals.store.store_update(
                                    'account',
                                    'fund_has_captcha', 
                                    response.has_captcha
                                );
                            }

                            if ($.isFunction(orig_success)) {
                                orig_success.apply(this, arguments);
                            }
                        },
                fail    : function(response) {
                            if (response.hasOwnProperty('has_captcha')) {
                                globals.store.store_update(
                                    'account',
                                    'fund_has_captcha', 
                                    response.has_captcha
                                );
                            }

                            if ($.isFunction(orig_fail)) {
                                orig_success.fail(this, arguments);
                            }
                        }
            });

            callables.ajax(ajax_setup, { request_key:'fund_transfer', block:{} });
        },

        /**
         * This will get statement data
         * @param  int    month_number 
         * @param  object ajax_setup   
         * @return void
         */
        get_statement: function(month_number, ajax_setup) {
            ajax_setup = ajax_setup || {};

            var orig_success = ajax_setup.success;
            ps_helper.assoc_merge(ajax_setup, { 
                url    : 'get',
                method : 'post',
                data   : { g:'statement',no:month_number,_token: globals.csrf.token },
                success: function(response,status,jqXHR) {
                            globals.store.store_update('statement', response);

                            if ($.isFunction(orig_success)) {
                                orig_success.call(this,globals.store.store_fetch('statement'),status,jqXHR);
                            }
                        } 
            });

            callables.ajax(ajax_setup, { request_key:'statement', block:{} });
        },

        /**
         * This will get transaction detail of a statement
         * @param  string type         
         * @param  object report_details  { transactionID, page }
         * @param  object ajax_setup   
         * @return void
         */
        get_statement_details: function(type, report_details, ajax_setup) {
            ajax_setup       = ajax_setup || {};
            var store_key    = 'statement_details_' + type;
            var orig_success = ajax_setup.success;
            ps_helper.assoc_merge(ajax_setup, { 
                url    : 'get',
                method : 'post',
                data   : { 
                            g     : 'statementdetails', 
                            type  : report_details.subtype, 
                            trans : report_details.transactionID, 
                            p     : report_details.page , 
                            _token: globals.csrf.token 
                        },
                success: function(response,status,jqXHR) {
                            globals.store.store_update(store_key, response);

                            if ($.isFunction(orig_success)) {
                                orig_success.call(this, globals.store.store_fetch(store_key), status, jqXHR);
                            }
                        } 
            });

            callables.ajax(ajax_setup, { request_key:store_key, block:{} });
        },

        /**
         * This will get running bets 
         * @param  int    page         
         * @param  object ajax_setup   
         * @return void
         */
        get_running_bets: function(page, ajax_setup) {
            ajax_setup       = ajax_setup || {};

            var orig_success = ajax_setup.success;
            ps_helper.assoc_merge(ajax_setup, { 
                url    : 'get',
                method : 'post',
                data   : { 
                            g     : 'running_bets', 
                            p     : page, 
                            _token: globals.csrf.token 
                        },
                success: function(response,status,jqXHR) {
                            globals.store.store_update('running_bets', response);

                            if ($.isFunction(orig_success)) {
                                orig_success.call(this, globals.store.store_fetch('running_bets'), status, jqXHR);
                            }
                        } 
            });

            callables.ajax(ajax_setup, { request_key:'running_bets', block:{} });
        },

        /**
         * This will get transaction logs
         * @param  object form_data   
         * @param  int    page         
         * @param  object ajax_setup   
         * @return void
         */
        get_transaction_logs: function(form_data, page, ajax_setup) {
            ajax_setup = ajax_setup || {};
            form_data  = form_data  || {};

            var orig_success = ajax_setup.success;
            ps_helper.assoc_merge(ajax_setup, { 
                url    : 'get',
                method : 'post',
                data   :  ps_helper.assoc_merge(form_data, {
                             g    : 'transaction_logs', 
                             p    : page, 
                            _token: globals.csrf.token
                         }),
                success: function(response,status,jqXHR) {
                            globals.store.store_update('transaction_logs', response);

                            if ($.isFunction(orig_success)) {
                                orig_success.call(this, globals.store.store_fetch('transaction_logs'), status, jqXHR);
                            }
                        } 
            });

            callables.ajax(ajax_setup, { request_key:'transaction_logs', block:{} });
        },

        /**
         * This will get faqs
         * @param  string productID  
         * @param  object ajax_setup 
         * @return void
         */
        get_faqs: function(productID, ajax_setup) {
            ajax_setup = ajax_setup || {};

            var orig_success = ajax_setup.success;
            ps_helper.assoc_merge(ajax_setup, { 
                url    : 'page',
                method : 'post',
                data   : { pid: productID, _token: globals.csrf.token, p: 'faq' },
                success: function(response,status,jqXHR) {
                            globals.store.store_update('faq_'+productID, response);

                            if ($.isFunction(orig_success)) {
                                orig_success.call(this, globals.store.store_fetch('faq_'+productID), status, jqXHR);
                            }
                        } 
            });

            callables.ajax(ajax_setup, { request_key:'get_faqs', block:{} });
        },

        /**
         * This will get gaming rules
         * @param  string productID  
         * @param  object ajax_setup 
         * @return void
         */
        get_gaming_rules: function(productID, ajax_setup) {
            ajax_setup       = ajax_setup || {};
            var orig_success = ajax_setup.success;
            ps_helper.assoc_merge(ajax_setup, { 
                url    : 'page',
                method : 'post',
                data   : { pid: productID, _token: globals.csrf.token, p: 'gm' },
                success: function(response,status,jqXHR) {
                            globals.store.store_update('gaming_rules_'+productID, response);

                            if ($.isFunction(orig_success)) {
                                orig_success.call(
                                    this, 
                                    globals.store.store_fetch('gaming_rules_'+productID), 
                                    status, 
                                    jqXHR
                                );
                            }
                        } 
            });

            callables.ajax(ajax_setup, { request_key:'gaming_rules_'+productID, block:{} });
        },

        /**
         * This will get game guide page
         * @param  object    data
         * @param  object ajax_setup [description]
         * @return void
         */
        get_game_guide: function(data, ajax_setup) {
            ajax_setup    = ajax_setup || {};
            
            ps_helper.assoc_merge(ajax_setup, { 
                url    : 'game_guide',
                method : 'post',
                data   : ps_helper.assoc_merge(data, { _token: globals.csrf.token })
            });

            callables.ajax(ajax_setup, { request_key:'gaming_guide_'+data.gname+'_'+data.gpage, block:{} });
        },

        /**
         * This will get terms and conditions
         * @return void
         */
        terms_and_conditions: function(ajax_setup) {
            ajax_setup = ajax_setup || {};
            
            ps_helper.assoc_merge(ajax_setup, { 
                url    : 'page',
                method : 'post',
                data   : { _token: globals.csrf.token, p: 'terms_and_conditions' }
            });

            callables.ajax(ajax_setup, { request_key:'terms_and_conditions', block:{} });
        },

        /**
         * This will get terms and conditions
         * @return void
         */
        contact_us: function(ajax_setup) {
            ajax_setup = ajax_setup || {};
            
            ps_helper.assoc_merge(ajax_setup, { 
                url    : 'page',
                method : 'post',
                data   : { _token: globals.csrf.token, p: 'contact_us' }
            });

            callables.ajax(ajax_setup, { request_key:'contact_us', block:{} });
        },

        /**
         * Get list of games by product
         * Request to backend via ajax only if the productID wasn't on the store yet
         * @return void
         */
        get_games: function(productID, ajax_setup) {
            ajax_setup       = ajax_setup || {};
            var store_key    = 'games_' + productID;

            ps_helper.assoc_merge(ajax_setup, { 
                url    : 'get',
                method : 'post',
                data   : { _token: globals.csrf.token, g: 'ps_games', category: productID, filter: 'ALL' }
            });


            if (globals.store.store_exists(store_key) && globals.store.store_fetch(store_key).is_success) {

                callables.abort_success(ajax_setup, globals.store.store_fetch(store_key), function() {
                    callables.debug('Games list was already fetched, get from store.');
                });

            } else {

                var orig_success   = ajax_setup.success;
                ajax_setup.success = function(response,status,jqXHR) {
                                        globals.store.store_update(store_key, { 
                                            rows      : response.rows,
                                            is_success: true,
                                            err_code  : null
                                        });

                                        if ($.isFunction(orig_success)) {
                                            orig_success.call(this,globals.store.store_fetch(store_key),status,jqXHR);
                                        }
                                    };   

                var orig_fail   = ajax_setup.fail;
                ajax_setup.fail = function(response,status,jqXHR) {
                                        globals.store.store_update(store_key, { 
                                            rows      : [],
                                            is_success: false,
                                            err_code  : response.err_code
                                        });

                                        if ($.isFunction(orig_fail)) {
                                            orig_fail.call(this,globals.store.store_fetch(store_key),status,jqXHR);
                                        }
                                    };  

                var orig_error   = ajax_setup.error;
                ajax_setup.error = function() {
                                        globals.store.store_update(store_key, { 
                                            rows      : [],
                                            is_success: false,
                                            err_code  : null
                                        });

                                        if ($.isFunction(orig_error)) {
                                            orig_error.apply(this,arguments);
                                        }
                                    };  

                callables.ajax(ajax_setup, { request_key:store_key, block:{}  });

            }
        },

        /**
         * This will get game details
         * @param  string gameID  
         * @param  string productID  
         * @param  object ajax_setup [description]
         * @return void
         */
        play: function(gameID, productID, ajax_setup) {
            ajax_setup    = ajax_setup || {};
            var store_key = 'games_' + productID;
            
            var orig_fail = ajax_setup.fail;
            ps_helper.assoc_merge(ajax_setup, { 
                url    : 'play',
                method : 'post',
                data   : { 
                            _token   : globals.csrf.token, 
                            _GID     : gameID, 
                            g        : 'payload',
                            is_mobile: ps_helper.detect_mobile() ? 1 : 0
                        },
                fail   : function(response) {

                            if (globals.store.store_exists(store_key)) {
                                switch (response.dcode) {
                                    case 'MSP': case 'MSS': case 'MSC': case 'MSL': case 'MSD': case 'CSS': case 'CSC':
                                        // This group is for member status
                                    case 'PTM':

                                        globals.store.store_update(store_key, {
                                            is_success: false,
                                            err_code  : response.err_code,
                                        });
                                        break;

                                    case 'DNR':
                                    case 'HRG':
                                        // do nothing, 
                                        // requesting modules will provide a handler
                                        break;

                                    default: 

                                        globals.store.store_list_update(store_key, function(value) {
                                            return value.gameID == gameID;
                                        }, 'rows', { playable: false });
                                }
                            }

                            if ($.isFunction(orig_fail)) {
                                orig_fail.apply(this,arguments);
                            }

                        }
            });

            callables.ajax(ajax_setup, { request_key:'play', block:{} });
        },

        /**
         * This will get all announcement
         * @param  object ajax_setup 
         * @return void            
         */
        get_announcement: function(ajax_setup) {
            ajax_setup = ajax_setup || {};
            var orig_success = ajax_setup.success;

            ps_helper.assoc_merge(ajax_setup,{
                url    : 'get',
                method : 'post',
                data   : { _token: globals.csrf.token, g: 'announcement'},
                success: function(response,status,jqXHR) {
                            globals.store.store_update('announcement', response);

                            if ($.isFunction(orig_success)) {
                                orig_success.call(this, globals.store.store_fetch('announcement'), status, jqXHR);
                            }
                        }
            });

            callables.ajax(ajax_setup, { request_key:'announcement', block:{} });

        },

        /**
         * This will deduct currently open game window in session
         * if count reaches 0 then this will delete gameID websession record
         * @param  int    gameID 
         * @param  object ajax_setup 
         * @return void
         */
        reset_websession: function(gameID,ajax_setup) {
            ajax_setup = ajax_setup || {};

            ps_helper.assoc_merge(ajax_setup, { 
                url    : 'process',
                method : 'post',
                data   : {
                            'ps_form-process': 'RESET_WEBSESSION',
                            _GID             : gameID,
                            _token           : globals.csrf.token
                        }
            });

            callables.ajax(ajax_setup, { request_key:'reset_websession' });
        },

        /**
         * Get bet details
         * @param  string transactionID 
         * @param  object ajax_setup 
         * @return void
         */
        bet_details: function(transactionID, ajax_setup) {
            ajax_setup = ajax_setup || {};

            ps_helper.assoc_merge(ajax_setup, { 
                url    : 'bet_details',
                method : 'post',
                data   : { g: 'payload', _BID: transactionID, _token: globals.csrf.token }
            });

            callables.ajax(ajax_setup, { request_key:'bet_details' });
        },

        /**
         * Get needed data for continuing a game
         * @param  string gid   
         * @param  string tid     
         * @param  object ajax_setup
         * @return void
         */
        continue_game: function(gid, tid, ajax_setup) {
            ajax_setup = ajax_setup || {};

            ps_helper.assoc_merge(ajax_setup, { 
                url    : 'continuegame',
                method : 'post',
                data   : { g: 'payload', _GID: gid, _TID: tid, _token: globals.csrf.token }
            });

            callables.ajax(ajax_setup, { request_key:'continue_game' });
        },

        /**
         * This will get all available promo data
         * @param  object ajax_setup
         * @return void
         */
        get_promo: function(ajax_setup) {
            ajax_setup = ajax_setup || {};

            var orig_success = ajax_setup.success;
            ps_helper.assoc_merge(ajax_setup,{
                url    : 'v_get',
                method : 'post',
                data   : { _token: globals.csrf.token, g: 'promotions', filter: 'ALL'},
                success: function(response,status,jqXHR) {
                            globals.store.store_update('promotions', response);

                            if ($.isFunction(orig_success)) {
                                orig_success.call(this, globals.store.store_fetch('promotions'), status, jqXHR);
                            }
                        }
            });

            callables.ajax(ajax_setup, { request_key:'promotions', block:{} });
            
        },

        /**
         * Accept terms and conditions
         * @param  object ajax_setup
         * @return void
         */
        accept_terms_conditions: function(ajax_setup) {
            ajax_setup = ajax_setup || {};
            
            ps_helper.assoc_merge(ajax_setup,{
                url    : 'accept_terms',
                method : 'post'
            });

            callables.ajax(ajax_setup, { request_key:'promotions', block:{} });
        },

        /**
         * This will submit change credential form
         * @param  object form_data  
         * @param  object ajax_setup 
         * @return void
         */
        change_credentials: function(form_data, ajax_setup) {
            form_data  = form_data  || {};
            ajax_setup = ajax_setup || {};

            ps_helper.assoc_merge(ajax_setup, { 
                url    : 'process',
                method : 'post',
                data   : ps_helper.assoc_merge(form_data, {
                            'ps_form-process': 'CHANGE_LOGIN_PASSWORD',
                            _token: globals.csrf.token
                        })
            });

            callables.ajax(ajax_setup, { request_key:'change_credentials', block:{} });
        },

        /**
         * This will submit reset expired password form
         * @param  object form_data  
         * @param  object ajax_setup 
         * @return void
         */
        expired_password: function(form_data, ajax_setup) {
            form_data  = form_data  || {};
            ajax_setup = ajax_setup || {};

            ps_helper.assoc_merge(ajax_setup, { 
                url    : 'process',
                method : 'post',
                data   : ps_helper.assoc_merge(form_data, {
                            'ps_form-process': 'EXPIRED_PASSWORD',
                            _token: globals.csrf.token
                        })
            });

            callables.ajax(ajax_setup, { request_key:'expired_password', block:{} });
        },

        /**
         * This will submit forgot password reset
         * @param  string code  
         * @param  object form_data  
         * @param  object ajax_setup 
         * @return void
         */
        new_password: function(code, form_data, ajax_setup) {
            form_data  = form_data  || {};
            ajax_setup = ajax_setup || {};

            ps_helper.assoc_merge(ajax_setup, { 
                url    : 'process',
                method : 'post',
                data   : ps_helper.assoc_merge(form_data, {
                            'ps_form-process': 'NEW_PASSWORD',
                            code             : code,
                            _token           : globals.csrf.token
                        })
            });

            callables.ajax(ajax_setup, { request_key:'new_password', block:{} });
        },

        /**
         * This will verify auth
         * @param  object ajax_setup 
         * @return void
         */
        verify_auth: function(ajax_setup) {
            ajax_setup = ajax_setup || {};

            ps_helper.assoc_merge(ajax_setup, { 
                url    : 'verify_auth',
                method : 'post',
                data   : { _token: globals.csrf.token }
            });

            callables.ajax(ajax_setup, { request_key:'verify_auth', block:{} });
        },
        
        /**
         * This will update user store member status
         * @param  object   status    
         * @return void
         */
        update_member_status: function(status) {

            if (!ps_helper.empty(status.derived_status_id)) {
                globals.store.store_update('user', {
                    derived_status_id: status.derived_status_id
                });
            }

            if (!ps_helper.empty(status.derived_is_transactable)) {
                globals.store.store_update('user', {
                    derived_is_transactable: parseInt(status.derived_is_transactable)
                });
            }
            
            if (!ps_helper.empty(status.status_error)) {
                globals.store.store_update('user', {
                    status_error: status.status_error
                });
            }

        },

        /**
         * This will update compatibility.recommended store to false
         * @return void
         */
        browser_not_recommended: function() {
            globals.store.store_update('compatibility', { recommended: false });
        },
        
        /**
         * get display for lobby
         * @param  string gameID     
         * @param  object ajax_setup 
         * @return void            
         */
        get_lobby: function(gameID, ajax_setup) {
            ajax_setup       = ajax_setup || {};
            var orig_success = ajax_setup.success;
            var orig_fail    = ajax_setup.fail;

            ps_helper.assoc_merge(ajax_setup, {
                url    : 'get',
                method : 'post',
                data   : { _token: globals.csrf.token, g: 'ps_lobby',filter: 'ALL', _GID: gameID },
                success: function (response,status,jqXHR) {

                            globals.store.store_update(gameID+'_lobby', response);
                            globals.store.store_update(gameID+'_lobby', {is_success: true, err_code: null  });
                            if ($.isFunction(orig_success)) {
                                orig_success.call(this, globals.store.store_fetch(gameID+'_lobby'), status, jqXHR);
                            }

                },
                fail    : function (response,status,jqXHR) {

                            globals.store.store_update(gameID+'_lobby', response);
                            globals.store.store_update(gameID+'_lobby', {is_success: false });
                            if ($.isFunction(orig_fail)) {
                                orig_fail.call(this, globals.store.store_fetch(gameID+'_lobby'), status, jqXHR);
                            }

                }
            });

            callables.ajax(ajax_setup, { request_key:gameID+'_lobby', block:{} });

        },

        /** 
         * Get tournament details
         * @param  object ajax_setup 
         * @return void
         */
        tournament_details: function(ajax_setup) {
            ajax_setup = ajax_setup || {};
            var orig_success = ajax_setup.success;

            ps_helper.assoc_merge(ajax_setup, {
                url: 'tournament_details',
                method: 'post',
                data: { _token: globals.csrf.token},
                success: function (response, status, jqXHR) {


                    for (var i = 1; i <= response.length; i++) { 
                        response[i].data = [];
                    }
                                
                        globals.store.store_update('tournament', response);

                        if ($.isFunction(orig_success)) {
                            orig_success.call(this,globals.store.store_fetch('tournament'), status, jqXHR);           
                        }


                    }


            });

            callables.ajax(ajax_setup, { request_key:'tournament', block:{} });

        },

        /**
         * Get tournament phase rankings
         * @param  int    phaseNo    
         * @param  object ajax_setup 
         * @return void
         */
        get_phase_ranks: function(phaseNo,ajax_setup) {
            ajax_setup = ajax_setup || {};
            var orig_success = ajax_setup.success;
            ps_helper.assoc_merge(ajax_setup, {
                url: 'phase_top',
                method: 'post',
                data: { _token: globals.csrf.token , phase: phaseNo },
                success: function (response, status, jqXHR) {
                        globals.store.store_update('tournament',phaseNo+'.data', response);

                        if ($.isFunction(orig_success)) {
                            orig_success.call(this,globals.store.store_fetch('tournament'), status, jqXHR);           
                        }
                }
            });
            callables.ajax(ajax_setup, { request_key:'tournament' });
        },

        /**
         * This will signal backend that a game has closed window
         * @param  object params    
         * @param  object ajax_setup 
         * @return void
         */
        game_window_closed: function(params, ajax_setup) {
            ajax_setup = ajax_setup || {};

            ps_helper.assoc_merge(ajax_setup, {
                url   : 'game_window_closed',
                method: 'post',
                data  : { 
                            game_token: params.game_token,
                            _GID      : params.gameID,
                            _CID      : params.cid
                        }
            });

            callables.ajax(ajax_setup, { request_key:'game_window_closed' });
        },

       /**
        * This will set app language base on passed languageID
        * @param  string language  
        * @param  string window_id 
        * @param  object ajax_setup
        * @return void
        */
        set_language: function(language, window_id, ajax_setup) {
            ajax_setup = ajax_setup || {};


            ps_helper.assoc_merge(ajax_setup, {
                url   : 'language',
                method: 'post',
                data  : { 
                            _token   : globals.csrf.token,
                            language : language,
                            window_id: window_id
                        }
            });

            callables.ajax(ajax_setup, { request_key:'set_language' });
        },

        /**
         * This will resend all blocked message from chatbox
         * @return void
         */
        retry_blocked_message: function() {

            var message_array = globals.store.store_list_fetch('chat_messages', function(value) {
                                    return value.blocked == true;
                                });

            if (!ps_helper.empty(message_array) && $.isArray(message_array)) {

                callables.resend_message_array(message_array);

            } else {

                callables.debug("ps_model.retry_blocked_message Couldn't find any blocked messages");

            }
        }
    };
});