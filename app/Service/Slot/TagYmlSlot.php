<?php

namespace Service\Slot;

use Commands\Command\DeployFlow\DeployFlowInterface;
use Commands\Command\DeployFlow\TagDeployFlow;

/**
 * Deploy via increment a minor version for tag which trigger a external CI/CD (ex. gitlab ci)
 * @package Service\Slot
 */
class TagYmlSlot extends YmlSlotProto
{
    public const STATE_INIT   = 'init';
    public const STATE_NONAME = 'slot name cannot be empty';
    public const STATE_NOTAG  = 'slot tag cannot be empty, fill regex pattern for release tag on your CI/CD (ex. "/^release.*$/")';

    protected $tag;

    public function validate()
    {
        $this->state = self::STATE_INIT;

        if (empty($this->getName())) {
            $this->state = self::STATE_NONAME;
        }
        if (empty($this->getTag())) {
            $this->state = self::STATE_NOTAG;
        }

        return $this->state === self::STATE_INIT;
    }

    /**
     * @return \Commands\Command\DeployFlow\DeployFlowInterface
     */
    public function getDeployCommandFlow() : DeployFlowInterface
    {
        return new TagDeployFlow();
    }

    /**
     * @return mixed
     */
    public function getTag()
    {
        return $this->tag;
    }
}