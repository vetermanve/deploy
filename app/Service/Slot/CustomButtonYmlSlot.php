<?php

namespace Service\Slot;

use Commands\Command\CommandProto;
use Commands\Command\Pack\CustomButton;

class CustomButtonYmlSlot extends YmlSlotProto
{
    public const STATE_HREF_EMPTY = 'Config field `href` is required and must contains a url-pattern for redirect';

    /**
     * @var string
     */
    public $href = '';


    public function validate()
    {
        $this->state = self::STATE_INIT;
        if (empty($this->href)) {
            $this->state = self::STATE_HREF_EMPTY;
        }

        return $this->state === self::STATE_INIT;
    }

    /**
     * @return \Commands\Command\CommandProto|null
     */
    public function createCommand() : ?CommandProto
    {
        return new CustomButton();
    }
}