<?php

namespace Backend\controllers;

use Redirect;
use Input;
use Config;
use Auth;
use Lang;

/**
 * functions that will affect view directly, 
 * app data that will be translated to JS variable (banners, language, badges, help),
 * and any site logical methods that can affect our display
 * 	
 * @author PS Team
 */
class Ccomponents extends Basecontroller {


    /**
     * filter what method should use depends on process type
     * @return mixed
     */
    public function get_components()
    {

        $process_type = strtolower(Input::get('g'));

        $this->service('Svalidate')->validate(array(

            'process_type' => array(
                                'value'     => array(
                                                'type'     => 'hidden',
                                                'input'    => $process_type
                                            ),
                                'validator' => 'required_param'
                            )

        ), TRUE);

        switch ($process_type) {

            case 'promotions':

                return $this->repository('Rwhitelabel')->get_promotions(
                    array(
                        'whiteLabelID' => Config::get('settings.WL_CODE'),
                        'filter'       => Input::get('filter', ''),
                        'search'       => Input::get('search', '')
                    ),

                    $this->service('Ssiteconfig')->paging_offset_limit(Input::get('page', 1), 'promotion'),
                    function(&$promotion) {

                        $promotion->bid = $this->service('Scrypt')->crypt_encrypt($promotion->promotionID);

                    }

                );
            
            case 'captcha':

                return $this->service('Scaptcha')->get();

            case 'announcement':
                $announcement_config = Config::get('settings.announcement');
                return $this->repository('Rannouncement')->get_announcements(
                    Auth::user()->clientID,
                    Config::get('settings.WL_CODE'),
                    assoc_array_merge($announcement_config, array(
                        'language' => $this->service('Ssiteconfig')->get_lang_id()
                    ))
                );

        }

    }

    /**
     * Components that process form 
     * @return mixed
     */
    public function process_components()
    {

        $process_type = strtolower(Input::get('ps_form-process'));

        $this->service('Svalidate')->validate(array(
            'process_type' => array(
                                'value'     => array(
                                                'type'     => 'hidden',
                                                'input'    => $process_type
                                            ),
                                'validator' => 'required_param'
                            )
        ), TRUE);

        switch ($process_type) {
            case 'captcha': return $this->process_captcha();
        }
    }

	/**
     * Set application to selected language
     * @param type $language param from routes
     */
    public function set_language() 
    {   
        $language  = Input::get('language');
        $window_id = Input::get('window_id', '');

        $this->service('Svalidate')->validate(array(
            'lang_id' => array('value'=> $language)
        ), TRUE);

        $this->service('Ssiteconfig')->set_lang_id($language);
        $this->service('Ssocket')->push_main($this->service('Ssession')->sessionID(),
            array('event' => 'REFRESH', 'message' => $window_id)
        );

        return array('result' => true);
    }

    /**
     * This will get all data for our plugins from cache and force refresh all non cacheable
     * @return array
     */
    public function get_plugin() 
    {
        // initialize by getting data from cache
        $plugin_data   = $this->service('Scache')->get_plugin();

        // force refresh all plugin that is non cacheable
        $non_cacheable = $this->service('Ssiteconfig')->theme('plugin_cache')['disable'];

        foreach ($non_cacheable as $plugin_name) {

            $plugin_data[$plugin_name] = $this->service('Scache')->refresh_plugin($plugin_name);

        }

        return array('result'=> true, 'content' => $plugin_data, 'dist' => true);
    }


    /**
     * Check if parent is online
     * @return boolean
     */
    public function check_chatStatus()
    {
        $parentID   = $this->service('Ssession')->get('parentID');
        $chatStatus = $this->service('Ssiteconfig')->chatStatus($parentID);
        $sessionID  = $this->service('Ssession')->sessionID();

        // hide/show status
        $this->service('Ssocket')->push(array(
            'session_id' => $sessionID,
            'event'      => $chatStatus['chatStatus'] 
        ));

        // online/offline status
        $this->service('Ssocket')->push(array(
            'session_id' => $sessionID,
            'event'      => $chatStatus['status']
        ));

        return array(
            'result'     => true, 
            'chatStatus' => $chatStatus['chatStatus'], 
            'status'     => $chatStatus['status'],
            'unread'     => $this->repository('Rchat')->count_unread(
                                $this->service('Ssession')->chat_receiver(),
                                $this->service('Ssession')->chat_sender(),
                                Config::get('settings.chat.message_range')
                            ),
            'can_send'   => $chatStatus['can_send']
        );
    }

    /**
     * process captcha submit by form
     * @return array 
     */
    private function process_captcha()
    {

        $validate_captcha = $this->service('Scaptcha')->check(Input::get('login_captchainput'));
        $this->service('Ssession')->forget_login_attempts();
        return $validate_captcha;
    }

