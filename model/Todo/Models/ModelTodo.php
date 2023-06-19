<?php
class ModelTodo extends BaseModel
{
    static $table = 'todo';
    static $columns = [
        'token' => ['string'],
        'name' => ['string'],
        'description' => ['string'],
        'priority' => ['int'],
        'status'    => ['int'],
        'created_at' => ['datetime'],
        'updated_at' => ['datetime']
    ];

}
