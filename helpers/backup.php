<?php
function set_history(){
    global $db;
    $args = func_get_args();
    $argc = func_num_args();


    if($argc == 2){

        foreach($args[1] as $key=>$value) {

            if(!isset($arr['type']))
                $arr['type'] = 'edit';

            $arr['name'] = $key;
            $arr['user_id'] = user_id();
            $arr['value'] = $value;


            $db->ins($args[0].'_history', $arr);
        }
    }else{
        //TO DO:
    }

}



?>