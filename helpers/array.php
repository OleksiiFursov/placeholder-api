<?php


function is_map($arr, $map, $full = 0)
{
    for ($i = 0, $len = sizeof($arr); $i < $len; $i++) {
        if (empty(array_diff_key($arr[$i], $map))) {
            if (!$full) {
                return true;
            }
        } else {
            return false;
        }
    }
    return false;
}

function isTrue(&$arr, $fields, $type = 'and')
{
    for ($i = 0, $len = sizeof($fields); $i < $len; $i++) {
        if ($type === 'and') {
            if (!isset($arr[$fields[$i]]) || !$arr[$fields[$i]]) {
                return false;
            }
        } elseif ($type === 'or') {
            if (isset($arr[$fields[$i]]) && $arr[$fields[$i]]) {
                return true;
            }
        }
    }
    return $type === 'and';
}

function arr_remove_field($arr, $columns)
{
    for ($i = 0, $len = sizeof($columns); $i < $len; $i++) {
        unset($arr[$columns[$i]]);
    }
    return $arr;
}

function arr_remove(&$arr, $callback)
{
    foreach ($arr as $k => $v) {
        if ($callback($v)) {
            unset($arr[$k]);
        }
    }
}

function arr_filter($obj, $find = [])
{

    for ($i = 0, $len = sizeof($find); $i < $len; $i++) {
        $index = array_search($find[$i], $obj);
        if ($index !== false) {
            array_splice($obj, $index, 1);
        }
    }
    return $obj;
}

function get_arr_col($arr, $col)
{
    $new = [];
    for ($i = 0, $len = sizeof($arr); $i < $len; $i++) {
        $new[] = $arr[$i][$col];
    }
    return $new;
}

function arr_move_key($arr)
{
    $new = [];
    foreach ($arr as $k => $v) {

        foreach ((array)$v as $k2 => $v2) {
            $new[$k2][$k] = $v2;
        }
    }
    return $new;
}

function arr_push(&$arr, $key, $item)
{
    if (!isset($arr[$key])) {
        $arr[$key] = [$item];
    } else {
        $arr[$key][] = $item;
    }
    return $item;
}

function arr_is_assoc($arr)
{
    $keys = array_keys($arr);
    return array_keys($keys) !== $keys;
}

function toArray($v)
{
    return is_array($v) && !arr_is_assoc($v)? $v : [$v];
}

//function toArrayDeep($arr){
//    $res = [];
//    foreach ($arr as $k=>$v){
//        if(is_array($v)){
//            $res[$k]  = toArrayDeep($v);
//        }else{
//            $res[$k]  = $v;
//        }
//    }
//    return $res;
//}


function mix($words)
{
    $result = [];
    $n = count($words);
    $f = 1;
    for ($i = 1; $i <= $n; $i++) $f = $f * $i;
    for ($i = 0; $i < $f; $i++) {
        $pos = $i % ($n - 1);
        if ($pos == 0) $first = array_shift($words);
        $result[$i] = [];
        for ($j = 0; $j < $n - 1; $j++) {
            if ($j == $pos) $result[$i][] = $first;
            $result[$i][] = $words[$j];
        }
        if ($pos == ($n - 2)) {
            $words[] = $first;
        }
    }
    return ($result);
}

function my_array_group_callback($arr, $col, $cols)
{
    $res = [];


    for ($i = 0, $len = sizeof($arr); $i < $len; $i++) {
        $key = $arr[$i][$col];
        if (!isset($res[$key])) {
            $res[$key] = $arr[$i];
        } else {
            foreach ($cols as $k) {
                if (!is_array($res[$key][$k]))
                    $res[$key][$k] = [$res[$key][$k]];

                if ($arr[$i][$k] && !in_array($arr[$i][$k], $res[$key][$k]))
                    $res[$key][$k][] = $arr[$i][$k];

//                if(is_array($res[$key][$k])){
//
//                    array_push($res[$key][$k],$arr[$i][$k]);
//                }else{
//                    $res[$key][$k] = [$res[$key][$k]];
//                    array_push($res[$key][$k],$arr[$i][$k]);
//                }
            }
//            foreach ($arr[$i] as $k => $a){
//                if(is_array($a)){
//                    $res[$key][$k] = [...$res[$key][$k], ...$arr[$i][$k]];
//                }
//            }
        }
    }
    return array_values($res);
}



function array_group_callback($arr, $col = 'id', $callback = false, $one = false)
{
    $res = [];
    for ($i = 0, $len = sizeof($arr); $i < $len; $i++) {

        if (!isset($arr[$i][$col])) continue;

        $key = $arr[$i][$col];

        if (!isset($res[$key])) {
            $res[$key] = [];
        }
        if (is_bool($callback)) {
            if (!$callback)
                unset($arr[$i][$col]);

            if (!$one)
                $res[$key][] = $arr[$i];
            else
                $res[$key] = $arr[$i];
            continue;
        }
        if (!$one)
            $res[$key][] = $callback($arr[$i]);
        else
            $res[$key] = $callback($arr[$i]);
    }

    return $res;
}

