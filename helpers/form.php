<?php

function GET($name=NULL, $type=NULL, $def=false, $method='REQUEST'){
    if($name === NULL){
        return $type === NULL ? $GLOBALS['_'.$method] : $GLOBALS['_'.$type];
    }
    if(isset($GLOBALS['_'.$method][$name])){
        $var = $GLOBALS['_'.$method][$name];
        if(is_array($var)){
            return $var;
        }
        if($type !== NULL && strlen($type)>2){
            if($type[1] !== '~' || $type[2] !== '~')
                $type = '/^['.$type.']+$/';
            else{
                $type = '/'.$type.'/';
            }
            if(!preg_match($type, $var)){
                ini('IS_ERROR', true);
                Response::error( 'Field "'.$name.'" - is not valid');
                $var = $def;
            }
        }
    }else
        $var = $def;

    return $var;
}
