<?php

class CI_Customer extends Controller{
    public function __construct()
    {

    }
    function demo(){
        $params = array_merge([
            'reset_vocabulary' => true,
            'reset_customer'   => true
        ], GET());

        if($params['reset_vocabulary']){

            ModelVocabulary::delete([
               'context' => 'customer-status'
            ]);

            ModelVocabulary::insert([
                [
                    'context' => 'customer-status',
                    'name' => '0',
                    'value' => 'inactive'
                ],
                [
                    'context' => 'customer-status',
                    'name' => '1',
                    'value' => 'active'
                ],
            ]);
        }
        if($params['reset_customer']){
            ModelCustomer::delete();
        }
        $res = file_get_contents(DIR.'/model/Customer/demo.json');
        return ModelCustomer::insert(json_decode($res, true));
    }
    function _get($id){
        return ModelCustomer::find($id ?? GET('filter'));
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
