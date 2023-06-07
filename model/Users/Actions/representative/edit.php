<?php
/**
 * @var Users $this
 * @var array|int $filters
 * @var array $data
 */


// FIND users:
if (empty($filters)) {
    return Response::error('Filter is empty');
}

if (empty($data)) {
    return Response::error('Data is empty');
}

$this->representative_del($filters['id']);

$this->representative_add($data, $filters);

return is_done();
