<?php


namespace Commands\Command\Delivery;


use Commands\Command\CommandProto;
use Commands\Command\SlotDeploy;

class LinkSources extends CommandProto
{
    
    public function prepare()
    {
        // TODO: Implement prepare() method.
    }
    
    public function run()
    {
        $sourcesDir =  $this->context->getCheckpoint()->getBuildPath();
        $this->runtime->log($sourcesDir, 'remote dir ');
        
        $res = $this->getSlot()->stdExec('ls '.$sourcesDir, __METHOD__);
        $this->runtime->log($res, 'remote dir contents');
        
        $dirs = $this->context->getPack()->getProject()->getPaths();
        foreach ($dirs as $path) {
            $path = trim($path, '/');
            $this->getSlot()->rmLink($path, __METHOD__);
            $res = $this->getSlot()->createLink($sourcesDir.'/'.$path, $path, __METHOD__);
            $this->runtime->log($res, 'link dir: '.$path);
        }
    }
    
    public function getId()
    {
        return 'balbal';
    }
    
    public function getHumanName()
    {
        return 'Залинковать директорию в слот';
    }
}