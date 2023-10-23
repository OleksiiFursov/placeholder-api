<?php
class ModelCustomer extends BaseModel
{
    static $table = 'customers';
    static $columns = [
        'id' => ['int'],
        'tu' => ['string', 'safe' => false],
        'username' => ['string'],
        'first_name' => ['string'],
        'last_name' => ['string'],
        'email' => ['string'],
        'phone' => ['string'],
        'createdAt' => ['datetime', 'require' => false],
        'updatedAt' => ['datetime', 'require' => false],
        'birthday' => ['datetime'],
        'about' => ['string'],
        'location' => ['location'],
        'balance' => ['string'],
        'gender' => ['int'],
        'company' => ['string'],
        'address' => ['string'],
        'status'    => ['int', 'default'=>1, 'sync' => ['ModelVocabulary', 'name']],
    ];
    static function onInsert(){
        self::$columns['tu']['default'] = ini('user.token');
    }

}
