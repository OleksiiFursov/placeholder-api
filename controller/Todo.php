<?php

class CI_Todo extends Controller{
    public function __construct()
    {

    }
    function _get($id){
        $filter = GET('filter') ?? $id;
        return ModelTodo::find($filter);
    }
    function _post(){
        $params = GET();
        return ModelTodo::insert($params);
    }
    function _patch($id){
        $params = GET();
        json($GLOBALS);
        $filters = take($params, 'filters', $id);
        return ModelTodo::update($params, $filters);
    }
    function add(){
        return $this->_post();
    }
}
