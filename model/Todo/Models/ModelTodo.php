<?php
class ModelTodo extends BaseModel
{
    static $table = 'todo';
    static $columns = [
        'tu' => ['string', ],
        'name' => ['string'],
        'date' => ['datetime'],
        'description' => ['string'],
        'priority' => ['int', 'default'=>1, 'sync'=>['ModelVocabulary', 'name']],
        'status'    => ['int', 'default'=>0, 'sync' => ['ModelVocabulary', 'name']],
    ];
    static function onInsert(){
        self::$columns['tu']['default'] = ini('user.token');
    }

}
