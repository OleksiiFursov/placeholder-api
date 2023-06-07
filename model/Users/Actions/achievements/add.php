<?php

/**
 * @var Users $this
 * @var array|int $data
 */


$_data = remove_items($data, ModelUsersAchievements::getColumnsForInsert());

$res = ModelUsersAchievements::insert($_data);


return is_done($data, $res);
