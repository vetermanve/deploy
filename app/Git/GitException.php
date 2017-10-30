<?php


namespace Git;


class GitException extends \Exception
{
    const INIT = 1;
    
    /**
     * @var array
     */
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
