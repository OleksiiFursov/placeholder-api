<?php

class Errors
{

    public static function captureNormal($number, $message, $file, $line)
    {
        if (!defined('IS_DEV')) {

            @file_put_contents(DIR . '/logs/error.log', 'Error ' . $number . ', ' . $message . ' <p><strong>' . $file . ' line ' . $line . ' </strong></p>', FILE_APPEND);
            return;
        }

        Response::end([
            'code' => $number,
            'message1' => $message,
            'file' => $file,
            'line' => $line
        ]);


    }


    public static function captureException($exception)
    {

        if (get_option('document.type') === 'json') {
            $obj = (array)$exception;
            Response::$exception = $obj;
            Response::end(current($obj));
        } else {
            show($exception);
        }

    }

    public static function captureShutdown()
    {


        $error = error_get_last();
        if ($error) {
            if (!get_option('document.type') || get_option('document.type') === 'json') {
                Response::error($error);
            } else {
                show($error);
            }

        }

        return true;
    }

    public static function show_404()
    {
        header('HTTP/1.1 404 Not Found');
        header("Status: 404 Not Found");
    }
}
