<?php
/**
 * @var Users $this
 * @var $params
 */

[$params] = $this->init_action([
    'params' => $params,
    'params_default' => [
        'all'   => false
    ]
]);

if(isset($params['token']) && $params['token']){
    $token = $params['token'];
}else{
    $token = getBearerToken();
}


$duration = time()+get_option('user.duration', 365 * 24 * 60 * 60);
setcookie('token', '', $duration, '/', URL_DOMAIN);
if(!$token){
    return Response::error('Пользователь не авторизован');
}



if($params['all']){
    [$user_id] = ModelUsersToken::findOne(['token' => $token], ['user_id'], 2);

    if($user_id){
        $where = ['user_id' => (int)$user_id];
    }else{
        $where = ['token' => $token];
    }
}else{
    $where = ['token' => $token];
    $tokenData = ModelUsersToken::findOne($where);
    $_tokenData = remove_items($tokenData, ModelUsersLoginHistory::getColumnsForInsert());
    $_tokenData['date_created'] = date('Y-m-d H:i:s', $tokenData['date_created']);
    ModelUsersLoginHistory::insert($_tokenData);
}



event('users.logout');
ModelUsersToken::query()->delete($where)->run();
return Response::success();







