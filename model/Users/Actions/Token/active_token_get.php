<?php
/**
 * @var Users $this
 * @var array|int $filters
 * @var array|null $params
 */


use Browser\Browser;

[$extends, $params, $filters] = $this->init_action([
    'extends' => [
    ],
    'extends_default' => [
    ],
    'params' => $params,
    'params_default' => [
        'columns' => ModelUsersToken::columnsSafe(),
        'meta' => true
    ],
    'filters' => $filters,
    'filters_default' => [
//        'u.status' => 1,
    ],
    'model' => 'ModelUsersToken',
]);


$sql = $this->dbBuild();


if(!isset($filters['user_id'])){
    $filters['user_id'] = user_id();
}
$sql->model('ModelUsersToken')
    ->where($filters)
    ->select($params['columns']);

$res = $sql->run();

$ids = get_ids($res);

$browser = new Browser();

foreach ($res as &$v) {
    $browser->__construct($v['user_agent']);
    $v['browser'] = $browser->getBrowser();
    $v['isMobile'] = $browser->isMobile();
    $v['platform'] = $browser->getPlatform();
}

return $res;
