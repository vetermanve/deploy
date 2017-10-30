<?php


namespace Commands\Command\Delivery;


use Commands\Command\CommandProto;
use Commands\Command\SlotDeploy;
use Commands\CommandConfig;

class SourceTargetUnarchive extends CommandProto
{
    
    public function prepare()
    {
        // TODO: Implement prepare() method.
    }
    
    public function run()
    {
        $buildPath = $this->context->getCheckpoint()->getBuildPath();
        $remoteFile = 'builds/'.basename($buildPath.'.tgz');
        $res = $this->getSlot()->stdExec(' tar xf '.$remoteFile. '  ', __METHOD__, 12);
        $this->runtime->log($res, 'remote unarchive');  
    }
    
    public function getId()
    {
        return CommandConfig::SOURCES_BUILD_TARGET_UNARCHIVE;
    }
    
    public function getHumanName()
    {
        return 'Разархивировать пакет на сервере';
    }
}