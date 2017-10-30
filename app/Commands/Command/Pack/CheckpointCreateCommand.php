<?php


namespace Commands\Command\Pack;

use Commands\Command\CommandProto;
use Commands\CommandConfig;

class CheckpointCreateCommand extends CommandProto
{
    private $checkpointName = '';
    
    public function prepare()
    {
        $sandbox = $this->context->getPack();
        $this->checkpointName = 'build-' . $sandbox->getName() . '-' . date('Ymd-His');
        $sandbox->loadSandboxRepos();
        $sandbox->cloneMissedRepos();
    }
    
    public function run()
    {
        $sandbox = $this->context->getPack();
        foreach ($sandbox->getRepos() as $id => $repo) {
            $repo->fetch();
            $repo->fullReset();
            $repo->checkoutToNewBranch('origin/master', $this->checkpointName);
            $this->runtime[$repo->getPath()] = ['ok '.$this->checkpointName];
        }
    }
    
    public function getId()
    {
        return CommandConfig::CHECKPOINT_CREATE;
    }
    
    public function getHumanName()
    {
        return 'Создать новую сборку';
    }
}