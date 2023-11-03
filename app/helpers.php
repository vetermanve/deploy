<?php

if (! function_exists('env')) {
    /**
     * Gets the value of an environment variable.
     *
     * @param  string  $key
     * @param  mixed  $default
     * @return mixed
     */
    function env(string $key, $default = null)
    {
        return \Service\Util\Env::get($key, $default);
    }
}

if (! function_exists('__')) {
    function __($key, $lang = 'en') {
        // get translation and return
        return \Admin\App::getInstance()->getLangStringForKey($key, $lang) ?? $key;
    }
}


if (! function_exists('request')) {
    function request() {
        return \Admin\App::getInstance()->getRequest();
    }
}