<?php

class UsersToken extends Model{
    function create($user_id){
        return include __DIR__ . '/Actions/Token/create.php';
    }
    function clear(){
        $duration = time()+get_option('user.duration', 365 * 24 * 60 * 60);
        setcookie('token', '', $duration, '/', URL_DOMAIN);
    }
}
