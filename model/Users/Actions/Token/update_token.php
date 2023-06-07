<?php

/**
 * @var $token
 */

$token = !$token ? getBearerToken(): $token;
$duration = time()+get_option('user.duration', 365 * 24 * 60 * 60);

ModelUsersToken::update([
    'date_expiration' => $duration,
    'last_connect' => 'NOW()',
    'ip' => $_SERVER['REMOTE_ADDR'],
], ['token' => $token]);

if (!ini('IS_ERROR')) {
    setcookie('token', $token, $duration, '/', URL_DOMAIN);
    return [
        'token' => $token,
        'date_expiration' => $duration,
    ];
}
return false;
