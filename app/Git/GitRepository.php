<?php
/**
 * Default implementation of IGit interface
 *
 * @author  Jan Pecha, <janpecha@email.cz>
 * @license New BSD License (BSD-3), see file license.md
 */

namespace Git;

use Admin\App;
use Service\Util\Fs;

class GitRepository
{
    /** @var  string */
    private $repository;
    
    private $path;
    
    /** @var  string|NULL  @internal */
    private $cwd;
    
    
    private $remoteBranches = [];
    
    private $lastOutput = '';
    
    /**
     * @var Fs
     */
    private $fs;
    
    private $sshKeyPath = '';
    
    /**
     * @param $repository
     *
     * @throws GitException
     */
    public function __construct($repository)
    {
        GitException::INIT;
        
        if (basename($repository) === '.git') {
            $repository = dirname($repository);
        }
        
        $this->path       = $repository;
        $this->repository = realpath($repository);
        
        $this->fs = new Fs();
        $this->fs->setWorkDir($this->repository);
        
        if ($this->repository === false) {
            $this->exception("Repository '$repository' not found.");
        }
    }
    
    
    /**
     * @return string
     */
    public function getRepositoryPath()
    {
        return $this->repository;
    }
    
    
    /**
     * Creates a tag.
     * `git tag <name>`
     *
     * @param  string
     *
     * @throws GitException
     * @return self
     */
    public function createTag($name)
    {
        return $this->begin()->run('git tag', $name)->end();
    }
    
    
    /**
     * Removes tag.
     * `git tag -d <name>`
     *
     * @param  string
     *
     * @throws GitException
     * @return self
     */
    public function removeTag($name)
    {
        return $this->begin()->run('git tag', array(
            '-d' => $name,
        ))->end();
    }
    
    
    /**
     * Renames tag.
     * `git tag <new> <old>`
     * `git tag -d <old>`
     *
     * @param  string
     * @param  string
     *
     * @throws GitException
     * @return self
     */
    public function renameTag($oldName, $newName)
    {
        return $this->begin()// http://stackoverflow.com/a/1873932
        // create new as alias to old (`git tag NEW OLD`)
        ->run('git tag', $newName, $oldName)// delete old (`git tag -d OLD`)
        ->removeTag($oldName)// WARN! removeTag() calls end() method!!!
        ->end();
    }
    
    
    /**
     * Returns list of tags in repo.
     * @return string[]|NULL  NULL => no tags
     */
    public function getTags()
    {
        return $this->extractFromCommand('git tag', 'trim');
    }
    
    
    /**
     * Merges branches.
     * `git merge <options> <name>`
     *
     * @param  string
     * @param  array|NULL
     *
     * @throws GitException
     * @return self
     */
    public function merge($branch, $options = null)
    {
        return $this->begin()->run('git merge ', $options, $branch)->end();
    }
    
    
    /**
     * Creates new branch.
     * `git branch <name>`
     * (optionaly) `git checkout <name>`
     *
     * @param  string
     * @param  bool
     *
     * @throws GitException
     * @return self
     */
    public function createBranch($name, $checkout = false)
    {
        $this->begin();
        
        // git branch $name
        $this->run('git branch', $name);
        
        if ($checkout) {
            $this->checkout($name);
        }
        
        return $this->end();
    }
    
    
    /**
     * Removes branch.
     * `git branch -d <name>`
     *
     * @param  string
     *
     * @throws GitException
     * @return self
     */
    public function removeBranch($name)
    {
        return $this->begin()->run('git branch', array(
            '-D' => $name,
        ))->end();
    }
    
    
    /**
     * Gets name of current branch
     * `git branch` + magic
     * @return string
     * @throws GitException
     */
    public function getCurrentBranchName()
    {
        try {
            $branch = $this->extractFromCommand('git branch -a', function ($value) {
                if (isset($value[0]) && $value[0] === '*') {
                    return trim(substr($value, 1));
                }
                
                return false;
            });
            
            if (is_array($branch)) {
                return $branch[0];
            }
        } catch (GitException $e) {
        }
        $this->exception('Getting current branch name failed.');
    }
    
    
    /**
     * Returns list of all (local & remote) branches in repo.
     * @return string[]|NULL  NULL => no branches
     */
    public function getBranches()
    {
        return $this->extractFromCommand('git branch ', function ($value) {
            if (strrpos($value, 'HEAD detached') !== false ) {
                return false;
            }
            return trim(str_replace('*', '', $value));
        });
    }
    
    
    /**
     * Returns list of all (local & remote) branches in repo.
     * @return string[]|NULL  NULL => no branches
     */
    public function getRemoteBranches()
    {
        if (!$this->remoteBranches) {
            $this->remoteBranches = $this->extractFromCommand('git branch -r', function ($value) {
                return trim(str_replace('origin/', '', $value));
            });
        }
        
        return $this->remoteBranches;
    }
    
