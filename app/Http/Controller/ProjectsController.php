<?php

namespace App\Http\Controller;

use Admin\App;
use Commands\Command\Project\FetchProjectRepos;
use Commands\CommandContext;
use Service\Pack;
use Service\Project;
use Service\Node;
use Service\Data;

class ProjectsController extends AbstractAuthController
{
    protected $rootDir;
    
    /**
     * @var
     */
    protected $node;
    
    /**
     * @var Project
     */
    private $project;
    
    private $projectId; 
    
    public function before()
    {
        // TODO: ПОКА НЕ РАБОТАЕТ ПОЛУЧЕНИЕ ПАРАМЕТРОВ ИЗ ЗАПРОСА!
        $this->projectId = $this->p('id', $this->app->itemId);
//        var_dump($this->projectId);exit;
        if ($this->projectId) {
            try {
                $this->project = new Project($this->projectId);
                $this->project->init();       
            } catch (\Exception $e) {
                $this->notFound($e->getMessage());
            }
        }
        
        parent::before();
    }
    
    public function index($request, $response, $args)
    {
//        var_dump($this->project->getName());exit;
        $this->setTitle( '<i class="fa-solid fa-folder-tree"></i>' . __('projects'));
        
        $projects = (new Data(App::DATA_PROJECTS))->setReadFrom(__METHOD__)->readCached();
        $packsData    = (new Data(App::DATA_PACKS))->setReadFrom(__METHOD__)->readCached();
        
        $sets = [];
        foreach ($packsData as $id => $data) {
            $sets[$data['pack']][$id] = $data;
        }

        $this->view->render('projects/index.blade.php', [
            'request' => $request,
            'dirSets'    => $projects,
            'branchSets' => $sets,
        ]);
    }

    public function show()
    {
        $this->setTitle('<i class="fa-solid fa-folder-open"></i>' . $this->project->getName());
        $this->project->getSlotsPool()->loadProjectSlots();
        $this->project->initPacks();
        
        $fetchCommand = new FetchProjectRepos();
        $fetchCommand->setContext((new CommandContext())->setProject($this->project));
        
        $this->response([
            'project' => $this->project,
            'fetchCommand' => $fetchCommand,
            'id'        => $this->projectId,
            'setData'   => $this->project->getPaths(),
            'packs' => $this->project->getPacks(),
            'slots' => $this->project->getSlotsPool()->getSlots(),
        ]);
    }
}