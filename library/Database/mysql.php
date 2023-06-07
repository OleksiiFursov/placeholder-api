<?php

define('DB_SQL_FUNCTIONS', ['now()', 'md5', 'NULL', 'UNIX_TIMESTAMP()', 'NOW()']);
define('DB_SQL_OPERATORS', ["LIKE", "NOT LIKE", "=", "!=", '<>', '>', '<', '>=', '<=', "REGEXP", "NOT REGEXP", 'IN', "NOT IN", "BETWEEN", "NOT BETWEEN", "IS NULL", "IS NOT NULL", "IS", 'MATCH']);
include __DIR__ . '/DB_build.php';


function db_error($prefix = 'Запись')
{
    global $db;
    if ($db->errno === 1062) {
        return Response::error($prefix . ' ' . str_get($db->error, "Duplicate entry '([^']+)-\d+") . ' уже используется');
    } else {
        return Response::error($db->error);
    }
}

class db
{
    public $last_query;
    public $insert_id = NULL;
    public $history;
    public $prefix;
    public $error = false;
    public $errno = false;
    public $force = false;
    public $debugGlobal = true;
    public $db;
    public $stats = [
        'select' => 0,
        'insert' => 0,
        'update' => 0,
        'delete' => 0,
        'system' => 0,
        'all' => 0,
        'error' => 0,
        'memory' => 0,
        'time' => 0
    ];

    function __construct($dbhost = null, $dbuser = null, $dbpass = null, $dbname = null)
    {
        global $conf;

        $db = @new mysqli($dbhost ?? $conf['db']['host'], $dbuser ?? $conf['db']['user'], $dbpass ?? $conf['db']['pass'], $dbname ?? $conf['db']['name']);
        if ($db->connect_errno) {
            $msg = $db->connect_error . ' (' . $db->connect_errno . ')';
            file_put_contents(DIR . '/logs/db_error.txt', date('Y-m-d H:i:s') . ' - ' . $msg . "  \n", FILE_APPEND);
            Response::error($msg);
        }

        $this->prefix = $dbpref ?? $conf['db']['pref'];
        $this->db = $db;
        //$this->query('SET NAMES utf8');
        return $this;
    }


    function query($sql)
    {
        $this->last_query = $sql;
        $this->history[] = $sql;
        try {
            $q = $this->db->query($sql);
        } catch (Exception $e) {
            $q = false;
            if ($this->db->error && !$this->force) {

                ini('IS_ERROR', true);
                ini('ERROR_DB', [$this->db->error, $this->last_query]);

                Response::error($e);
                if (defined('IS_DEV'))
                    Response::error($this->db->error . ' => ' . $sql);

                if ($this->db->errno === 1062) {
                    Response::end('Duplicate record', 488);
                }
            }
        }

        if ($this->db->error && !$this->force) {

            ini('IS_ERROR', true);
            ini('ERROR_DB', [$this->db->error, $this->last_query]);

            if (defined('IS_DEV'))
                Response::error($this->db->error . ' => ' . $sql);

            if ($this->db->errno === 1062) {
                Response::end('Duplicate record', 488);
            }
        }
        $this->error = $this->db->error;
        $this->errno = $this->db->errno;
        return $q;
    }

    function ins($table, $arr, $multi = false, $only_code = false)
    {
        if (str_contains($table, ' as')) {
            $table = explode(' as', $table);
            $table = $table[0];
        }
        $sql = 'INSERT INTO ' . $this->prefix . $table;

        if ($multi) {
            $keys = array_keys($arr[0]);
            $sql .= ' (' . implode(',', $keys) . ') VALUES';
            $values = [];
            for ($i = 0, $len = sizeof($arr); $i < $len; $i++) {
                $val_cur = [];
                for ($j = 0, $lenj = sizeof($keys); $j < $lenj; $j++) {
                    $val_cur[] = $this->normalize_type_data($arr[$i][$keys[$j]]);
                }
                $values[] = '(' . implode(',', $val_cur) . ')';
            }
            $sql .= implode(',', $values);
        } else {
            $sql .= ' SET ' . $this->set_arr($arr);
        }
        if ($only_code) return $sql;
        $this->query($sql);

        if ($this->db->error) {
            ini('IS_ERROR', true);
            ini('ERROR_DB', [$this->db->error, $this->last_query]);
            $this->insert_id = null;
            // $log->set('Error DB', $this->db->error, 'error');
        } else {
            $this->insert_id = $this->db->insert_id;
        }

        return $this->insert_id;
    }

