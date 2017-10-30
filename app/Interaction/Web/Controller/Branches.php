<?php


namespace Interaction\Web\Controller;


use Admin\App;
use Service\Data;
use Service\Node;
use Service\Project;

class Branches extends AuthControllerProto
{
    const ACTION_PACK_CREATE          = 'create';
    const ACTION_PACK_ADD_BRANCH      = 'add';
    const ACTION_PACK_CHANGE_BRANCHES = 'change';
    const ACTION_PACK_FORK            = 'fork';

    /**
     * @var Node
     */
    private $node;

    /**
     * @var int
     */
    private $projectId;
    
    /**
     * @var Project
     */
    private $project;
    
    /**
     * @var \Admin\DoView
     */
    private $view;
    
    private $packId;
    
    private $branches = [];
    
    private $packBranches = [];
    private $packData     = [];
    
    public function before()
    {
        $this->projectId = $this->app->itemId;
        $this->packId    = $this->p('packId');
        $this->branches  = $this->p('branches', []);
        
        if (!$this->projectId) {
            $this->notFound();
        }
        
        $this->project = new Project($this->projectId);
        $this->project->init();
        
        $node = $this->project->getNode();
        $node->subLoad();
        $node->loadRepos();
        $node->loadBranches();

        $this->node = $node;
        if ($this->packId) {
            $currentPacks = (new Data(App::DATA_PACKS))->setReadFrom(__METHOD__)->read();
            $this->packData     = $currentPacks[$this->packId];
            $this->packBranches = $this->packData['branches'] ?: [];
            natsort($this->packBranches);
        }
        
        parent::before();
    }
    
    public function createPack()
    {
        $this->setTitle("Создание пака");
    
        $this->renderList([
            'action' => self::ACTION_PACK_CREATE
        ]);
    }
    
    public function addBranch()
    {
        $this->setTitle("Добавление веток");
    
        $this->renderList([
            'action' => self::ACTION_PACK_ADD_BRANCH
        ]);
    }
    
    public function forkPack()
    {
        $this->setTitle("Форк пака");
    
        $this->renderList([
            'selected' => array_flip($this->packBranches) ?: [],
            'action'   => self::ACTION_PACK_FORK
        ]);
    }
    
    public function removeBranch()
    {
        $this->setTitle("Удаление веток из пака");
        
        $this->renderList([
            'selected' => array_flip($this->packBranches) ?: [],
            'action'   => self::ACTION_PACK_CHANGE_BRANCHES
        ]);
    }
    
    private function renderList($data) {
        $this->setSubTitle('Проект ' . $this->project->getName() . ' #' . $this->projectId);
    
        $branches = $this->project->getNode()->getRepoDirsByBranches();

        $packReposByBranches = $this->node->getToMasterStatus($this->packBranches);
        
        $this->template = 'list';
        $this->response($data + [
            'project'  => $this->project,
            'packId'   => $this->packId,
            'selected' => [],
            'packBranches' => $this->packBranches,
            'branches' => $branches,
            'branchesData' => $packReposByBranches
        ]);
    }
    
    /**
     * Сохранение выбора
     */
    public function save()
    {
        $action = $this->p('action');
        
        if ($action === self::ACTION_PACK_CREATE || $action === self::ACTION_PACK_FORK) {
            $this->_createPack();
        } elseif ($action === self::ACTION_PACK_ADD_BRANCH) {
            $this->_updatePack();
        } elseif ($action === self::ACTION_PACK_CHANGE_BRANCHES) {
            $this->_changePack();
        }
        
        $this->app->redirect($this->app->request->getReferer());
    }
    
    /**
     * Создание пака
     */
    private function _createPack()
    {
        $name = $this->p('name', '');
        $name = preg_replace('/\W+/', '_', $name);
        
        natsort($this->branches);
        
        $this->packData = [
            'name'     => $name,
            'pack'     => $this->project->getId(),
            'branches' => $this->branches,
        ];
        
        $packs = new Data(App::DATA_PACKS);
        $packs->setReadFrom(__METHOD__);
    
        $this->packId = crc32(microtime(1));
        $packs->setData([$this->packId => $this->packData] + $packs->read());
        $packs->write();
        
        $this->_goPack();
    }
    
    /**
     * Добавление веток в пак
     */
    private function _updatePack()
    {
        $packs = new Data(App::DATA_PACKS);
        $packs->setReadFrom(__METHOD__);
        
        $this->packData['branches'] = array_unique(array_merge($this->packBranches, $this->branches));
        natsort($this->packData['branches']);
        
        $packs->setData([$this->packId => $this->packData] + $packs->read());
        $packs->write();
        
        $this->_goPack();
    }
    
    /**
     * Удаление веток из пака
     */
    private function _changePack()
    {
        $packs       = new Data(App::DATA_PACKS);
        $packs->setReadFrom(__METHOD__);
        $oldBranches = $this->p('oldBranches');
        $oldBranches = json_decode($oldBranches, 1);
        $branchesToRemove = array_diff($oldBranches, $this->branches);
        $newBranchesIdx   = array_flip($this->packBranches);
        
        foreach ($branchesToRemove as $branch) {
            unset($newBranchesIdx[$branch]);
        }
        
        $this->packData['branches'] = array_flip($newBranchesIdx);
        natsort($this->packData['branches']);
        
        $packs->setData([$this->packId => $this->packData] + $packs->read());
        $packs->write();
        
        $this->_goPack();
    }
    
    private function _goPack()
    {
        if($this->p('return')){
            $this->app->redirect($this->app->request->getReferrer());
        }
        $this->app->redirect('/web/pack/' . $this->packId);
    }
}