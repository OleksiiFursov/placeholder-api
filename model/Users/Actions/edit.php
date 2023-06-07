<?php
/**
 * @var Users $this
 * @var array|int $filters
 * @var array $data
 */

// FIND users:
if (empty($filters)) {
    return Response::error('Filter is empty');
}

if (empty($data)) {
    return Response::error('Data is empty');
}

[$filters] = $this->init_action([
    'filters' => $filters,
]);


$users_ids = ModelUsers::select('id')
    ->where($filters)
    ->where(['id' => [1, '!=']])
    ->run(2);


$users_ids = array_flat($users_ids);

$_data = remove_items($data, [...ModelUsers::getColumnsForInsert(), 'password']);
$is_user_confirmed = Access::check('users.confirmed');

if (empty($users_ids)) {
    return Response::error('Пользователи не найдены');
}


if (isset($_data['password'])) {
    $_data['len_password'] = strlen($_data['password']);
    $_data['password'] = password_hash($_data['password'], PASSWORD_DEFAULT);
}


$phones = remove_item($data, 'phones');
$emails = remove_item($data, 'emails');
$address = remove_item($data, 'address');


$tax_id = ['tax_id' => $users_ids[0], 'tax_type' => 'user'];

if ($phones) {
    $_phones = (new Phones)->edit($phones, $tax_id);
}
if ($emails) {
    $_emails = (new Emails)->edit($emails, $tax_id);
}
if ($address) {
    $_address = (new Address)->edit($address, $tax_id);
}

$_user_data = remove_items($data, ModelUsersData::available_name(), ['isset' => true]);

// UsersData
$ins = [];
if (!empty($_user_data)) {
    $_data_olds = ModelUsersData::findGroup([
        'name' => array_keys($_user_data),
        'user_id' => $users_ids,
    ], ['user_id', 'name', 'value'], 'user_id');


    foreach ($users_ids as $index => $user_id) {
        if ($_data_olds)
            $_data_old = array_group($_data_olds[$user_id]);

        foreach ($_user_data as $key => $val) {
            if ($_data_olds) {

                if (array_key_exists($key, $_data_old)) {

                    if ($_data_old[$key] === $val) continue;

                    ModelUsersData::disabled(['user_id' => $user_id, 'name' => $key, 'status' => [0, '>']]);
                    if ($_data_old[$key] === null) {
                        continue;
                    }
                }
            }

            if ($val === null) continue;
            $t = [
                'user_id' => $user_id,
                'name' => $key,
                'value' => (string)$val,
            ];
            if ($is_user_confirmed) {
                $t += [
                    'confirmed_id' => user_id(),
                    'confirmed_date' => 'NOW()'
                ];
            }
            $ins[] = $t;
        }
    }

    if (sizeof($ins))
        ModelUsersData::insert($ins);
}
if (sizeof($ins) || sizeof($_data)) {
    if ($is_user_confirmed) {
        $_data['confirmed_id'] = user_id();
        $_data['confirmed_date'] = 'NOW()';
    } else {
        $_data['confirmed_id'] = null;
        $_data['confirmed_date'] = null;
    }
}


if (sizeof($_data))
    ModelUsers::update($_data, $users_ids);


return is_done($data);
