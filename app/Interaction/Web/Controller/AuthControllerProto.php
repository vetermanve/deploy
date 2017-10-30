<?php


namespace Interaction\Web\Controller;


use Interaction\Base\Controller\ControllerProto;
use \Service\Data;

class AuthControllerProto extends ControllerProto
{
    public function before()
    {
        $data = (new Data('user'))->readCached();

        if(!$data && empty($data)){
            $this->app->auth->setToken(\User\Auth::USER_ANONIM_TOKEN);
        } else {
            $this->app->auth->setToken($this->app->getCookie('tkn'));
        }

        $this->app->auth->loadUser();
        $this->app->auth->setUser($this->app->auth->getUser());

        if (!$this->app->auth->getUserId()) {
            $this->app->redirect('/web/auth/login');
        }
        
        parent::before();
    }

}