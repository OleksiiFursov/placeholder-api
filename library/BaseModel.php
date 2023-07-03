<?php

class BaseModel
{
    static function hasId($id)
    {
        global $db;

        if (is_numeric($id)) {
            $wh = ['id' => $id];
        }
        return $db->count(static::$table, $id) > 0;
    }

    static function get_table_name_alt(){
        return static::$table_name_alt ?? static::$table;
    }
    static function getById($id)
    {
        global $db;
        return $db->sel(static::$table, (int)$id);
    }


    static function get_meta($ids, $table = null)
    {
        global $db;

        if (!$table) $table = self::get_meta_table();
        $_meta = $db->sel('id, name, value, tax_id', $table, ['tax_id' => $ids, 'status' => 1]);

        return array_group_callback_key($_meta, 'tax_id');
    }

    static function get_meta_table()
    {
        return explode(' ', static::$table)[0] . '_meta';
    }


    static function getColumns()
    {
        $col = static::columnsSafe();

        for ($i = 0, $len = sizeof($col); $i < $len; $i++) {
            $col[$i] = static::$table_name_alt . '.' . $col[$i];
        }
        return $col;
    }

    static function columnsSafe($prefix = true, $filters = false): array
    {
        $columns = [];
        foreach(static::$columns as $k=>$v){

            if(!isset($v['safe']) || $v['safe']){
                $columns[] = $k;
            }
        }
        if ($filters) {
            $columns = array_values(array_filter($columns, fn($v) => !in_array($v, $filters)));
        }
        if (!$prefix)
            return $columns;
        return array_map(fn ($item) => (static::$table_name_alt ?? static::$table ). '.' . $item, $columns);
    }

    static function getInitData()
    {

        $res = [];
        foreach (static::$columns as $key => $val) {
            if (in_array($key, ['tax_id', 'id'])) continue;
            $res[$key] = $val['default'] ??
                (($val[0] === 'string' || $val[0] === 'date') ? '' :
                    (($val[0] === 'int' || $val[0] === 'float') ? 0 :
                        (($val[0] === 'boolean') ? false : [])));
        }
        return $res;
    }

    static function getColumnsForGet($exclude = ['id', 'tax_id'])
    {

        $columns = static::columnsSafe(false);
        $res = [];
        for ($i = 0, $len = sizeof($columns); $i < $len; $i++) {
            if (in_array($columns[$i], $exclude)) {
                continue;
            }
            $res[] = $columns[$i];
        }
        return $res;
    }

    static function getColumnsForInsert()
    {

        $columns = static::columnsSafe(false);
        $res = [];
        for ($i = 0, $len = sizeof($columns); $i < $len; $i++) {
            if (in_array($columns[$i], ['id', 'date_created', 'date_updated', 'date', 'time_update'])) {
                continue;
            }
            $res[] = $columns[$i];
        }
        return $res;
    }

    static function get_nodes()
    {
        return [];
    }

    static function query($type=null){
        global $db;
        $res = $db->build()->model(static::class);

        if(!$type){
            $res->type = $type;
        }
        return $res;
    }
    static function find($where = null, $col = null, $run=1)
    {
        global $db;
        if(!$col) {
            $col = static::columnsSafe();
        }
        $res = $db->build()->select($col)->model(static::class)->where($where);

//        if(!isset($where['status'])){
//            $res->where(['status' => [0, '>']]);
//        }

        return $res->run($run);
    }

    static function findOne($where = null, $col = null, $run=1)
    {
        global $db;
        if(!$col){
            $col = static::columnsSafe();
        }
        return $db->build()->select($col)->model(static::class)->where($where)->one($run);
    }
    static function has($where){
        return self::count($where) > 0;
    }
    static function count($where){
        global $db;
        return $db->count(static::$table, $where);

    }
    static function update($data, $where)
    {
        global $db;
        return $db->build()->model(static::class)->update($data)->where($where)->run();
    }

    static function select($col=false)
    {
        global $db;
        return $db->build()->select($col)->model(static::class);
    }
    static function insert($data, $params=[])
    {
        global $db;
        if(empty($data)) return false;
        $r = $db->build()->insert($data)->model(static::class);
        if(isset($params['force'])){
            $r->force(true);
        }
        return $r->run();
    }
    static function delete($data)
    {
        global $db;
        return $db->build()->delete($data)->model(static::class);
    }
    static function disabled($where, $status=0){
        global $db;
        return $db->build()->update(['status' => $status])->model(static::class)->where($where)->run();
    }

    static function params($params, $filters=null, $run=1){
        global $db;
        $res = $db->build()->params($params)->model(static::class);
        if($filters){
          $res->where($filters);
        }
        if($run !== null)
        return $res->run($run);
    }

    static function findGroup($where = null, $col = null, $groupBy='tax_id', $run=1){
        $res = self::find($where, $col, $run);
        return array_group_callback($res, $groupBy);
    }
}
