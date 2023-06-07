<?php

class UsersAchievements extends Model {

    protected $model = ModelUsersAchievements::class;

    function __construct(){}
	function get( $filters = [], $params = [] ) {
		return include __DIR__.'/Actions/achievements/get.php';
	}

	function add( $data = [], $params = [] ) {
		return include __DIR__.'/Actions/achievements/add.php';
	}

//	function edit( $data = [], $filters = []) {
//		return include __DIR__.'/Actions/achievements/edit.php';
//	}

	function del($filters) {
		return include __DIR__.'/Actions/achievements/del.php';
	}

}