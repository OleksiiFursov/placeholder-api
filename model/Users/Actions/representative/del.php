<?php

/**
 * @var Users $this
 * @var int $id
 */
$id = parse_id($id);

// Check id;

if(!$id){
    return Response::error('Не правильный ид', 422);
}

if(!ModelUsersRepresentative::has($id)){
    return Response::error('Не найден ид в базе', 404);
}
ModelUsersRepresentative::disabled($id);

return is_done();
