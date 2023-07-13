<?php

class CI_Todo extends Controller{
    public function __construct()
    {

    }

    function demo(){
        return 'demo';
    }
    function _get($args){
        notice($args);
        return 'get';
        $filter = GET('filter') ?? [];
        return ModelTodo::find($filter);
    }
    function add(){
        $data = GET();
        return 1;
       // ModelTodo::insert($data)
    }
}
