<?php

namespace App\Http\Controller;

use \Service\Data;

abstract class AbstractAuthController extends AbstractController
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

        if (!$this->isEnabled()) {
            $this->app->redirect('/web/errors/403');
            return;
        }

        parent::before();
    }

    public function isEnabled(): bool
    {
        return true;
    }

}