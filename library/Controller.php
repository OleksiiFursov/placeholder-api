<?php

class Controller extends Model
{
    public $is_public = false;
    function __call($method, $arguments)
    {
        $className = get_called_class();
        $cls = $className . '_' . $method;
        return Response::end( 'Not found method "' . $method . '" in ' . $className, 404);
    }


    function child($className, $method, $args){
        $className = $className.ucfirst($method);

        $c = new $className;
        $get_method = array_shift($args);

        return $c->{$get_method}(...$args);
    }

    function get_short($short, &$filters, &$params)
    {
        foreach ($short as $k => $v) {
            if (is_int($k)) {
                $k = $v;
            }
            $value = remove_item($params, $k, ($filters[$k] ?? null));
            if ($value !== null) {
                unset($filters[$k]);
                $filters[$v] = $value;
            }
        }
    }

    function init_ctrl($params=null, $options=null){
        if(!$params){
            $params = GET();
        }
        $res =  $this->get_params($params['params'] ?? [], $params['merge'] ?? [], $params['short'] ?? []);

        return match($options){
            default => $res,
            'filter' => [...$res[0], ...$res[1]]

        };
    }
    function get_params($params = null, $merge = null, $short = [], $args=null)
    {
        if (!is_array($params)) {
            $params = [];
        }
        $params = array_merge(GET(), $params ?? []);

        if ($merge)
            $params = array_merge_recursive($params, $merge);

        $filters = remove_item($params, 'filters', []);
        if($args){
            if(is_numeric($args)){
                $filters['id'] = $args;
            }
        }else{
            $GET_ID = ini('router.args');
            if (!empty($GET_ID)) {
                if ((int)$GET_ID[0]) {
                    $filters['id'] = parse_id($GET_ID[0]);
                }
            }
        }

        $this->get_short($short, $filters, $params);

        return [$filters, $params];
    }


}
