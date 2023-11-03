<?php

namespace App\Http\Controller;

use \Service\Data;

abstract class AbstractAuthController extends AbstractController
{
    public function before()
    {
        $data = (new Data('user'))->readCached();

        if(!$data && empty($data)){
            $this->app->getAuth()->setToken(\User\Auth::USER_ANONIM_TOKEN);
        } else {
            $this->app->getAuth()->setToken($this->app->getRequest()->getCookieParam('tkn'));
        }

        $this->app->getAuth()->loadUser();
        $this->app->getAuth()->setUser($this->app->getAuth()->getUser());

        if (!$this->app->getAuth()->getUserId()) {
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