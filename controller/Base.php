<?php

class CI_Base extends Controller
{
    public function __construct()
    {
        $this->is_public = true;
    }


    function NOT_FOUND(){
        return Response::error('Страница не найдена', 404);
    }
}
