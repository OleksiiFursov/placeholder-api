<?php

class CI_TodoUsers extends Controller{
    public function __construct()
    {

    }

    function demo(){
        return 'demo1';
    }
    function _post($args){
        return 'todo->post->users->'.$args;
    }
    function _get($args){

        return 'todo->get->users->'.$args;
        $filter = GET('filter') ?? [];
        return ModelCustomer::find($filter);
    }
    function add(){
        $data = GET();
        return 1;
        // ModelTodo::insert($data)
    }
}
