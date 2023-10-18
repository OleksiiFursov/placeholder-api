<?php

class ModelSysUsers extends BaseModel
{
    static $table = 'sys_users';
    static $table_name_alt = 'su';
    static $columns = [
        'tu' => ['string'],
        'name' => ['string'],
        'created_at' => ['datetime'],
        'status' => ['int', 'default' => 1, 'sync' => ['ModelVocabulary', 'name']],
    ];
}
