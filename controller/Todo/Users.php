<?php

class CI_TodoUsers extends Controller{
    public function __construct()
    {

    }

    function demo(){
        return 'demo';
    }
    function _get(){
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
