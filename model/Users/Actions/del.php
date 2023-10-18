<?php

/**
 * @var Users $this
 * @var int $id
 */

$id = parse_id($id);

// Check id;

if(!$id){
    return Response::error('Не правильный ид', 422);
}

if(!ModelSysUsers::has($id)){
    return Response::error('Не найден ид в базе', 404);
}

$tax_data = ['tax_id' => $id, 'tax_type' => 'user'];
$user_id_data = ['user_id' => $id];
ModelSysUsers::disabled($id);
ModelAddress::disabled($tax_data);
ModelEmails::disabled($tax_data);
ModelPhones::disabled($tax_data);
ModelFiles::disabled($tax_data);
ModelUsersAccess::disabled($tax_data);
ModelUsersAchievements::disabled($user_id_data);
ModelUsersData::disabled($user_id_data);
ModelUsersRepresentative::disabled($user_id_data);
ModelUsersToken::delete($user_id_data);


return is_done();
