<?php

$_INI = [];

function stop($type='dev', $msg = null)
{
    header("Content-Type: application/json; charset=utf-8");

    if ($msg !== NULL) {
        $msg = $type . ': ' . $msg;
    } else {
        $msg = $type;
    }

    Response::end($msg);
    exit(-1);
}

function cmp($a, $b)
{
    return $b["date_achieve"] - $a["date_achieve"];
}
function remove_tax($data)
{
    remove_field($data, ['tax_id', 'tax_type']);
    return $data;
}

function crop($arr, $column = [])
{
    $res = [];
    for ($i = 0, $len = sizeof($column); $i < $len; $i++) {
        if (isset($arr[$column[$i]]))
            $res[$column[$i]] = $arr[$column[$i]];
    }
    return $res;
}

function remove_field(&$data, $column = [])
{
    if (empty($data)) {
        return $data;
    }
    if (!isset($data[0])) {
        $data = [$data];
    }
    for ($i = 0, $len = sizeof($data), $lenC = sizeof($column); $i < $len; $i++) {
        for ($j = 0; $j < $lenC; $j++) {
            unset($data[$i][$column[$j]]);
        }
    }
    return $data;
}

function benchmark($func, $loop = 10000)
{
    $t = microtime(true);
    $m = memory_get_peak_usage(false);

    $i = 0;
    while ($i++ < $loop) {
        $func();
    }
    return ([
        'time' => microtime(true) - $t,
        'memory' => number_format((memory_get_peak_usage(false) - $m) / 1024, 3) . ' KB'
    ]);
}

function console($data = null)
{
    return json([
        'notice' => ini('notice'),
        'data' => $data
    ]);
}


function show()
{
    $args = func_get_args();
    $argc = func_num_args();

    if ($argc === 0) return show('FurCore Test ' . date('Y/m/d H:i:s'));
    if (is_bool($args[$argc - 1]) && $argc > 1) {
        $opt = true;
        array_pop($args);
        $argc--;
    } else {
        $opt = false;
    }

    if ($argc > 1) {
        for ($i = 0; $i < $argc; $i++) {
            show($args[$i], $opt);
        }
        return 0;
    }
    if (ini('document.type') === 'html')
        echo "\n<pre>";

    if (!isset($args[0]) || $args[0] === false)
        echo "false\n";
    elseif ($opt === false) {
        if (ini('system.mode') === 'api')
            json($args[0], false);
        else
            print_r($args[0]);
    } else {
        if (ini('system.mode') === 'api')
            json($args[0], true);
        else
            var_dump($args[0]);
    }
    if (ini('document.type') === 'html')
        echo '</pre>';
    return 0;
}


function json($arr, $exit = true)
{
    header("Content-Type: application/json; charset=utf-8");
    echo str_replace('\\u0000', "", json_encode($arr, 256));
    if ($exit)
        exit;

    return 0;
}

function ini($key = null, $value = null, $type = null, $separator = ' ')
{
    global $_INI;

    if ($key === null) {
        return $_INI;
    }

    if ($key && $value === null) {
        if (!isset($_INI[$key]))
            return null;

        if (!$type) {
            return $_INI[$key];
        }

        if ($type === 'link') {
            $a = &$_INI['key'];
            return $a;
        }

        if ($type === 'str') {
            if (is_callable($separator)) {
                return $separator($_INI['key']);
            } else {
                return implode($separator, $_INI['key']);
            }
        }

    } else {
        if ($type === 'arr' && !isset($_INI[$key])) {
            $_INI[$key] = [];
        }
        if (isset($_INI[$key]) && gettype($_INI[$key]) === 'array' && ($type === 'add' || $type === true)) {
            if (is_array($value)) {
                if ($separator === true) {
                    $_INI[$key] = array_merge($_INI[$key], $value);
                } else {
                    $_INI[$key][$value[0]] = $value[1];
                }
            } else {
                $_INI[$key][] = $value;
            }
        } else {
            $_INI[$key] = $value;
        }

    }
    return $_INI[$key];
}

