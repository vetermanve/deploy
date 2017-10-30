<?php


namespace Service;


use Admin\App;

class Data
{
    const DEFAULT_DATA_DIR         = 'data';
    const DEFAULT_DELETED_DATA_DIR = 'data_deleted';
    
    const MASTER_FILE = 'master.json';
    
    protected static $dataDirChecked = [];
    protected static $scopes         = [];
    
    protected $scope    = 'test';
    protected $workDir  = '';
    protected $modifier = 'system';
    protected $data     = [];
    protected $string   = '';
    protected $items    = [];
    protected $dataDir  = self::DEFAULT_DATA_DIR;
    
    private $readFrom = '';
    
    protected static $cache = [];
    
    public function getScopes($dataDir = self::DEFAULT_DATA_DIR, $reload = false)
    {
        if (!self::$scopes || $reload) {
            $this->checkDataDir();
            self::$scopes = scandir($dataDir);
            
            foreach (self::$scopes as $k => $dir) {
                if ($dir === '.' || $dir === '..' || !is_dir($dataDir . '/' . $dir)) {
                    unset(self::$scopes[$k]);
                }
            }
        }
        
        return self::$scopes;
    }
    
    
    public function __construct($scope, $dataDir = self::DEFAULT_DATA_DIR, $autoCreate = true)
    {
        self::checkDataDir();
        $this->scope = $scope;
        $this->dataDir = $dataDir;
        $this->initScope($autoCreate);
    }
    
    public function initScope($autoCreate = true)
    {
        $this->workDir = $this->getDir($this->scope, $autoCreate);
    }
    
    private function checkDataDir()
    {
        if (!isset(self::$dataDirChecked[$this->dataDir]) && !file_exists($this->dataDir)) {
            mkdir($this->dataDir, 0777, true);
            chmod($this->dataDir, 0777);
            self::$dataDirChecked[$this->dataDir] = true;
        }
    }
    
    public function lock()
    {
        
    }
    
    public function unlock()
    {
        
    }
    
    public function write($writeVersion = true)
    {
        $this->string = json_encode($this->data, JSON_PRETTY_PRINT | JSON_PARTIAL_OUTPUT_ON_ERROR | JSON_UNESCAPED_UNICODE);
        
        if ($writeVersion) {
            $fileName     = $this->getVersionFileName();
            $file         = $this->getFile($fileName);
            $start = microtime(1);
            file_put_contents($file, $this->string, FILE_TEXT);
            App::i()->log('Writing file: '.$file, __METHOD__, $start);
            unset(self::$cache[$this->workDir][$fileName]); //clear cache
        }
        
        return $this->commit();
    }
    
    public function readCached ($fileName = self::MASTER_FILE) 
    {
        if (!isset(self::$cache[$this->workDir][$fileName])) {
            self::$cache[$this->workDir][$fileName] = $this->read($fileName);
        }
        
        return $this->data = self::$cache[$this->workDir][$fileName];
    }
    
    public function readCachedId ($id, $default = null) 
    {
        $this->readCached();
        return isset($this->data[$id]) ? $this->data[$id] : $default;
    }
    
    public function readCachedIdAndWriteDefault ($id, $default = '')
    {
        $this->readCached();
        
        if (isset($this->data[$id])) {
            return $this->data[$id]; 
        }
        
        $this->read();
        $this->data[$id] = $default;
        $this->write();
        
        return $default;
    }
    
    public function readCachedFilter ($key, $value)
    {
        $this->readCached();
        $result = [];
        foreach ($this->data as $id => $data) {
            if(is_array($data) && isset($data[$key]) && $data[$key] == $value) {
                $result[$id] = $data;  
            }      
        }
        
        return $result;
    }
    
    public function read($fileName = self::MASTER_FILE)
    {
        $file = $this->getFile($fileName);
        
        $start = microtime(1);
        $this->string = file_exists($file) ? file_get_contents($file) : '{}';
        $this->data   = json_decode($this->string, 1);
        App::i()->log('Reading file: '.$file, $this->readFrom ? $this->readFrom : __METHOD__, $start);
        
        if (!is_array($this->data)) {
            $this->data = [];
        }
        
        return $this->data;
    }
    
    private function commit()
    {
        $file = $this->getFile(self::MASTER_FILE);
        unset(self::$cache[$this->workDir][self::MASTER_FILE]); //clear cache
        return file_put_contents($file, $this->string, FILE_TEXT) !== false;
    }
    
    public function getFile($file)
    {
        $name = $this->workDir . '/' . $file;
        
        if (!is_writeable($this->workDir)) {
            throw new \Exception('Target file not writable: ' . $name . ' by user ' . shell_exec('whoami'));
        }
        
        return $name;
    }
    
    public function getDir($dirName, $authCreate = true, $dataDir = self::DEFAULT_DATA_DIR)
    {
        $dir = $dataDir . '/' . $dirName;
        
        if ($authCreate && !file_exists($dir)) {
            mkdir($dir, 0777, true);
            chmod($dir, 0777);
            
            if (!file_exists($dir)) {
                throw new \Exception('Cannot create dir: ' . $dir);
            }
        }
        
        return $dir;
    }
    
    public function getVersionFileName()
    {
        return date('H.i.s_d-m-Y') . '_' . $this->modifier . '.json';
    }
    
    
    public function rename($name)
    {
        $newDirName = $this->getDir($name, false);
        try {
            rename($this->workDir, $newDirName);
            $this->scope   = $name;
            $this->workDir = $newDirName;
            $this->initScope();
            
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
    
    /**
     * @param string $modifier
     */
    public function setModifier($modifier)
    {
        $this->modifier = $modifier;
    }
    
    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }
    
    /**
     * @param array $data
     *
     * @return $this
     */
    public function setData($data)
    {
        $this->data = $data;
        foreach ($this->data as $key => $value) {
            if(!$key) {
                unset($this->data[$key]);
            }
        }
        
        return $this;
    }
    
    /**
     * @return array
     */
    public function getItems()
    {
        return $this->items;
    }
    
    public function getName()
    {
        return $this->scope;
    }
    
    public function isExist()
    {
        return file_exists($this->workDir);
    }
    
    public function remove()
    {
        $newDirName = $this->getDir($this->scope, true, self::DEFAULT_DELETED_DATA_DIR);
        
        try {
            rename($this->workDir, $newDirName);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
    
    /**
     * @param string $readFrom
     *
     * @return $this
     */
    public function setReadFrom($readFrom)
    {
        $this->readFrom = $readFrom;
        return $this;
    }
}