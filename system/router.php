<?php
/**
 * @var $db db
 */

ini('system.mode', 'api');
ini('document.type', 'json');
define('URL_QUERY', $_SERVER['REQUEST_URI']);

$short_return = explode(':', substr(URL_QUERY, 1));
$url_param = explode('?', $short_return[0]);

if (isset($short_return[1])) {
    $_REQUEST['return'] = $short_return[1];
}
$router = $url_args = explode('/', $url_param[0]);
if (end($router) === '') {
    array_pop($router);
}

event('router.before', ['router' => &$router]);


$routers = include DIR . '/config/router.php';
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



$method_args = null;
if(is_numeric(end($router_path))){
    $method_args = array_pop($router_path);
}

$router_path = array_map(fn($v) => ucfirst($v), $router_path);



if(empty($router_path)){
    $class_name = 'Base';
    $method_name = 'root';
}else{
    $DIR_CONTROLLER = DIR . '/controller/';
    $temp_method = null;
    while($router_path){
        if (file_exists($DIR_CONTROLLER . implode('/', $router_path).'.php')) {
            $fileCtrl = array_pop($router_path);
            $class_name = implode('', $router_path).$fileCtrl;
            $method_name = $temp_method;
            break;
        }
        $temp_method= array_pop($router_path);
    }
    if(empty($class_name)){
        $class_name = get_option('router.class_404');
        $method_args = $class_name;
        $method_name = 'not_exists_class';
    }
}
$obj = 'CI_' . $class_name;

if (!class_exists($obj)) {
    $class_name = get_option('router.class_404');
    $method_args = $class_name;
    $method_name = 'not_exists_class';

}
$obj = new $obj;

if (!$method_name || !method_exists($obj, $method_name)) {
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
    }
    unset($_method);
}


event('router.after', [
    'class' => &$class_name,
    'method' => &$method_name,
    'param' => &$url_param
]);

if (!$method_name || !method_exists($obj, $method_name)) {

    if (method_exists($obj, 'def')) {
        $method_args = array_slice($url_args, 1);
        $method_name = 'def';
    } else {
        include DIR . '/controller/Base.php';
        $obj = new CI_Base;
        $method_args = $method_name;
        $method_name = 'error_404';
    }

    ini('router.method_name', $method_name);
}

ini('router.method_name', $method_name);
if ($method_name && $method_name === get_option('router.method_404')) {
    $method_args = $router;
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

$res = call_user_func([$obj, $method_name], $method_args);
json(Response::out($res));
