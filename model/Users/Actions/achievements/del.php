<?php

/**
 * @var UsersAchievements $this
 * @var int $id
 */

$id = parse_id($id);

if(!$id){
    return Response::error('Не правильный id', 422);
}
if($id>0){
    if(!ModelUsersAchievements::has($id)){
        return Response::error('Не найден id в базе', 404);
    }
    ModelUsersAchievements::disabled($id, -1);
}else{
    $id = -$id;
    if(!ModelUsersData::has($id)){
        return Response::error('Не найден id в базе', 404);
    }
    ModelUsersData::disabled($id, -1);
}




return is_done();
