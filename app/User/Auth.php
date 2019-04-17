<?php


namespace User;


use Service\Data;

class Auth
{
    const USER_ID = 'id';
    const USER_NAME = 'name';
    const USER_PASS = 'pass';
    const USER_LOGIN = 'login';
    const USER_ANONIM = 'Anonimus';
    const USER_ANONIM_TOKEN = 'cfcd208495d565ef66e7dff9f98764da';

    private $token = '';

    /**
     * @var \User\User
     */
    private $user;
    
    /**
     * @param string $token
     */
    public function setToken($token)
    {
        $this->token = $token;
    }
    
    public function loadUser () 
    {
        $sessionsData = (new Data('sessions'));
        $auth = $sessionsData->readCached();
        $user = new User();
        if (isset($auth[$this->token])) {
            $users = (new Data('user'))->readCached();
            $user->load($users[$auth[$this->token]]);
        }

        if($this->token === self::USER_ANONIM_TOKEN){
            $user->load($this->getAnonim());
        }

        $this->user = $user;
    }

    /**
     * @return array
     */
    private function getAnonim(){

        $user = [
            self::USER_ID => self::USER_ANONIM_TOKEN,
            self::USER_PASS => self::USER_ANONIM_TOKEN,
            self::USER_LOGIN => self::USER_ANONIM
        ];

        return $user;
    }


    /**
     * @return \User\User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param array $user
     */
    public function setUser(User $user)
    {
        $this->user = $user;
    }

    /**
     * @deprecated use App::i()->auth->getUser()->getId();
     * @return int|mixed
     */
    public function getUserId()
    {
        return $this->user->getId();
    }

    /**
     * @return mixed|string
     */
    public function getUserName ()
    {
        if (isset($this->user[self::USER_NAME]) && $this->user[self::USER_NAME]) {
            return $this->user[self::USER_NAME];
        }
    
        if (isset($this->user[self::USER_LOGIN]) && $this->user[self::USER_LOGIN]) {
            return $this->user[self::USER_LOGIN];
        }
        
        return self::USER_ANONIM;
    }

    /**
     * @return mixed|string
     */
    public function getUserLogin ()
    {
        return isset($this->user[self::USER_LOGIN]) ? $this->user[self::USER_LOGIN] : self::USER_ANONIM;
    }

    /**
     * @return bool
     */
    public function isAuth()
    {
        if($this->getUserId() !== 0 && $this->getUserId() !== self::USER_ANONIM_TOKEN){
            return true;
        }
        return false;
    }

    /**
     * @return bool
     */
    public function isAnonim()
    {
        if($this->getUserId() == self::USER_ANONIM_TOKEN){
            return true;
        }
        return false;
    }
    
}