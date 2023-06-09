<?php

namespace Git;

use Admin\App;
use Service\Util\Fs;
use User\Auth;

class GitRepository
{
    /** @var string */
    private $repository;
    
    private $path;

    /** @var string|NULL @internal */
    private $cwd;
    
    
    private $remoteBranches = [];
    
    private $lastOutput = '';
    
    /**  @var Fs */
    private $fs;
    
    private $sshKeyPath = '';

    private $ignoreKnownHosts = true;
    
    /**
     * @param $repository
     *
     * @throws GitException
     */
    public function __construct($repository)
    {
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

        $userLogin = App::i()->auth->getUserLogin();
        if ($userLogin !== Auth::USER_ANONIM) {
            $this->setSshKeyPath(getcwd() . "/ssh_keys/{$userLogin}");
        }
    }
    
    public function getRepositoryPath(): string
    {
        return $this->repository;
    }
    
    /**
     * Creates a tag.
     * `git tag <name>`
     *
     * @throws GitException
     */
    public function createTag(string $name): self
    {
        return $this->begin()->run('git tag', $name)->end();
    }
    
    /**
     * Removes tag.
     * `git tag -d <name>`
     *
     * @throws GitException
     */
    public function removeTag(string $name): self
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
     * @throws GitException
     */
    public function renameTag(string $oldName, string $newName): self
    {
        return $this->begin()// http://stackoverflow.com/a/1873932
        // create new as alias to old (`git tag NEW OLD`)
        ->run('git tag', $newName, $oldName)// delete old (`git tag -d OLD`)
        ->removeTag($oldName)// WARN! removeTag() calls end() method!!!
        ->end();
    }

    /**
     * Returns list of tags in repo.
     *
     * @throws GitException
     * @return string[]|NULL  NULL => no tags
     */
    public function getTags(): ?array
    {
        return $this->extractFromCommand('git tag', 'trim');
    }
    
    /**
     * Merges branches.
     * `git merge <options> <name>`
     *
     * @throws GitException
     */
    public function merge(string $branch, ?array $options = null): self
    {
        return $this->begin()->run('git merge ', $options, $branch)->end();
    }
    
    /**
     * Creates new branch.
     * `git branch <name>`
     * (optionaly) `git checkout <name>`
     *
     * @throws GitException
     */
    public function createBranch(string $name, bool $checkout = false): self
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
     * @throws GitException
     */
    public function removeBranch(string $name): self
    {
        return $this->begin()->run('git branch', array(
            '-D' => $name,
        ))->end();
    }

    /**
     * Gets name of current branch
     * `git branch` + magic
     *
     * @throws GitException
     */
    public function getCurrentBranchName(): string
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
     *
     * @throws GitException
     * @return string[]|NULL  NULL => no branches
     */
    public function getBranches(): ?array
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
     *
     * @throws GitException
     * @return string[]|NULL  NULL => no branches
     */
    public function getRemoteBranches(): ?array
    {
        if (!$this->remoteBranches) {
            $this->remoteBranches = $this->extractFromCommand('git branch -r', function ($value) {
                return trim(str_replace('origin/', '', $value));
            });
        }
        
        return $this->remoteBranches;
    }

    /**
     * @param string $branch
     * @return false|string
     * @throws GitException
     */
    public function mergeRemoteIfHas(string $branch)
    {
        $this->getRemoteBranches();
        
        if (in_array($branch, $this->remoteBranches)) {
            $this->merge('origin/' . $branch, ['--no-ff', '--log=50', '--stat']);
            
            return $this->lastOutput;
        }
        
        return false;
    }
    
    public function getRemotesLastChangeTime(): array
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
     *
     * @throws GitException
     * @return string[]|NULL  NULL => no branches
     */
    public function getLocalBranches(): ?array
    {
        return $this->extractFromCommand('git branch', function ($value) {
            return trim(substr($value, 1));
        });
    }
    
    
    /**
     * Checkout branch.
     * `git checkout <branch>`
     *
     * @throws GitException
     */
    public function checkout(string $name): self
    {
        return $this->begin()->run('git checkout', $name)->end();
    }
    
