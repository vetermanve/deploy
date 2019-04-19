<?php

namespace Service;

use Admin\App;
use Service\Slot\SlotProto;

class SlotsPool
{
    const SLOT_TYPE_LOCAL  = 'local';
    const SLOT_TYPE_REMOTE = 'remote';
    const SLOT_TYPE_TAG = 'tag';

    /**
     * @var SlotProto[]
     */
    private $slots = [];
    private $projectId;
    
    public function addSlot (SlotProto $slot) 
    {
        $this->slots[$slot->getId()] = $slot;
    }

    /**
     * @param string $dir
     * @param string $filename
     * @return $this
     * @throws \Exception\BuilderException
     */
    public function loadYmlSlots(string $dir, $filename = 'builder.yml')
    {
        $ymlSlots = SlotFactory::makeYmlSlots($dir, $filename);
        if (!empty($ymlSlots)) {
            $this->slots += $ymlSlots;
        }

        return $this;
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