    function insMulti()
    {
        $args = func_get_args();

        $table = array_shift($args);

        $rows = [];


        for ($i = 0, $len = sizeof($args); $i < $len; $i++) {
            $arg = $args[$i];

            $multi = false;
            if (isset($arg[0]) && is_array($arg[0])) {


                if (isset($arg[1]) && !arr_is_assoc($arg[0])) {
                    $c1 = 0;
                    foreach ($arg[1] as $k => $v) {

                        $rows[$c1][$arg[0][0]] = $k;
                        $rows[$c1][$arg[0][1]] = $v;
                        $c1++;
                    }


                } else {
                    $rows = $args[0];
                    $multi = true;
                    $c1 = sizeof($rows);

                }
                continue;

            }


            if (!$multi) {

                foreach ($arg as $k => $v) {
                    $c2 = 0;
                    if (is_array($v)) {

                        for ($j = 0, $lenj = sizeof($v); $j < $lenj; $j++) {
                            $rows[$c2][$k] = $v[$j];
                        }
                        $c2++;
                    } else {


                        while (isset($c1) && $c2 < $c1) {
                            $rows[$c2++][$k] = $v;
                        }
                        continue;
                    }

                }
            }
        }


        $ids = [];


        for ($i = 0, $len = sizeof($rows); $i < $len; $i++) {

            $ids[] = $this->ins($table, $rows[$i]);
        }
        return $ids;
    }

    function upd($table, $arr, $where, $multi = false)
    {


        global $log;

        $table = preg_replace('#as [a-zA-Z\d]$#', '', $table);
        if (!sizeof($arr)) {
            return null;
        }
        if ($multi) {
            $sql = '';
            foreach ($arr as $key => $item) {
                $sql .= 'UPDATE ' . $this->prefix . $table . ' SET ' . $this->set_arr($item) . ' WHERE ' . $this->where_exp($where[$key]) . ';';

            }
            $this->db->multi_query($sql);
            return $arr;
        } else {
            $sql = 'UPDATE ' . $this->prefix . $table . ' SET ' . $this->set_arr($arr) . ' WHERE ' . $this->where_exp($where);
        }


        $res = $this->query($sql);

        if ($this->db->error) {
            ini('IS_ERROR', true);
            ini('ERROR_DB', [$this->db->error, $this->last_query]);
            if ($this->debugGlobal) {
                $this->stats['update']++;
            }

            // $log->set('Error DB', $this->db->error, 'error');
        }
        $this->insert_id = $this->db->insert_id;

        return $res;
    }

    function del($table, $where)
    {
        global $log;
        $res = $this->query('DELETE FROM ' . $this->prefix . $table . ' WHERE ' . $this->where_exp($where));

        if ($this->db->error) {
            ini('IS_ERROR', true);
            ini('ERROR_DB', [$this->db->error, $this->last_query]);
            // $log->set('Error DB', $this->db->error, 'error');
        }

        return $res;
    }

    function count($table, $where = null)
    {
        global $log;
        $res = $this->result('SELECT COUNT(*) FROM ' . $this->prefix . $table . ' WHERE ' . $this->where_exp($where), 'row')[0][0];
        if ($this->db->error) {
            ini('IS_ERROR', true);
            ini('ERROR_DB', [$this->db->error, $this->last_query]);
            // $log->set('Error DB', $this->db->error, 'error');
        }

        return $res;
    }

    function col($col, $table, $where = null)
    {
        $q = $this->sel($col, $table, $where, 1);
        if (!$q || !isset($q[0][$col])) {
            return null;
        }
        return $q[0][$col];
    }

    function row($col, $table, $where = false)
    {
        $q = $this->sel($col, $table, $where, 1);
        if (!$q || !isset($q[0])) {
            return null;
        }
        return $q[0];
    }

    function sel()
    {
        if ($this->debugGlobal) {
            $time_last = microtime(TRUE);
            $memory_last = memory_get_peak_usage(false);
        }
        $len = func_num_args();
        $args = func_get_args();
        $type = 'assoc';

        $last = $args[$len - 1];
        if (gettype($last) === 'string' && $last[0] === '#' && $last[1] === '!') {
            if (array_pop($args) === '#!2') {
                $type = 'row';
            }
            $len--;
        }
        if (!is_array($args[0])) {

            if ($len === 1):
                $args = ['table' => $args[0]];

            elseif ($len === 2):
                $args = ['table' => $args[0], 'where' => $args[1]];

            elseif ($len === 3):
                $args = ['from' => $args[0], 'table' => $args[1], 'where' => $args[2]];

            elseif ($len === 4):
                $args = ['from' => $args[0], 'table' => $args[1], 'where' => $args[2], 'limit' => $args[3]];

            elseif ($len === 5):
                $args = ['from' => $args[0], 'table' => $args[1], 'where' => $args[2], 'limit' => $args[3], 'cache' => $args[4]];
            endif;
        } else {
            $args = $args[0];
        }

        $this->stats['select']++;
        $args += [
            'from' => false,
            'table' => false,
            'where' => false,
            'limit' => false,
            'cache' => false
        ];
        if ($len > 0) {
            $args['table'] = $this->prefix . $args['table'];
            $args['where'] = $this->where_exp($args['where']);
        }

        $this->last_query = "SELECT " . ($args['from'] === false ? '*' : $args['from']) . " FROM " . $args['table'] . " " . ($args['where'] !== false ? ' WHERE ' . $args['where'] : '') . ($args['limit'] !== false ? ' LIMIT ' . $args['limit'] : '');


        // if ($args['cache']) {
//            $file = get_dir('cache_db').'/'.md5($this->last_query).'.php';
//
//            $age_file = file_exists($file) ? filemtime($file) : 0+$args['cache']>$_SERVER['REQUEST_TIME'];
//            if($age_file){
//                include $file;
//                return $qaz;
//            }
        //    }

        $ret = $this->result($this->last_query, $type);


//        if($args['cache'] && !$age_file){
        /*            file_put_contents($file, '<?php $qaz = '.var_export($ret, TRUE).'; ?>');*/
//        }

        if ($this->debugGlobal) {
            $time_last = microtime(TRUE) - $time_last;
            $memory_last = memory_get_peak_usage(false) - $memory_last;

            $this->stats['time'] += $time_last;
            $this->stats['memory'] += $memory_last;

        }
        return $ret;

    }


