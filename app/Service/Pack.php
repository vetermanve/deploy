<?php


namespace Service;


use Admin\App;
use Commands\Command\SlotDeploy;
use Commands\CommandContext;
use Git\GitRepository;
use Commands\Command\LocalDeploy;
use Commands\Command\CommandProto;
use Commands\Command\Pack\CheckpointCreateCommand;
use Commands\Command\Pack\ConflictAnalyzeCommand;
use Commands\Command\Pack\FetchSandbox;
use Commands\Command\Pack\CheckpointMergeBranches;
use Commands\Command\Pack\GitMergeToMaster;
use Commands\Command\Pack\GitPushCheckpoint;
use Commands\Command\Pack\RemoveCheckpoint;
use Commands\Command\Pack\RemovePackWithData;

class Pack
{
    const DIR = 'sandbox';
    
    protected $id;
    
    /**
     * @var Node
     */
    protected $node;
    
    protected $cwd;
    
    protected $projectId;
    
    protected $dirsToInit = [];
    
    /**
     * @var GitRepository[]
     */
    protected $repos = [];
    
    protected $mergeResults = [];
    
    private $branches = [];
    
    private $name;
    
    private $data;
    
    /**
     * @var Checkpoint[]
     */
    private $checkPoints = [];
    
    protected $error = '';
    
    protected $allowPush = false;
    
    /**
     * @var Project
     */
    protected $project;
    
    /**
     * Sandbox constructor.
     *
     * @param $cwd
     */
    public function __construct($cwd = null)
    {
        $this->cwd = $cwd ?: dirname(getcwd());
        
        if (isset($_SERVER) ) {
            $hostData = explode(':', $_SERVER['HTTP_HOST']);
            $host = $hostData[0];
            if ($host == 'localhost' || $host == 'deploy.local' || $host == 'config.alol.local') {
                $this->allowPush = true;
            }
        }
    }
    
    public function getPath()
    {
        $name = $this->getName();
        
        if (!$name) {
            throw new \Exception('Call '.__FUNCTION__.' without "name" set');
        }
        
        $projectDir = $this->getProject()->getNameQuoted();
        
        return $this->cwd . DIRECTORY_SEPARATOR . self::DIR . DIRECTORY_SEPARATOR . $projectDir . DIRECTORY_SEPARATOR . $name;
    }
    
    public function prepareCommand (CommandProto $command) 
    {
        $context = $command->getContext();
        $context->setPack($this);
        
        $lastCheckpoint = $this->getLastCheckPoint() ?: null;
        if ($lastCheckpoint) {
            $context->setCheckpoint($lastCheckpoint);
        }
        
        $command->setContext($context);
        
        return $command;
    }
    
    /**
     * @param $commands CommandProto[]
     *
     * @return CommandProto[]
     */
    public function prepareCommands($commands) {
    
        $lastCheckpoint = $this->getLastCheckPoint() ?: null;
        foreach ($commands as $command) {
            $context = $command->getContext();
            $context->setPack($this);
            if ($lastCheckpoint) {
                $context->setCheckpoint($lastCheckpoint);    
            }
            $command->setContext($context);
        }
        
        return $commands; 
    }
    
    /**
     * @param $command CommandProto
     */
    public function runCommand ($command) 
    {
        $command->getContext()->setPack($this);
        $command->prepare();
        $command->run();
    }
    
    /**
     * @return CommandProto[]
     */
    public function getCheckpointCommands()
    {
        /* @var $commands CommandProto[] */
        $commands = [
//            new LocalDeploy(),
            new CheckpointMergeBranches(),
            new ConflictAnalyzeCommand(),
            //            new BuildReleaseByDirectories(),
            new RemoveCheckpoint(),
        ];
        
        return $this->prepareCommands($commands);
    }
    
    public function getPackCommands ()
    {
        /* @var $commands CommandProto[] */
        $commands = [
            new CheckpointCreateCommand(),
            new RemovePackWithData(),
            new FetchSandbox(),
        ];
        
        if ($this->getLastCheckPoint() && $this->allowPush) {
            $commands[] = new GitPushCheckpoint();
            $commands[] = new GitMergeToMaster();
        }
        
        return $this->prepareCommands($commands);
    }
    
