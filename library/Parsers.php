<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 07.03.2019
 * Time: 15:29
 */

class Parsers{
    var $err_parse_line;
    function __construct(){
        set_time_limit(0);
        $this->err_parse_line =  array();
        header("Content-Type: text/html; charset=utf-8");
        ini_set('memory_limit', '99999M');
    }

    function reg_parser(&$arr, $str, $limit){

        $i=0;
        $newarr = array();
        while($item = array_shift($arr)){

            if($limit != 0 && $limit < $i) break;

            $i++;
            if(!preg_match($str, $item, $match)){

                $this->err_parse_line[] = $i;
                continue;
            }
            array_shift($match);

            $newarr[] = $match;
        }
        return $newarr;
    }

    function strToDb($filename, $table='hs_member_names', $limit=0){
        global $db, $log;
        $arr = file($filename);
        if(!sizeof($arr)){
            return -1;
        }

        $arr = $this->reg_parser($arr, '/\{"ID":([0-9]+),"Name":"([^"]+)","Sex":"?([a-z]+)"?,"PeoplesCount":([0-9]+)\}/ui', $limit);

        foreach($this->err_parse_line as $v){
            $log->set('err_parse_strToDb', 'File: '.$filename.', line: '.$v);
        }

        usort($arr, function($a,$b){
            return ($b[3]-$a[3]);
        });

        for($i=0,$len = sizeof($arr); $i<$len; $i++){

            $sex = (($arr[$i][2] == 'wman') ? 0 : (($arr[$i][2] == 'man')?1:2));
            if($db->result('SELECT COUNT(`id`) FROM `'.$table.'` WHERE `name`="'.$arr[$i][1].'"', 'row')[0][0] == '0') {
                $db->query('INSERT INTO `' . $table . '` SET `name` = "' . $arr[$i][1] . '", `sex` = ' . $sex . ', `q` = ' . $arr[$i][3]);

            }else{
                $db->query('UPDATE  `'.$table.'` SET `q` = `q` + '.$arr[$i][3].' WHERE `name`="'.$arr[$i][1].'"');
            }
        }


        exit;
    }

}