<?php
/**
 * @var Users $this
 * @var array $params
 */

[$extends, $params] = $this->init_action([
    'extends' => ['users', 'token'],
    'extends_default' => ['users', 'token'],
    'params' => $params,
    'params_default' => [
        'login' => null,
        'email' => null,
        'password' => null,
        'disabled_error' => false,
        'user_id' => null
    ]
]);


$disabled_error = remove_item($params, 'disabled_error');
$login = remove_item($params, 'login', '');
$password = remove_item($params, 'password', '');


$methodHandler = $disabled_error ? 'warn' : 'error';

if (!$login || !$password) {
    return Response::error("The login or password is empty :(", 401);
}

$data = ModelUsers::findOne(['name' => $login], ['password', 'id', 'status']);

if (!$data) {
    return Response::error('Wrong login:(', 401);
}

if (!$data['status']) {
    return Response::error('User is disabled', 401);
}

if (!password_verify($password, $data['password'])) {
    return Response::{$methodHandler}('Wrong password :(', $disabled_error ? 200 : 401);
}
$where = $data['id'];
$user_id = $data['id'];

$res = [];

if ($extends['users']) {
    $res += $this->get($where,
        [
            'limit' => 1,
        ]
    );
}

$token_data = $this->create_token($user_id);
if ($extends['token']) {
    $res['token'] = remove_items($token_data, ['token', 'date_expiration', 'date']);

}

event('users.login.after', [&$name, &$password, &$res]);

return $res;
