<?php

/**
 * Class DB_build
 * * @method db query($sql)
 */
class DB_build
{
    public $type, $select = [], $update = [], $insert = [], $delete = [], $from = [], $limit = null, $offset = 0, $order = [],
        $time_start = 0, $memory_start = 0, $time_last = 0, $memory_last = 0, $debug = false, $sub = [],
        $leftJoin = [], $rightJoin = [], $innerJoin = [], $join = [], $groupBy = [], $having = [], $where = [], $events = [], $columns = [],
        $db = null,
        $isEmpty = false,
        $model = null,
        $replace = [],
        $safe = false,
        $nodes = [];

    private $prefix_use = [];
    private $nodes_use = [];


    function force($status = true)
    {
        $this->db->force = $status;
        return $this;
    }

    function replace($find, $replace = '', $regExp = false)
    {
        $this->replace[] = [$find, $replace, $regExp];
        return $this;
    }

    function __construct(&$db)
    {
        $this->debug = IS_DEV || $db->debugGlobal;
        $this->db = &$db;
        event('DB_build.init', ['context' => &$this]);
        return $this;
    }

    function params($params)
    {
        if (!empty($params['order'])) {
            $this->order($params['order'], $params['order_dir']);
        }
        if (!empty($params['limit'])) {
            $this->limit($params['offset'], $params['offset']);
        }
        if (!empty($params['sql'])) {
            $this->merge($params['sql']);
        }
        if (isset($params['columns'])) {
            $this->select(isset($params['exclude_columns']) ? arr_filter($params['columns'], $params['exclude_columns']) : $params['columns']);
        }
        return $this;
    }

