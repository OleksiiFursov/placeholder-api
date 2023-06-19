<?php

// No cache JS or CSS:

include DIR . '/helpers/backup.php';


function load_models($name)
{
    global $log;
    $dir = DIR . '/model/' . $name . '.php';

    if (file_exists($dir)) {
        require_once $dir;
        return true;
    } else {
        $log->set('Not Found model', 'library ' . $name . ' - not fined', 'warning');
        return false;
    }

}


function arrNodeToJson($conf)
{
    $newConf = array();

    foreach ($conf as $key => $val) {
        $keys = explode('.', $key);
        if (sizeof($keys) == 1)
            $newConf[$key] = $val;
        else {

            $link = &$newConf;

            for ($i = 0, $len = sizeof($keys); $i < $len; $i++) {
                if (!isset($link[$keys[$i]])) {
                    $link[$keys[$i]] = array();
                }

                if ($i == $len - 1) {
                    $link[$keys[$i]] = $val;
                    break;
                } else {

                    $link = &$link[$keys[$i]];
                }
            }
        }
    }

    return $newConf;
}


/**
 * @throws Exception
 */
function resizeAvatar($image)
{
    $paths = explode('/', $image);
    $name = array_pop($paths);
    array_pop($paths);
    $dir = implode('/', $paths);
    $obj = new Thumbs($image);
    $obj->resizeCanvas(128, 0, [255, 255, 255]);
    $obj->saveJpg($dir . '/small/' . $name, 90);
}


function resizeImage($image, $sizes, $q = 90, $method = 'thumb')
{
    $paths = explode('/', $image);
    $name = array_pop($paths);
    // выход с папки орилдинал
    array_pop($paths);
    $dir = implode('/', $paths);
    foreach ($sizes as $size) {
        $obj = new Thumbs($image);
        if ($method === 'resizeCanvas')
            $obj->resizeCanvas($size[0], $size[1], [255, 255, 255]);
        else
            $obj->{$method}($size[0], $size[1]);
        $obj->saveJpg($dir . '/' . $size[0] . 'x' . $size[1] . '/' . $name, $q);
    }
}

function resizeImageSmall($image, $size = 256)
{
    $paths = explode('/', $image);

    $name = array_pop($paths);

    array_pop($paths);

    $dir = implode('/', $paths);

    $obj = new Thumbs($image);
    $obj->resizeCanvas($size, 0, [255, 255, 255]);
//    $obj->resize(200, 0);

    if(!is_dir($dir.'/small')){
        mkdir($dir.'/small');
    }
    $obj->saveJpg($dir . '/small/' . $name);
}


function resizeImage320000($image)
{

    $paths = explode('/', $image);

    $name = array_pop($paths);
    notice($name);

    // выход с папки орилдинал
    array_pop($paths);


    $dir = implode('/', $paths);
    $obj = new Thumbs($image);
//    $obj->resize(36, 0);
//    $obj->saveJpg($dir .'/36/'.$name, 90);
//
//    $dir = implode('/', $paths);
//    $obj = new Thumbs($image);
//    $obj->resize(48, 0);
//    $obj->saveJpg($dir .'/48/'.$name, 90);
//
//    $dir = implode('/', $paths);
//    $obj = new Thumbs($image);
//    $obj->resize(128, 0);
//    $obj->saveJpg($dir .'/128/'.$name, 90);
//
//    $dir = implode('/', $paths);
//    $obj = new Thumbs($image);
//    $obj->resize(320, 0);
//    $obj->saveJpg($dir .'/320/'.$name, 90);

    $obj = new Thumbs($image);
    $obj->resize(640, 0);
    $obj->saveJpg($dir . '/640/' . $name, 90);

    $obj = new Thumbs($image);
    $obj->resize(960, 0);
    $obj->saveJpg($dir . '/960/' . $name, 90);

    $obj = new Thumbs($image);
    $obj->resize(1280, 0);
    $obj->saveJpg($dir . '/1280/' . $name, 90);
//
//    $obj = new Thumbs($image);
//    $obj->resize(1920, 0);
//    $obj->saveJpg($dir .'/1920/'.$name, 90);
//
//    $obj = new Thumbs($image);
//    $obj->resize(2560, 0);
//    $obj->saveJpg($dir .'/2560/'.$name, 90);

}

function resizeImageInstagram($image)
{

    $paths = explode('/', $image);

    $name = array_pop($paths);
    notice($name);

    // выход с папки орилдинал
    array_pop($paths);

    $dir = implode('/', $paths);

    $obj = new Thumbs($image);
    $obj->resize(375, 0);
    $obj->saveJpg($dir . '/375/' . $name, 90);


}


function findItemByTaxType($tax_type, $filters, $col = null)
{
    /* @var $model BaseModel */
    $model = 'Model' . ucfirst($tax_type) . 's';
    return $model::findOne($filters, $col);
}
