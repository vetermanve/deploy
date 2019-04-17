<?php

namespace Service;

use Exception\BuilderException;
use Service\DataAdapter\Filesystem;
use Service\DataAdapter\Storage;

class Yml
{
    private $adapter;

    /**
     * @param $path
     * @return array
     * @throws BuilderException
     */
    public function parse($path) : array
    {
        if (!function_exists('yaml_parse')) {
            throw new BuilderException('Yaml extension is not installed');
        }
        if (!$this->getAdapter()->has($path)) {
            throw new BuilderException('Yaml configuration does not exists on `' . $path . '`');
        }

        try {
            $content = $this->getAdapter()->pull($path);
            $yaml = yaml_parse($content);
        } catch (\ErrorException $e) {
            throw new BuilderException($e->getMessage());
        }

        return (array) $yaml;
    }

    /**
     * @return \Service\DataAdapter\Storage
     */
    public function getAdapter() : Storage
    {
        if (null === $this->adapter) {
            $this->adapter = new Filesystem;
        }

        return $this->adapter;
    }

    /**
     * @param \Service\DataAdapter\Storage $adapter
     */
    public function setAdapter(Storage $adapter) : void
    {
        $this->adapter = $adapter;
    }
}