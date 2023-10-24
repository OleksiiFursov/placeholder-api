<?php

class CI_Customer extends Controller{
    public string|ModelCustomer $model="ModelCustomer";
    function demo(){
        $params = array_merge([
            'reset_vocabulary' => true,
            'reset'   => true
        ], GET());

        if($params['reset_vocabulary']){

            ModelVocabulary::delete([
               'context' => 'customer-status'
            ]);

            ModelVocabulary::insert(gen_voc('customer-status', [
                'inactive', 'active'
            ]));
        }
        if($params['reset']){
            $this->model::delete();
        }
        $folder = str_replace('CI_', '', __class__);
        $res = file_get_contents(DIR.'/model/'.$folder.'/demo.json');
        return $this->model::insert(json_decode($res, true));
    }
}
