<?php


namespace Interaction\Web\Controller;


use Admin\App;
use Commands\Command\Project\FetchProjectRepos;
use Commands\CommandContext;
use Exceptions\UrlMovedException;
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
        throw new UrlMovedException("/projects");
    }
    
    public function slots () 
    {
        $this->setTitle(__('release_servers'));
        $this->setSubTitle($this->project->getName());
        
        $slots = $this->project->getSlotsPool()->loadProjectSlots()->validate()->getSlots();
        
        $this->response([
            'slots' => $slots,
            'id' => $this->projectId,
        ]);
    }
    
    public function show()
    {
        throw new UrlMovedException("/projects/{$this->projectId}");
    }

    /**
     * @TODO: LOOKS LIKE COULD BE REMOVED! BUT CHECK CAREFULLY!!!!
     */
    public function fetch()
    {
        $id = $this->p('id', $this->app->itemId);
        
        $projects = (new Data(App::DATA_PROJECTS));
        $projects->setReadFrom(__METHOD__);
        $projects->read();
        $projectsDirs = $projects->getData();
        $dirs         = $projectsDirs[$id];
        
        $node = new Node();
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
     * @TODO: LOOKS LIKE COULD BE REMOVED! BUT CHECK CAREFULLY!!!!
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
        $sshPrivateKey = SSH_KEYS_DIR . '/' . App::i()->getAuth()->getUserLogin();
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