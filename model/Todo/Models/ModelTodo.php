<?php
class ModelTodo extends BaseModel
{
    static $table = 'todo';
    static $columns = [
        'id' => ['int'],
        'tu' => ['string', 'safe' => false],
        'name' => ['string'],
        'date' => ['datetime'],
        'created_at' => ['datetime', 'require' => false],
        'description' => ['string'],
        'priority' => ['int', 'default'=>1, 'sync'=>['ModelVocabulary', 'name']],
        'status'    => ['int', 'default'=>0, 'sync' => ['ModelVocabulary', 'name']],
    ];
    static function onInsert(){
        self::$columns['tu']['default'] = ini('user.token');
    }

}
