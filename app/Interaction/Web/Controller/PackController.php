<?php


namespace Interaction\Web\Controller;


use Admin\App;
use Commands\Command\Pack\CheckpointCreateCommand;
use Commands\CommandRunner;
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
        $this->setSubTitle('<a href="/web/project/show/' . $this->pack->getProject()->getId() . '">Проект ' . $this->pack->getProject()->getName().'</a>');
    }
    
    public function showAction()
    {
        $this->template = 'index';
        $this->indexAction();
    }
    
    public function indexAction()
    {
        $this->setTitle('Пакет ' . $this->pack->getName());
        $node = $this->pack->getNode();
        $packReposByBranches = $node->getToMasterStatus($this->pack->getBranches());

        try {
            $this->pack->cloneMissedRepos();
            $this->pack->loadCheckpoints();


            if (!$this->pack->getCheckPoints()) {
                $this->pack->runCommand(new CheckpointCreateCommand());
                $this->pack->loadCheckpoints();
            }

        } catch (\Exception $e) {
            App::i()->log($e->getMessage().' at '.$e->getFile().':'.$e->getLine());
        }
        
        
        $this->pack->loadCheckpoints();
        
        $dirs = array_intersect_key($node->getDirs(), $node->getRepos());
        
        $this->response([
            'data'         => $this->pack->getData(),
            'pId'          => $this->pack->getProject()->getId(),
            'id'           => $this->packId,
            'branches'     => $packReposByBranches,
            'dirs'         => $dirs,
            'pack'         => $this->pack,
            'sandboxReady' => !$this->pack->getDirsToInit(),
        ]);
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