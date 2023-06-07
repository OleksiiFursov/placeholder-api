<?php

class FormatData{
    static $params = [
        ['id', 'level_id', 'clinic_id', 'last_connect', 'telegram_chat_id', 'user_id', 'clinic_patient_id',
            'tooth', 'category_id', 'group_id', 'doc_id', 'owner_id', 'sum', 'sum_real', 'status_payment',
            'sum_rvg', 'sum_doc', 'sum_priority', 'promo', 'rg_user_id', 'order_id', 'method_payment', 'payment_doc',
            'payment_rg', 'sex', 'version', 'code', 'order', 'parent', 'profession_id', 'patient_id',
            'is_order',
            'patient_clinic_patient_id',
            'price_id', 'q', 'sum_rg',
            'cash_check', 'cashless_check', 'cash',
            'parent_id',
            'min_q', 'material_id',
            'exchange',
            'tax_id',
            'type',
            'count',
            'chance',
            'armchair',
            'tooth_id',
            'laboratory_id',
            'apm_item',
            'notify_level',
            'firewall',
            'windows_defender',
            'auto_update',
            'monitor_off',
            'hard_disk_off',
            'suspend',
            'hour',
            'day',
            'association_id',

            'int'],
        ['status', 'wa',

            'boolean'],
        ['date', 'date_created', 'date_update', 'birthday', 'time_update', 'date_expiration', 'payment_date','date_finished','date_start','date_publication','date_public','date_entry',

            'datetime'],

        ['price', 'x' , 'y','double'] //todo

    ];

    static function datetime($a){
        if(is_numeric($a))  return (int)$a;
        if(is_string($a))   return strtotime($a);
        return $a;
    }
    static function json($a){
        return json_decode($a, 256);
    }


}
