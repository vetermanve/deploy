<?php


namespace Interaction\Web\Controller;


use Interaction\Base\Controller\ControllerProto;
use \Service\Data;

class AuthControllerProto extends ControllerProto
{
    public function before()
    {
        $data = (new Data('user'))->readCached();

        /** @var \User\Auth $auth */
        $auth = $this->app->getContainer()->get('auth');
        /** @var \Slim\Http\Response $response */
        $response = $this->app->getContainer()->get('response');

        if(!$data && empty($data)){
            $auth->setToken(\User\Auth::USER_ANONIM_TOKEN);
        } else {
            $auth->setToken($this->app->getRequest()->getCookieParam('tkn'));
        }

        $auth->loadUser();
        $auth->setUser($auth->getUser());

        if (!$auth->getUserId()) {
            $response->redirect('/web/auth/login');
        }

        if (!$this->isEnabled()) {
            $response->redirect('/web/errors/403');
            return;
        }

        parent::before();
    }

    public function isEnabled(): bool
    {
        return true;
    }

}