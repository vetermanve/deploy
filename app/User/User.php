<?php

namespace User;

use Service\Data;

class User implements \ArrayAccess
{
    protected $attributes;

    /**
     * User constructor.
     * @param $attributes
     */
    public function __construct(array $attributes = [])
    {
        $this->attributes = $attributes;
    }

    /**
     * @param string|null $token
     * @return \User\User
     */
    public function loadBySessionToken(?string $token) : User
    {
        $sessionsData = new Data('sessions');
        $auth = $sessionsData->readCached();
        $user = $this->anonimUser();
        if (isset($auth[$token])) {
            $users = (new Data('user'))->readCached();
            $user = $users[$auth[$token]];
        }

        $this->load($user);

        return $this;
    }

    /**
     * @param $id
     * @return \User\User
     */
    public function loadById($id) : User
    {
        $user = $this->anonimUser();
        $users = (new Data('user'))->readCached();
        foreach ($users as $userData) {
            if (!isset($userData['id']) || $userData['id'] !== $id) {
                continue;
            }

            $user = $userData;
            break;
        }

        $this->load($user);

        return $this;
    }

    /**
     * User login
     * @return string
     */
    public function getLogin()
    {
        return $this->attributes[Auth::USER_LOGIN] ?? Auth::USER_ANONIM;
    }

    /**
     * @return string
     */
    public function isAnonim()
    {
        return $this->getId() === Auth::USER_ANONIM_TOKEN;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->attributes[Auth::USER_ID] ?? 0;
    }

    /**
     * @param $attributes
     */
    public function load($attributes) : void
    {
        if (empty($attributes)) {
            $attributes = [];
        }

        if (!is_array($attributes) && !$attributes instanceof \ArrayAccess) {
            throw new \LogicException('User profile must be array');
        }

        $this->attributes = $attributes;
    }

    /**
     * Get path for ssh key
     * @return string|null
     */
    public function getSSH() : ?string
    {
        $file = getcwd() . '/ssh_keys/' . $this->getLogin();
        return file_exists($file) && is_readable($file) ? getcwd() . '/ssh_keys/' . $this->getLogin() : null;
    }

    /**
     * @param mixed $offset
     * @return mixed|null
     */
    public function offsetGet($offset)
    {
        return $this->attributes[$offset] ?? null;
    }

    public function offsetSet($offset, $value)
    {
        $this->attributes[$offset] = $value;
    }

    /**
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return isset($this->attributes[$offset]) || array_key_exists($offset, $this->attributes);
    }

    /**
     * @param mixed $offset
     */
    public function offsetUnset($offset)
    {
        unset($this->attributes[$offset]);
    }

    /**
     * @return array
     */
    private function anonimUser() : array
    {
        return [
            Auth::USER_ID => Auth::USER_ANONIM_TOKEN,
            Auth::USER_PASS => Auth::USER_ANONIM_TOKEN,
            Auth::USER_LOGIN => Auth::USER_ANONIM
        ];
    }
}