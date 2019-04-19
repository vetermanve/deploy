<?php

namespace User;

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
     * User login
     * @return string
     */
    public function getLogin() : string
    {
        return $this->attributes[Auth::USER_LOGIN] ?? Auth::USER_ANONIM;
    }

    /**
     * @return int
     */
    public function getId() : int
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
}