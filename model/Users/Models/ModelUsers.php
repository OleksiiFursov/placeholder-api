<?php

class ModelUsers extends BaseModel
{
    static $table = 'users';
    static $table_name_alt = 'u';
    static $columns = [
        'tu' => ['string'],
        'name' => ['string'],
        'password' => ['password'],
        'created_at' => ['datetime'],
        'status'    => ['int', 'default'=>1, 'sync' => ['ModelVocabulary', 'name']],
    ];

    static function onInsert(){
        self::$columns['tu']['default'] = ini('user.token');
    }
}