    /**
     * @param null $type 0 - return resource, 1 - return assoc, 2 - return row, 3 - return object, 4 - return sql-query
     * @param bool $format
     * @return array|bool|mysqli_result|string|string[]
     */
    public function run($type = null, $format = true)
    {
        event('DB_build.run', [
            'context' => &$this,
        ]);
        if ($this->debug) {
            $this->time_start = microtime(TRUE);
            $this->memory_start = memory_get_peak_usage();
        }
        if ($this->model && ($this->type === 'insert' || $this->type === 'update')) {
            /** @var $columns */
            if (method_exists($this->model, 'onInsert')) {
                $this->model::onInsert();
            }
            $this->valid($this->model::$columns);
        }

        $result = false;

        $this->db->stats[$this->type]++;
        $from = implode(',', $this->from);

        if ($this->offset || $this->limit) {
            $_limit = 'LIMIT ';
            if ($this->offset) {
                $_limit .= $this->offset . ', ';
            }

            $_limit .= (int)$this->limit ?? 999999999;

        } else {
            $_limit = '';
        }

        switch ($this->type) {
            case 'select':

                if (is_string($type)) {
                    if ($type === 'row') {
                        $type = 2;
                    } else {
                        $type = null;
                    }
                }

                if (empty($this->select)) {
                    $select = '*';
                } else {
                    $select = $this->select;
                    for ($i = 0, $len = sizeof($select); $i < $len; $i++) {
                        $select[$i] = $this->column_normalize($select[$i]);
                    }

                    $select = implode(', ', $select);
                }
                $sub = implode(', ', $this->sub);

                $groupBy = implode(',', $this->groupBy);
                $having = $this->where_export($this->having, [
                    'column_normalize' => false
                ]);


                $leftJoin = implode(" ", $this->leftJoin);
                $rightJoin = implode(" ", $this->rightJoin);
                $innerJoin = implode(" ", $this->innerJoin);
                $join = implode(" ", $this->join);

                $where = $this->where_export();

                if (empty($where))
                    $where = 1;

                $order = implode(',', $this->order);

                $result = "SELECT " . $select .
                    (!empty($sub) ? ", " . $sub : '') .
                    (!empty($from) ? " FROM " . $from : '') . " " .
                    (!empty($leftJoin) ? $leftJoin . " " : '') .
                    (!empty($rightJoin) ? $rightJoin . " " : '') .
                    (!empty($innerJoin) ? $innerJoin . " " : '') .
                    (!empty($join) ? $join . " " : '') .
                    ((!empty($where) && $where !== 1) ? "WHERE " . $where . " " : '') .
                    (!empty($groupBy) ? 'GROUP BY ' . $groupBy . " " : '') .
                    (!empty($having) ? "HAVING " . $having . " " : '') .
                    (!empty($this->order) ? "ORDER BY " . $order . " " : '') .
                    $_limit;


                if ($this->replace) {
                    foreach ($this->replace as $item) {
                        if (!$item[2])
                            $result = str_replace($item[0], $item[1], $result);
                        else
                            $result = preg_replace($item[0], $item[1], $result);
                    }
                }
                if ($type !== 4) {
                    if ($type === 1 || $type === null) {

                        $result = $this->db->result($result);

                        if ($format) {

                            if ($this->model)
                                formatAll($result, $this->model::$columns);
                            else {
                                $Model = new Model();
                                for ($i = 0, $len = sizeof($result); $i < $len; $i++) {
                                    $Model->format($result[$i], is_array($format) ? $format : NULL);
                                }
                            }
                        }
                    }
                    if ($type === 2) {
                        $result = $this->db->result($result, 'row');
                    }
                    if ($type === 3) {
                        $result = $this->db->result($result, 'object');
                    }
                    $this->db->len = sizeof($result);
                }

                $this->event('select.after', [&$this, &$result]);
                break;
            case 'delete':
                $from = preg_replace('/as [a-zA-Z_0-9]+/', '', $from);

                $where = $this->where_export();

                $innerJoin = implode(" ", $this->innerJoin);
                $order = implode(',', $this->order);
                if (empty($where))
                    $where = 1;

                $result = "DELETE FROM " . $from . " " .
                    (!empty($innerJoin) ? $innerJoin . " " : '') .
                    "WHERE " . $where . " " .
                    (!empty($this->order) ? "ORDER BY " . $order . " " : '') .
                    (($this->offset || $this->limit) ? $_limit : '');


                if ($where == 1 && !$this->safe) {
                    return Response::error('Query ' . $result . ' is not safe');
                }

                if ($type === 4) {
                    return $result;
                } else {
                    $result = $this->db->query($result);
                }


                break;
            case 'insert':
                if (empty($this->insert)) return false;

                $from = preg_replace('/as \w+/', '', $from);


                if (!arr_is_assoc($this->insert) && sizeof($this->insert) > 1) {
                    $arr = $this->insert;
                } else {
                    $this->from[0] = str_replace($this->db->prefix, '', $this->from[0]);

                    if ($type !== 4) {
                        return $this->db->ins($this->from[0], $this->insert[0]);
                    } else {
                        return $this->db->ins($this->from[0], $this->insert[0], false, true);

                    }
                }

                if (!sizeof($arr)) break;


                if ($this->model) {
                    $columns = $this->model::getColumnsForInsert();
                    $diff = array_diff(array_keys($arr[0]), $columns);
                    if ($diff) {
                        return Response::error('Model ' . $this->model . '->insert failed ' . json_encode($diff, 256));
                    }
                } else {
                    $columns = array_keys($arr[0]);
                }


                $buf = [];
                for ($i = 0, $len = sizeof($arr); $i < $len; $i++) {
                    $item = [];

                    for ($j = 0, $lenj = sizeof($columns); $j < $lenj; $j++) {
                        $item[$j] = (isset($arr[$i][$columns[$j]]) ?
                            $this->_where_correct_type($arr[$i][$columns[$j]]) : 'NULL');
                    }

                    $buf[] = '(' . implode(',', $item) . ')';
                }

                $result = 'INSERT INTO ' . $from . ' (`' . implode('`,`', $columns) . '`) VALUES ' . implode(',', $buf);

                if ($type === 4) {
                    return $result;
                } else {
                    $result = $this->db->query($result);
                }
                if ($result) {
                    $this->insert_id = $this->db->insert_id;
                    return $this->insert_id;
                }

                break;
            case 'update':


                if (empty($this->update)) {
                    return false;
                }

                $where = $this->where_export();
                if (empty($where))
                    return Response::warn('MySQL: Update is empty');


                if ($this->model) {
                    $columns = $this->model::getColumnsForInsert();
                    $diff = array_diff(array_keys($this->update[0]), $columns);
                    if ($diff) {
                        return Response::error('Model ' . $this->model . '->update failed ' . json_encode($diff, 256));
                    }
                } else {
                    $columns = array_keys($this->update[0]);
                }
                if (!arr_is_assoc($this->update) && sizeof($this->update) > 1) {
                    $arr = $this->update;
                } else {
                    $this->from[0] = str_replace($this->db->prefix, '', $this->from[0]);
                    return $this->db->upd($this->from[0], $this->update[0], $where);
                }

                if (!sizeof($arr)) break;

                $is_columns = empty($this->columns);


                $buf = [];
                for ($i = 0, $len = sizeof($arr); $i < $len; $i++) {
                    if ($is_columns) {
                        $item = array_values($arr[$i]);
                        for ($j = 0, $lenj = sizeof($item); $j < $lenj; $j++) {
                            $item[$j] = $this->_where_correct_type($item[$j]);
                        }
                    } else {
                        $item = [];

                        for ($j = 0, $lenj = sizeof($this->columns); $j < $lenj; $j++) {
                            $item[$j] = (isset($arr[$i][$this->columns[$j]]) ?
                                $this->_where_correct_type($arr[$i][$this->columns[$j]]) : 'NULL');
                        }
                    }
                    $buf[] = '(' . implode(',', $item) . ')';
                }
                $result = 'UPDATE  ' . $from . ' (`' . implode('`,`', $columns) . '`) VALUES ' . implode(',', $buf) . " WHERE " . $where . ' ' . $_limit;
                if ($where == 1 && !$this->safe) {
                    return Response::error('Query ' . $result . ' is not safe');
                }
                if ($type === 4) {
                    return $result;
                } else {

                    $result = $this->db->query($result);
                }


                break;
            default:
                notice('No selected type query');
                break;
        }

        if ($this->debug) {
            $this->time_last = microtime(TRUE) - $this->time_last;
            $this->memory_last = memory_get_peak_usage(false) - $this->memory_last;

            $this->db->stats['time'] += $this->time_last;
            $this->db->stats['memory'] += $this->memory_last;

        }

        return $result;
    }


