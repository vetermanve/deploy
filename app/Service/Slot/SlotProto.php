<?php


namespace Service\Slot;


abstract class SlotProto
{
    protected $id;
    protected $name;
    protected $type;
    protected $host;
    protected $path;
    protected $projectId;
    
    protected $data = [];
    protected $state;
    
    private $isValid;
    
    public function init () 
    {
        foreach ($this->data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }
    
    public function isValid () 
    {
        if ($this->isValid === null) {
            $this->isValid = $this->validate();
        }
        
        return $this->isValid;
    }
    
    abstract public function validate();
    
    /**
     * @param $dirName
     *
     * @return bool
     */
    abstract public function ensureDir($dirName);
    
    /**
     * 
     * @param $cmd
     * @param $out
     * @param $result
     * @param $from
     *
     * @return mixed
     * @throws \Exception
     */
    abstract public function exec($cmd, &$out, &$result, $from);
    
    /**
     * @param $cmd
     * @param $from
     *
     * @return mixed
     */
    abstract public function silentExec($cmd, $from);
    
    /**
     * @param     $cmd
     * @param     $from
     * @param int $outLines
     *
     * @return [
            'result' => $result !== 0 ? "Fail" : "Success",
            'cmd' => $cmd,
            'out' => array_slice($out, 0, $outLines),
        ];
     * @throws \Exception
     */
    abstract public function stdExec($cmd, $from, $outLines = 10);
    
    /**
     * @param $targetPath
     * @param $from
     *
     * @return mixed
     */
    abstract public function rmLink($targetPath, $from);
    
    /**
     * @param $targetPath
     * @param $from
     *
     * @return mixed
     */
    abstract public function createLink($fromPath, $to, $from);
    
    /**
     * @param $localPath
     * @param $targetPath
     * @param $from
     *
     * @return mixed
     */
    abstract public function deliveryFile($localPath, $targetPath, $from);
    
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
     * @param mixed $data
     */
    public function setData(array $data)
    {
        
        $this->data = $data;
    }
    
    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }
    
    public function getDescription () 
    {
        return $this->name . ' '. $this->host . ':' . $this->path;
    }
    
    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
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
    
    /**
     * @return mixed
     */
    public function getProjectId()
    {
        return $this->projectId;
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
    public function getType()
    {
        return $this->type;
    }
    
    /**
     * @param mixed $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }
    
    /**
     * @return mixed
     */
    public function getHost()
    {
        return $this->host;
    }
    
    /**
     * @param mixed $host
     */
    public function setHost($host)
    {
        $this->host = $host;
    }
    
    /**
     * @return mixed
     */
    public function getState()
    {
        return $this->state;
    }
    
    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }
    
    
}