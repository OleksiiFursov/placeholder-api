<?php

class ModelUsers extends BaseModel
{
    static $table = 'sys_users';
    static $table_name_alt = 'su';
    static $columns = [
        'token' => ['string'],
        'name' => ['string'],
        'created_at' => ['datetime']
    ];

}