    /**
     * @param $columns
     * @return $this
     */
    public function select($columns = null)
    {
        $this->type = 'select';
        if (!$columns) return $this;

        if (!is_array($columns)) {
            $columns = explode(',', $columns);
            $columns = array_map('trim', $columns);
        }


        $this->select = array_merge($this->select, $columns);
        return $this;
    }

    /**
     * @param $query
     * @return $this
     */

    function reset($name, $value = [])
    {
        $this->{$name} = $value;
        return $this;
    }

    public function unselect($query)
    {
        return $this->_un($this->select, $query);
    }

    public function unwhere($query)
    {
        return $this->_un($this->where, $query);
    }

    /**
     * @param $from
     * @param $prefix
     * @return $this
     */
    public function from($from, $prefix = null)
    {
        if ($prefix === null) {
            $prefix = $this->db->prefix;
        }
        if ($from instanceof DB_build) {
            $from = ['(' . $from->run(4) . ') as ' . $prefix];
            $prefix = null;
        } else if (!is_array($from)) {
            $from = explode(',', $from);
        }

        $this->from = array_merge($this->from, $this->_prefix($from, $prefix));
        return $this;
    }

    public function update($arr)
    {
        $this->type = 'update';

        if (is_array($arr) && !empty($arr)) {
            if (!isset($arr[0])) {
                $arr = [$arr];
            }
            $this->update = array_merge($this->update, $arr);
        }
        return $this;
    }

    public function unupdate($query)
    {
        return $this->_un($this->update, $query);
    }


    public function insert($arr)
    {
        $this->type = 'insert';

        if (is_array($arr) && !empty($arr)) {
            if (!isset($arr[0])) {
                $arr = [$arr];
            }
            $this->insert = array_merge($this->insert, $arr);
        }
        return $this;
    }

    public function uninsert($query)
    {
        return $this->_un($this->insert, $query);
    }

    public function sub($obj, $name)
    {
        if (!is_string($obj) && !($obj instanceof DB_build)) {
            Response::error('Подзапрос не верного формата');
            ini('IS_ERROR', true);
            $this->db->stats['error']++;
            return $this;
        }
//        if (!is_array($obj)) {
//            json($obj);
//            $obj = explode(',', $obj);
//        }

        if ($obj instanceof DB_build)
            $obj = $obj->run(4);
        $this->sub[] = '(' . trim($obj) . ') as ' . $name;

        return $this;
    }

    /**
     * @param $query
     * @return $this
     */
    public function unfrom($query)
    {
        return $this->_un($this->from, $query);
    }

