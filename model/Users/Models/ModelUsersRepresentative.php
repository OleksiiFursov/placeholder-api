<?php


class ModelUsersRepresentative extends BaseModel
{
    static $table = 'users_representative';
    static $columns = [
        'id' => ['int'],
        'user_id' => ['int'],
        'representative_id' => ['int'],
        'representative_type' => ['string'],
        'user_type' => ['string'],
        'owner_id' => ['int'],
        'date' => ['datetime','require' => false],
        'status' => ['bool', 'default' => true]
    ];

    static function onInsert(){
        ModelUsersRepresentative::$columns['owner_id']['default'] = user_id();
    }
}
