<?php


namespace Service;


use Admin\App;

class Project
{
    private $name;
    
    private $id;
    
    private $projectRootDirs;
    
    protected $dirSets = [];
    
    /**
     * @var Pack[]
     */
    protected $packs = [];
    
    /**
     * @var Node
     */
    private $node;
    
    /**
     * @var SlotsPool
     */
    protected $slotsPool;
    
    /**
     * Project constructor.
     *
     * @param $projectId
     */
    public function __construct($projectId)
    {
        $this->setId($projectId);
    }
    
    public function init()
    {
        if (!$this->id) {
            throw new \Exception('Project id not set');
        }
        
        /* load project data */
        $projects = (new Data(App::DATA_PROJECTS))->setReadFrom(__METHOD__)->readCached();
        if (!isset($projects[$this->id])) {
            throw new \Exception('Project #' . $this->id . ' not found');
        }
        
        /* get project data */
        $this->projectRootDirs = $projects[$this->id];
        
        /* boot node */
        $this->node = new Node();
        $this->node->setDirs($this->projectRootDirs);
    
        $this->slotsPool = new SlotsPool();
        $this->slotsPool->setProjectId($this->id);
    }
    
    public function initPacks () 
    {
        $packs = (new Data(App::DATA_PACKS))->setReadFrom(__METHOD__)->readCached();
        foreach ($packs as $id => $data) {
            if ($data['pack'] == $this->id) {
                $pack = new Pack();
                $pack->setId($id);
                $pack->init();
                $this->packs[$id] = $pack;
            }
        }
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
     * @return array
     */
    public function getDirSets()
    {
        return $this->dirSets;
    }
    
    /**
     * @return mixed
     */
    public function getProjectRootDirs()
    {
        return $this->projectRootDirs;
    }
    
    public function getPaths()
    {
        return $this->projectRootDirs;
    }
    
    /**
     * @return Pack[]
     */
    public function getPacks()
    {
        return $this->packs;
    }
    
    public function getName($withId = true)
    {
        if (!$this->name) {
            $rootDirs = $this->projectRootDirs;
            array_walk($rootDirs, function (&$val) {
                $val = trim($val, '/');
            });
            $this->name = implode(', ', $rootDirs);
        }
        
        return $this->name . ($withId ? ' #' . $this->id : '');
    }
    
    public function getNameQuoted () 
    {
        return preg_replace('/\W+/', '-', $this->getName());
    }
    
    /**
     * @return SlotsPool
     */
    public function getSlotsPool()
    {
        return $this->slotsPool;
    }
    
    
}