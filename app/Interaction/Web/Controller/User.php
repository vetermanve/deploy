<?php


namespace Interaction\Web\Controller;

use Admin\App;
use Service\Data;
use Service\Project;
use Service\Sandbox;

class User extends AuthControllerProto
{
    private $userId;

    private $packsData = [];

    private $packs = [];

    public function before()
    {
        parent::before();

        $this->userId = $this->app->auth->getUserLogin();
        if (!$this->userId || $this->app->auth->isAnonim()) {
            $this->app->response->redirect('/web/project/');
        }

        $packs  = (new Data(App::DATA_PACKS))->readCached();
        foreach ($packs as $id => $data) {
            $this->packs[$data['pack']][$id] = $data;
        }

    }
    
    public function indexAction () 
    {
        $this->setTitle($this->app->auth->getUserName());
        $this->setSubTitle('@' . $this->app->auth->getUserLogin());

        $projects = [];
        $userData = [];
        $projectsData = [];
        $branches = [];

        $branchesDataByUserPacks = [];
//        $branchesDataByUserProjects = [];
        foreach ($this->packs as $projectId => $packs) {
            foreach ($packs as $packId => $scope) {
                $this->packsData[$projectId][$packId] = (isset($scope['name']))?$scope['name']:'NoName';
                
                foreach ($scope['branches'] as $branch) {
                    if (stristr($branch, $this->app->auth->getUserLogin())) {
                        $userData[$projectId][$branch][$packId] = $scope['name'];
                    }
                }
            }

            //Мастер статус для веток паков
            if (!empty($userData[$projectId])) {
                $projects[$projectId] = $projectId;
                $node = $this->_getCurrentNode($projectId);
                $branchesDataByUserPacks[$projectId] = $node->getToMasterStatus(array_keys($userData[$projectId]));
            }

        };
        
        $branchesDataByUserProjects = [];
        //Мастер статус для веток проектов
        foreach ($projects as $projectId) {
            $project = $this->_getCurrentProject($projectId);
            $branches[$projectId] = $project->getNode()->subLoad()->loadRepos()->loadBranches()->getRepoDirsByBranches();
            $projectsData[$project->getId()] = $project->getName();
            foreach ($branches[$projectId] as $branch => $repo) {
                if (!stristr($branch, $this->app->auth->getUserLogin())) {
                    unset($branches[$projectId][$branch]);
                }
            }

            $branchesDataByUserProjects[$projectId] = $project->getNode()->getToMasterStatus(array_keys($branches[$projectId]));
        }

        $this->response([
            'userData' => $userData,
            'packsData' => $this->packsData,
            'projectsData' => $projectsData,
            'branches' => $branches,
            'branchesProjData' => $branchesDataByUserProjects,
            'branchesPackData' => $branchesDataByUserPacks,
        ]);
    }
    
    public function addkey () 
    {
        $this->setTitle('Задать ssh ключ');
        
        $text = 'Сгенерируй ключик, добавь его публичную часть в главное git хранилище (github, gitlab, etc..), приватную часть вставь сюда';
        
        if ($this->app->request->isPost()) {
            $key = $this->p('key');
            $text = 'Ключик не записан';
            if ($key) {
                $filename = 'ssh_keys/'. $this->app->auth->getUserLogin();
                file_put_contents($filename, $key);
                chmod($filename, 0600);
                $text = 'Ключик успешно записан';
            }
        }
        
        $this->response([
            'msg' => $text,
        ]);
    }

    private function _getCurrentNode($projectId){
        $project = $this->_getCurrentProject($projectId);

        $node = $project->getNode();
        $node->subLoad();
        $node->loadRepos();
        $node->loadBranches();

        return $node;
    }

    private function _getCurrentProject($projectId){
        $project = new Project($projectId);
        $project->init();

        return $project;
    }


}
