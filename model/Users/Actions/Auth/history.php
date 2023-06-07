<?php
/**
 * @var Users $this
 * @var array|int $filters
 * @var array|null $params
 */

use Browser\Browser;

[$extends, $params, $filters] = $this->init_action([
    'extends' => [
        'token'
    ],
    'extends_default' => [

    ],
    'params' => $params,
    'params_default' => [
        'meta' => false
    ],
    'filters' => $filters,
    'model' => 'ModelUsersLoginHistory'
]);
if(!isset($filters['users_login_history.user_id'])) {
    $filters['user_id'] = user_id();
}else{
    $filters['user_id'] = remove_item($filters, 'users_login_history.user_id');
}

$res = ModelUsersLoginHistory::select()
    ->where($filters)
    ->params($params);

$res = $res->run();

$ids = get_ids($res);



$browser = new Browser();

foreach ($res as &$v) {
    $browser->__construct($v['user_agent']);
    $v['browser'] = $browser->getBrowser();
    $v['isMobile'] = $browser->isMobile();
    $v['platform'] = $browser->getPlatform();
}

if ($extends['token']) {
    $token = $this->active_token_get(['user_id' => $filters['user_id']]);
    return [
        'token' => $token,
        'history' => $res
    ];
}




return $res;
