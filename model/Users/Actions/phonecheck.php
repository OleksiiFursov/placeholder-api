<?php
/**
 * @var Users $this
 * @var array|int $filters
 * @var array|null $params
 */

[$extends, $params, $filters] = $this->init_action([
    'extends' => [
        'auth'
    ],
    'extends_default' => [],
    'params' => $params,
    'filters' => $filters,
    'filters_default' => [
        'tax_type' => 'user',
        'status' => [1, 2]
    ],
    'model' => ModelPhones::class
]);

$res = ModelPhones::select()
    ->params($params)
    ->where($filters)
    ->one();

$reg = ModelOptions::findOne(['name' => 'reg'], 'value');


$reg = filter_var($reg['value'], FILTER_VALIDATE_BOOLEAN);
$status_reg = $reg;


if ($extends['auth']) {
    if ($res) {
        if($res['status'] === 1 && !$status_reg){
            $res['status'] = -10;
        }
        $user = ModelUsers::findOne(['id' => $res['tax_id']], ['len_password']);
        if (isset($user['len_password']) && $user['len_password'] === 0) {
            $res['status'] = -1;
            if(!$status_reg){
                $res['status'] = -10;
            }
        }
    }elseif(!$status_reg){
        $res = [];
        $res['status'] = -10;
    }
}

return $res;
