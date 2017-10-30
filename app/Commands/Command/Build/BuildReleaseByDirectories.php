<?php


namespace Commands\Command\Build;


use Commands\Command\CommandProto;
use Commands\Command\SlotDeploy;
use Commands\CommandConfig;
use Service\Util\Fs;

class BuildReleaseByDirectories extends CommandProto
{
    /**
     * @var Fs
     */
    private $fs;
    
    private $targetPath = '';
    
    public function getId()
    {
        return CommandConfig::BUILD_DIRECTORY;
    }
    
    public function prepare()
    {
        if (!$this->context->getPack() || !$this->context->getCheckpoint()) {
            $this->runtime->error(['missingContext' => base64_decode($this->context->serialize())]);
            
            return;
        }
        
        $globalWorkDir = $this->context->get(CommandConfig::GLOBAL_WORK_DIR); 
        $this->fs = new Fs($globalWorkDir);
            
        $this->targetPath = $this->context->getCheckpoint()->getBuildPath();
        
        $this->runtime->log($this->targetPath, 'targetPath');
        $this->fs->ensureDir($this->targetPath);
    }
    
    public function run()
    {
        if (!$this->targetPath) {
            return;
        }
        
        $checkpoint = $this->context->getCheckpoint()->getName();
        $pack       = $this->context->getPack();
        
        foreach ($pack->getRepos() as $id => $repo) {
            $repo->checkout($checkpoint);
        }
        
        $sourcePath = $pack->getPath();
        $this->runtime->log($sourcePath, 'source');
        
        $this->fs->exec('rsync -av --delete-after ' . $sourcePath . '/* ' . $this->targetPath . ' --exclude .git', $out,
            $result, __METHOD__, 30);
        
        $this->runtime->log($result === 0 ? 'Success' : 'Error', 'result');
        $this->runtime->log($out, 'out');
    }
    
    public function getHumanName()
    {
        return "Собрать папку";
    }
}