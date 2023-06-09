<?php

namespace Git;

class GitException extends \Exception
{
    /** @var array */
    protected $output = [];
    
    /**
     * @return array
     */
    public function getOutput()
    {
        return $this->output;
    }
    
    /**
     * @param array $output
     */
    public function setOutput($output)
    {
        $this->output = (array)$output;
    }
    
    
}
