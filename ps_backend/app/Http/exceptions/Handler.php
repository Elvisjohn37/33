<?php

namespace Backend\exceptions;

use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Foundation\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Config;
use Response;
use Layer;
use Request;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        AuthorizationException::class,
        HttpException::class,
        ModelNotFoundException::class,
        ValidationException::class,
    ];

    protected $dont_log = array(
                            'Svalidateexception' 
                        );

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception  $e
     * @return void
     */
    public function report(Exception $e)
    {
        $exception_type=basename(get_class($e));

        if (!in_array($exception_type, $this->dont_log)) {

            $error=array(
                'exception'     => $exception_type,
                'error_message' => $e->getMessage(),
                'error_code'    => $e->getCode(),
                'error_file'    => $e->getfile(),
                'error_line'    => $e->getLine()
            );

            Layer::service('Slogger')->file($error);
            
        }

        return parent::report($e);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $e
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $e)
    {
        $exception_type=remove_namespace(get_class($e));

        switch($exception_type) {

            case 'Svalidateexception':
                $decoded_message = json_decode($e->getMessage(), true);

                if (Request::ajax()) {

                    return Response::json($decoded_message);

                } else {

                    return $this->error_page($decoded_message);

                }

            default:

                // exceptions that depends on app.debug config
                if (Config::get("app.debug")==false) {

                    switch($exception_type) {

                        case 'NotFoundHttpException': 

                            return $this->error_page(array('err_code' => 'ERR_00071'), 404);

                        default:                      

                            return $this->error_page(array('err_code' => 'ERR_00001'), 500);

                    }

                } else {
        
                    return parent::render($request, $e);

                }

        }
    }

    /**
     * Dispatch error page
     * @param  array  $err_message 
     * @param  int    $status_code 
     * @return object
     */
    private function error_page($err_message, $status_code = 200)
    {
        return Layer::service('Sview')->create(array(
            'route' => 'error_window',
            'input' => $err_message,
            'param' => ''
        ))->setStatusCode($status_code);
    }
}