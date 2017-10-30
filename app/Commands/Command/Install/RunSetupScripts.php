<?php


namespace Commands\Command\Install;


use Commands\Command\DeployCommandProto;
use Commands\CommandConfig;
use Service\Util\Fs;

class RunSetupScripts extends DeployCommandProto
{
    protected $upScript = 'setup.sh';
    
    public function prepare()
    {
        
    }
    
    public function run()
    {
        $slot = $this->getSlot();
        $sourcesDir = $this->context->getCheckpoint()->getBuildPath();
        
        $fs = new Fs($this->context->get(CommandConfig::GLOBAL_WORK_DIR));
        
        foreach ($this->context->getPack()->getNode()->getRepos() as $repository) {
            $path = $repository->getPath();
            $dir = trim($path, '/');
            $localFile = $sourcesDir.'/'.$dir.'/'.$this->upScript;
            
            if ($fs->hasFile($localFile)) {
                $cmd = '(cd '.$dir.' && bash '.$this->upScript.')';
                $res = $slot->stdExec($cmd, __METHOD__);
                $this->runtime->log($res, $path);
            } else {
                $this->runtime->log($this->upScript.' not found', $path);
            }
        }
    }
    
    public function getId()
    {
        return CommandConfig::BUILD_RUN_SETUP;
    }
    
    public function getHumanName()
    {
        return "Запустить Setup";
    }
}