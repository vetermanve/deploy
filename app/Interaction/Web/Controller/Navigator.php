<?php


namespace Interaction\Web\Controller;


use Admin\App;
use Service\Node;
use Interaction\Base\Controller\ControllerProto;
use Service\Data;

class Navigator extends AuthControllerProto
{
    public function indexAction()
    {
        $this->setTitle('Навигатор по репоизаториям');
        $pack = $this->p('pack');
        
        $node = new Node();
        
        if ($pack) {
            $node->setRoot($node->getRoot() . $pack, false);
        } else {
            $pack = $this->p('dirScan');
        }
        
        $node->setDepth(0);
        
        $dirs   = $this->p('dirs', []);
        $passed = $dirs;
        
        try {
    
            if ($dirs) {
                $node->setDirs($dirs);
                $node->subLoad();
                $node->loadRepos();
            } else {
                $node->loadDirs();
                $node->loadRepos();
            }
    
            $node->loadBranches();
        } catch (\Exception $e) {
            
        }
        
        $this->response([
            'node'       => $node,
            'passedDirs' => $passed,
            'showScan'   => $pack,
            'msg' => isset($e) ? $e->getMessage() : '',
        ]);
    }
    
    public function saveAction()
    {
        $saveDirs = $this->p('saveDirs');
        
        $projects = new Data(App::DATA_PROJECTS);
        $projects->setReadFrom(__METHOD__);
        $dirs     = explode(',', $saveDirs);
        sort($dirs);
        
        $id = crc32($saveDirs);
        
        $projects->setData($projects->read() + [$id => $dirs]);
        $projects->write(false);
        
        $this->app->redirect('/web/project/show/'.$id);
    }
}