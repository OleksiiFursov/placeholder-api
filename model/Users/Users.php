<?php

class Users extends Model
{
    protected $model = ModelUsers::class;


    //Users:
    function get                ($filters = [], $params = [])               { return include __DIR__ . '/Actions/get.php';}
    function add                ($data = [])                                { return include __DIR__ . '/Actions/add.php';}
    function del                ($id)                                       { return include __DIR__ . '/Actions/del.php';}
    function edit               ($data = [], $filters=[])                   { return include __DIR__ . '/Actions/edit.php';}

    //Authorization:
    function login              ($params)                                   { return include __DIR__ . '/Actions/Auth/login.php';}
    function auth               ($params = [])                              { return include __DIR__ . '/Actions/Auth/auth.php';}
    function logout             ($params = [])                              { return include __DIR__ . '/Actions/Auth/logout.php';}
    function history            ($filters = [], $params = [])               { return include __DIR__ . '/Actions/Auth/history.php';}

    function phonecheck         ($filters = [], $params = [])               { return include __DIR__ . '/Actions/phonecheck.php';}

    //Token:
    function create_token       ($user_id)                                  { return include __DIR__ . '/Actions/Token/create_token.php';}
    function update_token       ($token=null)                               { return include __DIR__ . '/Actions/Token/update_token.php';}
    function get_token          ($value=null, $columns='*')                 { return include __DIR__ . '/Actions/Token/get_token.php';}
    function active_token_get   ($filters = [], $params = [])               { return include __DIR__ . '/Actions/Token/active_token_get.php';}

    function search             ($filters = [], $params = [])               { return include __DIR__ . '/Actions/search.php';}



    function avatar_add   ($data)               { return include __DIR__ . '/Actions/avatar/add.php';}
    function birthday   ($data)               { return include __DIR__ . '/Actions/birthday.php';}

    function achievements_get   ($filters = [], $params = [])               { return include __DIR__ . '/Actions/achievements/get.php';}
    function achievements_add   ($data =[])                                 { return include __DIR__ . '/Actions/achievements/add.php';}
    function achievements_del   ($id)                                       { return include __DIR__ . '/Actions/achievements/del.php';}

    function representative_get ($filters = [], $params = [])               { return include __DIR__ . '/Actions/representative/get.php';}
    function representative_add ($data = [], $filters = [])                 { return include __DIR__ . '/Actions/representative/add.php';}


    function is_auth(){             return user_id() === 1;}

}
