<?php

class ModelUsersAccess extends BaseModel
{
    static $table = 'users_access';
    static $name_alt = 'u_access';
    static $columns = [
        'id' => ['int'],
        'name' => ['string'],
        'value' => ['bool'],
        'tax_type' => ['string'],
        'tax_id' => ['int'],
        'priority' => ['int', 'default' => 0],
        'status' => ['int', 'default' => 1]
    ];

}
