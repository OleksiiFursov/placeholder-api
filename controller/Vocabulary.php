<?php
class CI_Vocabulary extends Controller{
    function __construct(){

    }
    function get(){
        return ModelVocabulary::query()->unWhere(0)->where(GET())->run();
    }
}
