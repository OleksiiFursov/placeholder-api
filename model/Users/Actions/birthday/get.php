<?php
/**
 * @var Users $this
 * @var array|int $filters
 * @var array|null $params
 */


[$extends, $params, $filters] = $this->init_action([
    'extends' => [],
    'extends_default' => [],
    'params' => $params,
    'params_default' => [
        'columns' => ModelUsersBirthday::columnsSafe(),
        'meta' => true
    ],
    'filters' => $filters,
    'filters_default' => [
        'users_birthday.status' => 1,
    ],
    'model' => 'ModelUsers',
]);

$sql = $this->dbBuild();



$sql->model('ModelUsersBirthday')
    ->where($filters)
    ->order($params['order'], $params['order_dir'])
    ->select($params['columns']);

$res = $sql->run();


return $res;