    /**
     * @param $a
     * @param null $b
     * @return $this
     */
    public function limit($a, $b = null)
    {

        if ($a === 999999999999 && $b === null)
            return $this;
        if ($b === null) {
            $this->limit = $a;
        } else {
            $this->offset = $a;
            $this->limit = $b;
        }
        return $this;
    }

    /**
     * @param $num
     * @return $this
     */
    public function offset($num)
    {
        $this->offset = $num;
        return $this;
    }

    /**
     * @param $column
     * @param string $dir
     * @return $this
     */
    public function order($column, $dir = 'ASC')
    {
        if (is_bool($dir)) {
            $dir = $dir ? 'ASC' : 'DESC';
        }
        $this->order[] = $column . ' ' . $dir;


        return $this;
    }

    /**
     * @param $query
     * @return $this
     */
    public function unorder($query)
    {
        return $this->_un($this->order, $query);
    }

    /**
     * @param $table
     * @param $on
     * @return $this
     */
    public function leftJoin($table, $on)
    {
        $this->leftJoin[] = $this->_join($table, $on, 'LEFT');
        return $this;
    }

    /**
     * @param $query
     * @return $this
     */
    public function unleftjoin($query)
    {
        return $this->_un($this->leftJoin, $query);
    }

    /**
     * @param $table
     * @param $on
     * @return $this
     */
    public function rightJoin($table, $on)
    {
        $this->rightJoin[] = $this->_join($table, $on, 'RIGHT');
        return $this;
    }

    /**
     * @param $query
     * @return $this
     */
    public function unrightJoin($query)
    {
        return $this->_un($this->rightJoin, $query);
    }

    /**
     * @param $table
     * @param $on
     * @return $this
     */
    public function innerJoin($table, $on)
    {
        $this->innerJoin[] = $this->_join($table, $on, 'INNER');
        return $this;
    }

    /**
     * @param $query
     * @return $this
     */
    public function uninnerJoin($query)
    {
        return $this->_un($this->innerJoin, $query);
    }

    /**
     * @param $table
     * @param $on
     * @param string $type
     * @return DB_build
     */
    public function join($table, $on, $type = '')
    {
        $this->join[] = $this->_join($table, $on, $type);
        return $this;
    }

    /**
     * @param $query
     * @return $this
     */
    public function unjoin($query)
    {
        return $this->_un($this->join, $query);
    }

    /**
     * @param $column
     * @return $this
     */
    public function groupBy($column)
    {
        if (!is_array($column)) {
            $column = explode(',', $column);
        }
        $this->groupBy = array_merge($this->groupBy, $column);
        return $this;
    }

    /**
     * @param $query
     * @return $this
     */
    public function ungroupBy($query)
    {
        return $this->_un($this->groupBy, $query);
    }


    /**
     * @param $query
     * @return $this
     */
    public function unhaving($query)
    {
        return $this->_un($this->having, $query);
    }

    protected function filter_operation($columns, $operand = 'AND', $operandGroup = 'AND', $group = false, $variable = 'where')
    {
        if (!is_array($columns)) {
            if (is_string($columns) && $operand !== 'AND') {

                if ($operandGroup !== 'AND' && in_array($operandGroup, DB_SQL_OPERATORS))
                    $columns = [[$columns, $operand, $operandGroup]];
                else
                    $columns = [[$columns, $operand, is_array($operand) ? 'IN' : '=']];

                $operand = 'AND';
                $operandGroup = 'AND';
            } else {

                if (is_numeric($columns)) {
                    $columns = [['id', $columns, '=']];
                } else {
                    $columns = [['name', $columns, '=']];
                }
            }
        } else {
            if (sizeof($columns) === 0) return $this;

            if (!is_assoc($columns)) {
                if (!is_array($columns[0]) && isset($columns[2]) && in_array(strtoupper($columns[2]), DB_SQL_OPERATORS)) {
                    $columns = [$columns];
                }

                if (array_every($columns, 'is_numeric')) {
                    $columns = [['id', $columns, 'IN']];
                } elseif (array_every($columns, 'is_string')) {
                    $columns = [['name', $columns, 'IN']];
                } else {
                    $res = [];

                    foreach ($columns as $v) {
                        $res[] = $this->AssocToRow($v)[0];
                    }
                    $columns = $res;
                }
            } else {
                $columns = $this->AssocToRow($columns);

            }
        }

        $this->{$variable}[] = [
            'columns' => $columns,
            'operand' => $operand,
            'operandGroup' => $operandGroup,
            'group' => $group
        ];

        return $this;
    }

