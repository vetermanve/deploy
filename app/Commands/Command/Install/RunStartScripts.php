<?php


namespace Commands\Command\Install;


use Commands\Command\CommandProto;
use Commands\CommandConfig;
use Service\Util\Fs;

class RunStartScripts extends CommandProto
{
    private $upScript = 'start.sh';
    
    public function prepare()
    {
        // TODO: Implement prepare() method.
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
                $res = $slot->stdExec($cmd, __METHOD__, 100);
                $this->runtime->log($res, $path);
            } else {
                $this->runtime->log($this->upScript.' not found', $path);
            }
        }
    }
    
    public function getId()
    {
        return CommandConfig::BUILD_RUN_START;
    }
    
    public function getHumanName()
    {
        return 'Запустить релиз';
    }
}