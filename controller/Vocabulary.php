<?php
class CI_Vocabulary extends Controller{
    function __construct(){

    }
    function get(){
        return ModelVocabulary::query()->unWhere(1)->where(GET())->run(4);
    }
}
