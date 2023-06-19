<?php
define('DIR', __DIR__);
define('DIR_UPLOADS', DIR.'/uploads');
define('DIR_CACHE_IMGS', DIR.'/cache/imgs');
require DIR.'/helpers/uri.php';
define('URL_UPLOADS', URL.'/uploads');

if(( defined('IS_DEV') && IS_DEV ) || isset($_GET['debug'])) {
    ini_set('display_errors', 1);
    error_reporting(-1);
    ini_set('display_startup_errors', 1);

    //define('IS_DEV', true);
}


set_error_handler(['Errors', 'captureNormal']);
set_exception_handler(['Errors', 'captureException']);
register_shutdown_function(['Errors', 'captureShutdown']);


if(!sizeof($_POST)) {
    $pseudoPost = file_get_contents('php://input');
    if ($pseudoPost) {
        $_POST = json_decode($pseudoPost, true) ?? [];

        $_REQUEST+=$_POST;
    }
}


// SYSTEM:


require DIR.'/helpers/string.php';
require DIR.'/helpers/time.php';
require DIR.'/helpers/parser.php';
require DIR.'/helpers/array.php';



require DIR.'/helpers/event.php';


require DIR.'/library/Database/db.php';
$db =  new db();
require DIR.'/helpers/debug.php';
require DIR .'/helpers/tools.php';

require DIR.'/helpers/form.php';
