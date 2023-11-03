<?php

declare(strict_types=1);

namespace Service\Util;

class Config
{
    protected static $instance;

    public static function getInstance()
    {
        if (!self::$instance instanceof Config) {
            self::$instance = new self();
        }
    }
}
