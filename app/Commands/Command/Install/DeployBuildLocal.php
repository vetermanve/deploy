<?php


namespace Commands\Command\Install;


use Commands\Command\DeployCommandProto;
use Commands\CommandConfig;

class DeployBuildLocal extends DeployCommandProto
{
    
    public function prepare()
    {
        
    }
    
    public function run()
    {
        $sourcePath = $this->getBuildSourcesPath();
        
//        $fs = new Fs($sourcePath);
        $fs = $this->context->getSlot();
        
        foreach ($this->getBuildDirs() as $dir) {
            $targetPath = $this->deployRoot.$dir;
            $fs->ensureDir(dirname($targetPath));
            $res = $fs->rmLink($targetPath, __METHOD__);
            $this->runtime->log($res ? 'Success' : 'Fail', $dir . ' remove link');
            $cmd = 'ln -s '. $sourcePath.''.$dir.' '.$targetPath;
            $exRes = $fs->stdExec($cmd, __METHOD__);
            $this->runtime->log($exRes, $dir. ' create link');
        }
    }
    
    public function getId()
    {
        return CommandConfig::DEPLOY_LOCAL;
    }
    
    public function getHumanName()
    {
        return 'Разлить локально';
    }
}