function ini_r($key)
{
    global $_INI;
    return remove_item($_INI, $key, null);
}

function ini_push($key, $value, $index = null)
{
    global $_INI;
    if (!isset($_INI[$key])) {
        $_INI[$key] = [];
    }
    if (!is_array($_INI[$key])) {
        return Response::error('INI: variable "' . $key . '" - is not array');

    }
    if ($index) {
        $_INI[$key][$index] = $value;
    } else {
        $_INI[$key][] = $value;
    }
    return $_INI[$key];
}

function remove_items(&$arr, $keys, $options = [])
{
    $res = [];

    $options += [
        'isset' => false,
        'default' => NULL,
        'is_null' => false,
        'not_default' => true
    ];

    if ($options['isset']) {
        $options['default'] = '!!--!!';
    }
    for ($i = 0, $len = sizeof($keys); $i < $len; $i++) {
        $def = $options['default'];

        $has_option = is_array($keys[$i]);
        if ($has_option) {
            $key = $keys[$i][0];
            $def = $keys[$i][1]['default'] ?? $def;
            $required = $keys[$i][1]['required'] ?? NULL;
            $valid = $keys[$i][1]['valid'] ?? NULL;
            $error = $keys[$i][1]['error'] ?? function () {
            };
        } else {
            $key = $keys[$i];
        }

        $key = explode(' as ', $key);
        $item = remove_item($arr, $key[0], $def);

        $key = $key[1] ?? $key[0];
        if ($has_option) {
            if ($required && $item === $def) {
                $error(['item' => &$item, 'input' => $keys[$i], 'type' => 'required']);
            }
            if ($valid) {
                if ($valid[1] !== '~' || $valid[2] !== '~')
                    $valid = '/^[' . $valid . ']+$/';
                else {
                    $valid = '/' . $valid . '/';
                }
                if (!preg_match($valid, $item)) {
                    $error(['item' => &$item, 'input' => $keys[$i], 'type' => 'valid']);
                }
            }
        }
        if ($options['is_null'] || ($options['not_default'] && $item !== $options['default']))
            $res[$key] = $item;
    }
    return $res;
}


function remove_item(&$arr, $key, $def = '')
{
    if (is_array($arr) && array_key_exists($key, $arr)) {
        $a = $arr[$key];
        unset($arr[$key]);
        return $a;
    } else {
        return $def;
    }
}

function def($a, $b)
{
    if ($a === false || $a === null) return $b;
    return $a;
}


if (!function_exists('mb_ucfirst')) {
    function mb_ucfirst($string, $enc = 'UTF-8'){
        return mb_strtoupper(mb_substr($string, 0, 1, $enc), $enc) .
            mb_substr($string, 1, mb_strlen($string, $enc), $enc);
    }
}

if (!function_exists('mb_lcfirst')) {
    function mb_lcfirst($string, $enc = 'UTF-8'): string
    {
        $arr = explode(' ', $string);

        for ($i = 0, $len = sizeof($arr); $i < $len; $i++) {
            $arr[$i] = mb_ucfirst($arr[$i], $enc);
        }
        return implode(' ', $arr);
    }
}


function is_assoc($arr)
{
    if (!is_array($arr)) return null;
    if ([] === $arr) return false;
    return array_keys($arr) !== range(0, count($arr) - 1);
}

function get_option($key = null, $def = null)
{
    global $conf;

    if ($key === null) {
        return $conf;
    }

    if (isset($conf[$key])) {
        return $conf[$key];
    } else {
        return $def;
    }
}

function set_option($key, $val, $replace = true)
{
    global $conf;
    if (isset($conf[$key]) && $replace) {
        $conf[$key] = $val;
    }
    return $conf[$key];
}

function debug($data, $name = null, $limit = 3)
{
    global $conf;
    $meta = debug_backtrace();
    $buf = [];
    if (!$limit)
        $len = sizeof($meta);
    else
        $len = $limit;
    for ($i = 0; $i < $len; $i++) {
        if (!isset($meta[$i]['file'])) break;
        $buf[] = str_replace('/', '->', explode($conf['DIR_ROOT'] . '/', $meta[$i]['file'])[1] ?? '') .
            '(' . $meta[$i]['line'] . ') [' . $meta[$i]['function'] . ']';
    }
    ini_push('notice', [$data, $buf], $name);
    return $data;
}

