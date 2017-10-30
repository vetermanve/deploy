<?php


namespace Commands\Command\Pack;


use Admin\App;
use Commands\Command\CommandProto;
use Commands\CommandConfig;
use Service\Data;
use Service\Util\Fs;

class RemovePackWithData extends CommandProto
{
    
    public function prepare()
    {
         
    }
    
    public function run()
    {
        $path = $this->context->getPack()->getPath();
        
        $log = Fs::i()->stdExec('rm -rf ' . $path, __METHOD__);
    
        $this->runtime->log($log, 'Sandbox remove');
        
        $packs = new Data(App::DATA_PACKS);
        $packs->setReadFrom(__METHOD__);
        $packId = $this->context->getPack()->getId(); 
        $data = $packs->read();
        unset($data[$packId]);
        $packs->setData($data);
        $packs->write();
        
        $this->runtime->log('Pack '.$packId.' removed.', 'Pack remove');
        
        return $this->runtime;
    }
    
    public function getId()
    {
        return CommandConfig::PACK_CLEAR_DATA;
    }
    
    public function getHumanName()
    {
        return 'Удалить пак';
    }
}