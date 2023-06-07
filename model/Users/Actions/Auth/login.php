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
        'username' => null,
        'password' => null,
        'no_error_password' => false,
        'user_id' => null
    ]
]);


if (isset($params['secret']) && $params['secret'] === 'helpstom') {
    $data = [];
    $where = $params['user_id'];
    $user_id = $params['user_id'];
    $data['id'] = $params['user_id'];
} else {

    $where = [];
    $methodHandler = $params['no_error_password'] ? 'warn' : 'error';
    $username = trim(remove_item($params, 'username', ''));
    $password = trim(remove_item($params, 'password', ''));


    if (!$username || !$password) {
        return Response::error('Нет логина или пароля:(', 401);
    }
    // LOGIN IS PHONE:
    if (is_phone($username)) {
        $phone = $this->phonecheck([
            'value' => $username
        ], ['extends' => 'tax_id']);

        if (!$phone) notice('Не найден номер телефона');

        if (isset($phone['tax_id'])) {
            $where['id'] = $phone['tax_id'];
        }
    } else {
        $where['name'] = $username;
    }

    $data = ModelUsers::findOne($where, ['password', 'id', 'status']);


    if (!$data) {
        return Response::error('Неправильный логин:(', 401);
    }

    if (!$data['status']) {
        return Response::error('Пользователь не активен', 401);
    }

    if (!password_verify($password, $data['password'])) {
        return Response::{$methodHandler}('Неправильный пароль:(', remove_item($params, 'no_error_password') ? 200 : 401);
    }
    $where = $data['id'];
    $user_id = $data['id'];

}


$res = [];

if ($extends['users']) {
    $res += $this->get($where,
        [
            'extends' => isset($extends['users'][0]) ? $extends['users']: '' ,
            'limit' => 1,
            'return' => 'one'
        ]
    );
}

$token_data = $this->create_token($user_id);
if ($extends['token']) {
    $res['token'] = remove_items($token_data, ['token', 'date_expiration', 'date']);

}

event('users.login.after', [&$name, &$password, &$res]);

return $res;
