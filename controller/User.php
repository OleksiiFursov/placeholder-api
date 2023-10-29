<?php

class CI_User extends Controller
{
    private Users $model;

    function __construct()
    {
        $this->model = new Users;
        $this->is_public = true;
        ini('auth.not_auto', true);
    }

    function login()
    {
        return $this->model->login(GET());
    }
    function auth()
    {
        return $this->model->auth(GET());
    }
    function logout()
    {
        return $this->model->logout(GET());
    }
}
