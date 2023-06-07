<?php

class UsersAccess extends Model{
    function __construct( public $model = ModelUsersAccess::class)
    {}

	function get($filters = [], $params = []){
		return include __DIR__ . '/Actions/access/get.php';
	}

    function getListAllow($folder=DIR.'/controller', $pref='', $prefName=''){
        $ctrls = scandir($folder);
        $res = [];
        for($i=2, $len=sizeof($ctrls); $i<$len; $i++){
            if(str_ends_with($ctrls[$i], '.php')){
                $name = explode('.php', $ctrls[$i])[0];
                $class_name = 'CI_'.$pref.$name;
                $f = new ReflectionClass($class_name);
                foreach ($f->getMethods() as $m) {
                    if ($m->class == $class_name && $m->name !== '__construct') {
                        $res[] = $prefName.lcfirst($name).'.'.$m->name;
                    }
                }
            }else{
                array_push($res, ...$this->getListAllow(
                    $folder.'/'.$ctrls[$i],
                    $pref.$ctrls[$i],
                    lcfirst($ctrls[$i]).'.'.str_replace($ctrls[$i], '',$ctrls[$i])));
            }
        }
        return $res;
    }

}
