<?php

/**
 * @var $user_id
 */

$token = md5(time() . $user_id . rand(0, 10000));
$duration = time()+get_option('user.duration', 365 * 24 * 60 * 60);

ModelUsersToken::insert([
    'token' => $token,
    'user_id' => +$user_id,
    'date_expiration' => $duration,
    'last_connect' => 'NOW()',
    'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
    'ip' => $_SERVER['REMOTE_ADDR'],
    'referer' => $_SERVER['HTTP_REFERER'] ?? ''
]);

if (!ini('IS_ERROR')) {
    setcookie('token', $token, $duration, '/', URL_DOMAIN);
    $_COOKIE['token'] = $token;

    return [
        'token' => $token,
        'date_expiration' => $duration,
    ];

}
return false;
