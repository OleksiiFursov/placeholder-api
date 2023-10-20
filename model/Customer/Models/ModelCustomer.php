<?php
class ModelCustomer extends BaseModel
{
    static $table = 'todo';
    static $columns = [
        'id' => ['int'],
        'tu' => ['string', 'safe' => false],
        'username' => ['string'],
        'first_name' => ['string'],
        'last_name' => ['string'],
        'email' => ['string'],
        'phone' => ['string'],
        'createdAt' => ['datetime', 'required' => false],
        'updatedAt' => ['datetime', 'required' => false],
        'birthday' => ['datetime'],
        'about' => ['string'],
        'location' => ['string'],
        'company' => ['string'],
        'status'    => ['int', 'default'=>0, 'sync' => ['ModelVocabulary', 'name']],
    ];
    static function onInsert(){
        self::$columns['tu']['default'] = ini('user.token');
    }

}