    public function checkoutToNewBranch(string $name, bool $toBranch): self
    {
        return $this->begin()->run('git checkout ', $name, ['-b' => $toBranch])->end();
    }
    
    public function checkoutNewBranchFromDetached(string $name): self
    {
        return $this->begin()->run('git checkout -b ', $name)->end();
    }

    public function getLastTag(string $prefix = '') : ?string
    {
        foreach (array_reverse((array) $this->getTags()) as $tag) {
            if ($prefix && false === strpos($tag, $prefix)) {
                continue;
            }

            return (string) $tag;
        }

        return null;
    }
    
    /**
     * Checkout branch.
     * `git reset --hard && git clean -df`
     *
     * @throws GitException
     * @return self
     */
    public function fullReset(): self
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
    public function removeFile($file): self
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
    public function addFile($file): self
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
    public function renameFile($file, ?string $to = null): self
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
     * @throws GitException
     */
    public function commit(string $message, ?array $params = null): self
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
     */
    public function hasChanges(): bool
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
     * @return self
     */
    protected function begin(): self
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
    protected function end(): self
    {
        if (is_string($this->cwd)) {
//            chdir($this->cwd);
        }
        
        $this->cwd = null;
        
        return $this;
    }

    /**
     * Fetch and remove any remote-tracking references that no longer exist on the remote
     * `git fetch -p`
     * @throws GitException
     */
    public function fetch(): self
    {
        return $this->begin()->run('git fetch -p')->end();
    }
    
    /**
     * Pull changes from a remote
     *
     * @throws GitException
     */
    public function pull(?string $remote = null, ?array $params = null): self
    {
        if (!is_array($params)) {
            $params = array();
        }
        
        return $this->begin()->run("git pull $remote", $params)->end();
    }
    
    /**
     * Push changes to a remote
     *
     * @throws GitException
     */
    public function push(?string $remote = null, ?array $params = null): self
    {
        if (!is_array($params)) {
            $params = array();
        }
        
        return $this->begin()->run("git push $remote", $params)->end();
    }

    /**
     * @return NULL|\string[]
     * @throws GitException
     */
    protected function extractFromCommand(string $cmd, $filterCallback = null): ?array
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
        
        if (!isset($output[0])) { // empty array
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
        $args = func_get_args();
        $cmd  = $this->processCommand($args);
        $this->fs->exec($cmd, $output, $ret, __METHOD__);
        $this->lastOutput = $output;
        
        if ($ret !== 0) {
            $this->exception("Command '$cmd' failed on " . $this->repository , $output);
        }
        
        return $this;
    }

    protected function processCommand(array $args): string
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

        $sshParams = '';

        if ($this->sshKeyPath) {
            $sshParams .= ' -i '.$this->sshKeyPath.' ';
        }

        if ($this->ignoreKnownHosts) {
            $sshParams .= ' -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no ';
        }

        if ($sshParams) {
            $sshParams = ' GIT_SSH_COMMAND="ssh '.$sshParams.'" ';
        }

        return trim("{$sshParams} {$programName} " . implode(' ', $cmd));
    }

    /**
     * Clones GIT repository from $url into $directory
     *
     * @throws GitException
     */
    public function cloneLocalRepository(string $localPath, ?string $targetPath = null, bool $saveOriginalRemote = true): bool
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
    public static function extractRepositoryNameFromUrl(string $url): string
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
     */
    public static function isAbsolute($path): bool
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

    public function setPath(string $path)
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
    
    public function getLastOutput(): string
    {
        return $this->lastOutput;
    }
    
    public function diff(): string
    {
        $this->run('git diff');
        return $this->lastOutput;
    }
    
    public function getBehindStatus($branch)
    {
        $this->begin()->run('git rev-list --left-right --count origin/master...'.$branch)->end();
        return preg_split('/\s+/', implode('', $this->lastOutput));
    }
    
    public function getSshKeyPath(): string
    {
        return $this->sshKeyPath;
    }
    
    public function setSshKeyPath(?string $sshKeyPath)
    {
        $this->sshKeyPath = $sshKeyPath;
    }
}
