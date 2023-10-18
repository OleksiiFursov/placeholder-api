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
        'meta' => true
    ],
    'filters' => $filters,
]);
/********* EXTENDS PRE *********/
$res = ModelUsers::select()
    ->where($filters)
    ->params($params);

$res->autoNode($extends);


$res = $res->run();

//$ids = get_ids($res);

//$tax_data = ['tax_id' => $ids, 'tax_type' => 'user', 'status' => [0, '>']];


//foreach ($res as &$v) {
//
//}
//unset($v);
return $res;
