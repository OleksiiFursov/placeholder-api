<?php

// get data for Database
class FormatData{
    static array $params = [
        ['id', 'level_id', 'user_id', 'int'],
        ['date', 'date_created', 'date_update', 'date_expiration', 'datetime'],
    ];

    static function getPassword($a){
        return $a;
    }

    static function setPassword(&$a): bool
    {
        $a = password_hash($a, PASSWORD_DEFAULT);
        return true;
    }

    static function setInt(&$a): bool
    {
        $is_valid = is_numeric($a);
        if ($is_valid)
            settype($a, 'integer');
        return $is_valid;
    }
    static function setFloat(&$a): bool
    {
        $is_valid = is_double($a);
        if ($is_valid)
            $a = +$a;
        return $is_valid;

    }
    static function setString(&$a){
        return is_string($a);
    }
    static function setDate(&$a){
        if($a === 'NOW()') return true;

        if (is_numeric($a)) {
            $is_valid = true;
            $a = date('Y-m-d', $a);
        } else {
            $is_valid = strtotime($a);
            if ($is_valid)
                $a = date('Y-m-d', $is_valid);
        }
        return $is_valid;
    }
    static function setDateTime(&$a){
        $format = 'Y-m-d H:i:s';
        if($a === 'NOW()') return true;
        if (is_numeric($a)) {
            $is_valid = true;
            $a = date($format, $a);
        } else {
            $is_valid = strtotime($a);
            if ($is_valid)
                $a = date($format, $is_valid);
        }
        return $is_valid;
    }
    static function setEnum(&$a, $rules){
        return in_array($a, $rules[1]);
    }
    static function setBool(&$a){
        $is_valid = is_bool($a) || $a == '0' || $a == '1';
        if ($is_valid)
            settype($a, 'boolean');
        return $is_valid;
    }

    static function getDatetime($a){
        if($a === '0000-00-00 00:00:00') return null;
        if(is_numeric($a))  return (int)$a;
        if(is_string($a))   return strtotime($a);
        return $a;
    }
    static function getLocation($a){
        $res = explode(';', $a);
        return [
            'lat' => $res[0],
            'long'=> $res[1]
        ];
    }
    static function setLocation(&$a){
        if(isset($a['lat'], $a['long'])){
            $a = $a['lat'].";".$a['long'];
            return true;
        }
        return false;
    }
    static function getJson($a){
        return json_decode($a, 256);
    }
}
