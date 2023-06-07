<?php


class ModelUsersLoginHistory extends BaseModel
{
    static $table = 'users_login_history';
    static $name_alt ='users_login_history';
    static $meta = false;
    static $columns = [
        'id'                => ['int'],
        'user_id'           => ['int'],
        'date_created'      => ['datetime'],
        'last_connect'      => ['datetime'],
        'user_agent'        => ['string'],
        'ip'                => ['string','require' => false],
    ];


    static function get_nodes(){
        return [];
    }

}