<?php
/**
 * @var $db db
 */
$routers = include DIR . '/config/router.php';
ini('system.mode', 'api');
ini('document.type', 'json');
define('URL_QUERY', $_SERVER['REQUEST_URI']);

$short_return = explode(':', substr(URL_QUERY, 1));
$url_param = explode('?', $short_return[0]);


if (isset($short_return[1])) {
    $_REQUEST['return'] = $short_return[1];
    $_REQUEST['limit'] = 1;
}
$router = $url_args = explode('/', $url_param[0]);

$token = array_shift($router);

if (preg_match('#^[0-9a-f]+$#', $token)) {
    ini('DB_build:init', function (&$context) use ($token) {
        $context->where(['token' => $token]);
    });

    $q = ModelUsers::findOne();
    if (!$q) {
        Response::end('Не верный токен в URL', 403);
    }
    ini('sys_user', $q);
} else {
    Response::end('Не передан в токен в URL', 403);
}


foreach ($routers as $r_url => $r_values) {
    if (preg_match('#' . $r_url . '#', $short_return[0], $matches)) {
        $short_return[0] = array_shift($r_values);
        foreach ($r_values as $index => $col) {
            $_REQUEST[$col] = $matches[$index + 1] ?? null;
        }
        break;

    }
}


$router_path = $router;

if (end($router_path) === '') {
    array_pop($router_path);
}


$router_path = array_map(fn($v) => ucfirst($v), $router_path);

$method_args = [];

if(sizeof($router_path) === 1){
    $class_name = array_pop($router_path);
    $router_path = '';
    $method_name = '';
}else{
    while ($router_path) {
        $method_args[] = array_pop($router_path);

        if (file_exists(DIR . '/controller/' . implode('/', $router_path) . '.php')) {
            $method_name = $method_name ?? strtolower(array_pop($method_args));
            $class_name = implode('', $router_path);
            $router_path = implode('/', $router_path);
            $router = array_reverse($method_args);
            break;
        }

    }
}


if (!isset($class_name)) {
    $class_name = get_option('router.class_404');
    $router_path = $class_name;
    $method_name = 'not_exists_class';

}

$class_name_lc = lcfirst($class_name);
$obj = 'CI_' . $class_name_lc;


$obj = new $obj;

if (!isset($short_return[1]) && !$method_name && !method_exists($obj, $method_name)) {

    $_method = [
        'GET' => '_get',
        'PATCH' => '_patch',
        'POST' => '_post',
        'DELETE' => '_delete',
        'VIEW' => '_get'
    ];
    $method_name_magic = $_method[$_SERVER['REQUEST_METHOD']];
    if(method_exists($obj, $method_name_magic)){
        $method_name = $method_name_magic;
        $router_path .= $method_name;
    }

    unset($_method);
}



event('router.after', [
    'class' => &$class_name,
    'method' => &$method_name,
    'param' => &$url_param
]);
if (isset($method_args[0]) && isset($method_args[1])) {
    $_met = lcfirst($method_args[0]) . $method_args[1];
    if (method_exists($obj, $_met)) {
        array_splice($method_args, 0, 2);
        $method_name = $_met;
    }
}

if (!$method_name || !method_exists($obj, $method_name)) {

    if (method_exists($obj, 'def')) {
        $router = array_slice($url_args, 1);
        $method_name = 'def';
    } else {
        include DIR . '/controller/Base.php';
        $obj = new CI_Base;
        $router = [$method_name];
        $method_name = 'NOT_FOUND';
    }

    ini('router.method_name', $method_name);
}

ini('router.method_name', $method_name);
if ($method_name && $method_name === get_option('router.method_404')) {
    $router = $method_args;
}


if (isset($url_param[1])) {
    ini('router.get', $url_param[1]);
    $str = explode('&', urldecode($url_param[1]));
    foreach ($str as $v) {
        $v = explode('=', $v);
        if (isset($v[1]))
            $_REQUEST[$v[0]] = $v[1];
    }
}


ini('router.args', $router);
event('onLoad.before');

$router = empty($router) ? [null] : array_map(fn($item) => urldecode($item), $router);

// ACCESS:
if (isset($router[0]) && is_numeric($router[0])) {
    $router[0] = parse_id($router[0]);
}


$res = call_user_func([$obj, $method_name], ...$router);
json(Response::out($res));
