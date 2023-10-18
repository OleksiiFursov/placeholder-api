<?php

define('START_TIME', microtime(true));
define('START_MEMORY', memory_get_peak_usage());
define("IS_DEV", true);


ini_set('error_reporting', -1);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
require 'boot.php';

add_event('router.before', function($args){

    $token = array_shift($args['router']);
    ini('user.token', $token);
    if ($token && preg_match('#^[0-9a-f]+$#', $token)) {
        add_event('DB_build.run', function ($args) use ($token) {
            if($args['context']->type !== 'insert'){
                $args['context']->where(['tu' => $token]);
            }
        });


        $q = ModelSysUsers::findOne();
        if (!$q) {
            Response::end('Не верный токен в URL', 403);
        }
        ini('sys_user', $q);
    } else {
        Response::end('Не передан в токен в URL', 403);
    }
});
require DIR.'/system/router.php';
