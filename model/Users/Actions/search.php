<?php
/**
 * @var Users $this
 * @var array|int $filters
 * @var array|null $params
 */

$ids = [];
$_phone = preg_replace('/\D/u', '', $filters['name']);
if ($_phone) {
    $phones = $this->dbBuild()
        ->select('tax_id')
        ->model('ModelPhones')
        ->where(['value', $_phone, 'LIKE'])
        ->where(['tax_type' => 'user', 'status' => [0, '>']])
        ->run();
    $ids = get_ids($phones, 'tax_id');
}
if(!$_phone){
    $users = ModelSysUsers::find(['name' => $filters['name']]);
    $mix = explode(" ", $filters['name']);
    if (count($mix) > 1) $mix = mix($mix);

    $users_sql = $this->dbBuild()
        ->select('id')
        ->model('ModelUsers')
        ->where(['status' => [0, '>']]);

    foreach ($mix as $v) {
        if (is_array($v)) {
            foreach ($v as &$vv) {
                $vv = '%' . $vv . '%';
            }
            $v = implode(" ", $v);
        } else {
            $v = '%' . $v . '%';
        }
        $users_sql->where([['name', $v, 'LIKE']], 'OR', 'OR');
        $users_sql->where([['name', change_qwerty($v), 'LIKE']], 'OR', 'OR');
    }

    $users = $users_sql->run();
    $ids = get_ids($users);
}

$users = $this->get(['id' => $ids], ['extends' => '+phones,emails,address,representative,data,avatar']);

return $users;
json($ids);

if (is_phone($filters['name'])) {
    $_phone = preg_replace('/[^0-9]/u', '', $filters['name']);
    $Phones = new Phones;
    $phone = $Phones->get(['value' => $_phone, 'tax_type' => 'user', 'status' => [0, '>']]);
    if ($phone) {
        $id = $phone[0]['tax_id'];
        $users_sql->where(['id' => $id]);
        $users = $users_sql->run();
        return $users;
    } else {
        return false;
    }
}

if (isset($filters['name']) && !is_phone($filters['name'])) {
    $mix = explode(" ", $filters['name']);
    if (count($mix) > 1) $mix = mix($mix);
    foreach ($mix as $v) {
        if (is_array($v)) {
            foreach ($v as &$vv) {
                $vv = '%' . $vv . '%';
            }
            $v = implode(" ", $v);
        } else {
            $v = '%' . $v . '%';
        }
        $users_sql->where(['status' => [0, '>']]);
        $users_sql->where([['name', $v, 'LIKE']], 'OR', 'OR');
    }
}


$users = $users_sql->run();

$ids = get_ids($users);

$users = $this->get(['id' => $ids], ['extends' => '+phones,emails,address,representative,data,avatar']);

return $users;


/**
 * @var Users $this
 * @var array|int $filters
 * @var array|null $params
 */


global $db;
$_EXTENDS = [];

$_EXTENDS_DEFAULT = [];

$_RETURNS = ['array'];
$_FILTERS = ['u.status' => 1];

$_PARAMS = [
    'order' => 'u.id',
    'order_dir' => 'ASC',
    'sql' => '',
    'offset' => 0,
    'limit' => null,
    'meta' => true,
    'full' => false,
    'columns' => ModelSysUsers::columnsSafe(true, ['password', 'hash'])
];


$this->get_filter($filters, $_FILTERS, [
    'number' => 'u.id',
    'string' => 'u.name',
    'number_array' => 'u.id',
]);

$extends = $this->init_params($_PARAMS, $_EXTENDS, $_EXTENDS_DEFAULT, $_RETURNS, $params);

if ($params === -1) {
    return [$_EXTENDS, $_EXTENDS_DEFAULT, $_RETURNS, $_PARAMS, $params, $filters, $extends];
}


$users_sql = $db->build();
$users_sql->model('ModelUsers')
    ->select();

if (is_phone($filters['name'])) {
    $_phone = preg_replace('/[^0-9]/u', '', $filters['name']);
    $Phones = new Phones;
    $phone = $Phones->get(['value' => $_phone, 'tax_type' => 'user', 'status' => [0, '>']]);
    if ($phone) {
        $id = $phone[0]['tax_id'];
        $users_sql->where(['id' => $id]);
        $users = $users_sql->run();
        return $users;
    } else {
        return false;
    }
}

if (isset($filters['name']) && !is_phone($filters['name'])) {
    $mix = explode(" ", $filters['name']);
    if (count($mix) > 1) $mix = mix($mix);
    foreach ($mix as $v) {
        if (is_array($v)) {
            foreach ($v as &$vv) {
                $vv = '%' . $vv . '%';
            }
            $v = implode(" ", $v);
        } else {
            $v = '%' . $v . '%';
        }
        $users_sql->where(['status' => [0, '>']]);
        $users_sql->where([['name', $v, 'LIKE']], 'OR', 'OR');
    }
}


$users = $users_sql->run();

$ids = get_ids($users);

$users = $this->get(['id' => $ids], ['extends' => '+phones,emails,address,representative,data,avatar']);

return $users;
