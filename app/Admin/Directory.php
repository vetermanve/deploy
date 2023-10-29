<?php

namespace Admin;

use Git\GitRepository;
use Service\Data;
use Service\Util\StringHelper;

class Directory
{
    
    protected $sitesDir = '/var/www/';
    
    protected $deployUser = null;
    protected $wwwUser    = null;
    
    function __construct()
    {
        $this->deployUser = 'deploy';
        $this->wwwUser    = 'www-data';
        $this->sitesDir   = REPOS_DIR . '/';
    }
    
    /**
     * @return string
     */
    public function getSitesDir()
    {
        return $this->sitesDir;
    }
    
    /**
     * @param string $sitesDir
     */
    public function setSitesDir($sitesDir)
    {
        $this->sitesDir = $sitesDir;
    }
    
    public function scanDir()
    {
        $data = $this->doScanDir($this->sitesDir);
        
        $exclude = (new Data('deploy_exclude'))->setReadFrom(__METHOD__)->read();
        
        foreach ($data as $name => &$item) {
            $keys = array_flip(explode('/', $name));
            if (array_intersect_key($exclude, $keys)) {
                unset($data[$name]);
            } else {
                $time = $this->getUpdateTime($name);
                $item += [
                    'branch' => $this->getBranch($name),
                    'time'   => $time,
                    'idx'    => $time['timestamp'],
                    'com'    => $this->getLastCommit($name),
                    'remote' => $this->getRemotes($name),
                ];
            }
        }
        
        $keys = array_keys($data);
        $idx  = array_column($data, 'idx');
        array_multisort($idx, SORT_DESC, $data, $keys);
        $data = array_combine($keys, $data);
        
        return $data;
    }
    
    public function doScanDir($dir, $deep = 1, $prefix = '')
    {
        $dir = rtrim($dir, '/');
        $prefix = $prefix ? $prefix . '/' : '';
        $data   = [];
        if ($handle = @opendir($dir)) {
            while (false !== ($entry = readdir($handle))) {
                $file = $dir . '/' . $entry;
                if ($entry != "." && $entry != ".." && is_dir($file)) {
                    $localName = $prefix . '' . $entry;
                    if (file_exists($file . '/.git')) {
                        
                        $data[$localName] = [
                            'file' => $file,
                        ];
                    }
                    
                    if ($deep < 3) {
                        $data += (array)$this->doScanDir($file, $deep + 1, $localName);
                    }
                }
            }
            closedir($handle);
        }

        return $data;
    }
    
    public function allData()
    {
        
        return $this->scanDir();
    }
    
    public function checkDir($dir)
    {
        return is_dir($this->sitesDir . $dir);
    }
    
    /**
     * @param $dir
     *
     * @return mixed
     */
    public function getBranch($dir)
    {
        if (!$this->checkDir($dir)) {
            return 'not a dir';
        }
        
        exec('cd ' . $this->sitesDir . $dir . ' && git branch -a', $result);
        
        return $result;
    }
    
    public function getLastCommit($dir)
    {
        exec('cd ' . $this->sitesDir . $dir . ' && git log -5 --pretty="%cn %B" ', $result);
        
        return array_filter($result);
    }
    
    public function getRemotes($dir)
    {
        exec('cd ' . $this->sitesDir . $dir . ' && git remote -v ', $result);
        
        return array_filter($result);
    }
    
    public function getCurrentBranch($dir)
    {
        $branches = $this->getBranch($dir);
        
        foreach ($branches as $branch) {
            if (strpos($branch, '*') === 0) {
                return trim($branch, ' *');
            }
        }
        
        return 'unknown';
    }
    
    public function getUpdateTime($dir)
    {
        $time = filemtime($this->sitesDir . $dir);
        
        $result = [
            'back'      => StringHelper::howMuchAgo($time),
            'date'      => date('d-m h:i', $time),
            'timestamp' => $time,
        ];
        
        return $result;
    }
    
    public function update($dir): string
    {
        if (!$this->checkDir($dir)) {
            return 'not a dir';
        }
        
        $branch = $this->getCurrentBranch($dir);
        $result = [];

//            if ($this->deployUser) {
//                exec('sudo /bin/chown -R ' . $this->deployUser . ':' . $this->wwwUser . ' ' . $this->sitesDir . $dir . ' 2>&1',
//                    $result);
//            }

        $repo = new GitRepository($this->sitesDir . $dir);

        return $repo->update($branch);
    }
    
    public function fix($dir, $realClear = null)
    {
        if (!$this->checkDir($dir)) {
            return 'not a dir: ' . $this->sitesDir . $dir;
        }
        
        $result[] = '$realClear:' . json_encode($realClear);
        
        $result[] = shell_exec('cd ' . $this->sitesDir . $dir . ' && git reset --hard FETCH_HEAD 2>&1');
        
        if (!$realClear) {
            exec('cd ' . $this->sitesDir . $dir . ' && git clean -dn ', $result);
        } else {
            exec('cd ' . $this->sitesDir . $dir . ' && git clean -df ', $result);
        }
        
        return implode("\n", $result);
    }
    