    public function mergeRemoteIfHas($branch)
    {
        $this->getRemoteBranches();
        
        if (in_array($branch, $this->remoteBranches)) {
            $this->merge('origin/' . $branch, ['--no-ff', '--log=50', '--stat']);
            
            return $this->lastOutput;
        }
        
//        $this->exception($this->path.' not found '. $branch .' in '.implode(', ',$this->remoteBranches).' branch');
        
        return false;
    }
    
    public function getRemotesLastChangeTime()
    {
        $cmg = "git for-each-ref --format='%(committerdate:format:%s) %(refname)' --sort -committerdate refs/remotes/";
        
        $d = $this->extractFromCommand($cmg, function ($value) {
            return explode(' ', $value, 2);
        });
        
        $result = array_column($d, 0, 1);
        arsort($result);
        
        return $result;
    }
    
    
    /**
     * Returns list of local branches in repo.
     * @return string[]|NULL  NULL => no branches
     */
    public function getLocalBranches()
    {
        return $this->extractFromCommand('git branch', function ($value) {
            return trim(substr($value, 1));
        });
    }
    
    
    /**
     * Checkout branch.
     * `git checkout <branch>`
     *
     * @param  string
     *
     * @throws GitException
     * @return self
     */
    public function checkout($name)
    {
        return $this->begin()->run('git checkout', $name)->end();
    }
    
    public function checkoutToNewBranch($name, $toBranch)
    {
        return $this->begin()->run('git checkout ', $name, ['-b' => $toBranch])->end();
    }
    
    public function checkoutNewBranchFromDetached($name)
    {
        return $this->begin()->run('git checkout -b ', $name)->end();
    }
    
    /**
     * Checkout branch.
     * `git checkout <branch>`
     *
     * @param  string
     *
     * @throws GitException
     * @return self
     */
    public function fullReset()
    {
        return $this->begin()->run('git reset --hard HEAD')->run('git clean -df')->end();
    }
    
    
    /**
     * Removes file(s).
     * `git rm <file>`
     *
     * @param  string|string[]
     *
     * @throws GitException
     * @return self
     */
    public function removeFile($file)
    {
        if (!is_array($file)) {
            $file = func_get_args();
        }
        
        $this->begin();
        
        foreach ($file as $item) {
            $this->run('git rm', $item, '-r');
        }
        
        return $this->end();
    }
    
    
    /**
     * Adds file(s).
     * `git add <file>`
     *
     * @param  string|string[]
     *
     * @throws GitException
     * @return self
     */
    public function addFile($file)
    {
        if (!is_array($file)) {
            $file = func_get_args();
        }
        
        $this->begin();
        
        foreach ($file as $item) {
            // TODO: ?? is file($repo . / . $item) ??
            $this->run('git add', $item);
        }
        
        return $this->end();
    }
    
    
    /**
     * Renames file(s).
     * `git mv <file>`
     *
     * @param  string|string[] from : array('from' => 'to', ...) || (from, to)
     * @param  string|NULL
     *
     * @throws GitException
     * @return self
     */
    public function renameFile($file, $to = null)
    {
        if (!is_array($file)) // rename(file, to);
        {
            $file = array(
                $file => $to,
            );
        }
        
        $this->begin();
        
        foreach ($file as $from => $to) {
            $this->run('git mv', $from, $to);
        }
        
        return $this->end();
    }
    
    
    /**
     * Commits changes
     * `git commit <params> -m <message>`
     *
     * @param           string
     * @param  string[] param => value
     *
     * @throws GitException
     * @return self
     */
    public function commit($message, $params = null)
    {
        if (!is_array($params)) {
            $params = array();
        }
        
        return $this->begin()->run("git commit", $params, array(
            '-m' => $message,
        ))->end();
    }
    
    
    /**
     * Exists changes?
     * `git status` + magic
     * @return bool
     */
    public function hasChanges()
    {
        $this->begin();
        $this->fs->exec('git status', $out, $res, __METHOD__);
        $this->end();
        $out = implode(' ', $out);
        
        $a1 = (strpos($out, 'nothing to commit')) === false; 
        $a2 = (strpos($out, 'no changes added to commit')) === false;
        $a3  = strrpos($out, 'All conflicts fixed but you are still merging') !== false;
        return ($a1 && $a2) || $a3; // FALSE => changes
    }
    
    
    /**
     * @deprecated
     */
    public function isChanges()
    {
        return $this->hasChanges();
    }
    
    
    /**
     * @return self
     */
    protected function begin()
    {
        if ($this->cwd === null) // TODO: good idea??
        {
            $this->cwd = getcwd();
//            chdir($this->repository);
        }
        
        return $this;
    }
    
    
    /**
     * @return self
     */
    protected function end()
    {
        if (is_string($this->cwd)) {
//            chdir($this->cwd);
        }
        
        $this->cwd = null;
        
        return $this;
    }
    
