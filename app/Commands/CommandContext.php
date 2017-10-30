<?php


namespace Commands;


use Service\Checkpoint;
use Service\Pack;
use Service\Project;
use Service\Slot\SlotProto;
use Service\SlotFactory;
use Service\SlotsPool;

class CommandContext
{
    const DATA_CHECKPOINT = 'checkpoint';
    const DATA_SLOT = 'slot';
    const DATA_PACK = 'pack';
    const DATA_PROJECT = 'project';
    
    /**
     * @var Checkpoint
     */
    protected $checkpoint;
    
    /**
     * @var Pack
     */
    protected $pack;
    
    /**
     * @var SlotProto
     */
    protected $slot;
    
    /**
     * @var Project
     */
    protected $project;
    
    private $data = [];
    
    public function get ($key, $default = null)
    {
        return isset($this->data[$key]) ? $this->data[$key] : $default;
    }
    
    public function set ($key, $value)
    {
        $this->data[$key] = $value;
    }
    
    public function serialize()
    {
        
        return base64_encode(json_encode($this->getState()));
    }
    
    public function getState()
    {
        $data = [];
        
        if ($this->pack) {
            $data[self::DATA_PACK] = $this->pack->getId();
        }
        
        if ($this->checkpoint) {
            $data[self::DATA_CHECKPOINT] = $this->checkpoint->getId();
        }
        
        if ($this->slot) {
            $data[self::DATA_SLOT] = $this->slot->getId();
        }
        
        if ($this->project) {
            $data[self::DATA_PROJECT] = $this->project->getId();
        } elseif($this->pack) {
            $data[self::DATA_PROJECT] = $this->pack->getProject()->getId();
        }
        
        return $data;
    }
    
    public function deserialize($string)
    {
        $data = json_decode(base64_decode($string), 1);
        $this->bind($data);
    }
    
    public function bind($data)
    {
        if (isset($data[self::DATA_PACK])) {
            $this->pack = new Pack();
            $this->pack->setId($data[self::DATA_PACK]);
            $this->pack->init();
        }
        
        if (isset($data[self::DATA_CHECKPOINT]) && $this->pack) {
            $this->checkpoint = new Checkpoint($this->pack, $data[self::DATA_CHECKPOINT]);
            $this->checkpoint->setCommands($this->pack->getCheckpointCommands()); // todo remove
        }
        
        if (isset($data[self::DATA_SLOT])) {
            $this->slot = SlotFactory::getSlot($data[self::DATA_SLOT]);
        }
        
        if (isset($data[self::DATA_PROJECT])) {
            $this->project = new Project($data[self::DATA_PROJECT]);
            $this->project->init();
        }
    }
    
    
    /**
     * @return Pack
     */
    public function getPack()
    {
        return $this->pack;
    }
    
    /**
     * @param Pack $pack
     */
    public function setPack($pack)
    {
        $this->pack = $pack;
    }
    
    /**
     * @return Checkpoint
     */
    public function getCheckpoint()
    {
        return $this->checkpoint;
    }
    
    /**
     * @param Checkpoint $checkpoint
     */
    public function setCheckpoint($checkpoint)
    {
        $this->checkpoint = $checkpoint;
    }
    
    /**
     * @return SlotProto
     */
    public function getSlot()
    {
        return $this->slot;
    }
    
    /**
     * @param SlotProto $slot
     *
     * @return $this
     */
    public function setSlot($slot)
    {
        $this->slot = $slot;
        
        return $this;
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
     *
     * @return $this
     */
    public function setProject($project)
    {
        $this->project = $project;
        return $this;
    }
    
}