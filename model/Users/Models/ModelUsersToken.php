<?php


class ModelUsersToken extends BaseModel
{
    static $table = 'users_token';
    static $table_name_alt ='ut';
    static $meta = false;
    static $columns = [
        'token'             => ['string'],
        'user_id'           => ['int'],
        'date_expiration'   => ['datetime'],
        'last_connect'      => ['datetime'],
        'date_created'      => ['datetime'],
        'user_agent'        => ['string'],
        'ip'                => ['string'],
        'referer'           => ['string'],
    ];


    static function get_nodes(){
        return [];
    }

}
