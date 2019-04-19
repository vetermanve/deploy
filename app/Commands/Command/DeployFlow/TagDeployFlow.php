<?php

namespace Commands\Command\DeployFlow;

use Commands\Command\Pack\GitIncreaseVersionByTag;
use Commands\Command\Pack\GitPushCheckpoint;

class TagDeployFlow implements DeployFlowInterface
{
    /**
     * @return array
     */
    public function getDeployFlow() : array
    {
        return [
            new GitPushCheckpoint,
            new GitIncreaseVersionByTag,
        ];
    }
}