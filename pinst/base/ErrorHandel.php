<?php
/**
 * Created by PhpStorm.
 * User: smile
 * Date: 16-1-6
 * Time: 下午3:24
 */

namespace pinst\base;


class ErrorHandel extends Object
{

    public function init()
    {
        parent::init();
        \Pinst::$app->registerShutdownFunction(function($app){
            $app->errorHandel->unregister();
        });
    }


    public function register(){
        ini_set('display_errors', false);
        set_exception_handler([$this, 'handleException']);
        set_error_handler([$this, 'handleError']);
        register_shutdown_function([$this, 'handleFatalError']);
    }

    /**
     * when application exception this function will be called
     * @param $exception
     */
    public function handleException($exception){
        echo "-------------------------------\n";
        var_dump($exception);
        die();
    }

    /**
     * when application obtain error this function will be called
     * @param $code error code
     * @param $message error message
     * @param $file means what file is error
     * @param $line means error line
     */
    public function handleError($code, $message, $file, $line){
        \Pinst::error("#{$code} {$message}\n\tin {$file}({$line})");
    }

    public function handleFatalError(){
        $error = error_get_last();
        if(empty($error)){
            return;
        }
        if (in_array($error['type'],array(E_ERROR,E_WARNING))) {
            \Pinst::error("{$error['message']}\n\tin{$error['file']}({$error['line']})");
        }
    }

    public function unregister(){
        restore_error_handler();
        restore_exception_handler();
    }

}