<?php


namespace Commands\Command\Pack;


use Commands\Command\CommandProto;
use Commands\CommandConfig;

class RemoveCheckpoint extends CommandProto
{
    
    public function prepare()
    {
        
    }
    
    public function run()
    {
        $branchName    = $this->context->getCheckpoint()->getName();
        foreach ($this->context->getPack()->getRepos() as $id => $repo) {
            $repo->checkout('origin/master');
            try {
                $repo->removeBranch($branchName);
                $this->runtime[$repo->getPath()] = 'success';
            } catch (\Exception $e) {
                $this->runtime[$repo->getPath()] = $e->getMessage();
            }
        }
        
        return $this->runtime;
    }
    
    public function getId()
    {
        return CommandConfig::CHECKPOINT_DELETE;
    }
    
    public function getHumanName()
    {
        return 'Удалить сборку';
    }
    
    
}