    public function checkout($dir, $branch): string
    {
        if (!$this->checkDir($dir)) {
            return 'not a dir';
        }
        
        if (!$branch) {
            return 'branch not passed';
        }
        
        if (strpos($branch, '/')) {
            $array = explode('/', $branch);
            $branch = end($array) . ' ' . $branch;
        }

        $repo = new GitRepository($this->sitesDir . $dir);

        $result[] = $repo->fetch()->getLastOutput();
        $result[] = $repo->checkoutBranchOrResetAndCheckout($branch)->getLastOutput();

        return implode("\n", $result);
    }
    
    public function createRepo($name)
    {
        var_dump($name);exit;
        $repoDir  = 'repo/';
        $commands = array(
            ['cd ' . $this->sitesDir . $repoDir, 'mkdir ' . $this->sitesDir . $repoDir],
            ['pwd ', ''],
            ['mkdir ' . $name, 'rmdir ' . $name],
            ['cd ' . $this->sitesDir . $repoDir . $name, ''],
            ['pwd ', ''],
            ['git init --bare', ''],
            ['chmod -R g+rw ./', ''],
            ['cd ' . $this->sitesDir, ''],
            ['pwd ', ''],
            ['git clone ' . $this->sitesDir . $repoDir . $name, ''],
        );
        
        $result = array();
        $state  = 0;

//            $try = exec(implode(' && '))
        $dir = 'cd ' . $this->sitesDir;
        
        $log = function ($c, $data) use (&$result) {
            $result[] = array(
                'com' => $c,
                'res' => $data,
            );
        };
        
        foreach ($commands as $cData) {
            list ($command, $pill) = $cData;
            if ($state) {
                break;
            }
            if (substr($command, 0, 3) == 'cd ') {
                $dir = $command;
            }
            
            $eCommand = $dir . ' && ' . $command . ' 2>&1';
            unset ($res);
            exec($eCommand, $res, $state);
            $log($command, !$state ? ($res ? implode('<br>', $res) : 'ok') : 'fail: ' . implode('<br>', $res));
            
            if ($state && $pill) {
                unset ($res);
                exec($dir . ' && ' . $pill, $res, $state);
                $log($command . ' pill => ' . $pill,
                    !$state ? ($res ? implode('<br>', $res) : 'ok') : 'fail: ' . implode('<br>', $res));
                unset ($res);
                exec($eCommand, $res, $state);
                $log($command . ' recall',
                    !$state ? ($res ? implode('<br>', $res) : 'ok') : 'fail: ' . implode('<br>', $res));
            }
        }
        
        return $result;
    }

    public function cloneRepository(string $repositoryPath, string $dirName)
    {
        if (file_exists($this->sitesDir . $dirName)) {
            $dirName .= date('Ymd_His');
        }

        $newRepoDir = $this->sitesDir . $dirName;
        $commands = array(
            ['mkdir ' . $newRepoDir, ''],
//            ['cd ' . $newRepoDir, ''],
//            ['pwd ', ''],
//            ['git init --bare', ''],
//            ['cd ..', ''],
            ['pwd ', ''],
//            ['chmod -R g+rw ./', ''],
//            ["git clone '{$repositoryPath}' '{$dirName}'", '' ],
//            ['cd ' . $this->sitesDir, ''],
//            ['pwd ', ''],
//            ['git clone ' . $this->sitesDir . $repoDir . $name, ''],
        );

        $result = [];
        $state  = 0;

//            $try = exec(implode(' && '))
        $dir = 'cd ' . $this->sitesDir;

        $log = function ($c, $data) use (&$result) {
            $result[] = array(
                'com' => $c,
                'res' => $data,
            );
        };

        foreach ($commands as $cData) {
            list ($command, $pill) = $cData;
            if ($state) {
                break;
            }
            if (substr($command, 0, 3) == 'cd ') {
                $dir = $command;
            }

            $eCommand = $dir . ' && ' . $command . ' 2>&1';
            unset ($res);
            exec($eCommand, $res, $state);
            $log($command, !$state ? ($res ? implode('<br>', $res) : 'ok') : 'fail: ' . implode('<br>', $res));

            if ($state && $pill) {
                unset ($res);
                exec($dir . ' && ' . $pill, $res, $state);
                $log($command . ' pill => ' . $pill,
                    !$state ? ($res ? implode('<br>', $res) : 'ok') : 'fail: ' . implode('<br>', $res));
                unset ($res);
                exec($eCommand, $res, $state);
                $log($command . ' recall',
                    !$state ? ($res ? implode('<br>', $res) : 'ok') : 'fail: ' . implode('<br>', $res));
            }
        }

        $repo = new GitRepository($this->sitesDir);
        $repo->cloneRemoteRepository($repositoryPath, $dirName);

        $result[] = $repo->getLastOutput();

var_dump($result);
        return $result;
    }
}
