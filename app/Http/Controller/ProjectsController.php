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
    
    public function index(): void
    {
        $this->setTitle( '<i class="fa-solid fa-folder-tree"></i>' . __('projects'));
        
        $projects = (new Data(App::DATA_PROJECTS))->setReadFrom(__METHOD__)->readCached();
        $packsData    = (new Data(App::DATA_PACKS))->setReadFrom(__METHOD__)->readCached();
        
        $sets = [];
        foreach ($packsData as $id => $data) {
            $sets[$data['pack']][$id] = $data;
        }

        $this->view->render('projects/index.blade.php', [
            'dirSets'    => $projects,
            'branchSets' => $sets,
        ]);
    }

    public function show($id): void
    {
        $this->project = new Project($id);
        $this->project->init();

        $this->setTitle('<i class="fa-solid fa-folder-open"></i>' . $this->project->getName());
        $this->project->getSlotsPool()->loadProjectSlots();
        $this->project->initPacks();
        
        $fetchCommand = new FetchProjectRepos();
        $fetchCommand->setContext((new CommandContext())->setProject($this->project));

        $this->view->render('projects/show.blade.php', [
            'project' => $this->project,
            'fetchCommand' => $fetchCommand,
            'id'        => $this->project->getId(),
            'setData'   => $this->project->getPaths(),
            'packs' => $this->project->getPacks(),
            'slots' => $this->project->getSlotsPool()->getSlots(),
        ]);
    }
}