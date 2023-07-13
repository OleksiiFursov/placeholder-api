<?php

class CI_TodoUsers extends Controller{
    public function __construct()
    {

    }

    function demo(){
        return 'demo1';
    }
    function _get($args){
        notice($args);
        return 'get1';
        $filter = GET('filter') ?? [];
        return ModelTodo::find($filter);
    }
    function add(){
        $data = GET();
        return 1;
        // ModelTodo::insert($data)
    }
}
