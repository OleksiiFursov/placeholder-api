<?php

class CI_Todo extends Controller{
    public function __construct()
    {

    }

    function demo(){
        return 'demo';
    }
    function _get($args){
        return 'todo->get->'.$args;
        $filter = GET('filter') ?? [];
        return ModelTodo::find($filter);
    }
    function _post($id){
        return 'todo->post->'.$id;
    }
    function add(){
        $data = GET();
        return 1;
       // ModelTodo::insert($data)
    }
}
