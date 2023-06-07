<?php
/**
 * @var Federations $this
 * @var array|int $filters
 * @var array|null $params
 */

[$extends, $params, $filters] = $this->init_action([
    'extends' => [
        'current_user_id', // Автоматически польчать информацию для себя
        'user_group', // группировать
        'federations',
        'regions',
        'organizations',
        'departments',
        'groups'
    ],
    'extends_default' => ['current_user_id'],
    'params' => $params,
    'params_default' => [
        'order' => 'user_id'
    ],
    'filters' => $filters,
]);

$res = ModelFederationsUsers::select()
    ->where($filters)
    ->params($params);

if ($extends['current_user_id'] && !isset($filters['user_id'])) {
    $res->where(['user_id' => user_id()]);
}
$res = $res->run();


$names = ['groups', 'departments', 'organizations', 'regions', 'federations'];
$itemPattern = [];
foreach ($names as $name) {
    if ($extends[$name]) {
        $itemPattern[$name] = [];
    }
}


$ids = $itemPattern;


foreach ($res as $item) {

    if ($extends['groups'] && $item['group_id']) {
        $ids['groups'][$item['group_id']] = 1;
    }
    if ($extends['departments'] && $item['department_id']) {
        $ids['departments'][$item['department_id']] = 1;
    }
    if ($extends['organizations'] && $item['organization_id']) {
        $ids['organizations'][$item['organization_id']] = 1;
    }
    if ($extends['regions'] && $item['region_id']) {
        $ids['regions'][$item['region_id']] = 1;
    }
    if ($extends['federations'] && $item['federation_id']) {
        $ids['federations'][$item['federation_id']] = 1;
    }

}

$datas = [];


foreach (array_keys($itemPattern) as $name) {
    $Uname = ucfirst($name);
    $datas[$name] = array_group_callback(
        (new $Uname)->get(
            array_keys($ids[$name]),
            (isset($extends[$name]) && !is_bool($extends[$name])) ? ['extends' => $extends[$name]] : []),
        'id', true, true);
}


$buf = [];

foreach ($res as $item) {
    $meta = remove_items($item, ['title', 'assignment_id', 'data_created', 'owner_id', 'confirmed_id', 'confirmed_date', 'status']);
    if (!isset($buf[$item['user_id']])) {
        $buf[$item['user_id']] = $itemPattern;
    }

    if ($extends['groups'] && $item['group_id']) {

        $is_meta_local = $item['group_id'];
        if (isset($buf[$item['user_id']]['groups'][$item['group_id']])) {
            $buf[$item['user_id']]['groups'][$item['group_id']]['meta'][] = $meta;
            if ($is_meta_local) {
                $buf[$item['user_id']]['groups'][$item['group_id']]['meta_local'][] = $meta;
            }
        } else {
            $buf[$item['user_id']]['groups'][$item['group_id']] = [
                ...$datas['groups'][$item['group_id']],
                'path' => [$item['federation_id'], $item['region_id'], $item['organization_id'], $item['department_id'], $item['group_id']],
                'meta' => [$meta]
            ];
            $buf[$item['user_id']]['groups'][$item['group_id']]['meta_local'] = $is_meta_local ? [$meta] : [];
        }


    }
    if ($extends['departments'] && $item['department_id']) {
        $is_meta_local = !$item['group_id'] && $item['department_id'];

        if (isset($buf[$item['user_id']]['departments'][$item['department_id']])) {
            $buf[$item['user_id']]['departments'][$item['department_id']]['meta'][] = $meta;
            if ($is_meta_local) {
                $buf[$item['user_id']]['departments'][$item['department_id']]['meta_local'][] = $meta;
            }
        } else {
            $buf[$item['user_id']]['departments'][$item['department_id']] = [
                ...$datas['departments'][$item['department_id']],
                'path' => [$item['federation_id'], $item['region_id'], $item['organization_id'], $item['department_id']],
                'meta' => [$meta]
            ];
            $buf[$item['user_id']]['departments'][$item['department_id']]['meta_local'] = $item['group_id'] ? [$meta] : [];
        }

    }
    if ($extends['organizations'] && $item['organization_id']) {
        $is_meta_local = !$item['group_id'] && !$item['department_id'] && $item['organization_id'];
        if (isset($buf[$item['user_id']]['organizations'][$item['organization_id']])) {
            $buf[$item['user_id']]['organizations'][$item['organization_id']]['meta'][] = $meta;
            if ($is_meta_local) {
                $buf[$item['user_id']]['organizations'][$item['organization_id']]['meta_local'][] = $meta;
            }
        } else {
            $buf[$item['user_id']]['organizations'][$item['organization_id']] = [
                ...$datas['organizations'][$item['organization_id']],
                'path' => [$item['federation_id'], $item['region_id'], $item['organization_id']],
                'meta' => [$meta]
            ];
            $buf[$item['user_id']]['organizations'][$item['organization_id']]['meta_local'] = $is_meta_local ? [$meta] : [];
        }
    }
    if ($extends['regions'] && $item['region_id']) {
        $is_meta_local = !$item['group_id'] && !$item['department_id'] && !$item['organization_id'] && $item['region_id'];
        if (isset($buf[$item['user_id']]['regions'][$item['region_id']])) {
            $buf[$item['user_id']]['regions'][$item['region_id']]['meta'][] = $meta;
            if ($is_meta_local) {
                $buf[$item['user_id']]['regions'][$item['region_id']]['meta_local'][] = $meta;
            }
        } else {
            $buf[$item['user_id']]['regions'][$item['region_id']] = [
                ...$datas['regions'][$item['region_id']],
                'path' => [$item['federation_id'], $item['region_id']],
                'meta' => [$meta]
            ];
            $buf[$item['user_id']]['regions'][$item['region_id']]['meta_local'] = $is_meta_local ? [$meta] : [];
        }
    }
    if ($extends['federations'] && $item['federation_id']) {
        $is_meta_local = !$item['group_id'] && !$item['department_id'] && !$item['organization_id'] && !$item['region_id'] && $item['federation_id'];
        if (isset($buf[$item['user_id']]['federations'][$item['federation_id']])) {
            $buf[$item['user_id']]['federations'][$item['federation_id']]['meta'][] = $meta;
            if ($is_meta_local) {
                $buf[$item['user_id']]['federations'][$item['federation_id']]['meta_local'][] = $meta;
            }
        } else {
            $buf[$item['user_id']]['federations'][$item['federation_id']] = [
                ...$datas['federations'][$item['federation_id']],
                'path' => [$item['federation_id']],
                'meta' => [$meta]
            ];
            $buf[$item['user_id']]['federations'][$item['federation_id']]['meta_local'] = $is_meta_local ? [$meta] : [];
        }
    }
}

// Normalize:
foreach ($buf as &$buf_user_id) {
    foreach ($buf_user_id as $k => $v) {
        $buf_user_id[$k] = array_values($v);
    }
}
unset($buf_user_id);

if (!$extends['user_group']) {
    $buf = reset($buf);
}

return match ($params['return']) {
    'array' => $buf,
};
//
//function is_extend($v)
//{
//    global $extends;
//
//    return ;
//}
