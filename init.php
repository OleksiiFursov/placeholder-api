<?php
define('DIR', __DIR__);
define('DIR_UPLOADS', DIR . '/uploads');
define('DIR_CACHE_IMGS', DIR . '/cache/imgs');
require DIR . '/helpers/uri.php';
define('URL_UPLOADS', URL . '/uploads');

if ((defined('IS_DEV') && IS_DEV) || isset($_GET['debug'])) {
    ini_set('display_errors', 1);
    error_reporting(-1);
    ini_set('display_startup_errors', 1);

    //define('IS_DEV', true);
}


set_error_handler(['Errors', 'captureNormal']);
set_exception_handler(['Errors', 'captureException']);
register_shutdown_function(['Errors', 'captureShutdown']);
if ($_SERVER['REQUEST_METHOD'] === 'PATCH' || $_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $input = file_get_contents('php://input');

    // Extracting the boundary
    preg_match('/boundary=(.*)$/', $_SERVER['CONTENT_TYPE'], $matches);
    if (!$matches) {
        $_REQUEST += json_decode($input, true) ?? [];
    } else {
        $boundary = $matches[1];

        // Splitting the data using the boundary
        $blocks = preg_split("/-+$boundary/", $input);
        array_pop($blocks);

        $data = [];
        foreach ($blocks as $id => $block) {
            if (empty($block))
                continue;

            if (str_contains($block, 'application/octet-stream')) {
                // Handle files here
                continue;
            }

            if (preg_match('/name="([^"]+)"\s*([\s\S]+)/', $block, $matches)) {
                $name = $matches[1];
                $value = rtrim($matches[2]);

                // Storing the parsed values
                $data[$name] = $value;
            }
        }
        $_REQUEST += $data;
    }
}
//    if (!sizeof($_POST)) {
//    $pseudoPost = file_get_contents('php://input');
//    if ($pseudoPost) {
//        $_POST = json_decode($pseudoPost, true) ?? [];
//
//        $_REQUEST += $_POST;
//    }
//}


// SYSTEM:
require DIR . '/helpers/string.php';
require DIR . '/helpers/time.php';
require DIR . '/helpers/parser.php';
require DIR . '/helpers/array.php';


require DIR . '/helpers/event.php';


require DIR . '/library/Database/db.php';
$db = new db();
require DIR . '/helpers/debug.php';
require DIR . '/helpers/tools.php';

require DIR . '/helpers/form.php';
