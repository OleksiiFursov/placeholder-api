<?php


class ModelUsersBirthday extends BaseModel
{
    static $table = 'users_birthday as users_birthday';
    static $name_alt = 'users_birthday';
    static $meta = false;
    static $columns = [
        'id' => ['int'],
        'user_id' => ['int'],
        'birthday' => ['string'],
        'status' => ['int'],
        'owner_id' => ['int'],
        'date_created' => ['datetime'],
        'date_update' => ['datetime', 'require' => false],
        'owner_update_id' => ['int', 'require' => false],
    ];

    static function get_nodes()
    {
        return [
        ];
    }

}