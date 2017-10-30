<?php


namespace Commands\Command\Pack;


use Commands\Command\CommandProto;
use Commands\CommandConfig;

class FetchSandbox extends CommandProto
{
    
    public function prepare()
    {
        
    }
    
    public function run()
    {
        foreach ($this->context->getPack()->getProject()->getNode()->getRepos() as $id => $repo) {
            $start = microtime(1);
            $repo->fetch();
            $this->runtime->log(microtime(1) - $start, $repo->getPath());
        }
        
        return $this->runtime;
    }
    
    public function getId()
    {
        return CommandConfig::PACK_FETCH_PROJECT;
    }
    
    public function getHumanName()
    {
        return 'Обновить репозитории';
    }
}