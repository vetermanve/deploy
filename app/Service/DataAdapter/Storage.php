<?php

namespace Service\DataAdapter;

interface Storage
{
    /**
     * @param $filename
     * @param array $options
     * @return mixed
     */
    public function pull($filename, array $options = []);

    /**
     * @param $filename
     * @return bool
     */
    public function has($filename) : bool;
}