    /**
     * @param $columns
     * @param string $operand
     * @param string $operandGroup
     * @param bool $group
     * @return $this
     */
    public function having($columns, $operand = 'AND', $operandGroup = 'AND', $group = false)
    {
        return $this->filter_operation($columns, $operand, $operandGroup, $group, __FUNCTION__);
    }

    public function where($columns, $operand = 'AND', $operandGroup = 'AND', $group = false)
    {
        if (empty($columns)) return $this;

        return $this->filter_operation($columns, $operand, $operandGroup, $group, __FUNCTION__);
    }

    public function row($type = null, $format = true, $def = null)
    {
        return $this->run($type, $format)[0] ?? $def;
    }

    public function AssocToRow($columns)
    {

        $newColumns = [];
        foreach ($columns as $name => $value) {

            $value_is_array = is_array($value);

            $value_length = $value_is_array ? sizeof($value) : null;

            if ($value_is_array && $value_length === 2 && in_array($value[1], DB_SQL_OPERATORS, true)) {
                $newColumns[] = [$name, $value[1] === 'LIKE' ? '%' . $value[0] . '%' : $value[0], $value[1]];
            } elseif ($value_is_array && $value_length === 1 && ($_k = array_key_first($value)) && in_array(strtoupper($_k), DB_SQL_OPERATORS)) {
                if ($_k === 'like') {
                    $value[$_k] = '%' . $value[$_k] . '%';
                }
                $newColumns[] = [$name, $value[$_k], strtoupper($_k)];
            } else {
                $operator = $value === null ? 'IS NULL' : ($value_is_array ? 'IN' : '=');
                $newColumns[] = [$name, $value, $operator];
            }

        }

        return $newColumns;
    }


    public function one($type = 1)
    {
        $this->limit = 1;
        $res = $this->run($type);

        if ($type === 4) {
            return $res;
        }
        if (is_array($res) && isset($res[0])) {
            return $res[0];
        } else {
            return false;
        }
    }

    /**
     * @param $col
     * @return bool|mixed|null
     */
    public function column($col)
    {
        $this->select = [$col];
        $this->type = 'select';

        $res = $this->run(2);

        if (is_array($res)) {
            return $res[0][0];
        } else {
            return false;
        }
    }

    function column_all($col)
    {
        $this->type = 'select';
        $this->select = [$col];
        $res = $this->run(2);


        if (is_array($res)) {
            return array_map(fn($v) => $v[0], $res);
        } else {
            return false;
        }


    }

    /**
     * @param null $from
     * @param null $where
     * @return bool|mixed|null
     */
    public function count($from = null, $where = null)
    {
        if ($from) $this->from($from);
        if ($where) $this->where($where);

        return $this->column('~COUNT(*)');
    }

    /*
     *
     */
    public function delete($delete = null, $run = 1)
    {
        $this->type = 'delete';

        if ($delete === false) {
            return $this;
        }
        if (is_null($delete)) {
            return $this->run($run);
        } else {
            $this->where($delete);
        }
        return $this;
    }

    /**
     * @param $col
     * @return string
     */
    public function column_normalize($col)
    {
        if ($col[0] === '~') return substr($col, 1);
        $add_q = explode('.', $col);
        $last = &$add_q[sizeof($add_q) - 1];
        $last = explode(' ', $last);
        if (preg_match('#^[a-zA-Z\d_-]+$#', $last[0]))
            $last[0] = '`' . $last[0] . '`';
        $last = implode(' ', $last);
        return implode('.', $add_q);
    }

    /**
     * @param $value
     * @return float|string
     */
    protected function _where_correct_type($value, $operand = '=')
    {
        if (!in_array($value, DB_SQL_FUNCTIONS)) {
            if (gettype($value) == 'string') {
                if ($operand === 'LIKE') {
                    $value = '%' . $value . '%';
                }
                $value = "'" . $this->db->escape_string($value) . "'";
            } else if (is_numeric($value)) {
                $value = (double)$value;
            }
        }
        return $value;
    }

