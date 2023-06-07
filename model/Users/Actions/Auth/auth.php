<?php global $USER;
/**
 * @var Users $this
 * @var array $params
 */

[$params, $extends] = $this->init_action([
    'params'            => $params,
    'extends'           => ['user'],
    'extends_default'   => ['user']
]);


$token_data = get_data($this->get_token(null, ['token', 'date_expiration', 'user_id']));
// CHECK:


$extends_merge = ['avatar','phones','access','data','representative','federations','regions','organizations','departments','groups','agreements'];
$params_merge = ['extends' => $extends_merge, 'return' => 'one'];

if (!empty($token_data)) {
    $res = $this->get(['u.id' => $token_data['user_id']], $params_merge);
}

if(empty($res) || empty($token_data)){
    $res = $this->get(['u.id' => 1], $params_merge);
    $duration = time()+get_option('user.duration', 365 * 24 * 60 * 60);
    setcookie('token', '', $duration, '/', URL_DOMAIN);
}

if(!empty($res) && $res['id'] !== 1){
    $res['token'] = remove_items($token_data, ['token', 'date_expiration', 'date']);
    $this->update_token();
}

$res['ip'] = $_SERVER['REMOTE_ADDR'];
$USER = $res;



return $res;
