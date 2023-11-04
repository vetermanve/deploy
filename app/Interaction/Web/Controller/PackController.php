<?php

namespace Interaction\Web\Controller;

use Admin\App;
use Commands\Command\Pack\CheckpointCreateCommand;
use Commands\CommandRunner;
use Exceptions\UrlMovedException;
use Service\Project;
use Service\Node;
use Service\Pack;

class PackController extends AuthControllerProto
{
    /**
     * @var int
     */
    private $packId;
    
    /**
     * @var Pack
     */
    private $pack;
    
    public function before()
    {
        parent::before();
       
        $this->packId = $this->p('id');
        if (!$this->packId) {
            $this->app->redirect('/web/project/');
        }
        
        $pack = new Pack();
        $pack->setId($this->packId);
        try {
            $pack->init();
            $pack->getNode()->loadBranches();
        } catch (\Exception $e) {
            $this->app->redirect('/web/project/');
        }

        $this->pack = $pack;
    }
    
    public function show()
    {
        $this->template = 'index';
        $this->index();
    }
    
    public function index()
    {
        throw new UrlMovedException("/packs/{$this->packId}");
    }
    
//    public function applyAction()
//    {
//        
//        $commandId    = $this->p('bId');
//        $checkpointId = $this->p('cpId');
//        
//        $this->pack->loadCheckpoints();
//        $this->setTitle('Работы над паком: ' . $this->pack->getName());
//        
//        
//        $runner = new CommandRunner();
//        
//        $checkpoint = $this->pack->getCheckPoint($checkpointId);
//        
//        if ($checkpoint) {
//            $this->setSubTitle('Сборка: ' . $checkpoint->getName());
//            $runner->setCommands($checkpoint->getCommands());
//            $runner->setCommandIdsToRun([$commandId]);
//        } else {
//            $runner->getRuntime()->error('Билд ' . $checkpointId . ' не найден');
//        }
//        
//        
//        $this->_runCommands($runner);
//    }
//    
//    public function applyPackAction()
//    {
//        $this->setTitle('Работы над паком: ' . $this->pack->getName());
//        $commandId = $this->p('bId');
//        $runner    = new CommandRunner();
//        $runner->setCommands($this->pack->getPackCommands());
//        $runner->setCommandIdsToRun([$commandId]);
//        
//        $this->_runCommands($runner);
//    }
//    
//    public function applySandboxAction()
//    {
//        $this->setTitle('Работы над песочницей: ' . $this->pack->getName());
//        
//        $commandId = $this->p('bId');
//        $runner    = new CommandRunner();
//        $runner->setCommands($this->pack->getSandboxCommands());
//        $runner->setCommandIdsToRun([$commandId]);
//        
//        $this->_runCommands($runner);
//    }
//    
//    /**
//     * @param $runner CommandRunner
//     */
//    private function _runCommands($runner)
//    {
//        $runner->run();
//        $this->response([
//            'runner'  => $runner,
//            'runtime' => $runner->getRuntime(),
//            'packId'  => $this->packId,
//        ], 'apply');
//    }
}