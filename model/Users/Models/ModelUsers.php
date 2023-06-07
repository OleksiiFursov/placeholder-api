<?php

class ModelUsers extends BaseModel
{
    static $table = 'users';
    static $name_alt = 'u';
    static $columns = [
        'id' => ['int'],
        'name' => ['string'],
        'password' => ['string', 'require' => false, 'safe' => false],
        'len_password' => ['int', 'require' => false],
        'first_name' => ['string', 'require' => false],
        'last_name' => ['string', 'require' => false],
        'patronymic' => ['string', 'require' => false],
        'sex' => ['int', 'require' => false],
        'level_id' => ['int', 'require' => false],
        'date_created' => ['datetime'],
        // 'last_connect' => ['datetime','require' => false],
        'owner_id' => ['int', 'default' => 0],
        'status' => ['bool', 'default' => true],
        'confirmed_id' => ['int', 'require' => false],
        'confirmed_date' => ['datetime', 'require' => false],
    ];

    static function onInsert(){
        ModelUsers::$columns['owner_id']['default'] = user_id();
    }

    static function get_nodes()
    {
        return [
            'token' => [ModelUsersToken::class, ['u.id' => 'ut.user_id'], ['token', 'date', 'date_expiration', 'user_agent', 'ip', 'referer']],
            'avatar' => [ModelFiles::class, ['u.id' => 'f.tax_id', 'tax_type' => '"user"', 'slug' => '"avatar"', 'f.status' => [0, '>']], ['value as avatar']]
        ];
    }

}
