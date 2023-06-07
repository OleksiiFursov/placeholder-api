<?php
$_EVENT = [];
$_EVENT_HISTORY = [];



function event($name, $args=[]){
    global $_EVENT, $_EVENT_HISTORY;
    $name = strtolower($name);


    if(!isset($_EVENT[$name])) return 0;


    $_EVENT_HISTORY[$name] = true;
    usort($_EVENT[$name], function($a,$b){
        return ($b['sort']-$a['sort']);
    });


    for($i=0, $len=sizeof($_EVENT[$name]); $i<$len; $i++){
        $args['event'] = [
            'name'  => $name,
            'i'     => $i
        ];
        $_EVENT[$name][$i]['function']($args);
    }

    return true;
}


function add_event($name, $function, $opts=[]){
    global $_EVENT, $_EVENT_HISTORY;

    $name = strtolower($name);

    if(!isset($_EVENT[$name])){
        $_EVENT[$name] = [];
    }


    $_EVENT[$name][] = array(
        'sort' => isset($opts['sort'])?$opts['sort']:0,
        'function'  => $function,
        'once' => $opts['once'] ?? false
    );

    if(isset($_EVENT_HISTORY[$name])){
        $function();
    }
    return sizeof($_EVENT[$name])-1;
}

function remove_event($name, $id){
    global $_EVENT;
    unset($_EVENT[$name][$id]);

    if(isset($_EVENT[$name]) && !sizeof($_EVENT[$name])){
        unset($_EVENT[$name]);
    }
}