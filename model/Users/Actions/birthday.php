<?php
/**
 * @var Users $this
 * @var array|int $data
 */


$birthday = ModelUsersData::select('value')
    ->where(['name' => 'birthday', 'status' => [0,'>']])
    ->run();


foreach ($birthday as &$v){
    $v = array_values($v)[0];
}