function notice($msg, $name = null, $group = true)
{
    if ($name !== NULL && $group) {
        $new_msg = ini('notice')[$name] ?? null;
        if ($new_msg === null) {
            $new_msg = [$msg];
        } else {
            if (!is_array($new_msg)) {
                $new_msg = [$new_msg];
            }
            $new_msg[] = $msg;
        }
    } else
        $new_msg = $msg;


    ini_push('notice', $new_msg, $name);
}

function info($msg, $name = null)
{
    ini_push('info', $msg, $name);
}

function getBearerToken(){
    if (isset($_COOKIE['token']))
        return $_COOKIE['token'];

    if (isset($_GET['token']))
        return $_GET['token'];

    $headers = trim($_SERVER['Authorization'] ?? $_SERVER['HTTP_AUTHORIZATION'] ?? '');
    if (!empty($headers) && preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
        return $matches[1];
    }
    return null;
}


function str_search($reg, $search)
{
    return preg_match($reg, $search, $match) ? -1 : $match[1];
}


function get_data($res)
{
    if (isset($res['error'], $res['data'], $res['status'])) {
        return $res['data'];
    }
    return $res;
}

function parse_id($id)
{
    if (is_string($id) && str_contains($id, ',')) {
        $id = explode(',', $id);
        return array_map(fn($id) => (int)$id, $id);
    }
    return (int)$id;
}

spl_autoload_register(function ($class_name) {
    if (str_contains($class_name, 'CI_')) {

        $class_name = substr($class_name, 3);
        $class_name = ucfirst($class_name);

        $path_start = DIR . '/controller/';
        $path = $path_start . $class_name . '.php';

        if (file_exists($path)) {
            include DIR . '/controller/' . $class_name . '.php';
        } else {

            $arrName = preg_split('/(?=[A-Z])/', $class_name);

            do {
                $path_start .= array_shift($arrName) . '/';
                $path = $path_start . implode('', $arrName) . '.php';
            } while (sizeof($arrName) && !file_exists($path));

            include $path;

        }

    } else {
        if (file_exists(DIR . '/library/' . $class_name . '.php')) {
            include DIR . '/library/' . $class_name . '.php';
        } else {
            preg_match('~([A-Z][^A-Z]+)?([A-Z][^A-Z]+)?~', $class_name, $match);
            if ($match[1] === 'Model') {
                include DIR . '/model/' . $match[2] . '/Models/' . $class_name . '.php';
            } else {
                include DIR . '/model/' . $match[1] . '/' . $class_name . '.php';
            }

        }
    }
});

function is_done($data = [], $res = null)
{
    if (!empty($data))
        Response::push('not_used_data', $data);

    return $res ?? !ini('IS_ERROR');
}



function get_ids($res, $column='id'){
    return array_map('intval', array_values(array_unique(array_column($res, $column))));
}

function get_file_ext($filename){
    $ext = explode('.', $filename);
    return strtolower(end($ext));
}

function safe_include(string $file, $def=[]){
    return file_exists($file) ? include $file: $def;
}
function safe_define(string $name, string $value){
    $res = !defined($name);
    if($res){
        define($name, $value);
    }
    return $res;
}

function take(&$arr, $key, $def=null){
    if(!isset($arr[$key])) return $def;
    $a = $arr[$key];
    unset($arr[$key]);
    return $a;
}

function takes(&$arr, $keys, $options=[]){
    $res = [];
    $def = $options['def'] ?? null;
    $takeNull = $options['takeNull'] ?? false;
    foreach($keys as $key){
        if(!isset($arr[$key])){
            if($takeNull){
                $res[$key] = $def;
            }
            continue;
        }
        $res[$key] = $arr[$key];
        unset($arr[$key]);
    }
    return $res;
}
