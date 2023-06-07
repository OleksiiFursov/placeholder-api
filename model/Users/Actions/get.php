<?php
/**
 * @var Users $this
 * @var array|int $filters
 * @var array|null $params
 */

[$extends, $params, $filters] = $this->init_action([
    'extends' => [
        'data',
        'phones',
        'emails',
        'address',
        'sites',
        'avatar',
        'achievements',
        'representative',
        'assignment',
        'access',
        'last_connect',
        'organizations',
        'regions',
        'federationUsers',
        'federations',
        'groups',
        'departments',
        'organizations',
        'active',
        'agreements'
    ],
    'extends_default' => [
        'agreements'
    ],
    'params' => $params,
    'params_default' => [
        'meta' => true
    ],
    'filters' => $filters,
]);
/********* EXTENDS PRE *********/
$res = ModelUsers::select()
    ->where($filters)
    ->params($params);

$res->autoNode($extends);

if ($extends['last_connect']) {
//    $res->select('(' . ModelUsersToken::findOne(['user_id' => '~u.id'], 'last_connect', 4) . ') as last_connect');

    $last_connect = ModelUsersToken::select()->where(['user_id' => $filters['u.id']])->order('last_connect', 'DESC')->run();
    $last_connect = array_group_callback($last_connect, 'user_id');

}
$res = $res->run();

$ids = get_ids($res);

if ($extends['federationUsers']) {
    $test = (new UsersFederations)->get(['user_id' => 10]);
}

$is_users_fedearations = $extends['federations'] || $extends['regions'] || $extends['organizations'] || $extends['departments'] || $extends['groups'];
if ($is_users_fedearations) {
    $names = ['groups', 'departments', 'organizations', 'regions', 'federations'];
    $_extends = ['user_group' => true];
    foreach ($names as $name) {
        if ($extends[$name]) {
            $_extends[$name] = $extends[$name];
        }
    }
    $users_federations = (new UsersFederations)->get(['user_id' => $ids], [
        'extends' => $_extends
    ]);
}

$tax_data = ['tax_id' => $ids, 'tax_type' => 'user', 'status' => [0, '>']];

if ($extends['phones']) {
    $phones = ModelPhones::find($tax_data);
    $phones = array_group_callback($phones, 'tax_id');
}
if ($extends['emails']) {
    $emails = ModelEmails::find($tax_data);
    $emails = array_group_callback($emails, 'tax_id');
}
if ($extends['address']) {
    $address = ModelAddress::find($tax_data, '*');
    $address = array_group_callback($address, 'tax_id', true);
}
if ($extends['sites']) {
    $sites = ModelSites::find($tax_data, '*');
    $sites = array_group_callback($sites, 'tax_id', true);
}
if ($extends['agreements']) {
    $agreements = ModelAgreements::find(['user_id' => $ids]);
    $agreements = array_group_callback($agreements, 'user_id', true);


}


//if ($extends['assignment']) {
//    $assignment = ModelUsersAssignment::find(['user_id' => $ids]);
//    $assignment = array_group_callback($assignment, 'user_id', true);
//}

if ($extends['data']) {
    $user_data = ModelUsersData::find(['user_id' => $ids, 'status' => [0, '>']]);
    $user_data = array_group_callback($user_data, 'user_id', true);
}

if ($extends['representative']) {

    $representative = $this->representative_get(['user_id' => $ids], ['extends' => '+users']);
    $representative = array_group_callback($representative, 'user_id', true);

    $representatived = $this->representative_get(['representative_id' => $ids], ['extends' => '+representative']);
    $representatived = array_group_callback($representatived, 'representative_id', true);

}

if ($extends['achievements']) {
    $achievements = $this->achievements_get(['user_id' => $ids], ['extends' => '+height']);
    $achievements = array_group_callback($achievements, 'user_id', true);
}


