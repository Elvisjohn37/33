<?php

namespace Backend\services;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Formatter\LineFormatter;
use Config;
use Request;
use Auth;

/**
 * Handles all PS logging in files or DB
 * 
 * @author PS Team
 */
class Slogger extends Baseservice {
    
    /**
     * Log type that should always run 
     * even if settings for debug is false.
     */
    private $priority_log = array(
                            'ERROR',
                            'CRITICAL',
                            'ALERT',
                            'EMERGENCY',
                            'SOCKET_ERROR',
                            'FAILED_API_REG',
                            'CURL_ERROR',
                            'GET_PLAYER_BALANCE',
                            'SBO_LOGIN',
                            'SBO_LOGOUT',
                            'CASINO_PAYLOAD',
                            'POST_STATUS_PLAYER',
                            'SERVER_ERROR',
                            'EMAIL_ERROR' 
                        );

    /**
     * Log file
     *  
     *  [Log composition]
     * 
     *  type            => Type of Log
     *  time_stamp      => Time when Log is created
     *  ip_address      => IP address of to whom it was requested 
     *  requested_url   => URL on where it was requested
     *  data_type       => Data type given 
     *  log_data        => the data or message to Log
     * 
     * @param  mixed  $log_data data to be logged
     * @param  string $type     type/category of data to be logged
     * @param  string $log_file log file name
     * @return void
     */
    public function file($log_data, $type = 'ERROR', $log_file = 'ps_logs') 
    {

        $type = strtoupper($type);

        $settings = Config::get('app.debug');

        if ( $settings or ( !$settings and in_array($type, $this->priority_log) ) ) {

            $file_path  = Config::get('settings.log.storage') . $log_file . '.log';
            $logger     = new Logger($type);
            $handler    = new StreamHandler($file_path);

            // pass the format to handler
            $handler->setFormatter(new LineFormatter('%message%'.PHP_EOL));
            $logger->pushHandler($handler);

            $log_info = array(
                            'type'          => $type,
                            'time_stamp'    => date('Y-m-d H:i:s'),
                            'requested_url' => Request::url(),
                            'ip_address'    => get_ip(),
                            'data_type'     => gettype($log_data),
                            'log_data'      => $log_data
                        );

            // addtional info if player is logged in
            if (Auth::check()) {

                assoc_array_splice($log_info, 3, array(
                    'username'  => Auth::user()->username,
                    'loginName' => Auth::user()->loginName,
                    'sessionID' => Auth::user()->sessionID
                ));
                
            }

            // add data to log
            $logger->addInfo(json_encode($log_info));
        }
    }

    /**
     * This will log data to DB
     * NOTE: loginlog wasn't included here because it has different table structure from other DB logs table.
     *       loginlog is included in repository > Rplayer > db_login and db_logout process instead
     * @param  array  $log_data data to be inserted to DB log
     * @param  string $type     this wil determine to which repository the logged data will be stored
     * @return void
     */
    public function db($log_data, $type)
    {
        switch ($type) {
            case 'profile'  :
            case 'account'  : $repository = 'Rplayer';          break;
            case 'transfer' : $repository = 'Rtransactions';    break;
        }

        $insert_method = 'insert_'.$type.'log';

        // append common log data
        $log_data['createdOn']   = date('Y-m-d H:i:s');
        $log_data['createdFrom'] = 'Player Site';
        $log_data['ipAddress']   = get_ip();

        set_default($log_data , 'username',  function(){  return  Auth::user()->username; });
        set_default($log_data , 'createdBy', function(){  return  Auth::user()->username; });

        $this->repository($repository)->$insert_method($log_data);
    }

    /**
     * This will log filtered fields changes,
     * remarks can be customized by log user but not the description because its standardized
     * NOTE: loginlog will not be included as client_changes because it has different table structure.
     *       This log should only watch real DB columns and not system generated ones
     * @param  array $new_data 
     * @param  array $old_data   (optional) default = true, if set to true this will get from  Auth::user(),
     * @param  array $process    (optional) default = empty, This will be the basis of our remarks per field
     * @return boolean
     */
    public function client_changes($new_data,  $old_data = true, $process = '')
    {
        if ($old_data === true) {

            if (Auth::check()) {

                $old_data = Auth::user();

            } else {

                // kill process no old data being passed
                return false;

            }
        }

        // new data should be array
        if (!is_array($new_data)) {

            // kill process, no old data being passed
            return false;

        }

        // process logging per field
        foreach ($new_data as $field => $value) {

            if (!isset($old_data[$field]) || empty($old_data[$field])) {
                $old_data[$field] = '';
            }

            if ($old_data[$field]!=$value) {
                
                $log_data = false;

                switch ($field) {

                    case 'memberStatusID':

                        $type     = 'account';

                        // get memberStatuses Names
                        $memberStatusNames = $this->repository('Rplayer')->get_memberStatusNames(array(
                                                $old_data['memberStatusID'],
                                                $new_data['memberStatusID']
                                            ));

                        $log_data = array(

                                        'description' => 'Account Information',
                                        'from'        => array(
                                                            'Status' => $memberStatusNames[$old_data['memberStatusID']]
                                                        ),
                                        'to'          => array(
                                                            'Status' => $memberStatusNames[$new_data['memberStatusID']]
                                                        )

                                    );

                        break;

                    case 'displayName':

                        $type     = 'account';

                        $log_data = array(
                                        'description' => 'Contact Information',
                                        'from'        => array('Display Name' => custom_ucwords($old_data[$field])),
                                        'to'          => array('Display Name' => custom_ucwords($new_data[$field]))
                                    );

                        if ($new_data['displayNameStatus'] == 2) {
                            
                            // custom remark if auto generated
                            $log_data['remark'] = array('Display Name Auto Generated');

                        }

                        break;
                        
                    case 'password':

                        $type     = 'account';

                        $log_data = array(
                                        'description' => 'Password Change',
                                        'from'        => array(),
                                        'to'          => array()
                                    );

                        if ($process != 'Account Page') {
                            
                            // custom remark if auto generated
                            $log_data['remark'] = array(custom_ucwords($process.' '.$field));

                        }

                        break;

                    case 'loginName':

                        $type     = 'account';

                        $log_data = array(
                                        'description' => 'Contact Information',
                                        'from'        => array('Login Name' => $old_data[$field]),
                                        'to'          => array('Login Name' => $new_data[$field])
                                    );

                        break;
                }

                // check if there's data to be logged then continue logging
                if ($log_data!=false) {
                    
                    // check if field has custom remark
                   if (!isset($log_data['remark'])) {

                        $log_data['remark'] = array(custom_ucwords(camel_to_space($field)));

                    }

                    // get from old data who's logging this
                    $log_data['username']  = $old_data['username'];
                    $log_data['createdBy'] = $old_data['loginName'];

                    $this->db($log_data, $type);
                }

            }
        }

        return true;
    }


}
