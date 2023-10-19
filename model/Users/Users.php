<?php

class Users extends Model
{
    protected string $model = ModelUsers::class;
    public $token;

    function __construct()
    {
        $this->token = new UsersToken();
    }
    //Users:
    function get($filters = [], $params = [])
    {
        return include __DIR__ . '/Actions/get.php';
    }

    //Authorization:
    function login($params)
    {
        return include __DIR__ . '/Actions/Auth/login.php';
    }

    function auth($params = [])
    {
        return include __DIR__ . '/Actions/Auth/auth.php';
    }

    function logout($params = [])
    {
        return include __DIR__ . '/Actions/Auth/logout.php';
    }

    function is_auth(): bool
    {
        return user_id() === 1;
    }

}
