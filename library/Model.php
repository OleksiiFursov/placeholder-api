<?php
const MODEL_PARAMS_INITIAL = [
    'order' => 'id',
    'order_dir' => 'desc',
    'sql' => '',
    'offset' => 0,
    'limit' => null,
    'full' => false,
    'return' => 'array',
];

class Model
{

    function dbBuild()
    {
        global $db;
        return $db->build();
    }

    function db()
    {
        global $db;
        return $db;
    }


    public function init_action($args, $fields = null): array
    {
        $model = $args['model'] ?? $this->model ?? null;

        $table_name_alt = $model ? (new $model)::get_table_name_alt().'.': '';


        $res = [
            'return' => ['array', 'one', 'stats'],
            'return_default' => 'array',
        ];

        if ($model) {
            $args['params_default'] = [
                'columns' => $model::columnsSafe(),
                ...($args['params_default'] ?? [])
            ];
        }
        if ($args['params_default'] ?? false) {

            $res['params'] = [...MODEL_PARAMS_INITIAL, ...$args['params_default'], ...($args['params'] ?? [])];
            foreach ($res['params'] as &$v) {
                if ($v === 'true') $v = true;
                elseif ($v === 'false') $v = false;
                elseif ($v === 'null') $v = null;
            }
            unset($v);
            if (IS_DEV) {
                $keys_params_initial = array_keys(MODEL_PARAMS_INITIAL);
                foreach (($args['params'] ?? []) as $param_name => $param_value) {
                    if ($param_name != 'extends' &&
                        !array_key_exists($param_name, $args['params_default']) &&
                        !in_array($param_name, $keys_params_initial)) {
                        Response::push('NOT_REGISTER_PARAMS', $param_name);
                    }
                }

            }
        }
        if (isset($args['extends'])) {
            $res['extends'] = $this->get_extend($args['params'], $args['extends_default'] ?? $args['extends'], $args['extends']);

            if (IS_DEV) {
                foreach ($res['extends'] as $extend_name => $extend_value) {
                    if (!in_array(explode('.', $extend_name)[0], $args['extends'])) {
                        Response::push('NOT_REGISTER_EXTENDS', $extend_name);
                    }
                }
            }
        }
        if (isset($args['filters'])) {

            $args['filters_params'] = $args['filters_params'] ?? [];

            $args['filters_params'] = array_merge([
                'number' => $table_name_alt . 'id',
                'string' => $table_name_alt . 'name',
                'number_array' => $table_name_alt . 'id'
            ], $args['filters_params']);


            $res['filters'] = $this->get_filter($args['filters'], $args['filters_default'] ?? [], $args['filters_params']);
            foreach($res['filters'] as $k=> $vFilter){
                if(!str_contains($k, '.')){
                    unset($res['filters'][$k]);
                    $res['filters'][$table_name_alt.$k] = $vFilter;
                }
            }
            if (!isset($res['filters'][$table_name_alt . 'status'])) {
                $res['filters'][$table_name_alt.'status'] = [0, '>'];
            //     notice('Почему то я тут'); По дефолту статус 1
            }
        }
        if (isset($args['return'])) {
            if (IS_DEV) {
                if (!in_array($res['return'], $args['return_default'])) {
                    Response::error('Not return format: ' . $res['return']);
                }
            }
        }


        $new = [];
        if (!$fields) $fields = array_filter(array_keys($args), fn($key) => !str_contains($key, '_default'));


        foreach ($fields as $field) {
            $new[] = $res[$field] ?? [];
        }

        if (ini('debug.method_info')) {
            /** @noinspection PhpUnhandledExceptionInspection */
            throw new ExitException('Exiting normally');
        }
        return $new;
    }

    function init_params(&$_PARAMS, &$_EXTENDS, &$_EXTENDS_DEFAULT, &$_RETURNS, &$params)
    {
        $_PARAMS = array_merge(MODEL_PARAMS_INITIAL, $_PARAMS);

        $_params = array_merge($_PARAMS, $params);

        foreach ($_params as &$v) {
            if ($v === 'true') $v = true;
            elseif ($v === 'false') $v = false;
            elseif ($v === 'null') $v = null;
        }
        unset($v);
        $extends = $this->get_extend($_params, $_EXTENDS_DEFAULT, $_EXTENDS);

        $this->checkParams($_EXTENDS, $extends, $_PARAMS, array_merge(MODEL_PARAMS_INITIAL, $params), $_RETURNS);

        $params = $_params;

        return $extends;
    }

