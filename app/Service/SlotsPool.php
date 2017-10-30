<?php


namespace Service;


use Admin\App;
use Service\Slot\LocalSlot;
use Service\Slot\RemoteSlot;
use Service\Slot\SlotProto;

class SlotsPool
{
    const SLOT_TYPE_LOCAL  = 'local';
    const SLOT_TYPE_REMOTE = 'remote';
    
    /**
     * @var SlotProto[]
     */
    private $slots = [];
    private $projectId;
    
    public function addSlot (SlotProto $slot) 
    {
        $this->slots[$slot->getId()] = $slot;
    }
    
    public function loadProjectSlots () 
    {
        $slots = (new Data(App::DATA_SLOTS))->setReadFrom(__METHOD__)->readCachedFilter('projectId', $this->projectId);
        
        foreach ($slots as $id => $slotData) {
            $this->slots[$id] = SlotFactory::getSlotModel($slotData);
        }
        
        return $this;
    }
    
    public function validate () 
    {
        foreach ($this->slots as $slot) {
            $slot->validate();
        }
        
        return $this;
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
     * @return Slot\SlotProto[]
     */
    public function getSlots()
    {
        return $this->slots;
    }
}