    /**
     * @param $col
     * @param $value
     * @param string $operand
     * @param array $options
     * @return string
     */
    public function where_item($col, $value, $operand = '=', $options = [])
    {
        if ($operand === 'MATCH')
            return 'MATCH(' . $col . ') AGAINST(\'' . $value . '\' IN BOOLEAN MODE)';
        if (!is_string($value) || !isset($value[0]) || $value[0] !== '~') {
            if (is_array($value)) {
                if (empty($value)) {
                    $this->where_impossible = true;
                    $value = [-1];
                }
                if (array_every($value, 'is_string')) {
                    $value = array_map(function ($value) use ($operand) {
                        if (!in_array($value, DB_SQL_FUNCTIONS)) {

                            $value = "'" . $this->db->escape_string($value) . "'";
                        }
                        return $value;
                    }, $value);
                }
                $value = '(' . implode(', ', $value) . ')';
            } else {
                $value = $this->_where_correct_type($value, $operand);
            }
        } elseif ((isset($value[0]) && $value[0] === '~')) {

            $value = substr($value, 1);
        }


        return (((isset($options['column_normalize']) && $options['column_normalize']) || !isset($options['column_normalize'])) ? $this->column_normalize($col) : $col) . ' ' . $operand . ' ' . $value;
    }

    public function node($name)
    {
        /** @var $table_name_alt */
        /** @var $table */


        $data = is_string($name) ? $this->nodes[$name] : $name;

        $where = remove_item($data, 'where');
        $join = remove_item($data, 'join', 'left');
        $groupBy = remove_item($data, 'groupBy',);
        if (property_exists($data[0], 'table_name_alt')) {
            $pref = $data[0]::$table_name_alt;
        } else {
            $pref = explode(' ', $data[0]::$table)[0];
        }

        if (isset($this->nodes_use[$data[0]::$table])) {
            $this->nodes_use[$data[0]::$table]++;

            $sufTable = ' as ' . $pref . $this->nodes_use[$data[0]::$table];
            $joinParams = [];
            foreach ($data[1] as $k => $v) {

                $knew = str_replace($pref . '.', $pref . $this->nodes_use[$data[0]::$table] . '.', $k);
                $joinParams[$knew] = str_replace($pref . '.', $pref . $this->nodes_use[$data[0]::$table] . '.', $v);
            }
            $pref .= $this->nodes_use[$data[0]::$table];
        } else {
            $sufTable = $data[0]::$table_name_alt ? ' as ' . $data[0]::$table_name_alt : '';
            $joinParams = $data[1];
            $this->nodes_use[$data[0]::$table] = 1;
        }

        $this->{$join . 'join'}($data[0]::$table . $sufTable, $joinParams);
        if ($groupBy) {
            $this->groupBy($groupBy);
        }
        for ($i = 0, $len = sizeof($data[2]); $i < $len; $i++) {

            if (str_contains($data[2][$i], ' as ')) {
                $na = stristr($data[2][$i], ' as ', true);
                $as = substr(strstr($data[2][$i], ' as '), 4, strlen($data[2][$i]));
                $data[2][$i] = $pref . '.' . $na . ' as ' . $as;
            } else {
                $data[2][$i] = $pref . '.' . $data[2][$i] . ' as ' . $name . '_' . $data[2][$i];
            }


        }//todo
        $this->select($data[2]);
        if ($where) {
            $this->where($where);
        }
        return $this;
    }

    function autoNode($extends)
    {
        foreach ($extends as $k => $v) {
            if ($v && isset($this->nodes[$k])) {
                $this->node($k);
            }
        }
        return $this;
    }

    public function merge($sql, $prior = true)
    {
        if ($sql instanceof DB_build) {
            $columns = ['select', 'update', 'insert', 'from', 'limit', 'offset', 'order', 'debug', 'sub',
                'leftJoin', 'rightJoin', 'innerJoin', 'join', 'having', 'where', 'events'];

            foreach ($columns as $v) {
                if (!$prior) {
                    if (is_array($sql->{$v}))
                        $this->{$v} = array_merge($sql->{$v}, $this->{$v});
                    elseif (empty($this->{$v}))
                        $this->{$v} = $sql->{$v};
                } else {
                    if (is_array($sql->{$v}))
                        $this->{$v} = array_merge($this->{$v}, $sql->{$v});
                    else
                        $this->{$v} = $sql->{$v};
                }
            }
            return $this;
        }
        return $this;
    }

