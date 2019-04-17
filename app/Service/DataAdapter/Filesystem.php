<?php

namespace Service\DataAdapter;

class Filesystem implements Storage
{
    /**
     * @param $filename
     * @param $options
     * @return false|string
     */
    public function pull($filename, array $options = [])
    {
        // @todo implement set_error_handler/restore_error_handler
        return file_get_contents($filename);
    }

    /**
     * @param $filename
     * @return bool
     */
    public function has($filename) : bool
    {
        return file_exists($filename);
    }
}