    function get_extend(&$params, $def, $_EXTENDS = null)
    {
        $is_full = $params['full'] ?? false;
        $struct = $_EXTENDS ? array_map(fn() => $is_full, array_flip(array_values($_EXTENDS))) : [];


        $func = fn() => true;
        if (empty($params)) return array_map($func, array_flip($def)) + $struct;


        $extends = remove_item($params, 'extends');

        if ($extends && !is_bool($extends)) {
            if (is_string($extends)) {
                $extends = explode(',', str_replace(' ', '', $extends));
                if ($extends[0][0] === '+') {
                    $extends[0] = substr($extends[0], 1);
                    $extends = array_merge($def, $extends);
                }
                $extends = array_flip(array_values($extends));
                foreach ($extends as $k => $v) {

                    $key = explode('.', $k);

                    if (isset($key[1])) {
                        $root_key = array_shift($key);
                        if (!isset($extends[$root_key])) {
                            $extends[$root_key] = [];
                        }

                        $extends[$root_key][] = implode('.', $key);

                        unset($extends[$k]);
                    } else {
                        $extends[$k] = true;
                    }
                }

            } else {
                if (isset($extends[0])) {
                    $extends = array_map($func, array_flip($extends));
                }
            }

            return $extends + $struct;
        }

        return array_map($func, array_flip($def)) + $struct;
    }

    function get_filter($filter, $def, $args_type = [])
    {
        if (empty($filter)) return $def;
        if (is_array($filter)) {
            if (is_assoc($filter)) {

                foreach ($filter as $k => $v) {
                    if ($v === 'null') {
                        unset($def[$k]);
                        continue;
                    }
                    $def[$k] = $v;
                }
                $filter = $def;
            } else {
                $filter = array_merge($def, self::type_args($filter, $args_type));
            }
        }
        return self::type_args($filter, $args_type);
    }

    function checkParams(&$_EXTENDS, &$extends, &$_PARAMS, $params, &$_RETURNS)
    {


        if (defined('IS_DEV')) {

            $keys_params_initial = array_keys(MODEL_PARAMS_INITIAL);
            // CHECK
            if (is_array($extends)) {
                foreach ($extends as $extend_name => $extend_value) {
                    if (!in_array(explode('.', $extend_name)[0], $_EXTENDS)) {
                        Response::push('NOT_REGISTER_EXTENDS', $extend_name);
                    }
                }
            }
            if (is_array($params)) {
                foreach ($params as $param_name => $param_value) {
                    if (!in_array($param_name, ['extends']) && !array_key_exists($param_name, $_PARAMS) && !in_array($param_name, $keys_params_initial)) {
                        Response::push('NOT_REGISTER_PARAMS', $param_name);
                    }
                }
            }

        }
        if (!in_array($params['return'], $_RETURNS)) {
            Response::error('Not return format: ' . $params['return']);
        }
    }


    function format(&$values, $custom_rules = NULL, $deep = false)
    {
        $rules = FormatData::$params;

        if ($custom_rules) {
            $rules = array_merge($rules, $custom_rules);
        }

        for ($i = 0, $len = sizeof($rules); $i < $len; $i++) {
            $rule = $rules[$i];
            $type = array_pop($rule);

            for ($j = 0, $lenj = sizeof($rule); $j < $lenj; $j++) {
                if (!isset($values[$rule[$j]])) continue;

                if (in_array($type, ['int', 'string', 'boolean', 'double']))
                    settype($values[$rule[$j]], $type);
                elseif ($deep && is_array($values[$rule[$j]])) {

                    $this->format($values[$rule[$j]], $custom_rules, $deep);
                } else {
                    $values[$rule[$j]] = FormatData::{$type}($values[$rule[$j]]);
                }

            }
        }

    }

    /**
     * @param $args
     * @param array $actions
     * @return array
     */
    function type_args(&$args, $actions = [])
    {
        if (empty($args)) {
            if (isset($actions['null']) && $actions['null'] === FALSE) {
                stop('dev', 'Class ' . __CLASS__ . '->get() is bad args');
            }
            return [];
        }

        if (is_assoc($args)) {
            return $args;
        }

        if (is_numeric($args)) {

            $col = $actions['number'] ?? 'id';
            if ($col === FALSE) {
                stop('dev', 'Class ' . __CLASS__ . '->get() is bad args');
            }

            return [$col => (int)$args];
        };

        if (is_string($args)) {
            $is_ids = explode(',', $args);

            if(sizeof($is_ids)>1){
                $args = array_map(fn($a) => (int)$a, $is_ids);
                $col = $actions['array_int'] ?? 'id';
            }else{
                $col = $actions['string'] ?? 'name';

                if ($col === FALSE) {
                    stop('dev', 'Class ' . __CLASS__ . '->get() is bad args');
                }
            }


            return [$col => $args];
        };

        if (is_array($args)) {
            if (is_numeric($args[0])) {

                $col = $actions['number_array'] ?? 'id';
                if ($col === FALSE) {
                    stop('dev', 'Class ' . __CLASS__ . '->get() is bad args');
                }

                return [$col => $args];
            }
            if (is_string($args[0])) {

                $col = $actions['string_array'] ?? 'name';
                if ($col === FALSE) {
                    stop('dev', 'Class ' . __CLASS__ . '->get() is bad args');
                }

                return [$col => $args];
            }
            return $args;

        }
        return [];
    }


