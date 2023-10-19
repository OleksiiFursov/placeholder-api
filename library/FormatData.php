<?php

class FormatData{
    static $params = [
        ['id', 'level_id', 'last_connect', 'user_id', 'int'],
        ['status', 'boolean'],
        ['date', 'date_created', 'date_update', 'birthday', 'time_update', 'date_expiration', 'datetime'],
    ];

    static function password($a){
        return $a;
    }
    static function datetime($a){
        if($a === '0000-00-00 00:00:00') return null;
        if(is_numeric($a))  return (int)$a;
        if(is_string($a))   return strtotime($a);
        return $a;
    }
    static function json($a){
        return json_decode($a, 256);
    }



}
