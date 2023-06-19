<?php

class ModelVocabulary extends BaseModel
{
    static $table = 'vocabulary';
    static $table_name_alt = 'vc';
    static $columns = [
        'context' => ['string'],
        'token'   => ['string', 'require' => false],
        'name' => ['string'],
        'value' => ['string'],
        'lang'  => ['string', 'default' => 'en']
    ];
}
