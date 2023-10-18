<?php

/**
 * @var Users $this
 * @var array $data
 */


$_data = remove_items($data, ModelSysUsers::getColumnsForInsert());
$_data['owner_id'] = user_id() === 1 ? null : user_id();

$is_user_confirmed = Access::check('users.confirmed');
if (isset($_data['password'])) {
    $_data['len_password'] = strlen($_data['password']);
    $_data['password'] = password_hash($_data['password'], PASSWORD_DEFAULT);
}

if (empty($_data['first_name']) && isset($_data['name'])) {
    $n = explode(' ', trim($_data['name']));
    $_data['first_name'] = $n[1] ?? '';
    $_data['last_name'] = $n[0] ?? '';
    if (isset($n[2])) {
        $_data['patronymic'] = $n[2];
    }
}

if ($is_user_confirmed) {
    $_data['confirmed_id'] = user_id();
    $_data['confirmed_date'] = 'NOW()';
    $_data['level_id'] = get_option('user.confirmed_level', 3);
} else {
    $_data['level_id'] = get_option('user.not_confirmed_level', 2);
}

$id = ModelSysUsers::insert($_data);

if (user_id() === 1) {
    $this->create_token($id);
    $this->auth();
}

$phones = remove_item($data, 'phones');
$emails = remove_item($data, 'emails');
$address = remove_item($data, 'address');


$_tax = ['tax_id' => $id, 'tax_type' => 'user'];

if ($phones) {
    $_phones = (new Phones)->add([...$_tax, 'values' => $phones]);
}
if ($emails) {
    $_emails = (new Emails)->add([...$_tax, 'values' => $emails]);
}
if ($address) {
    $_address = (new Address)->add([...$_tax, 'values' => $address]);
}

$_user_data = remove_items($data, ModelUsersData::available_name());

$buf = [];
foreach ($_user_data as $key => $val) {
    $t = [
        'user_id' => $id,
        'name' => $key,
        'value' => $val,
    ];
    if ($is_user_confirmed) {
        $t += [
            'owner_id' => user_id(),
            'confirmed_id' => user_id(),
            'confirmed_date' => 'NOW()'
        ];
    }
    $buf += $t;
}
if (sizeof($buf))
    ModelUsersData::insert($buf);

return is_done($data, $id);
