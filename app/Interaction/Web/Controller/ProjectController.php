<?php


namespace Interaction\Web\Controller;


use Admin\App;
use Commands\Command\Project\FetchProjectRepos;
use Commands\CommandContext;
use Service\Pack;
use Service\Project;
use Service\Node;
use Service\Data;

class ProjectController extends AuthControllerProto
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
        $this->projectId = $this->p('id', $this->app->itemId);
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
    
    public function index()
    {
        $this->setTitle('Проекты');
        
        $projects = (new Data(App::DATA_PROJECTS))->setReadFrom(__METHOD__)->readCached();
        $packsData    = (new Data(App::DATA_PACKS))->setReadFrom(__METHOD__)->readCached();
        
        $sets = [];
        foreach ($packsData as $id => $data) {
            $sets[$data['pack']][$id] = $data;
        }
        
        $this->response([
            'dirSets'    => $projects,
            'branchSets' => $sets,
        ]);
    }
    
    public function slots () 
    {
        $this->setTitle('Релизные сервера');
        $this->setSubTitle($this->project->getName());
        
        $slots = $this->project->getSlotsPool()->loadProjectSlots()->validate()->getSlots();
        
        $this->response([
            'slots' => $slots,
            'id' => $this->projectId,
        ]);
    }
    
    public function show()
    {
        $this->setTitle($this->project->getName());
        $this->project->getSlotsPool()->loadProjectSlots();
        $this->project->initPacks();
        
        $fetchCommand = new FetchProjectRepos();
        $fetchCommand->setContext((new CommandContext())->setProject($this->project));
        
        $this->response([
            'fetchCommand' => $fetchCommand,
            'id'        => $this->projectId,
            'setData'   => $this->project->getPaths(),
            'packs' => $this->project->getPacks(),
            'slots' => $this->project->getSlotsPool()->getSlots(),
        ]);
    }
    
    public function fetch()
    {
        $id = $this->p('id', $this->app->itemId);
        
        $projects = (new Data(App::DATA_PROJECTS));
        $projects->setReadFrom(__METHOD__);
        $projects->read();
        $projectsDirs = $projects->getData();
        $dirs         = $projectsDirs[$id];
        
        $node = new Node();
        $node->setRoot(dirname(getcwd()));
        $node->setDirs($dirs);
        $node->subLoad();
        $node->loadRepos();
        $node->loadBranches();
        
        $result = [];
        
        foreach ($node->getRepos() as $repo) {
            $start = microtime(1);
            $repo->fetch();
            $result[$repo->getRepositoryPath()] = round(microtime(1) - $start, 4);
        }
        
        if ($this->p('return')) {
            $this->app->redirect($this->app->request->getReferrer());
            return;
        }
        
        $this->response([
            'pId'    => $this->project->getId(),
            'result' => $result,
        ]);
    }

    /**
     *
     */
    public function removeBranch()
    {
        $id = $this->p('id', $this->app->itemId);
        $branchName = $this->p('branch');

        $project = new Project($id);
        $project->init();
        $dirs = $project->getProjectRootDirs();

        $node = $project->getNode();
        $node->setRoot(dirname(getcwd()));
        $node->setDirs($dirs);
        $node->subLoad();
        $node->loadRepos();
        $node->loadBranches();
        $sshPrivateKey = getcwd().'/ssh_keys/'.App::i()->auth->getUserLogin();
        $result = [];
        try {
            foreach ($node->getRepos() as $repo) {
                $start = microtime(1);
                if (in_array($branchName,  $repo->getRemoteBranches())) {
                    $repo->setSshKeyPath($sshPrivateKey);
//                    $repo->removeBranch('origin ' . $branchName);
                    $repo->push('origin',  ['--delete', $branchName]);
                    $repo->fetch();
//                    $repo->removeBranch($branchName);
                }
                $result[$repo->getRepositoryPath()] = round(microtime(1) - $start, 4);
            }

            $ref = $this->app->request->getReferrer();
            if ($ref) {
                $this->app->redirect($ref);    
            }
            
            $this->response([
                'pId'    => $this->project->getId(),
                'result' => $result,
            ]);
        } catch (\Exception $e) {
            App::i()->log($e->getMessage().' at '.$e->getFile().':'.$e->getLine());
            $this->response([
                'pId'    => $this->project->getId(),
                'result' => $result,
                'error' => $e->getMessage().' at '.$e->getFile().':'.$e->getLine(),
            ]);
        }
    }
}