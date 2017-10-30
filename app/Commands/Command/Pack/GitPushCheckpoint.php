<?php


namespace Commands\Command\Pack;


use Admin\App;
use Commands\Command\CommandProto;
use Commands\CommandConfig;
use Service\Event\EventConfig;

class GitPushCheckpoint extends CommandProto
{
    
    public function prepare()
    {
        
    }
    
    public function run()
    {
        $checkpoint = $this->context->getCheckpoint()->getName();
        
        $sshPrivateKey = getcwd().'/ssh_keys/'.App::i()->auth->getUserLogin();
    
        if (!file_exists($sshPrivateKey)) {
            $this->runtime->log('specific ssh private key "'.$sshPrivateKey.'" not found. Used default.', 'git config');
            $sshPrivateKey = null;
        }
        
        foreach ($this->context->getPack()->getRepos() as $id => $repo) {
            $repo->setSshKeyPath($sshPrivateKey);
            $repo->fetch();
            $repo->checkout($checkpoint);
            $repo->push('origin', [$checkpoint]);
            $repo->setSshKeyPath(null);
    
            $this->runtime[$repo->getPath()] = $repo->getLastOutput();
        }
    
        $branches = $this->context->getPack()->getBranches();
        natsort($branches);
        $msg = '🕺 Ветка '.$checkpoint.' опубликована:'."\n";
        $msg.= implode("\n", $branches);
    
        $this->runtime->getEventProcessor()->add(trim($msg), EventConfig::EVENT_TYPE_RELEASE_STATUS);
        
        return $this->runtime;
    }
    
    public function getId()
    {
        return CommandConfig::CHECKPOINT_PUSH_TO_ORIGIN;
    }
    
    public function getHumanName()
    {
        return 'Запушить';
    }
    
    public function isConfirmRequired()
    {
        return true;
    }
}