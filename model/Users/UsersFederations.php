<?php

class UsersFederations extends Model {

    protected $model = ModelFederationsUsers::class;

    function __construct(){}
	function get( $filters = [], $params = [] ) {
		return include __DIR__.'/Actions/federations/get.php';
	}

//	function add( $data = [], $params = [] ) {
//		return include __DIR__.'/Actions/federations/add.php';
//	}
//
//	function edit( $data = [], $filters = []) {
//		return include __DIR__.'/Actions/federations/edit.php';
//	}
//
//	function del($filters) {
//		return include __DIR__.'/Actions/federations/del.php';
//	}

}