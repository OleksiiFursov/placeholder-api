<?php
safe_define("IS_DEV", false);

$conf = [
	// META DATA SITE:
	'site.name'			=> 'crossFox',
	'site.lang'			=> 'ru',
	'site.year'			=> 2023,

	// URL:
	'url.protocol'	=> 1, # 0 - http, 1 - https, 2 - auto;
	'url.www'		=> true,
	'url.redirect'	=> true,
	'url.full'     => true,

    // ROUTER:
    'router.class_main'  => 'Main',
    'router.method_main' => 'index',
    'router.method_404'  => 'index',
    'router.class_404'  =>  'Base',

    //SYSTEM
    'system.mode'   => 'api',
    'document.type' => 'json',


	// INPUT
	'input.method'	=> 'POST',

	'isDev'	=> true,

    ...safe_include(__DIR__.'/config.local.php')
];