    function post($name = null, $type = null, $def = false)
    {
        return GET($name, $type, $def, 'POST');
    }


    function add_collection(&$data, $key, $model, $tax_id, $options = [])
    {
        global $db;

        $field = remove_item($data, $key);

        if (!$field) {
            $field = remove_item($data, substr($key, 0, -1));
            if ($field) {
                $field = [$field];
            }
        }
        $options += [
            'message_error_validate' => 'Поле "' . $key . '" - не указано верно',
            'message_error_exists' => 'Ваш  "' . $key . '" - уже есть в системе',
            'onRow' => null,
            'onValidate' => null
        ];


        if ($field) {
            $sql = $db->build()->model($model);

            for ($i = 0, $len = sizeof($field); $i < $len; $i++) {

                // Normalizie
                if (is_string($field[$i])) {
                    $field[$i] = [
                        'value' => $field[$i],
                        'comment' => ''
                    ];
                }

                if ($options['onRow']) {
                    $field[$i]['value'] = $options['onRow']($field[$i]['value']);
                }

                if (empty($field[$i]['value'])) {
                    continue;
                }
//                if ($sql->count(null, ['value' => $field[$i]['value']])) {
//                    return Response::error($options['message_error_exists']);
//                }

                if ($options['onValidate']) {
                    if (!$options['onValidate']($field[$i])) {
                        return Response::error($options['message_error_validate']);
                    }
                }

                $field[$i]['tax_id'] = $tax_id;

                $sql->insert($field[$i]);
            }

            return $sql->run();
        }
        return null;
    }

    function get_collection($model, $ids, $def = [])
    {
        global $db;
        $res = $db->build()
            ->select()
            ->model($model)
            ->where('tax_id', $ids)
            ->order('status', 'DESC')
            ->run();
        if (!$res) return $def;
        return array_group_callback($res, 'tax_id');
    }

    function update_collection(&$data, $model, $key, $id)
    {
        global $db;
        $sql = $db->build()->model($model)->where('tax_id', $id);

        $deleted = [];
        $updated = [];

        $collection = remove_item($data, $key, []);
        if ($key === 'phones') {
            for ($c = 0, $len = sizeof($collection); $c < $len; $c++) {
                $collection[$c]['value'] = preg_replace("/\D/", '', $collection[$c]['value']);
            }
        }

        if (!$collection) {
            return null;
        }

        if (!isset($v[0]['old_value'])) {
            $sql->delete();
            $new_collection = [];
            for ($i = 0, $len = sizeof($collection); $i < $len; $i++) {
                $collection[$i]['tax_id'] = $id;
                if (!empty($collection[$i]['value'])) {
                    $new_collection[] = $collection[$i];
                }
            }

            $sql->insert($new_collection)->run();

            return true;
        }


        foreach ($collection as $v) {

            if (isset($v['value']) && empty($v['value'])) {
                $deleted[] = $v['old_value'];
            } else {
                $updated['old_value'][] = remove_item($v, 'old_value', NULL);
                $updated['data'][] = is_array($v) ? $v : ['value' => $v];
            }
        }

        // DELETE:
        if (sizeof($deleted)) {
            (clone $sql)->delete(['value' => $deleted])->run();
        }

        // UPDATE:
        $is_exists = array_column($db->sel('value', $model::$table, [
            'tax_id' => $id
        ]), 'value');


        $insertSQL = clone $sql;


        for ($u = 0, $lenu = sizeof($updated['old_value']); $u < $lenu; $u++) {

            if (in_array($updated['data'][$u]['value'], $is_exists)) continue;

            if ($updated['old_value'][$u] !== NULL || in_array($updated['old_value'][$u], $is_exists)) {

                $where = [
                    'value' => $updated['old_value'][$u]
                ];

                (clone $sql)->update($updated['data'][$u])->where($where)->run();
            } else {
                $updated['data'][$u]['tax_id'] = (int)$id;
                $insertSQL->insert($updated['data'][$u]);
            }
        }

        return $insertSQL->run();

    }

