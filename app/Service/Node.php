<?php


namespace Service;


use Git\GitRepository;

class Node
{
    const DIR_PATH   = 'path';
    const DIR_PARENT = 'parent';
    
    protected $branchesToMasterStatus = [];
    
    private $depth = 0;
    
    private $dirs = [];
    
    /**
     * @var GitRepository[]
     */
    private $repos              = [];
    private $branchesByRepoDirs = [];
    
    private $root = '/';
    
    private $clearRoot = '/';
    
    private $repoDirsByBranches = [];
    
    /**
     * Node constructor.
     *
     * @param null $workDir
     */
    public function __construct($workDir = null)
    {
        $workDir = $workDir ?: (new Data('navigator'))->readCachedIdAndWriteDefault('root', dirname(getcwd()));
        $this->setRoot($workDir);
    }
    
    
    public function init()
    {
        $this->loadDirs($this->root);
        $this->loadRepos();
    }
    
    
    /**
     * @param int $depth
     *
     * @return Node
     */
    public function setDepth($depth)
    {
        $this->depth = $depth;
        
        return $this;
    }
    
    public function subLoad()
    {
        foreach ($this->dirs as $dir) {
            $this->loadDirs($this->clearRoot . $dir);
        }
        return $this;
    }
    
    public function loadDirs($rootDir = null)
    {
        if (!$rootDir) {
            $rootDir = $this->root;
        }
        $skipDirsIdx = array("." => 1, ".." => 1);
        $maxDepth    = $this->depth;
        $doScan      = function ($parentDir, $scan, $d = 0) use ($skipDirsIdx, $maxDepth) {
            if (!$parentDir) {
                return [];
            }
            $curDir = scandir($parentDir);
            $result = [];
            
            foreach ($curDir as &$childDir) {
                if (strpos($childDir, '.') !== 0) {
                    $dir = $parentDir . DIRECTORY_SEPARATOR . $childDir;
                    if (is_dir($dir)) {
                        $dirId = crc32($dir);
    
                        $result[$dirId] = $dir;
                        if ($d < $maxDepth) {
                            $result += $scan($parentDir . DIRECTORY_SEPARATOR . $childDir, $scan, ++$d);
                        }
                    }
                }
            }
            
            return $result;
        };
        
        $this->dirs += $doScan($rootDir, $doScan);
        $clearRoot = $this->clearRoot;
        
        array_walk($this->dirs, function (&$val) use ($clearRoot) {
            $val = str_replace($clearRoot, '', $val);
        });
        
        return $this;
    }
    
    public function loadRepos()
    {
        foreach ($this->dirs as $id => $dir) {
            if (!isset($this->repos[$id]) && file_exists($this->clearRoot . $dir . '/.git')) {
                $this->repos[$id] = new GitRepository($this->clearRoot . $dir);
                $this->repos[$id]->setPath($dir);
            }
        }

        return $this;
    }
    
    public function getBranchesByRepoId ($repoId) 
    {
        return $this->getBranchesByRepoDir($this->dirs[$repoId]);
    }
    
    public function getBranchesByRepoDir ($dir)
    {
        $this->scanBranches();
        return isset($this->branchesByRepoDirs[$dir]) ? $this->branchesByRepoDirs[$dir] : [];
    }
    
    public function loadBranches()
    {
        $this->scanBranches();
        return $this;
    }
    
    public function scanBranches()
    {
        if ($this->branchesByRepoDirs) {
            return;
        }
        
        foreach ($this->repos as $repoId => $repo) {
            $branches = $repo->getRemoteBranches();
            
            foreach ($branches as $branchOrderId => &$branch) {
                if (strpos($branch, 'HEAD') === 0) {
                    unset($branches[$branchOrderId]);
                }
            }
    
            $this->branchesByRepoDirs[$this->dirs[$repoId]] = $branches; 
        }
    }
    
    public function loadRepoDirsByBranches()
    {
        if ($this->repoDirsByBranches) {
           return ; 
        }
        
        $this->scanBranches();
        $commonBranches = [];
        
        foreach ($this->branchesByRepoDirs as $repoDir => $dirBranches) {
            foreach ($dirBranches as $branch) {
                $commonBranches[$branch][$repoDir] = $repoDir;
            }
        }
        
        $branches = array_keys($commonBranches);
        array_multisort($branches, SORT_NATURAL, $commonBranches);
        
        $this->repoDirsByBranches     = array_combine($branches, $commonBranches);
    }
    
    /**
     * @return array
     */
    public function getDirs()
    {
        return $this->dirs;
    }
    
    /**
     * @param string $root
     *
     * @param bool   $updateClearRoot
     *
     * @return Node
     */
    public function setRoot($root, $updateClearRoot = true)
    {
        $this->root = $root;
        
        if ($updateClearRoot) {
            $this->clearRoot = $root;    
        }
        
        return $this;
    }
    
    /**
     * @return \Git\GitRepository[]
     */
    public function getRepos()
    {
        return $this->repos;
    }
    
    /**
     * @param string $clearRoot
     */
    public function setClearRoot($clearRoot)
    {
        $this->clearRoot = $clearRoot;
    }
    
    /**
     * @param array $dirs
     *
     * @return Node
     */
    public function setDirs($dirs)
    {
        if (!$dirs) {
            return $this;
        }
        foreach ($dirs as $dir) {
            $this->dirs[crc32($dir)] = $dir;    
        }
        
        return $this;
    }
    
    /**
     * @param \Git\GitRepository[] $repos
     */
    public function setRepos($repos)
    {
        $this->repos = $repos;
    }
    
    /**
     * @return array
     */
    public function getRepoDirsByBranches()
    {
        $this->loadRepoDirsByBranches();
        return $this->repoDirsByBranches;
    }
    
    public function getToMasterStatus ($branches) 
    {
        $branchIdx = array_flip($branches);
        $repoIdToDirs = array_flip($this->dirs);
        
        foreach ($this->branchesByRepoDirs as $repoDir => $dirBranches) {
            foreach ($dirBranches as $branch) {
                if (!isset($branchIdx[$branch])) {
                    continue;
                }
                
                $this->branchesToMasterStatus[$branch][$repoDir] = $this->repos[$repoIdToDirs[$repoDir]]->getBehindStatus('origin/'.$branch);
            }
        }
    
        $branchesResult = array_intersect_key($this->branchesToMasterStatus, $branchIdx);
        
        $branches = array_keys($branchesResult);
        array_multisort($branches, SORT_NATURAL, $branchesResult);

        return array_combine($branches, $branchesResult);
    }
    
    
    
    /**
     * @return string
     */
    public function getRoot()
    {
        return $this->root;
    }
}