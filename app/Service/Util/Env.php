<?php

namespace Service\Util;

class Env
{
    private static $env;

    public static function get(string $key, $default = null)
    {
        if (self::$env === null) {
            self::loadEnvFile();
        }

        return self::$env[$key] ?? $default;
    }

    public static function loadEnvFile(string $dotEnvFilePath = null)
    {
        if ($dotEnvFilePath === null) {
            $dotEnvFilePath = ROOT_DIR . '/.env';
        }

        if (!file_exists($dotEnvFilePath) || !is_readable($dotEnvFilePath)) {
            throw new \Exception('Unable to read .env file from ' . $dotEnvFilePath);
        }

        self::$env = [];

        $fileLines = file($dotEnvFilePath);
        foreach ($fileLines as $line) {
            list($name, $value) = explode('=', $line, 2);
            self::$env[ trim($name) ] = self::parseEnvValue( trim($value) );
        }
    }

    /**
     * @param string $value
     * @return bool|string
     */
    private static function parseEnvValue(string $value)
    {
        // check for quotes before parse
        $first = substr($value, 0, 1);
        $last = substr($value, -1, 1);
        if (($first === '"' && $last === '"') || ($first === "'" && $last === "'")) {
            return substr($value, 1, -1);
        }

        if ($value === 'true') {
            return true;
        } elseif ($value === 'false') {
            return false;
        } elseif (is_numeric($value)) {
            return $value + 0;
        }

        return $value;
    }
}