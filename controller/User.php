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
        return Response::out($this->model->logout(GET()));
    }

    public function phonecheck($filters = NULL)
    {
        [$filters, $params] = $this->get_params([], [], [
            'phone' => 'value'
        ]);

        return $this->model->phonecheck($filters, $params);

    }
    function key_add()
    {
        $arr = GET(null, 'POST');
        $Phones = new Phones;
        return Response::out($Phones->key_add($arr));
    }
    function key_check()
    {
        $arr = GET(null, 'POST');
        $Phones = new Phones;
        return Response::out($Phones->key_check($arr));
    }
}
