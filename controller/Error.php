<?php

class CI_Error extends  Controller{
    function not_exists_class(){
        return $this->error_404();
    }
    function error_404(){
        if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest' || str_starts_with($_SERVER['HTTP_USER_AGENT'], 'Postman')) {
            return Response::error('Page is not found', 404);
        }else{
            exit(file_get_contents(DIR.'/web/404.php'));
        }

    }
}