    public function where_export($arr = null, $options = [])
    {
        $arr = $arr ?? $this->where;

        $operandGroup = '';
        $where_columns = '';


        for ($i = 0, $len = sizeof($arr); $i < $len; $i++) {
            $whi = $arr[$i];

            $buf = [];
            $where_columns .= $operandGroup . ($whi['group'] ? '(' : '');

            if (is_string($whi['columns'])) {
                $where_columns .= $whi['columns'];
            } else {


                for ($j = 0, $lenj = sizeof($whi['columns']); $j < $lenj; $j++) {
                    $col = $whi['columns'][$j];
                    if (is_object($col) && $col->_type === 'where_group') {
                        if (is_assoc($col->data[0]['columns'])) {
                            $col->data[0]['columns'] = $this->AssocToRow($col->data[0]['columns']);
                        }
                        $buf[] = $this->where_export($col->data);
                        continue;
                    }

                    $buf[] = $this->where_item($col[0], $col[1], (!$col[1] || !in_array($col[1], ['~IS NULL', '~IS NOT NULL']) ? ($col[2] ?? '=') : ''), $options);
                }
            }
            $where_columns .= implode(' ' . $whi['operand'] . ' ', $buf) . ($whi['group'] ? ')' : '');
            $operandGroup = ' ' . $whi['operandGroup'] . ' ';
        }
        return $where_columns;
    }

    public function last($show = false)
    {
        if (!$show)
            return $this->db->last_query;
        else {
            show($this->db->last_query);
        }
        return $this;
    }

    public function debug($status = true, $global = false)
    {
        $this->debug = $status;

        if ($global) {
            $this->debug = $status;
        }
        return $this;
    }

    public function show()
    {
        show([
            'last' => $this->last(),
            'perfomance' => [
                'time' => $this->time_start > 0 ? number_format(microtime(true) - $this->time_start, 5) : '--- ' . 's',
                'memory' => number_format((memory_get_peak_usage(false) - $this->memory_start) / 1024, 3) . ' KB'
            ],
            'stats' => $this->db->stats,
            'obj' => $this
        ]);

        return $this;

    }

    /**
     * @param $name
     * @param null $callback
     * @param array $args
     * @return $this
     */
    function event($name, $callback = null, $args = [])
    {
        if ($callback === null) {
            if (isset($this->events[$name])) {
                for ($i = 0, $len = sizeof($this->events[$name]); $i < $len; $i++) {
                    $this->events[$name][$i][0]([&$this, $this->events[$name][0][1]]);
                }

            }
            return $this;
        }
        $this->events[$name][] = [$callback, $args];
        return $this;
    }

    private function _join($table, $on, $type)
    {
        $buf = [];
        foreach ($on as $a => $b) {
            if (!is_array($b)) {
                $buf[] = $a . ' = ' . $b;
            } else {
                $buf[] = $a . ' ' . $b[1] . ' ' . $b[0];
            }

        }

        return $type . ' JOIN ' . $this->db->prefix . $table . ' ON ' . implode(' AND ', $buf);
    }

    private function _isReg($str)
    {
        return is_string($str) && $str[0] === '#' && $str[strlen($str) - 1] === '#';
    }

    private function _prefix($arr, $prefix)
    {
        if (!$prefix) return $arr;
        return array_map(fn($v) => $prefix . $v, $arr);
    }

    private function _un(&$var, $query)
    {
        if (is_callable($query)) {
            for ($i = 0, $len = sizeof($var); $i < $len; $i++) {
                if ($res = $query($var[$i])) {
                    array_splice($var, $i, 1);
                    $len = sizeof($var);
                    if ($res === 'break') {
                        json('break');
                    }
                }

            }
            return $this;
        }
        if ($this->_isReg($query)) {
            for ($i = 0, $len = sizeof($var); $i < $len; $i++) {

                if (!preg_match($query, $var[$i])) continue;
                array_splice($var, $i, 1);
            }

        } elseif (is_numeric($query)) {

            array_splice($var, $query, 1);
        } else {
            $var = array_filter($var, function ($item) use ($query) {
                if (is_array($query))
                    return !in_array($item, $query);
                else
                    return $item !== $query;
            });
        }
        $var = array_values($var);
        return $this;
    }


