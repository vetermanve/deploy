<?php


namespace Commands\Command\Build;


use Commands\Command\CommandProto;
use Commands\CommandConfig;
use Service\Util\Fs;

class RunCompile extends CommandProto
{
    
    public function prepare()
    {
        
    }
    
    public function run()
    {
        $sourcesDir = $this->context->getCheckpoint()->getBuildPath();
    
        $fs = new Fs($this->context->get(CommandConfig::GLOBAL_WORK_DIR));
    
        $releaseInfo = [
            'id'      => date('md.Hi.s'),
            'date'    => date('Y-m-d H:i:s'),
            'release' => $this->context->getCheckpoint()->getName(),
        ];
        
        $releaseFileBody = json_encode($releaseInfo, JSON_PRETTY_PRINT);
        $buildFile = 'build.sh';
    
        foreach ($this->context->getPack()->getNode()->getRepos() as $repository) {
            $path = trim($repository->getPath(), '/');
            
            $localBuildPath = $sourcesDir.'/'.$path;
            
            $fs->writeFile($localBuildPath.'/release.json', $releaseFileBody);
            $localFile = $localBuildPath.'/'.$buildFile;
            
            if ($fs->hasFile($localFile)) {
                $cmd = '(cd '.$localBuildPath.' && bash '.$buildFile.')';
                $res = $fs->stdExec($cmd, __METHOD__);
                $this->runtime->log($res, $path);
            } else {
                $this->runtime->log($path.'/'.$buildFile.' not found', $path);
            }
        }
    }
    
    public function getId()
    {
        return CommandConfig::BUILD_RUN_COMPILE;
    }
    
    public function getHumanName()
    {
        return 'Запустить компиляцию';
    }
}