    public function getDeployCommands ()
    {
        /* @var $commands CommandProto[] */
        $commands = [];
        
        $slots = $this->getProject()->getSlotsPool()->loadProjectSlots()->getSlots();
        
        foreach ($slots as $slot) {
            $command = new SlotDeploy();
            $command->getContext()->setSlot($slot);
            $commands[] = $command;
        }
        
        return $this->prepareCommands($commands);
    }
    
    
    public function init () 
    {
        $this->data  = (new Data(App::DATA_PACKS))->setReadFrom(__METHOD__)->readCached()[$this->id];
        $this->projectId = $this->data['pack'];
    
        $this->project = new Project($this->projectId);
        $this->project->init();
    
        $this->branches = $this->data['branches'] ?: [];
        natsort($this->branches);
    
        $this->name = isset($this->data['name']) && $this->data['name'] ? $this->data['name']
            : $this->id;
        
        $this->allowPush = 0 === strpos($this->name, 'release_');
    
        $node = $this->project->getNode();
        $node->subLoad();
        $node->loadRepos();
//        $node->loadBranches();
    
        $this->node = $node;
        
        $this->initRepos();
    }
    
    public function initRepos()
    {
        $path = $this->getPath();
        
        try {
            if (!file_exists($path)) {
                mkdir($path, 0774, true);
                chmod($path, 0774);
            }
        } catch (\Exception $e) {
            $msg = 'Cannot create directory ' . $path . ' by user: "' . `whoami` . '" by reason:"' . $e->getMessage();
            throw new \Exception($msg);
        }
        
        $this->loadSandboxRepos();
    }
    
    public function loadSandboxRepos()
    {
        $this->node->getDirs();
        $sandboxPath = $this->getPath();
        foreach ($this->node->getRepos() as $id => $repo) {
            $sandboxRepoPath = $sandboxPath . $repo->getPath();
            if (!file_exists($sandboxRepoPath . '/.git')) {
                $this->dirsToInit[$id] = $sandboxRepoPath;
            } else {
                $this->repos[$id] = new GitRepository($sandboxRepoPath);
            }
        }
    }
    
    public function loadCheckpoints()
    {
        $branchesByProject = [];
        
        foreach ($this->repos as $repo) {
            $branchesByProject[] = $repo->getBranches();
        }
    
        if (count($branchesByProject) > 1) {
            $commonLocalBranches = call_user_func_array('array_intersect', $branchesByProject);    
        } else if($branchesByProject) {
            $commonLocalBranches = array_filter((array)$branchesByProject[0]);
        } else {
            $commonLocalBranches = [];
        }
        
        foreach ($commonLocalBranches as $branch) {
            if ($branch === 'master') {
                continue;
            }
            
            $cp = new Checkpoint($this, $branch);
            $cp->setCommands($this->getCheckpointCommands());
            
            $this->checkPoints[$branch] = $cp;
        }
    }
    
    public function cloneMissedRepos()
    {
        $repos = $this->node->getRepos();
        foreach ($this->dirsToInit as $id => $targetPath) {
            $repos[$id]->cloneLocalRepository($repos[$id]->getRepositoryPath(), $targetPath);
            $this->repos[$id] = new GitRepository($targetPath);
            unset($this->dirsToInit[$id]);
        }
    }
    
    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }
    
    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }
    
    /**
     * @return Node
     */
    public function getNode()
    {
        return $this->node;
    }
    
    /**
     * @param Node $node
     */
    public function setNode($node)
    {
        $this->node = $node;
    }
    
    /**
     * @return array
     */
    public function getDirsToInit()
    {
        return $this->dirsToInit;
    }
    
    /**
     * @return \Git\GitRepository[]
     */
    public function getRepos()
    {
        return $this->repos;
    }
    
    /**
     * @return array
     */
    public function getMergeResults()
    {
        return $this->mergeResults;
    }
    
    /**
     * @return string
     */
    public function getError()
    {
        return $this->error;
    }
    
    /**
     * @return array
     */
    public function getBranches()
    {
        return $this->branches;
    }
    
    /**
     * @return Checkpoint[]
     */
    public function getCheckPoints()
    {
        return $this->checkPoints;
    }
    
    /**
     * @return Checkpoint
     */
    public function getLastCheckPoint () 
    {
        return $this->checkPoints ? end($this->checkPoints) : null; 
    }
    
    /**
     * @return Checkpoint
     */
    public function getCheckPoint($id)
    {
        return isset($this->checkPoints[$id]) ? $this->checkPoints[$id] : null;
    }
    
    /**
     * @param array $packBranches
     */
    public function setBranches($packBranches)
    {
        $this->branches = $packBranches;
    }
    
    /**
     * @param mixed $projectId
     */
    public function setProjectId($projectId)
    {
        $this->projectId = $projectId;
    }
    
    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }
    
    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }
    
    /**
     * @return Project
     */
    public function getProject()
    {
        return $this->project;
    }
    
    /**
     * @param Project $project
     */
    public function setProject($project)
    {
        $this->project = $project;
    }
    
    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }
}