function get_array_value()
{
    $args = func_get_args();
    $arr = array_shift($args);
    $current = $arr;
    foreach ($args as $arg) {
        $current = $current[$arg];
    }
    return $current;
}

function array_cascade($arr, $columns, $groupBy)
{
    if (!is_array($columns)) $columns = [$columns];
    $res = [];
    $columns_len = sizeof($columns);
    foreach ($arr as $item) {
        $_key_path = explode('.', $groupBy);
        if (isset($_key_path[1])) {
            $key = get_array_value($item, ...$_key_path);
        } else {
            $key = $item[$groupBy];
        }

        if (!isset($res[$key])) {
            $res[$key] = $item;
            for ($i = 0; $i < $columns_len; $i++) {
                $res[$key][$columns[$i]] = [$item[$columns[$i]]];
            }
        } else {
            for ($i = 0; $i < $columns_len; $i++) {
                $res[$key][$columns[$i]][] = $item[$columns[$i]];
            }
        }
    }
    return array_values($res);

}

function array_group_callback_key($arr, $col, $name = 'name', $value = 'value')
{
    $res = [];
    for ($i = 0, $len = sizeof($arr); $i < $len; $i++) {
        $key = $arr[$i][$col];
        if (!isset($res[$key])) {
            $res[$key] = [];
        }
        $res[$key][$arr[$i][$name]] = $arr[$i][$value];
    }
    return $res;
}

function array_group($arr, $col = 'name', $valName = 'value', $replaceDupl = true, $remove = [])
{
    $res = [];
    if ($remove === true) {
        $remove = [$col];
    }
    foreach ($arr as $v) {
        $key = $v[$col];
        if ($valName === '*') {
            $val = $v;
        } else {
            $val = $v[$valName];
        }
        if (!$replaceDupl && isset($res[$key])) {
            if ($remove) {
                for ($i = 0, $leni = sizeof($remove); $i < $leni; $i++) {
                    unset($val[$remove[$i]]);
                }
            }
            if (isset($res[$key][0])) {
                $res[$key][] = $val;
            } else {
                $_t = $res[$key];
                $res[$key] = [$_t, $val];
            }
        } else {
            if ($remove) {
                for ($i = 0, $leni = sizeof($remove); $i < $leni; $i++) {
                    unset($val[$remove[$i]]);
                }
            }
            if ($replaceDupl) {
                $res[$key] = $val;
            } else {
                $res[$key][] = $val;
            }

        }
    }

    return $res;
}


function arr_merge_by_column(&$arr, &$arr2, $columns_array = null, $uniq_column = 'id')
{
    if (!$columns_array) {
        $columns_array = array_keys($arr2);
    }
    if (!is_array($columns_array)) {
        $columns_array = [$columns_array];
    }

    $buffer_arr = array_flip($columns_array);
    foreach ($buffer_arr as $k => $v) {
        $buffer_arr[$k] = isset($arr[$k]) ? $arr[$k] : [];
    }
    for ($i = 0, $len = sizeof($columns_array); $i < $len; $i++) {
        if ($uniq_column !== null) {
            if (empty($arr2[$columns_array[$i]])) continue;
            for ($j = 0, $lenj = sizeof($arr2[$columns_array[$i]]); $j < $lenj; $j++) {
                $_data = $arr2[$columns_array[$i]][$j];
                $buffer_arr[$columns_array[$i]][$_data[$uniq_column]] = $_data;

            }
        } else {
            $arr[$columns_array[$i]] = array_merge($arr[$columns_array[$i]], $arr2[$columns_array[$i]]);
        }
    }


    if ($uniq_column !== null) {
        for ($i = 0, $len = sizeof($columns_array); $i < $len; $i++) {
            $arr[$columns_array[$i]] = array_values($buffer_arr[$columns_array[$i]]);
        }
    }
}

function array_every($arr, $call)
{
    foreach ($arr as $v) {
        if (!$call($v)) {
            return false;
        }
    }
    return true;
}

function array_each($arr, $call)
{
    foreach ($arr as $k => $v) {
        $arr[$k] = $call($v, $k);
    }
    return $arr;
}

function formatAll(&$arr, $types)
{

    $typeAs = [
        'date' => 'datetime'
    ];
    $simple_types = ['int', 'string', 'boolean', 'double', 'bool', 'double'];

    $sync = [];
    for ($i = 0, $len = sizeof($arr); $i < $len; $i++) {

        foreach ($arr[$i] as $key => &$value) {
            if (!isset($types[$key])) continue;

            $type = $types[$key][0];
//            if(isset($type['sync'])){
//                //$sync
//            }
            if (in_array($type, $simple_types)) {
                settype($value, $type);
            } else {
                if (isset($typeAs[$type])) {
                    $type = $typeAs[$type];
                }
                $value = FormatData::{$type}($value);
            }
        }

    }

}


function array_flat($arr, $type = null)
{
    return array_merge(...$arr);
}
