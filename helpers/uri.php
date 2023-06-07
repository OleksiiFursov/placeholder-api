<?php
define('URL_DOMAIN', $_SERVER['HTTP_HOST'] ?? false);
define('URL_PREV', isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '');

// Protocol:
define("IS_HTTPS", get_option('url.protocol', 2) === 1 ||
                    (get_option('url.protocol', 2) === 2 and !empty($_SERVER['HTTPS']) and 'off' !== strtolower($_SERVER['HTTPS'])));
define('URL_PROTOCOL', IS_HTTPS ? 'https://' : 'http://');
define('URL', URL_PROTOCOL . URL_DOMAIN);


function set_url($key, $value)
{
    global $_URL;
    return $_URL[$key] = $value;
}

function url($go = null, $full = null)
{
    if ($full === null) {
        $full = get_option('url.full');
    }
    $url = $full ? URL_PROTOCOL . URL_DOMAIN : '';

    if (!$go)
        return $url . '/' . $_SERVER['REQUEST_URI'];

    return $url . $go;
}

function redirect($url = false, $code = 301)
{
    if (is_bool($url)) {
        $url = $url ? $_SERVER['HTTP_REFERER'] : $_SERVER['REQUEST_URI'];
    } else if (is_string($url)) {
        $url = url($url, true);
    }

    header('location: ' . $url, true, $code);
    exit;
}



