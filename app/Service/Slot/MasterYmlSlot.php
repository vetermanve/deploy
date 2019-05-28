<?php

namespace Service\Slot;

class MasterYmlSlot extends YmlSlotProto
{
    /**
     * Deploy engineer mode enabled: only creator can add branches and deploy pack
     * @var bool
     */
    public $deployEngineerEnabled = false;

    public function validate()
    {
        return true;
    }
}