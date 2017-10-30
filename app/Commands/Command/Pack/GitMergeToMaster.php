<?php


namespace Commands\Command\Pack;


use Admin\App;
use Commands\Command\CommandProto;
use Commands\CommandConfig;
use Service\Event\EventConfig;

class GitMergeToMaster extends CommandProto
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
            
            $repo->checkout('master');
            $repo->pull();
            
            $repo->merge($checkpoint, '--no-ff');
            $repo->push('origin', ['master']);
            
            $repo->setSshKeyPath(null);
            // ssh-agent $(ssh-add /home/christoffer/ssh_keys/theuser; git clone git@github.com:TheUser/TheProject.git)
            // ssh-agent bash -c 'ssh-add /home/christoffer/ssh_keys/theuser; git clone git@github.com:TheUser/TheProject.git'
    
            $this->runtime[$repo->getPath()] = $repo->getLastOutput();
        }
        
        $branches = $this->context->getPack()->getBranches();
        natsort($branches);
        $msg = '🏡 Ветка '.$checkpoint.' принята в master'."\n";
        $msg.= implode("\n", $branches);
    
        $this->runtime->getEventProcessor()->add(trim($msg), EventConfig::EVENT_TYPE_RELEASE_STATUS);
        
        return $this->runtime;
    }
    
    public function getId()
    {
        return CommandConfig::CHECKPOINT_MERGE_TO_MASTER;
    }
    
    public function getHumanName()
    {
        return 'Принять в мастер';
    }
    
    public function isConfirmRequired()
    {
        return true;
    }
}