<?php
/**
 * @var Users $this
 * @var array|int $filters
 * @var array|null $params
 */

[$extends, $params, $filters] = $this->init_action([
    'extends' => [
        'users',
        'representative'
    ],
    'extends_default' => [],
    'params' => $params,
    'params_default' => [
        'columns' => ModelUsersRepresentative::columnsSafe(),
        'meta' => true
    ],
    'filters' => $filters,
    'filters_default' => [
    ],
    'model' => 'ModelUsersRepresentative'
]);

$sql = $this->dbBuild();


/********* EXTENDS PRE *********/
$sql->select($params['columns'])
    ->model('ModelUsersRepresentative')
    ->where($filters)
    ->order($params['order'], $params['order_dir'])
    ->limit($params['offset'], $params['limit'])
    ->merge($params['sql']);

$res = $sql->run();


$idsUsers = get_ids($res, 'user_id');

$idsRepresentative = get_ids($res, 'representative_id');


if($extends['users']){
    $users = $this->get(['id' => $idsRepresentative], ['extends'=>'+phones,emails,address,avatar']);
    $users = array_group_callback($users, 'id', true);
}
if($extends['representative']){
    $users = $this->get(['id' => $idsUsers], ['extends'=>'+phones,emails,address,avatar']);
    $users = array_group_callback($users, 'id', true);
}

foreach ($res as &$v){
    if ($extends['users']) {
        $v['user'] = $users[$v['representative_id']][0] ?? null;
    }
    if ($extends['representative']) {
        $v['user'] = $users[$v['user_id']][0] ?? null;
    }
}



return $res;
