<?php
global $db;

$duration = time()+get_option('user.duration', 365 * 24 * 60 * 60);

setcookie('token', '', $duration, '/', URL_DOMAIN);

