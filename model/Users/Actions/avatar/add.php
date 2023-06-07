<?php
/**
 * @var Users $this
 * @var array|int $data
 */

$owner_id = 2;


$_old_avatar = (new Files)->get(['tax_id' => $data['user_id'], 'tax_type' => 'user', 'status' => 1]);
if ($_old_avatar) {
    ModelFiles::update(['status' => 0], ['tax_id' => $data['user_id'], 'tax_type' => 'user', 'status' => 1]);
}

$file_name = (new Uploads)->uploads('users/avatar/full');

ModelFiles::insert(['tax_id' => $data['user_id'], 'tax_type' => 'user', 'status' => 1, 'value' => $file_name, 'slug' => 'avatar', 'type' => 'image', 'owner_id' => $owner_id]);

return $file_name;
