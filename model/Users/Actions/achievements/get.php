<?php
/**
 * @var Users $this
 * @var array|int $filters
 * @var array|null $params
 */


[$extends, $params, $filters] = $this->init_action([
    'extends' => [
        'height',
        'width'
    ],
    'extends_default' => [

    ],
    'params' => $params,
    'params_default' => [
        'columns' => ModelUsersAchievements::columnsSafe(),
        'meta' => true
    ],
    'filters' => $filters,
    'filters_default' => [
        'users_achievements.status' => 1,
    ],
    'model' => 'ModelUsersAchievements',
]);

$sql = $this->dbBuild();


$nodes = ModelUsersAchievements::get_nodes();

$sql->model('ModelUsersAchievements')
    ->where($filters)
    ->order($params['order'], $params['order_dir'])
    ->select($params['columns']);

$res = $sql->run();

$Achievements = new Achievements;
$achievements = $Achievements->get();
$achievements = array_group_callback($achievements, 'type', true);
$belt = array_group_callback($achievements['belt'], 'id', true);
$rank = array_group_callback($achievements['rank'], 'id', true);


foreach ($res as &$v) {

    if ($v['tax_type'] === 1) {
        $v['rank'] = $rank[$v['tax_id']][0];
    }
    if ($v['tax_type'] === 2) {
        $v['belt'] = $belt[$v['tax_id']][0];
    }
}
//temp
if(!function_exists("change_negative_id")){
    function change_negative_id($item){
        $item['id'] = - $item['id'];
        return $item;
    }
}


if ($extends['height']) {

    $height = array_map("change_negative_id", ModelUsersData::find(['user_id' => $filters['users_achievements.user_id'], 'name' => 'height', 'status' => [-1, '>']]));
    $weight = array_map("change_negative_id",  ModelUsersData::find(['user_id' => $filters['users_achievements.user_id'], 'name' => 'weight', 'status' => [-1, '>']]));

    array_push($res, ...$height);
    array_push($res, ...$weight);
}

//
//
//foreach ($res as &$v) {
//    if($v['tax_type'] === 1 ){
//        $v['name'] = $belt[$v['tax_id']][0]['name'];
//        $v['img'] = $belt[$v['tax_id']][0]['img'];
//        $v['tax_type_name'] = 'belt';
//    }
//    if($v['tax_type'] === 2 ){
//        $v['name'] = $rank[$v['tax_id']][0]['name'];
//        $v['img'] = $rank[$v['tax_id']][0]['img'];
//        $v['tax_type_name'] = 'rank';
//    }
//}

return $res;
