<?php


class ModelUsersAssignment extends BaseModel
{
    static $table = 'users_assignment as uassignment';
    static $name_alt = 'uassignment';
    static $meta = false;
    static $columns = [
        'assigment_id' => ['int'],
        'user_id' => ['int'],
        'date' => ['date'],
        'tax_id' => ['int'],
        'tax_type' => ['string'],
        'owner_id' => ['int'],
        'status' => ['int', 'require' => false],
    ];

    static function get_nodes()
    {
        return [];
    }

}