    function updateMeta($table, $data, $tax_ids, $user = [])
    {
        global $db;

        if (!sizeof($data)) {
            return 0;
        }

        $History = new History;


        $sql = $db->build()->from($table::get_meta_table());
        $metaData = (clone $sql)->select('name, tax_id')
            ->where('tax_id', $tax_ids)
            ->run(null, false);
        $metaData = array_group_callback($metaData, 'tax_id', fn($v) => $v['name']);

        foreach (toArray($tax_ids) as $tax_id) {
            $meta = [];
            $deletedIds = [];
            foreach ($data as $key => $val) {
                if ($val === NULL || $val === 'null' || $val === '') {
                    $deletedIds[] = $key;
                    continue;
                }

                if (isset($metaData[$tax_id]) && in_array($key, $metaData[$tax_id])) {
                    $prev = (clone $sql)->select([])->where([
                        'status' => 1,
                        'tax_id' => $tax_id,
                        'name' => $key
                    ])->one();

                    if ($prev['value'] === $val) continue;

                    (clone $sql)->update([
                        'status' => 0
                    ])->where([
                        'status' => 1,
                        'tax_id' => $tax_id,
                        'name' => $key
                    ])->run();

                    $prev_id = $prev['id'];
                    (clone $sql)->insert([
                        'value' => $val,
                        'tax_id' => $tax_id,
                        'name' => $key,
                    ])->run();
                    $new_id = $db->insert_id;

                    if ($table === 'ModelPatient') {
                        $action_name = 'patient/' . $key;
                    } else {
                        $action_name = $table;
                    }

                    $History->add([
                        'action_name' => $action_name,
                        'action' => 'edit',
                        'prev_id' => $prev_id,
                        'new_id' => $new_id,
                        'tax_id' => $tax_id,
                        'owner_id' => user_id(),
                        'clinic_id' => $user['clinic_id'],
                        'department_id' => $user['department_id']
                    ]);


                } else {
                    $meta[] = [
                        'tax_id' => $tax_id,
                        'name' => $key,
                        'value' => $val
                    ];
                }
            }

            if (sizeof($deletedIds)) {
                (clone $sql)->delete(['name' => $deletedIds])->run();
            }

            if (!empty($meta))
                $sql->insert($meta)->run();
        }
        return true;
    }


    function insertMeta($table, $data, $tax_id, $user = [])
    {
        global $db;
        $meta = [];
        $q = 0;
        $History = new History;
        foreach ($data as $key => $val) {
            $meta[$q] = [
                'tax_id' => $tax_id,
                'name' => $key,
                'value' => $val
            ];
            $db->build()->from($table::get_meta_table())
                ->insert($meta)
                ->run();

            $new_id = $db->insert_id;

            if ($table === 'ModelPatient') {
                $action_name = 'patient/' . $key;
            } else {
                $action_name = $table;
            }

            if (!isset($user['clinic_id']))
                $user['clinic_id'] = -1;
            if (!isset($user['department_id']))
                $user['department_id'] = -1;

            $History->add([
                'action_name' => $action_name,
                'action' => 'add',
                'prev_id' => null,
                'new_id' => $new_id,
                'tax_id' => $tax_id,
                'owner_id' => user_id(),
                'clinic_id' => $user['clinic_id'],
                'department_id' => $user['department_id']
            ]);
            $q++;
        }

    }


    function __call($method, $arguments)
    {
        $className = get_called_class();
        $cls = $className . '_' . $method;

        if (!class_exists($cls, false)) {
            $dir = DIR . '/model/' . $className . '/' . $method . '.php';
            if (file_exists($dir)) {
                include $dir;
            } else {
                return Response::end('Not found method "' . $method . '" in ' . $className, 404);
            }

        }
        $cls = new $cls();
        return call_user_func_array([$cls, 'main'], $arguments);
    }


    /**** RETURN *****////
    public function _build_tree(array $elements, $parentId = 0)
    {
        $branch = array();

        foreach ($elements as $element) {
            if ($element['parent_id'] == $parentId) {
                $parent = $this->_build_tree($elements, $element['id']);
                if ($parent) {
                    $element['children'] = $parent;
                }
                $branch[] = $element;
            }
        }

        return $branch;
    }

    public function _return_tree(&$categ, $parent_id, &$items, $opts = NULL)
    {
        $res = [];
        if ($opts === NULL)
            $opts = [
                'child_start_category' => false
            ];

        foreach ($categ as $k => &$v) {

            if ($v['parent_id'] == $parent_id) {
                if ($opts['child_start_category'])
                    $v['children'] = $this->_return_tree($categ, $v['id'], $items, $opts);

                foreach ($items as &$item) {
                    if ($v['id'] !== $item['category_id']) continue;
                    $v['children'][] = $item;

                    unset($item);
                }
                if (!$opts['child_start_category'])
                    $v['children'] = array_merge(($v['children'] ?? []), $this->_return_tree($categ, $v['id'], $items, $opts));

                $res[] = $v;
                unset($categ[$k]);
            }
        }
        return $res;
    }


//    public function get_user_active(){
//        global $USER;
//        return [
//            'id' => $USER['id'],
//
//        ]
//    }
}
