<?php

class ModelVocabulary extends BaseModel
{
    static $table = 'vocabulary';
    static $table_name_alt = 'vc';
    static $columns = [
        'context' => ['string'],
        'tu'   => ['string', 'safe' => true],
        'name' => ['string'],
        'value' => ['string'],
        'lang'  => ['string', 'default' => 'en']
    ];
    static function onInsert(){
        self::$columns['tu']['default'] = ini('user.token');
    }
}