    public function fetch()
    {
        return $this->begin()->run("git fetch -p")->end();
    }
    
    
    /**
     * Pull changes from a remote
     *
     * @param  string|NULL
     * @param  array|NULL
     *
     * @return self
     * @throws GitException
     */
    public function pull($remote = null, array $params = null)
    {
        if (!is_array($params)) {
            $params = array();
        }
        
        return $this->begin()->run("git pull $remote", $params)->end();
    }
    
    
    /**
     * Push changes to a remote
     *
     * @param  string|NULL
     * @param  array|NULL
     *
     * @return self
     * @throws GitException
     */
    public function push($remote = null, array $params = null)
    {
        if (!is_array($params)) {
            $params = array();
        }
        
        return $this->begin()->run("git push $remote", $params)->end();
    }
    
    
    /**
     * @param  $cmd
     * @param  string
     *
     * @return NULL|\string[]
     * @throws GitException
     */
    protected function extractFromCommand($cmd, $filterCallback = null)
    {
        $output   = array();
        $exitCode = null;
        
        $this->begin();
        $this->fs->exec($cmd, $output, $exitCode, __METHOD__);
        $this->end();
        
        if ($exitCode !== 0 || !is_array($output)) {
            $this->exception("Command $cmd failed.");
        }
        
        if ($filterCallback && is_callable($filterCallback)) {
            $newArray = array();
            
            foreach ($output as $line) {
                $value = $filterCallback($line);
                
                if ($value === false) {
                    continue;
                }
                
                $newArray[] = $value;
            }
            
            $output = $newArray;
        }
        
        if (!isset($output[0])) // empty array
        {
            return null;
        }
        
        return $output;
    }
    
    
    /** Runs command.
     *
     * @param  string|array
     *
     * @return self
     * @throws GitException
     */
    protected function run($cmd/*, $options = NULL*/)
    {
        $start = microtime(1);
        $args = func_get_args();
        $cmd  = $this->processCommand($args);
        $this->fs->exec($cmd, $output, $ret, __METHOD__);
        $this->lastOutput = $output;
        
        if ($ret !== 0) {
            $this->exception("Command '$cmd' failed on " . $this->repository , $output);
        }
        
        return $this;
    }
    
    
    protected function processCommand(array $args)
    {
        $cmd = array();
        
        $programName = array_shift($args);
        
        foreach ($args as $arg) {
            if (is_array($arg)) {
                foreach ($arg as $key => $value) {
                    $_c = '';
                    
                    if (is_string($key)) {
                        $_c = "$key ";
                    }
                    
                    $cmd[] = $_c . escapeshellarg($value);
                }
            } elseif (is_scalar($arg) && !is_bool($arg)) {
                $cmd[] = escapeshellarg($arg);
            }
        }
        
        $prefix = '';
        
        if ($this->sshKeyPath) {
            $prefix = 'GIT_SSH_COMMAND="ssh -i '.$this->sshKeyPath.'"';
        }
        
        return trim($prefix.' '.$programName.' ' . implode(' ', $cmd));
    }
    
    
    /**
     * Clones GIT repository from $url into $directory
     *
     * @param  $localPath
     * @param  string
     *
     * @return bool
     * @throws GitException
     */
    public function cloneLocalRepository($localPath, $targetPath, $saveOriginalRemote = true)
    {
        if ($targetPath !== null && is_dir("$targetPath/.git")) {
//            $this->exception("Repo already exists in $targetPath.");
            return false;
        }
        
        $cwd = getcwd();
        $this->cwd = $cwd;
        
        if (!self::isAbsolute($targetPath)) {
            $targetPath = "$cwd/$targetPath";
        }
    
        $this->fs->exec('git clone -q ' . escapeshellarg($localPath) . ' ' . escapeshellarg($targetPath), $output, $returnCode, __METHOD__);
        
        if ($returnCode !== 0) {
            $this->exception("Git clone failed (directory $targetPath).");
        }
        
        if ($saveOriginalRemote) {
            $localFs = new Fs($localPath);
            $originalRemote = $localFs->exec('git remote get-url origin', $output, $result, __METHOD__);
            
            if ($originalRemote) {
                $originFs = new Fs($targetPath);
                $setRemote = 'git remote set-url origin ' . $originalRemote;
                App::i()->log($originalRemote, __METHOD__);
                $originFs->exec($setRemote, $output, $returnCode, __METHOD__);
            }
        }
        
        $this->cwd = null;
        
        if ($returnCode !== 0) {
            $this->exception("Git clone failed (directory $targetPath).");
        }
        
        return true;
    }
    
    
    /**
     * @param  string /path/to/repo.git | host.xz:foo/.git | ...
     *
     * @return string  repo | foo | ...
     */
    public static function extractRepositoryNameFromUrl($url)
    {
        // /path/to/repo.git => repo
        // host.xz:foo/.git => foo
        $directory = rtrim($url, '/');
        if (substr($directory, -5) === '/.git') {
            $directory = substr($directory, 0, -5);
        }
        
        $directory = basename($directory, '.git');
        
        if (($pos = strrpos($directory, ':')) !== false) {
            $directory = substr($directory, $pos + 1);
        }
        
        return $directory;
    }
    
    
    /**
     * Is path absolute?
     * Method from Nette\Utils\FileSystem
     * @link   https://github.com/nette/nette/blob/master/Nette/Utils/FileSystem.php
     * @return bool
     */
    public static function isAbsolute($path)
    {
        return (bool)preg_match('#[/\\\\]|[a-zA-Z]:[/\\\\]|[a-z][a-z0-9+.-]*://#Ai', $path);
    }
    
    /**
     * @return mixed
     */
    public function getPath()
    {
        return $this->path;
    }
    
    /**
     * @param mixed $path
     */
    public function setPath($path)
    {
        $this->path = $path;
    }
    
    private function exception($msg, $output = [])
    {
        $this->end();
        App::i()->log("Error: ".$msg, __METHOD__);
        $e = new GitException($msg);
        $e->setOutput($output);
        throw $e;
    }
    
    /**
     * @return string
     */
    public function getLastOutput()
    {
        return $this->lastOutput;
    }
    
    public function diff()
    {
        $this->run('git diff');
        return $this->lastOutput;
    }
    
    public function getBehindStatus($branch)
    {
        $this->begin()->run('git rev-list --left-right --count origin/master...'.$branch)->end();
        return preg_split('/\s+/', implode('', $this->lastOutput)); 
    }
    
    /**
     * @return string
     */
    public function getSshKeyPath()
    {
        return $this->sshKeyPath;
    }
    
    /**
     * @param string $sshKeyPath
     */
    public function setSshKeyPath($sshKeyPath)
    {
        $this->sshKeyPath = $sshKeyPath;
    }
}
