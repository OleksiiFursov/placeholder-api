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
    function _delete($id){
        return ModelTodo::delete($id);
    }
    function _patch($id){
        $params = GET();
        $filters = take($params, 'filters', $id);
        //notice(GET());
        return ModelTodo::update($params, $filters);
    }
    function add(){
        return $this->_post();
    }
}
