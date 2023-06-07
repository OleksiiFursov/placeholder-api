<?php

/**
 * @var Users $this
 * @var array|int $data
 * @var array $filters
 */

$_data['birthday'] = $data;
$_data['user_id'] = $filters['user_id'];
$_data['owner_id'] = user_id();
$_data['status'] = 1;

$sql = $this->dbBuild()
    ->model('ModelUsersBirthday')
    ->insert($_data)
    ->run();

return true;
