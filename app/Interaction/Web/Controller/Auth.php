<?php


namespace Interaction\Web\Controller;


use Interaction\Base\Controller\ControllerProto;
use Service\Data;

class Auth extends ControllerProto
{
    /**
     * @var Data
     */
    protected $userData;

    /**
     * @var Data
     */
    protected $sessionsData;
    
    public function before()
    {
        $this->userData = new Data('user');
        $this->sessionsData = new Data('sessions');

        parent::before();
    }
    
    public function loginAction () {
        $this->setTitle('Представьтесь');

        switch($this->app->request->getMethod()){
            case \Slim\Http\Request::METHOD_POST :
                $status = false;
                $user = [];

                $key = $this->p('login');
                $userPassword = md5($this->p('password'));

                $users = $this->userData->readCached();

                if(isset($users[$key])  && $users[$key][\User\Auth::USER_PASS] == $userPassword){
                        $status  = true;
                        $user[$key] = $users[$key];
                }

                if ($status == true && !empty($user)) {
                    $token = $this->createToken($key);

                    $this->app->setCookie('tkn', $token, (time() + 31104000));

                    $sessions = $this->sessionsData->setData([$token => $key] + $this->sessionsData->read());
                    $sessions->write();

                    $this->app->redirect('/web/project');
                } else {
                    $this->app->redirect('/web/auth/login');
                }
                break;

            default :
                $this->response([]);
                break;
        }

    }

    public function logoutAction ()
    {
        $token = $this->app->getCookie('tkn');

        $session = $this->sessionsData->read();
        if (isset($session[$token])) {
            unset($session[$token]);
        }
        $sessions = $this->sessionsData->setData($session);
        $sessions->write();
        $this->app->deleteCookie('tkn');
        $this->app->redirect('/web/auth/login');

    }

    public function registerAction ()
    {
        $this->setTitle('Регистрация');

        switch($this->app->request->getMethod()){
            case \Slim\Http\Request::METHOD_POST :
                // обработка данных юзера, валидация, добавление  в scope users
                $status = false;

                $key = $this->p('login');
                $userName = ($this->p('name'))?$this->p('name'):"";
                $userPassword1 = md5($this->p('password1'));
                $userPassword2 = md5($this->p('password2'));

                //Проверка существования пользователя
                $users = $this->userData->readCached();

                if (isset($users[$key])) {
                    $status  = true;
                }

                $id = $this->createToken($key);

                //Создание пользователя
                if($status == false && $userPassword1 == $userPassword2) {

                    $user[$key][\User\Auth::USER_NAME] = $userName;
                    $user[$key][\User\Auth::USER_PASS] = $userPassword1;
                    $user[$key][\User\Auth::USER_ID] = $id;
                    $user[$key][\User\Auth::USER_LOGIN] = $key;
                    $userNew = $this->userData->setData($user + $this->userData->read());
                    $userNew->write();
                }

                //загрузка юзера и установка куков
                $token = $this->createToken($key);
                $this->app->setCookie('tkn', $token);
                $sessions = $this->sessionsData->setData([$token => $key] + $this->sessionsData->read());
                $sessions->write();

                $this->app->redirect('/web/project');
                break;

            default :
                $this->response([]);
                break;
        }

    }

    protected function createToken($name){

        $result = md5(microtime() . $name);

        return $result;

    }
}