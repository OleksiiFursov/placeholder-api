<?php

class CI_Todo extends Controller{
    public function __construct()
    {

    }

    function demo(){

    }
    function get(){
        $filter = GET('filter') ?? [];
        return ModelTodo::find($filter);
    }
    function add(){
        $data = GET();
        return 1;
       // ModelTodo::insert($data)
    }
}