    /**
     * Get messages of client 
     * @return array 
     */
    public function get_messages()
    {      
        $last_chatID = empty(Input::get('last_chatID')) ? null : $this->service('Scrypt')->crypt_decrypt(
                                                                    Input::get('last_chatID')
                                                                );
        
        // switch receiver and sender because we're getting your client own message
        $return = $this->repository('Rchat')->get_messages(
            array(
                'sender'          => $this->service('Ssession')->chat_sender(),
                'receiver'        => $this->service('Ssession')->chat_receiver(),
                'client_timezone' => Config::get('app.client_timezone')
            ),
            Config::get('settings.chat'),
            $last_chatID
        );

        $return['last'] = isset($return['last'])? $this->service('Scrypt')->crypt_encrypt($return['last']) : '' ;

        return $return;
    }

    /**
     * send message using chat app
     * @return array 
     */
    public function send_message()
    {   
        $parentID  = $this->service('Ssession')->get('parentID');
        $msg_input = array(
                        'messages' => Input::get('msg'), 
                        'sender'   => $this->service('Ssession')->chat_sender(),
                        'receiver' => $this->repository('Rplayer')->get_username($parentID)
                    );

        $this->service('Svalidate')->validate(array(
            'chat_access' => array('value' => array('input' => $msg_input, 'parentID'=> $parentID))
        ));

        // check if can continue sending  message
        $block_sending = $this->service('Ssession')->check_sent_message();

        if (Auth::check()) {
            
            $first_name = Auth::user()->firstName;
            $last_name  = Auth::user()->lastName;
            $clientID   = $this->service('Scrypt')->hashids_encode(Auth::user()->clientID, 1);

            $this->repository('Rplayer')->set_as_online($clientID);

        } else {
            
            $first_name = $msg_input['sender'];
            $last_name  = '';
            $clientID   = $this->service('Scrypt')->hashids_encode(
                                $this->repository('Rguest')->get_guestID($first_name), 
                                2
                            );
        
        }

        // save to DB
        $chat_db_insert     = $this->repository('Rchat')->send_message($msg_input);

        // send socket
        $socket_recepients  = $this->repository('Rplayer')->get_agent_sessionIDs($parentID);

        $message            = array(
                                        'messages'  => $chat_db_insert['message'],
                                        'f'         => $msg_input['sender'],
                                        'lastName'  => $last_name,
                                        'firstName' => $first_name,
                                        'dateTime'  => $chat_db_insert['dateTime'],
                                        'player'    => 'yes',
                                        'id'        => $clientID
                                );

        $send_socket        = $this->service('Ssocket')->push(
                                    array(
                                        'room'       => 'agent_site',
                                        'session_id' => $socket_recepients,
                                        'event'      => 'PS_chat',
                                        'message'    => $message
                                    )
                                );

        $message['send_id'] = Input::get('send_id');
        $send_socket        = $this->service('Ssocket')->push(
                                    array(
                                        'session_id' => $this->service('Ssession')->sessionID(),
                                        'event'      => 'chat',
                                        'message'    => $message
                                    )
                                );

        $send_socket['msg'] = $chat_db_insert['message'];

        //save the update of session after sending message

        $this->service('Ssession')->save_sent_message();
        return $send_socket;
    }

    /**
     * mark message seen
     * @return array 
     */
    public function seen_message()
    {
        $this->repository('Rchat')->mark_as_read(
            $this->service('Ssession')->chat_receiver(),
            $this->service('Ssession')->chat_sender()
        );

        $this->service('Ssocket')->push(array(
                'session_id' => $this->service('Ssession')->sessionID(),
                'event'      => 'chat_seen', 
                'message'    => $this->service('Ssession')->chat_receiver() 
            ));

        return array('result' => true);
    }

    /**
     * This will get a requested page content based on current language
     * @return mixed
     */
    public function get_language_page()
    {

        $page = strtolower(Input::get('p'));

        $this->service('Svalidate')->validate(array(

            'required_param' => array( 'value'=> array('type' => 'hidden', 'input' => $page) )

        ), TRUE);

        $language_key = false;

        switch ($page) {

            case 'contact_us':
            case 'terms_and_conditions':

                $language_key = $page.'.content';

                break;
            
            case 'faq':
            case 'gm':

                $productID = (String)Input::get('pid');

                $this->service('Svalidate')->validate(array(

                    'required_param' => array( 'value'=> array('type' => 'hidden', 'input' => $productID) )

                ), TRUE);

                if ($productID!=='0') {
                    
                    $language_key = $page.'.'.$this->service('Scrypt')->crypt_decrypt($productID);

                } else {

                    $language_key = $page.'.'.$productID;

                }

                break;
        }

        $this->service('Svalidate')->validate(array(

            'language_key' => array( 'value' => $language_key )

        ), TRUE);

        return Lang::get($language_key);
    }
    
