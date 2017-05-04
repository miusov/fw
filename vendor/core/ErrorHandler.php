<?php

namespace vendor\core;


class ErrorHandler
{
    public function __construct()
    {
        if (DEBUG)
        {
            error_reporting(-1);
        }
        else
        {
            error_reporting(0);
        }
        set_error_handler([$this, 'errorHandler']);
        ob_start();
        register_shutdown_function([$this, 'fatalErrorHandler']);
        set_exception_handler([$this, 'exceptionHandler']);
    }

    public function errorHandler($errno, $errstr, $errfile, $errline)
    {
        $this->logErrors($errstr, $errfile, $errline);
        $this->displayError($errno, $errstr, $errfile, $errline);

        return true;
    }

    public function fatalErrorHandler()
    {
        $error = error_get_last();
        if (!empty($error) && $error['type'] & ( E_ERROR | E_PARSE | E_COMPILE_ERROR | E_CORE_ERROR))
        {
            $this->logErrors($error['message'],$error['file'],$error['line']);
            ob_end_clean();
            $this->displayError($error['type'], $error['message'], $error['file'], $error['line']);
        }
        else
        {
            ob_end_flush();
        }
    }

    public function exceptionHandler($e)
    {
        $this->logErrors($e->getMessage(),$e->getFile(),$e->getLine());
        $this->displayError('Exception', $e->getMessage(), $e->getFile(), $e->getLine(), $e->getCode());
    }

    protected function logErrors($message = '', $file = '', $line = '')
    {
        error_log("[".date('Y-m-d H:i:s')."] ErrorText: {$message} | ErrorFile: {$file} | ErrorString: {$line}\n \n",3,ROOT.'/tmp/ERRORS.log');
    }

    protected function displayError($errno, $errstr, $errfile, $errline, $response = 500)
    {
        http_response_code($response);
        if ($response == 404)
        {
            require_once WWW.'/errors/404.html';
            die;
        }
        if (DEBUG)
        {
            require_once WWW.'/errors/development.php';
        }
        else
        {
            require_once WWW.'/errors/production.php';
        }
        die;
    }
}