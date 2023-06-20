<?php
class CI_Vocabulary extends Controller{
    function __construct(){

    }
    function get(){
        return ModelVocabulary::find(GET());
    }
    function update(){
        //if(ModelVocabulary::has())
    }
}