    /**
     * validation for websocket
     * @return array 
     */
    public function ws_validation()
    {
        $decrypted_payload = $this->service('Scrypt')->crypt_decrypt(Input::get('payload'));
        $rooms = $this->service('Ssiteconfig')->websocket_topics($decrypted_payload);

        $data = array(
                'namespace'       => Config::get('settings.WS_NAMESPACE'),
                'socket_id'       => Input::get('socket_id'), 
                'rooms'           => $rooms,
                'session_id'      => $this->service('Ssession')->get('TSESSION_ID'),
                'username'        => $this->service('Ssession')->get('guestName')
            );

        if (Auth::check()) {

            $data['session_id'] = $this->service('Ssession')->sessionID();
            $data['username']   = Auth::user()->username;

        }

        $this->service('Svalidate')->validate(array(
            'ws_validation' => array(
                                    'value' => array(
                                        'input' => $data['session_id']
                                    ),
                                    'validator' => 'truthy',
                                    'callback'  => function() use ($data) {

                                        $this->service('Slogger')->file(
                                                            $data,
                                                            'SOCKET_ERROR'
                                                        );
                                    }
                                )
        ),true);

        return $this->service('Ssocket')->validation($data);

    }

    /**
     * broadcast message uwing websocket
     * @return array 
     */
    public function ws_broadcast()
    {

        $this->service('Svalidate')->validate(array(
            'param' => array(
                            'value' => array(
                                'input' => is_json(Input::get('param'))
                                    ),
                                'validator'    => 'truthy'
                    )
        ), TRUE);

        $param  = json_decode(Input::get('param'), TRUE);
        $return = array('result' => false);

        switch ($param['category']) {
            case 'avatar':
                $sessionID = $this->repository('Rplayer')->get_sessionID($param['clientID']);
                $this->service('Ssocket')->push_main(
                    $sessionID, 
                    array('event' => 'OPEN_AVATAR', 'message' => 1)
                );

                $return = array('result' => true, 'sessionID' => $sessionID);
                break;

            case 'togel_lobby':

                $this->service('Ssocket')->broadcast('TOGEL_LOBBY', 'refresh');
                $return = array('result' => true);
                break;

            case 'game_guide':

                $this->service('Ssocket')->broadcast('CLEAR_SESS', 'game_guide');
                $return = array('result' => true);
                break;

        }

        $this->service('Svalidate')->validate(array(
            'broadcast' => array(
                                'value'=> array(
                                            'input' => $return['result']
                                        ),
                                'validator'    => 'truthy'

                            )
        ), TRUE);

        return $return;
    }

    /**
     * clear storage of cache key
     * @return boolean 
     */
    public function clear_storage()
    {

        $key    = Input::get('key');

        switch ($key) {
            case 'game_guide':
                
                $this->service('Scache')->forget('GG_GAMES');
                break;
            
            default:
                
                $this->service('Slogger')->file(
                    array(
                        'cache_key' => $key,
                        'message'   => 'invalid key'
                    )
                );
                break;
        }

        return array('result' => true);
    }
    
    /**
     * check chat suuport and update status by websocket
     * @return array 
     */
    public function check_support()
    {

        $whiteLabelID = Input::get('white_label_id');
        $chat_apps    = $this->repository('Rwhitelabel')->get_chat_app($whiteLabelID);
        $apps_per_wl   = array();

        foreach ($chat_apps as $chat_app) {

            if (!array_key_exists($chat_app['whiteLabelID'], $apps_per_wl)) {

                $apps_per_wl[$chat_app['whiteLabelID']] = array();

            }
            if (array_key_exists($chat_app['application'], $apps_per_wl[$chat_app['whiteLabelID']])) {

                $apps_per_wl[$chat_app['whiteLabelID']][$chat_app['application']][] = $chat_app['whiteLabelChatAppID'];

            } else {

                $apps_per_wl[$chat_app['whiteLabelID']][$chat_app['application']]   = array(
                                                                                        $chat_app['whiteLabelChatAppID']
                                                                                    );

            }

        }

        foreach ($apps_per_wl as $whiteLabelID => $applications) {            

            $this->service('Ssocket')->broadcast('chat_app', json_encode($applications), strtolower($whiteLabelID));
        
        }

        return array('result' => true, 'apps' => $apps_per_wl);

    
    }

}