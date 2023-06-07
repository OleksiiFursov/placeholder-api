<?php

/**
 * @var Users $this
 * @var array|int $data
 * @var array $filters
 */




$birthday = $this->dbBuild()
    ->model('ModelUsersBirthday')
    ->select()
    ->where(['user_id' => $filters, 'status' => [1,2]])
    ->one();

if ($birthday) {
    if ($data === $birthday['birthday']) {
        notice('Необходимо реализовать статус 2 - верификация');
    } else {
        notice($birthday['id']);
        $this->dbBuild()
            ->model('ModelUsersBirthday')
            ->update(['status' => 0, 'owner_update_id' => user_id()])
            ->where(['id' => $birthday['id']])
            ->run();
        $this->birthday_add($data, $filters);
    }
} else {
    $this->birthday_add($data, $filters);
}

return true;
