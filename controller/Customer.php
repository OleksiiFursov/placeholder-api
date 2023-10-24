<?php

class CI_Customer extends Controller{
    public string|ModelCustomer $model="ModelCustomer";
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
            $this->model::delete();
        }
        $res = file_get_contents(DIR.'/model/Customer/demo.json');
        return $this->model::insert(json_decode($res, true));
    }
    function _get($id){
        return $this->model::find($id ?? GET('filter'));
    }
    function _post(){
        return $this->model::insert(GET());
    }
    function _delete($id){
        return $this->delete_wrap( $id);
    }
    function _patch($id){
        return $this->patch_wrap( $id);
    }
    function add(){
        return $this->_post();
    }
}
