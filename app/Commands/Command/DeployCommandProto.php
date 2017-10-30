<?php


namespace Commands\Command;


use Commands\Command\Build\BuildReleaseByDirectories;
use Service\Node;

abstract class DeployCommandProto extends CommandProto
{
    protected $deployRoot = '/var/www';
    
    protected $sourcesPath;
    
    protected $projectDirs = [];
    
    protected function getBuildSourcesPath() {
        if (!$this->sourcesPath) {
            $dirCommand = new BuildReleaseByDirectories();
            $dirCommand->setContext($this->context);
            $this->sourcesPath = $dirCommand->getTargetPath();
        }
        
        return $this->sourcesPath;
    }
    
    protected function getBuildDirs() {
        if (!$this->projectDirs) {
            $node = new Node();
            $node->setRoot($this->getBuildSourcesPath());
            $node->loadDirs();
            $this->projectDirs = $node->getDirs();
        }
        
        return $this->projectDirs;
    }
    
    
    /**
     * @return string
     */
    public function getDeployRoot()
    {
        return $this->deployRoot;
    }
    
    /**
     * @param string $deployRoot
     *
     * @return $this
     */
    public function setDeployRoot($deployRoot)
    {
        $this->deployRoot = $deployRoot;
        
        return $this;
    }
}