    function valid($columns = null, $filter = true)
    {
        $isInsert = $this->type === 'insert';
        if (!$this->isEmpty && $isInsert && empty($this->insert)) {
            $this->insert = [[]];
        }

        $data = &$this->{$this->type};

        $len = sizeof($data);

        $cacheSync = [];
        $excludes = ['id', 'date_created', 'date_updated', 'date'];
        for ($k = 0; $k < $len; $k++) {
            foreach ($columns as $key => $rules) {
                if (in_array($key, $excludes)) continue;

                $insKey = &$data[$k][$key];

                // Required:
                if (!isset($insKey)) {

                    if (!$isInsert) continue;
                    if (!isset($rules['require']) || $rules['require'] === TRUE) {
                        if (!isset($rules['default'])) {
                            Response::error($this->model . ': Field ' . $key . ' - is require');
                            continue;
                        } else {

                            $insKey = $rules['default'];
                        }
                    } else {
                        continue;
                    }
                }


                // Type:
                $method = 'set' . ucfirst($rules[0]);
                $is_valid = FormatData::{$method}($insKey, $rules);

                if (!$is_valid) {
                    Response::error($this->model . ': Field "' . $key . '" (' . $rules[0] . ') - is wrong type. Value is ' . var_export($insKey, true));
                }

                // Valid:
                if (isset($rules['valid'])) {
                    if (!preg_match('~' . $rules['valid'] . '~u', $insKey)) {
                        Response::error($this->model . ': Field ' . $key . ' - is not valid');
                        continue;
                    }
                }

                // Is Empty:
                if (isset($rules['is_empty'])) {
                    if ($rules['is_empty'] === TRUE && empty($insKey)) {
                        Response::error($this->model . ': Field ' . $key . ' - is empty');
                        continue;
                    }
                }
                if (isset($rules['min'])) {
                    if (mb_strlen($insKey) < $rules['min']) {
                        Response::error($this->model . ': Field ' . $key . ' - is to short');
                        continue;
                    }
                }
                if (isset($rules['max'])) {
                    if (mb_strlen($insKey) > $rules['max']) {
                        Response::error($this->model . ': Field ' . $key . ' - is to large');
                        continue;
                    }
                }
                // Is sync:
                if (isset($rules['sync'])) {

                    if (is_array($rules['sync'])) {
                        [$model, $s_key] = $rules['sync'];
                    } elseif (is_string($rules['sync'])) {
                        $model = $rules['sync'];
                        $s_key = 'id';
                    }


                    $v_context = substr(strtolower($this->model), 5) . '-' . $key;

                    if ($model === 'ModelVocabulary') {
                        if (!isset($cacheSync[$v_context])) {
                            $q = $model::find([
                                'context' => $v_context,
                                'lang' => 'en'
                            ], ['name', 'value']);
                            $cacheSync[$v_context] = array_group($q, 'name', 'value');
                        }

                        if (!$cacheSync[$v_context][$insKey]) {
                            Response::error('Error! In "' . $s_key . '(' . $insKey . ')" - is not exists ' . $model);
                        }
                    } else {
                        if (!$model::count([$key => $insKey, 'context' => $v_context])) {
                            Response::error('Error! Node "' . $key . '(' . $insKey . ')" - is not exists ' . $model);
                        }
                    }

                }
            }
            if (!empty(Response::$error)) {
                Response::end(null, 422);
            }
        }
        if ($filter) {
            for ($i = 0; $i < $len; $i++) {
                arr_remove($data[$i], 'is_null');
            }

        }
        return $this;
    }

    function columns($arr)
    {
        if (!is_array($arr)) {
            $arr = explode(',', $arr);
            $arr = array_map('trim', $arr);
        }
        $this->columns = $arr;
        return $this;
    }

    function model($name)
    {
        $this->from($name::$table . ((isset($name::$table_name_alt) && $name::$table !== $name::$table_name_alt) ? ' as ' . $name::$table_name_alt : ''));
        $this->model = $name;
        if ($this->type === 'select')
            $this->setNodes($name);

        return $this;
    }

    function setNodes($model, $prefix = null)
    {
        if ($prefix) {
            foreach ($model::get_nodes() as $k => $v) {
                $this->nodes[$prefix . '.' . $k] = $v;
            }
        } else
            $this->nodes = array_merge($this->nodes, $model::get_nodes());
        return $this;
    }

    function canEmpty()
    {
        $this->isEmpty = true;
    }

    function notEmpty()
    {
        $this->isEmpty = false;
    }
}