    function build()
    {
        return new DB_build($this);
    }

    function where_exp($arr)
    {
        if (empty($arr)) return 0;

        if (!is_array($arr)) {
            if (!is_numeric($arr)) return $arr;

            $arr = array('id' => $arr);
        }
        $str = '';

        if (is_array($arr) && isset($arr[0])) {
            $flag = false;
//            if(isset($arr[1])){
//
//                if(preg_match('/^~~(.+)/',$arr[1], $po)){
//                    $flag = NULL;
//                }
//            }
            // if($flag !== NULL) {
            for ($i = 0, $len = sizeof($arr); $i < $len; $i++) {

                if (!is_numeric($arr[$i])) {
                    $flag = true;
                    break;
                }
            }
            if ($flag === false)
                return '`id` IN (' . implode(',', $arr) . ')';
            else
                return '`name` IN (' . implode(',', $arr) . ')';
            // }
        }

        foreach ($arr as $k => $v) {
            if ($k === 0)
                $k = (is_numeric($v)) ? 'id' : 'name';

            $str .= ($str != '' ? ' AND ' : '') . '`' . str_replace('.', '`.`', trim($k)) . '`';

            $typeAction = 'LIKE';

            if (is_object($v)) {
                $str .= $v->return;
                continue;
            }


//            if(isset($po[1])){
//                $typeAction = $po[1];
//
//            }

            if (is_array($v) && isset($v[0])) {
                if (sizeof($v) === 2 && in_array($v[0], DB_SQL_OPERATORS)) {
                    if (!is_array($v[1]))
                        $v[1] = trim($v[1]);

                    $str .= ' ' . $v[0] . ' ' . (is_numeric($v[1]) ? $v[1] : (in_array($v[1], DB_SQL_FUNCTIONS) ? $v[1] : '"' . $v[1] . '"'));
                } else {
                    $str .= $this->where_in($v);
                }

            } else {
                if (!is_array($v)) {
                    $v = trim($v);
                } else {
                    if (!sizeof($v))
                        $v = '';
                }
                $_v = (is_numeric($v) ? $v : (in_array($v, DB_SQL_FUNCTIONS) ? $v : '"' . $v . '"'));

                if (gettype($_v) === 'string' && !preg_match('/"?%/u', $_v)) {
                    $typeAction = '=';
                }
                $str .= ' ' . $typeAction . ' ' . $_v;
            }
        }
        return $str;
    }

    function where_in($values)
    {
        for ($i = 0, $len = sizeof($values); $i < $len; $i++) {
            if (is_string($values[$i]))
                $values[$i] = '"' . $values[$i] . '"';
        }
        return ' IN (' . implode(',', $values) . ')';

    }

    function result($sql, $type = 'assoc')
    {
        $q = $this->query($sql);

        $arr = [];
        if ($q) {
            while ($row = $q->{'fetch_' . $type}()) {
                $arr[] = $row;
            }
        }


        return $arr;
    }


    function normalize_type_data($value)
    {

        if (is_array($value)) {
            $value = json_encode($value, 256);
        }
        if (!in_array($value, DB_SQL_FUNCTIONS)) {
            if (is_string($value))
                $value = "'" . $this->escape_string($value) . "'";
            elseif (is_null($value)) {
                $value = 'NULL';
            } else {
                $value = (float)$value;
            }
        }
        return $value;
    }

    function set_arr($arr)
    {
        $buf = [];
        foreach ($arr as $key => $value) {

            $value = $this->normalize_type_data($value);

            $buf[] = '`' . $key . '` = ' . $value;
        }
        return implode(', ', $buf);

    }

    function escape_string($sql)
    {
        return $this->db->real_escape_string($sql);

    }

    function error()
    {
        return $this->db->error ? $this->db->error . '| ' . $this->last_query : null;
    }

    function schema($table)
    {
        return $this->result('explain ' . $this->prefix . $table);
    }

}