foreach ($res as &$v) {
    $v['name_translit'] = change_translate($v['name']);

//    if ($extends['regions']) {
//        $v['regions'] = $regions[$v['id']] ?? [];
//        $v['organizations'] = $organizations[$v['id']] ?? [];
//        $v['test'] = $test[$v['id']] ?? [];
//    }
    if ($extends['representative']) {
        $v['representative'] = $representative[$v['id']] ?? null;
        $v['representatived'] = $representatived[$v['id']] ?? null;
    }
    if ($extends['data']) {
        $v += ModelUsersData::format_value($user_data[$v['id']] ?? []);
    }

    if ($is_users_fedearations && isset($users_federations[$v['id']])) {
        $v += $users_federations[$v['id']];
    }
    if ($extends['phones']) {
        $v['phones'] = remove_tax($phones[$v['id']] ?? null);
    }
    if ($extends['emails']) {
        $v['emails'] = remove_tax($emails[$v['id']] ?? null);
    }

    if ($extends['address']) {
        $v['address'] = $address[$v['id']] ?? null;
    }
    if ($extends['agreements']) {
        if (isset($agreements[$v['id']])) {
            $agr = [];
            foreach ($agreements[$v['id']] as $a){
                $agr[] = $a['agreement_id'];
            }
            $v['agreements'] = $agr ?? null;
        }else{
            $v['agreements'] = [];
        }
    }


    if ($extends['achievements']) {

        $v['achievements'] = $achievements[$v['id']] ?? [];
        $v['belt'] = null;
        $v['rank'] = null;


        foreach ($v['achievements'] as &$a) {
            if (isset($a['tax_type'])) {

                if ($a['tax_type'] === 2) {
                    if ($v['belt']) {
                        if ($v['belt']['belt']['id'] < $a['belt']['id']) $v['belt'] = $a;
                    } else {
                        $v['belt'] = $a;
                    }
                }
                if ($a['tax_type'] === 1) {
                    if ($v['rank']) {
                        if ($v['rank']['rank']['id'] < $a['rank']['id']) $v['rank'] = $a;
                    } else {
                        $v['rank'] = $a;
                    }
                }
            } else {
                $a['date_achieve'] = $a['date_created'];
            }
        }
        if ($v['achievements']) {
            usort($v['achievements'], "cmp");
        }
    }
    if ($extends['last_connect']) {
        if (isset($last_connect[$v['id']])) {
            $v['last_connect'] = 0;
            foreach ($last_connect[$v['id']] as $l) {
                if ($l['last_connect'] > $v['last_connect']) {
                    $v['last_connect'] = $l['last_connect'];
                }
            }
        }
    }

    if ($extends['access']) {

        $_res = ModelUsersAccess::select(['name', 'value', 'tax_type', 'priority'])
            ->where(['tax_id' => $v['id'], 'tax_type' => 'user'], 'AND', 'OR', true)
            ->where(['tax_id' => $v['level_id'], 'tax_type' => 'level'], 'AND', 'OR', true)
            ->where(['tax_type' => 'all'], 'AND', 'OR');

        if (isset($v['assignment'])) {
            $_res = $_res->where(['tax_id' => get_ids($v['assignment']), 'tax_type' => 'user'], 'AND', 'OR', true);
        }

        $_res = $_res->run();


        $v['access'] = [];
        foreach ($_res as $item) {
            $priority = $item['priority'] * 10 + match ($item['tax_type']) {
                    'user' => 3,
                    'level' => 2,
                    'assigment' => 1,
                    'all' => 0
                };
            if (isset($v['access'][$item['name']]) && $v['access'][$item['name']][1] > $priority) {
                continue;
            }
            $v['access'][$item['name']] = [$item['value'], $priority];
        }
        uasort($v['access'], fn($a, $b) => $a[1] > $b[1] ? 1 : -1);
    }
//
//    if ($extends['regions']) {
//        $v['federations'] = $_federations[$v['id']] ?? [];
//        $v['regions'] = $_regions[$v['id']] ?? [];
//        $v['organizations'] = $_organizations[$v['id']] ?? [];
//        $v['departments'] = $_departments[$v['id']] ?? [];
//        $v['groups'] = $_groups[$v['id']] ?? [];
//    }

}
unset($v);
return match ($params['return']) {
    'one' => $res[0] ?? [],
    default => $res
};
