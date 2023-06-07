<?php
/**
 * @var Users $this
 * @var string|int $value
 * @var string $columns
 */

$duration = time()+get_option('user.duration', 365 * 24 * 60 * 60);

$where = [
   // 'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'php',
];

if(!$value) {
    $where['token'] = getBearerToken();
    if(!$where['token'] ){
//        send_telegram($where);
        return [];
        //return Response::error('Not send token', 401);
    }

} else if(is_numeric($value)){
    $where['user_id'] = $value;
}else{
    $where['token'] = $value;
}


$res = ModelUsersToken::select($columns)
    ->where($where)
    ->where(['status' => [0, '>']])
    ->order('date_expiration', 'DESC')
    ->one();

if(!$res){
    return Response::error('Not fined token', 401);
}else{
    if ( $res['date_expiration'] < time()) {
        return Response::error('Токен не действительный', 401);
    }
    return $res;
}


