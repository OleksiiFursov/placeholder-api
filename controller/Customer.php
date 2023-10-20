<?php

class CI_Customer extends Controller{
    public function __construct()
    {

    }
    function demo(){
        $res = file_get_contents(DIR.'/model/Customer/demo.json');
        return ModelCustomer::insert(json_decode($res, true));
    }
    function _get($id){
        $filter = GET('filter') ?? $id;
        return ModelCustomer::find($filter);
    }
    function _post(){
        $params = GET();
        return ModelCustomer::insert($params);
    }
    function _delete($id){
        return ModelCustomer::delete($id);
    }
    function _patch($id){
        $params = GET();
        $filters = take($params, 'filters', $id);
        //notice(GET());
        return ModelCustomer::update($params, $filters);
    }
    function add(){
        return $this->_post();
    }
}
