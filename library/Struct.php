<?php

class Struct
{
    var $data = [];
    var $data_system = ['document'];

    var $struct_def = [
        'required' => true,
        'type' => 'string',
        'valid' => null,
        'regxp' => null,
        'default' => null,
        'flags'   => []

    ];


    function set_system($arr)
    {
        if (is_array($arr)) {
            for ($i = 0, $len = sizeof($arr); $i < $len; $i++) {
                $this->set_system($arr[$i]);
            }
            return;
        }

        $data_system[] = $arr;
    }


    function set($key, $value = NULL)
    {
        if (is_array($key)) {
            foreach ($key as $k => $v) {
                $this->set($k, $v);
            }
            return;
        }

        if (!isset($value['label'])) {
            $value['label'] = $key;
        }

        $this->data[$key] = array_merge($this->struct_def, $value);
        return $value;
    }

    function get($arr)
    {
        if (!is_array($arr)) {
            $arr = [$arr];
        }
        $new_arr = [];
        for ($i = 0, $len = sizeof($arr); $i < $len; $i++) {
            if (isset($this->data[$arr[$i]])) {
                $msg = 'Не найдена структура для ' . $arr[$i];
                $this->log('struct', $msg);
                throw new Exception($msg, -1);
            }
        }
        return $new_arr;
    }

    function valid($arr, $cols, $params=[]){

        $params+= [
            'key_prefix' => '',
            'key_suffix' => ''
        ];

        if(!arr_is_assoc($arr)){
            $res = [];
            for($i=0, $len=sizeof($arr); $i<$len; $i++){
                $params['key_suffix'] = '_'.$i;
                $t =  $this->valid($arr[$i], $cols, $params);

               if($i === 0)
                   $res = $t;
               else{
                   $res = array_merge_recursive($res, $t);
               }
            }




            $res['status'] = sizeof($res['error']) === 0;
            return $res;
        }

        // BUFFER:
        $new_arr = [];
        $errs_network = [];
        $errs = [];
        $data = [];


        // COLUMN GROUPS:
        foreach($cols as $group_name =>  $col) {
            $data[$group_name] = [];

            // COLUMN:
            for ($i = 0, $len = sizeof($col); $i < $len; $i++) {

                $noError = true;

                // MERGE STRUCT:
                if (is_array($col[$i])) {
                    $col_i = array_shift($col[$i]);
                    if (isset($this->data[$col_i])) {
                        $c = array_merge($this->data[$col_i], $col[$i][0]);
                    } else {
                        $c = $col[$i][0];

                        $c['label'] = $col_i;
                        $c+=$this->struct_def;
                    }
                    $col[$i] = $col_i;
                } else {
                    $c = isset($this->data[$col[$i]]) ? $this->data[$col[$i]] : null;
                }

                // FIND STRUCT:


                if ($c === null) {
                    $msg = 'Не найдена структура для ' . $col[$i];
                    $this->log('struct', $msg);
                    throw new Exception($msg, -1);
                }

                // DROP STRUCT WITH SCOPE:
                $item = remove_item($arr, $col[$i], NULL); // Data item arr

                $col_name = $params['key_prefix'].$col[$i].$params['key_suffix'];
                //VALIDATION STRUCT:
                    #IS REQUIRED:
                if ($item === null) {
                    if ($c['required'] && $c['default'] === NULL) {
                        $errs[] = arr_push($errs_network, $col_name, 'Поле "' . $c['label'] . '" - обязательное для заполнения');
                        $noError = false;
                    }

                    if ($c['default'] !== NULL && !in_array('NOT_NULL', $c['flags'])) {
                        $data[$group_name][$col[$i]] = $c['default'];
                    }
                    continue;
                }

                    #STRUCT IS TYPE:
                $temp = $item;


                switch($c['type']){
                    case 'array':
                        if(!is_array($item)){
                            $errs[] = arr_push($errs_network, $col_name, 'Поле "' . $c['label'] . '" - не верного типа');
                            $noError = false;
                        }
                    break;
                    case 'struct':
                        $keys_str = array_keys($c['struct']);

                        $res = $this->valid($item, $c['struct'], [
                            'key_prefix'    => $col_name.'_'
                        ]);

                        for($l=0, $lenl=sizeof($keys_str); $l<$lenl; $l++){


                            $data[$keys_str[$l]] = arr_move_key($res[$keys_str[$l]]);

                        }
                        if($res['error']){
                            $errs = array_merge($errs, $res['error']);
                            $errs_network = array_merge($errs_network, $res['error_network']);
                            $noError = false;
                        }
                    break;
                    default:

                        @settype($temp, $c['type']);
                        settype($temp, gettype($item));

                        if ($temp !== $item) {
                            $errs[] = arr_push($errs_network, $col_name, 'Поле "' . $c['label'] . '" - не верного типа');

                            $noError = false;
                        }
                    break;
                }


                if ($c['valid'] !== NULL && !preg_match('#^[' . $c['valid'] . ']+$#ui', $item)) {
                    $errs[] = arr_push($errs_network, $col_name, 'Поле "' . $c['label'] . '" - не верного формата');
                    $noError = false;
                }
                if ($c['regxp'] !== NULL && !preg_match('/'.$c['regxp'].'/ui', $item)) {
                    $errs[] = arr_push($errs_network, $col_name, 'Поле "' . $c['label'] . '" - не валидно');
                    $noError = false;
                }

                if ($noError && $c['type'] !== 'struct') {
                    $data[$group_name][$col[$i]] = $temp;
                }
            }
        }

        $data_system = [];

        for($i=0, $len=sizeof($this->data_system); $i<$len; $i++){
            if($t = remove_item($arr, $this->data_system[$i])){
                $data_system[$this->data_system[$i]] = $t;
            }
        }
        return array_merge([
            'status' => sizeof($errs) === 0,
            'error' => $errs,
            'error_network' => $errs_network,
            'data_system'    => $data_system,
            'extra' => $arr
        ], $data);
    }

    function log($name, $msg, $level = 1)
    {
        global $log;
        return $log->set($name, $name, $level);
    }
}