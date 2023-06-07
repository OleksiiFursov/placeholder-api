<?php
require __DIR__ . '/boot.php';
require __DIR__ . '/app/user.php';



if (date('H:i') === '08:00' || date('H:i') === '09:36') {
    $news = (new Test)->parser();
}

chdir(__DIR__);
$text = getcwd();



