<?php

/**
 * @var Users $this
 * @var array|int $data
 */

$sex = $data['user_sex'];
$rep_sex = $data['representative_sex'];
$rep_who = $data['type'];
$who = '';

if(+$sex === 1){
    if($rep_who === 'son' || $rep_who === 'daughter'){
        $who = 'father';
    }
    if($rep_who === 'mother' || $rep_who === 'father'){
        $who = 'son';
    }
    if($rep_who === 'wife' || $rep_who === 'husband'){
        $who = 'husband';
    }
    if($rep_who === 'grandmother' || $rep_who === 'grandfather'){
        $who = 'grandson';
    }
    if($rep_who === 'granddaughter' || $rep_who === 'grandson'){
        $who = 'grandfather';
    }

    if($rep_who === 'uncle' || $rep_who === 'aunt'){
        $who = 'nephew';
    }
    if($rep_who === 'nephew' || $rep_who === 'niece'){
        $who = 'uncle';
    }
    if($rep_who === 'sister' || $rep_who === 'brother'){
        $who = 'brother';
    }
    if($rep_who === 'guardian' || $rep_who === 'trustee'){
        $who = 'ward';
    }
}
if(+$sex === 0){

    if($rep_who === 'son' || $rep_who === 'daughter'){
        $who = 'mother';
    }
    if($rep_who === 'mother' || $rep_who === 'father'){
        $who = 'daughter';
    }
    if($rep_who === 'wife' || $rep_who === 'husband'){
        $who = 'wife';
    }
    if($rep_who === 'grandmother' || $rep_who === 'grandfather'){
        $who = 'granddaughter';
    }
    if($rep_who === 'granddaughter' || $rep_who === 'grandson'){
        $who = 'grandmother';
    }
    if($rep_who === 'uncle' || $rep_who === 'aunt'){
        $who = 'niece';
    }
    if($rep_who === 'nephew' || $rep_who === 'niece'){
        $who = 'aunt';
    }
    if($rep_who === 'sister' || $rep_who === 'brother'){
        $who = 'sister';
    }
    if($rep_who === 'guardian' || $rep_who === 'trustee'){
        $who = 'ward';
    }
}

$_data = remove_items($data, ModelUsersRepresentative::getColumnsForInsert());
$_data_representative = $_data;
$_data_representative['user_id'] = $_data['user_id'];
$_data_representative['representative_id'] = $_data['representative_id'];
$_data_representative['representative_type'] = $rep_who;
$_data_representative['user_type'] = $who;

$sql = $this->dbBuild()
    ->model('ModelUsersRepresentative')
    ->insert($_data_representative)
    ->run();

return true;
