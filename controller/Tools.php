<?php

class CI_tools extends Controller
{

    public function __construct()
    {
        //ini('auth.not_auto', true);
    }
    function password($password)
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }
}
