<?php


namespace Commands\Command\Project;


use Commands\Command\CommandProto;
use Commands\CommandConfig;

class FetchProjectRepos extends CommandProto
{
    
    public function prepare()
    {
        
    }
    
    public function run()
    {
        $node = $this->context->getProject()->getNode();
        $node->subLoad();
        $node->loadRepos();
        foreach ($node->getRepos() as $repo) {
            $start = microtime(1);
            $repo->fetch();
            $this->runtime->log(microtime(1) - $start, $repo->getPath());
        }
        
        return $this->runtime;
    }
    
    public function getId()
    {
        return CommandConfig::PROJECT_FETCH_REPOS;
    }
    
    public function getHumanName()
    {
        return 'Обновить репозитории';
    }
}