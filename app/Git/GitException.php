<?php

namespace Git;

class GitException extends \Exception
{
    /** @var array */
    protected $output = [];

    public function getOutput(): array
    {
        return $this->output;
    }

    public function setOutput(array $output)
    {
        $this->output = $output;
    }
}
