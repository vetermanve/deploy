<?php


namespace Commands\Command\Delivery;


use Commands\Command\CommandProto;
use Commands\Command\SlotDeploy;
use Commands\CommandConfig;
use Commands\CommandContext;
use Service\Util\Fs;

class SourcesBuildArchiveCreate extends CommandProto
{
    
    public function prepare()
    {
        // TODO: Implement prepare() method.
    }
    
    public function run()
    {
        $fs = new Fs($this->context->get(CommandConfig::GLOBAL_WORK_DIR));
        
        $sourcesDir = $this->context->getCheckpoint()->getBuildPath();
        
        $archiveName = $sourcesDir.'.tgz';
        $this->runtime->log($archiveName, 'name');
        
        // выключено потому что с обновлением архивов есть определенные проблемы
//        $tarName = $sourcesDir.'.tar';
//        if (!$fs->hasFile($tarName)) {
//            $res = $fs->stdExec('tar cf '. $tarName . ' ' .$sourcesDir, __METHOD__);
//            $this->runtime->log($res, 'create archive');
//        } else {
//            $res = $fs->stdExec('tar ufv '. $tarName . ' ' .$sourcesDir, __METHOD__);
//            $this->runtime->log($res, 'update archive');
//        }
//        
//        $res = $fs->stdExec('gzip -c '. $tarName . ' > ' .$archiveName, __METHOD__);
    
        $res = $fs->stdExec('tar cfz '. $archiveName . ' ' .$sourcesDir, __METHOD__);
        $this->runtime->log($res, 'pack and compress');
    }
    
    public function getId()
    {
        return CommandConfig::SOURCES_BUILD_ARCHIVE_CREATE;
    }
    
    public function getHumanName()
    {
        return 'Запаковать исходники';
    }
}