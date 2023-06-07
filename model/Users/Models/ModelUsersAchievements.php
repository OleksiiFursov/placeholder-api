<?php


class ModelUsersAchievements extends BaseModel
{
    static $table = 'users_achievements';
    static $columns = [
        'id' => ['int'],
        'tax_id' => ['int'],
        'tax_type' => ['int'],
        'user_id' => ['int'],
        'owner_id' => ['int'],
        'date_created' => ['datetime', 'require' => false],
        'date_achieve' => ['datetime', 'require' => false],
        'status' => ['int', 'default' => 1]
    ];

    static function onInsert(){
        ModelUsersAchievements::$columns['owner_id']['default'] = user_id();
    }

}
