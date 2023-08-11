<?php

class Response
{
    static $error = [];
    static $status = null;
    static $other = [];
    static $warn = [];
    static $exception = null;

    static function error($err, $status = NULL)
    {


        self::$status = $status === null ? self::$status ?? 500 : $status;

        ini('IS_ERROR', true);


//        if (is_array($err)) {
//
//            $buf = [];
//            foreach ($err as $name => $value) {
//                $buf[] = $name . ':' . (!is_array($value) ? $value : json_encode($value, 256));
//            }
//
//            //$text = implode('%0A', $buf);
//
//        } else {
//            //$text = 'message: ' . $err;
//        }

        return self::out([], $err, $status);
    }

    static function warn($msg)
    {
        self::$warn[] = $msg;
        return true;
    }

    static function success($data = [], $status = NULL)
    {
        return self::out($data, NULL, $status);
    }

    static function set($key, $value)
    {
        self::$other[$key] = $value;
    }

    static function out($data = [], $error = NULL, $status = NULL)
    {
        $is_dev = (defined('IS_DEV') && IS_DEV);


        if ($error) {
            self::$error[] = $error;
        }
        $status = $status ?? self::$status;

        if (!sizeof(self::$error) && !$status) {
            $status = 200;
        } else if ($status === 200) {
            $status = 500;
        }
        $res = [];
        if (ini('notice')) {
            $res['notice'] = ini('notice');
        }

        if (isset($data['data']) && isset($data['error'])) {
            $res += [
                'data' => $data['data'],
                'error' => self::$error,
            ];
        } else {
            $res += [
                'data' => $data,
                'error' => self::$error,
            ];
        }

        if (isset(self::$other['not_used_data'])) {
            $res['not_used_data'] = self::$other['not_used_data'];
        }
        if (ini('ERROR_DB') || $is_dev) {
            global $db;
            $res['sql_last'] = $db->last_query ?? null;
            $res['sql_history'] = $db->history ?? null;

        }
        if (self::$exception) {
            $res['exception'] = self::$exception;
        }
        foreach (self::$other as $key => $val) {
            $res[$key] = $val;
        }

        if ($is_dev) {
            $res['time'] = number_format(microtime(true) - START_TIME, 5) . 's';
            $res['memory'] = number_format((memory_get_peak_usage(false) - START_MEMORY) / 1024, 3) . ' KB';
        }
        if ($status > 99) {
            http_response_code($status);
            $res['status'] = $status;
        } else if (!$status) {
            http_response_code(500);
        }

        return $res;
    }

    static function push($key, $value)
    {
        if (!isset(self::$other[$key])) {
            self::$other[$key] = [];
        }
        self::$other[$key][] = $value;
    }

    static function end($error = NULL, $status = NULL)
    {

        return json(self::out([], $error, $status));
    }


}
