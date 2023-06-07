<?php


class ModelUsersData extends BaseModel
{
    static $table = 'users_data';
    static $name_alt = 'ud';
    static $columns = [
        'id' => ['int'],
        'user_id' => ['int'],
        'name' => ['string'],
        'value' => ['string'],
        'owner_id' => ['int','require' => false],
        'date_created' => ['datetime', 'require' => false],
        'confirmed_date' => ['datetime', 'require' => false],
        'confirmed_id' => ['int', 'require' => false],
        'status' => ['int', 'default' => 1],
    ];

    static function available_name(){
        return ['birthday', 'height', 'weight', 'belt'];

    }
    static function format_value($values){

        $res = [];
        foreach ($values as $item){

            $value = $item['value'];
            $name = $item['name'];
            $res[$name] = [...arr_remove_field($item, ['user_id', 'name']),
                'value' => match($name){
                    'birthday' => Birthday::when($value),
                    'height', 'weight' => +$value,
            }];
        }
        return $res;
    }
    static function onInsert(){
        ModelUsersData::$columns['owner_id']['default'] = user_id();
    }

}

