/**
 * PS state management
 * 1. can update items only using store.update()
 * 2. can operate only inside the module that instantiate the store
 * 3. If needed outside the module, create a getter or setter methods
 * 4. store root items should be an object
 * 
 * @author PS Team
 */

define(['ps_gconfig', 'ps_helper', 'jquery'], function(ps_gconfig, ps_helper, $) {

    'use strict';

    var globals   = { strict: ps_gconfig.debug };
    var callables = {
                        /**
                         * Extend helper debug using local configuration
                         */
                        debug: ps_helper.debug(globals.strict)
                    };
    
    if (globals.strict) {
        console.warn('ps_store Note: Do not edit store values directly after fetching it via store.fetch(), doing so'
                      +' will not update other instances that is using the same store, use store.update() instead.' );
    }

    /**
     * Return a constructor, we need the module name that will use the store
     * @param   string  store_name  store name
     * @param   object  store_items initial items of store        
     * @param   boolean is_lock     if true and pre_stored is not empty, you cant add new root items to store
     * @return  object              store methods
     */
    return function ps_store(store_name, store_items, is_lock) {

        'use strict';

        var store     = {};
        var instances = {};
        var locked    = false;
        var methods   = {
            /**
             * This will set/update store value
             * @param  string store_key         
             * @param  mixed  property_or_value  If string this will serve as a property to be updated.
                                                 If object this will be the value to overwrite the existing.
             * @param  mixed  alternative_value  If second argument serves as index, this will be the value 
             * @return object                    { is_updated, is_recent };
             */
            store_update: function(store_key, property_or_value, alternative_value) {

                if (!store.hasOwnProperty(store_key)) {

                    var is_recent = true;

                    if (locked) {

                        callables.debug('Cannot add new store item ' + store_key + ', ' + store_name + ' is locked!');
                        return { is_updated: false, is_recent: is_recent };

                    } else {

                        store[store_key] = {};

                    }


                } else {

                    var is_recent = false;

                }

                if (property_or_value === undefined) {
                    callables.debug('store.update 2nd argument must not be empty.');
                    return { is_updated: false, is_recent: is_recent };
                }

                switch ($.type(property_or_value)) {

                    case 'object':

                        ps_helper.assoc_merge(store[store_key], property_or_value);

                        iterate_intances(store_key, function(intance) {
                            ps_helper.assoc_merge(intance, $.extend(true, {}, property_or_value));
                        });

                        return { is_updated: true, is_recent: is_recent };

                    case 'array' :

                        var new_value = { list: property_or_value };
                        ps_helper.assoc_merge(store[store_key], new_value);

                        iterate_intances(store_key, function(intance) {
                            ps_helper.assoc_merge(intance, $.extend(true, {}, new_value));
                        });

                        callables.debug(store_name+'['+store_key+'] store cannot contain array, '
                                       + 'value converted to object {list:[array]}.');
                        
                        return { is_updated: true, is_recent: is_recent };


                    default:

                        if ($.type(alternative_value) !== 'undefined') {
                            ps_helper.set_property(store[store_key],property_or_value,alternative_value);

                            iterate_intances(store_key, function(instance) {
                                ps_helper.set_property(instance, property_or_value, alternative_value);
                            });

                            return { is_updated: true, is_recent: is_recent };

                        } else {

                            callables.debug('Only object or array can be assigned to store root value.');
                            return { is_updated: false, is_recent: is_recent };

                        }
                }
            },

             /**
              * Use this when pushing new item in a store array
              * @param  string store_key        
              * @param  mixed  property_or_value 
              * @param  mixed  alternative_value
              * @return object                    { is_updated }
              */
            store_list_push: function(store_key, property_or_value, alternative_value) {

                if ($.type(alternative_value) !== 'undefined') {

                    var property = property_or_value;
                    var value    = alternative_value;

                } else {

                    var property = 'list';
                    var value    = property_or_value;

                }

                var get_value = ps_helper.get_property(store[store_key], property);

                if ($.isArray(get_value)) {

                    get_value.push(value);

                    iterate_intances(store_key, function(instance) {
                        var instance_value = ps_helper.get_property(instance, property);
                        instance_value.push(value);
                    });

                    return { is_updated: true };

                } else {

                    callables.debug('store.store_list_push is for adding new item in store list/array only.');
                    return { is_updated: false };

                }
            },

             /**
              * Use this when pushing new item in a store array
              * @param  string store_key        
              * @param  mixed  property_or_value 
              * @param  mixed  alternative_value
              * @return object                    { is_updated }
              */
            store_list_unshift: function(store_key, property_or_value, alternative_value) {

                if ($.type(alternative_value) !== 'undefined') {

                    var property = property_or_value;
                    var value    = alternative_value;

                } else {

                    var property = 'list';
                    var value    = property_or_value;

                }

                var get_value = ps_helper.get_property(store[store_key], property);

                if ($.isArray(get_value)) {

                    get_value.unshift(value);

                    iterate_intances(store_key, function(instance) {
                        var instance_value = ps_helper.get_property(instance, property);
                        instance_value.unshift(value);
                    });

                    return { is_updated: true };

                } else {

                    callables.debug('store.store_list_push is for adding new item in store list/array only.');
                    return { is_updated: false };

                }
            },

            /**
              * This will update array property to a new value if it matches the filter_callback 
              * @param  string    store_key        
              * @param  function  filter_callback   gets the current value as argument
              * @param  mixed     property_or_value 
              * @param  mixed     alternative_value
              * @return object                       { is_updated }
              */
            store_list_update: function(store_key, filter_callback, property_or_value, alternative_value) {

                if ($.type(alternative_value) !== 'undefined') {

                    var property = property_or_value;
                    var value    = alternative_value;

                } else {

                    var property = 'list';
                    var value    = property_or_value;

                }

                var get_value = ps_helper.get_property(store[store_key], property);

                 if ($.isArray(get_value)) {

                    get_value.forEach(function(array_value, array_index) {
                        if (filter_callback(array_value, array_index) === true) {
                            ps_helper.assoc_merge(array_value, value);
                        }
                    });

                    iterate_intances(store_key, function(instance) {
                        var instance_value = ps_helper.get_property(instance, property);
                        instance_value.forEach(function(array_value, array_index) {
                            if (filter_callback(array_value, array_index) === true) {
                                ps_helper.assoc_merge(array_value, $.extend(true, {}, value));
                            }
                        });
                    });

                    return { is_updated: true };

                } else {

                    callables.debug('store.store_list_update is for updating store list/array only.');
                    return { is_updated: false };

                }
            },

            /**
              * This will update array property to a new value if it matches the filter_callback 
              * @param  string    store_key        
              * @param  function  filter_callback   gets the current value as argument
              * @param  string    property 
              * @return object                       { is_updated }
              */
            store_list_delete: function(store_key, filter_callback, property) {

                if (ps_helper.empty(property)) {
                    var property = 'list';
                } 

                var get_value = ps_helper.get_property(store[store_key], property);

                 if ($.isArray(get_value)) {

                    var original_filter_callback = filter_callback;

                    // reverse the $.grep
                    filter_callback = function() {
                                        return original_filter_callback.apply(this, arguments) === false;
                                    };

                    ps_helper.set_property(store[store_key], property, $.grep(get_value, filter_callback));

                    iterate_intances(store_key, function(instance) {
                        var instance_value = ps_helper.get_property(instance, property);
                        ps_helper.set_property(instance, property, $.grep(instance_value, filter_callback));
                    });

                    return { is_updated: true };

                } else {

                    callables.debug('store.store_list_delete is for updating store list/array only.');
                    return { is_updated: false };

                }
            },

            /**
              * This is like update but multiple items
              * @param  object store_items
              * @return boolean
              */
            store_multi_update: function(store_items) {

                var has_updated = false;

                if ($.isPlainObject(store_items) && !ps_helper.empty(store_items)) {
                    for (var store_key in store_items) {
                        if (methods.store_update(store_key, store_items[store_key]).is_updated) {
                            has_updated = true;
                        }
                    }
                }

                return has_updated;
            },

            /**
             * This will check if store item is existing
             * @param  string store_key 
             * @return boolean
             */
            store_exists: function(store_key) {
                return store.hasOwnProperty(store_key);
            },

            /**
             * Get a single store item
             * @param  string store_key
             * @return object
             */
            store_fetch  : function(store_key) {
                if (store.hasOwnProperty(store_key)) {

                    if (globals.strict) {

                        instances[store_key] = instances[store_key] || [];
                        var readonly_intance = $.extend(true, {}, store[store_key]);
                        instances[store_key].push(readonly_intance);
                        return readonly_intance;

                    } else {

                        return store[store_key];

                    }

                } else {

                    return false;

                }
            },

            /**
             * Get an item from array/list type store by giving a filter function
             * @param  string   store_key       
             * @param  function filter_callback 
             * @return object
             */
            store_list_fetch: function(store_key, filter_callback) {
                var store_value = methods.store_fetch(store_key);

                if (store_value != false) {
                    return store_value.list.filter(filter_callback);
                }
            },

            /**
             * Get multiple store items
             * @param  array  store_keys
             * @return object
             */

            store_multi_fetch : function(store_keys) {
                var list = { existing: {}, missing:[] };

                if ($.isArray(store_keys)) {

                    store_keys.forEach(function(store_key) {
                        var store_value = methods.store_fetch(store_key);

                        if (store_value != false) {
                            list.existing[store_key] = store_value;
                        } else {
                            list.missing.push(store_key);
                        }
                    });

                } else {

                    // fetch all
                    for (var store_key in store) {
                        list.existing[store_key] = methods.store_fetch(store_key);
                    }

                }

                return list;
            }
        };

        // set initial items and lock if needed
        if (methods.store_multi_update(store_items) && is_lock) {
            locked = true;
        }

        // extra service
        function iterate_intances(store_key, setter) {
            if (globals.strict && instances.hasOwnProperty(store_key)) {
                instances[store_key].forEach(function(instance) { setter(instance); });
            }
        };

        return methods;
    };
});

