<?php

namespace Service\Slot;

use Commands\Command\CommandProto;
use Commands\Command\DeployFlow\DeployFlowInterface;
use Commands\Command\DeployFlow\TagDeployFlow;
use Commands\Command\SlotDeploy;

/**
 * Deploy via increment a minor version for tag which trigger a external CI/CD (ex. gitlab ci)
 * @package Service\Slot
 */
class TagYmlSlot extends YmlSlotProto
{
    public const RELEASE_MINOR     = 'minor';
    public const RELEASE_MAJOR     = 'major';
    public const RELEASE_PATCH     = 'patch';
    public const CALLBACK_TEXT_TPL = '%text%';

    protected const AVAILABLE_RELEASES = [
        self::RELEASE_MAJOR => true,
        self::RELEASE_MINOR => true,
        self::RELEASE_PATCH => true,
    ];

    public const STATE_NONAME         = 'slot name cannot be empty';
    public const STATE_NOTAG          = 'slot tag cannot be empty, fill regex pattern for release tag on your CI/CD (ex. "/^release.*$/")';
    public const STATE_NORELEASE      = 'slot release is invalid';
    public const STATE_WRONG_CALLBACK = 'slot callback is invalid';

    /**
     * Tag pattern in yml file
     * @var string|null
     */
    public $tag;

    /**
     * Major, minor or patch version increment-style
     * @var string
     */
    public $release;

    /**
     * @param string $callback
     * @param $text
     * @return mixed
     */
    public static function mutateCallback(string $callback = '', string $text = '')
    {
        return str_replace(self::CALLBACK_TEXT_TPL, $text, $callback);
    }

    public function validate()
    {
        $this->state = self::STATE_INIT;

        if (empty($this->getName())) {
            $this->state = self::STATE_NONAME;
        }
        if (empty($this->tag)) {
            $this->state = self::STATE_NOTAG;
        }
        if (!$this->release) {
            $this->release = self::RELEASE_PATCH;
        }
        if (!isset(self::AVAILABLE_RELEASES[$this->release])) {
            $this->state = self::STATE_NORELEASE;
        }

        $callback = $this->getCallback();
        if (!empty($callback) && self::mutateCallback($callback) === $callback) {
            $this->state = self::STATE_WRONG_CALLBACK;
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
     * @return \Commands\Command\CommandProto|null
     */
    public function createCommand() : ?CommandProto
    {
        return new SlotDeploy();
    }
}