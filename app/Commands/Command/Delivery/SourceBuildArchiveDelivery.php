<?php


namespace Commands\Command\Delivery;


use Commands\Command\CommandProto;
use Commands\Command\SlotDeploy;
use Commands\CommandConfig;

class SourceBuildArchiveDelivery extends CommandProto
{
    
    public function prepare()
    {
        // TODO: Implement prepare() method.
    }
    
    public function run()
    {
        $rootDir = $this->context->get(CommandConfig::GLOBAL_WORK_DIR);
    
        $sourcesDir = $this->context->getCheckpoint()->getBuildPath();
        $archiveFile = $sourcesDir.'.tgz';
        
        $localArchiveFileAbsolutePath = $rootDir.'/'.$archiveFile;
        
        $remoteDir = 'builds';
        $this->getSlot()->ensureDir($remoteDir);
        $remoteFile = $remoteDir.'/'.basename($archiveFile);
        
        $this->runtime->log($archiveFile, 'local file');
        $this->runtime->log($remoteFile, 'remote file');
        
        $result = $this->getSlot()->deliveryFile($localArchiveFileAbsolutePath, $remoteFile, __METHOD__);
        $this->runtime->log($result ? 'Success' : 'Fail', 'upload result');
    }
    
    public function getId()
    {
        return CommandConfig::SOURCES_BUILD_ARCHIVE_DELIVERY;
    }
    
    public function getHumanName()
    {
        return 'Доставить архив на сервер';
    }
}