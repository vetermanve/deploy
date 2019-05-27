<?php

namespace Commands\Command\Pack;

use Commands\Command\CommandProto;
use Service\Slot\YmlSlotProto;

abstract class YmlSlotCommandProto extends CommandProto
{
    public const DEFAULT_NAME = 'Настрой меня';

    /**
     * @return string
     */
    public function getHumanName()
    {
        $slot = $this->getSlot();
        if (!$slot) {
            return self::DEFAULT_NAME;
        }

        return $slot->text;
    }

    /**
     * @return bool
     */
    public function forkPage() : bool
    {
        $slot = $this->getSlot();
        if (!$slot) {
            return parent::forkPage();
        }

        return (bool) $slot->newPage;
    }

    /**
     * @return string
     */
    public function getHtmlClass() : string
    {
        $slot = $this->getSlot();
        if (!$slot) {
            return parent::getHtmlClass();
        }

        return (string) $slot->class;
    }

    /**
     * @return YmlSlotProto|\Service\Slot\SlotProto|null
     */
    public function getSlot() : ?YmlSlotProto
    {
        // only for evident typehint
        return parent::getSlot();
    }

}