<?php

namespace Backend\services;

use Exception;
use Config;           
use Mail;     

class Semails extends Baseservice {
    
    /**
     * Main process for e-mail sending.
     * @param  array    $data
     * @return boolean
     */
    public function send($data)
    {
        try {
            
            Mail::send($data['view'], $data, function( $message ) use ( $data ) {

                $message_id = $message->getHeaders()->get('Message-ID');
                $message_id->setId(sha1(microtime()) . "@" . Config::get('mail.host'));
                $message->to($data['to'])->subject($data['subject']);
                
            });

            $failures = Mail::failures();

            if ($failures) {

                $this->log($data, $failures);
                return FALSE;
            }

        } catch (Exception $ex) {
            
            $this->log($data, $ex->getMessage());
            return FALSE;

        }
        
        return TRUE;
    }

    /**
     * This will log email errors
     * @param  array  $data  
     * @param  mixed  $error
     * @return void
     */
    private function log($data, $error)
    {
        $this->service('Slogger')->file(array(
            'email_data'    => $data,
            'error'         => $error
        ), 'EMAIL_ERROR');
    }
}