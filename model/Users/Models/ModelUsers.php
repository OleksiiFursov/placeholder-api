<?php

class ModelUsers extends BaseModel
{
    static $table = 'sys_users';
    static $table_name_alt = 'su';
    static $columns = [
        'tu' => ['string'],
        'name' => ['string'],
        'password' => ['password'],
        'created_at' => ['datetime'],
        'status'    => ['int', 'default'=>1, 'sync' => ['ModelVocabulary', 'name']],
    ];

    static function onInsert(){
        self::$columns['token']['default'] = ini('user.token');
    }
}
