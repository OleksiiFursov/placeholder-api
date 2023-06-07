<?php
$routers = include DIR . '/config/router.php';
ini('system.mode', 'api');
ini('document.type', 'json');
define('URL_QUERY', $_SERVER['REQUEST_URI']);

$short_return = explode(':', substr(URL_QUERY, 1));

foreach ($routers as $r_url => $r_values) {
    if (preg_match('#' . $r_url . '#', $short_return[0], $matches)) {
        $short_return[0] = array_shift($r_values);
        foreach ($r_values as $index => $col) {
            $_REQUEST[$col] = $matches[$index + 1] ?? null;
        }
        break;

    }
}


$url_param = explode('?', $short_return[0]);


if (isset($short_return[1])) {
    $_REQUEST['return'] = $short_return[1];
    $_REQUEST['limit'] = 1;
}
$router = $url_args = explode('/', $url_param[0]);
$router_path = $router;

if (end($router_path) === '') {
    array_pop($router_path);
}


if ($_SERVER['REQUEST_METHOD'] !== 'POST' && !isset($short_return[1])) {
    $_method = [
        'GET' => 'get',
        'PATCH' => 'update',
        'PUT' => 'add',
        'DELETE' => 'delete',
        'VIEW' => 'get'
    ];

    $method_name = $_method[$_SERVER['REQUEST_METHOD']];
    $router_path[] = $method_name;
    unset($_method);
}


$router_path = array_map(fn($v) => ucfirst($v), $router_path);

$method_args = [];

while ($router_path) {
    $method_args[] = array_pop($router_path);


    if (file_exists(DIR . '/controller/' . implode('/', $router_path) . '.php')) {
        $access_path = strtolower(implode('.', $router_path));
        $method_name = $method_name ?? strtolower(array_pop($method_args));
        $class_name = implode('', $router_path);
        $router_path = implode('/', $router_path);
        $router = array_reverse($method_args);
        break;
    }

}


if (!isset($class_name)) {
    $class_name = get_option('router.class_404');
    $router_path = $class_name;
    $method_name = 'not_exists_class';
}

$class_name_lc = lcfirst($class_name);
$obj = 'CI_' . $class_name_lc;
/** @noinspection PhpUndefinedVariableInspection */
ini('router.method_name', $method_name);
$obj = new $obj;


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

if (!method_exists($obj, $method_name)) {


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
    $access_path = null;
} else {

    if ($class_name !== get_option('router.class_404'))
        $access_path .= '.' . $method_name;
}


if ($method_name === get_option('router.method_404')) {
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


if (isset($access_path) && !$obj->is_public) {
    Access::strict_check($access_path);
}

if (isset($router[0]) && is_numeric($router[0])) {
    $router[0] = parse_id($router[0]);
}


$res = call_user_func([$obj, $method_name], ...$router);
json(Response::out($res));
