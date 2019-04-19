<?php

namespace Commands\Command\DeployFlow;

use Commands\Command\Pack\GitIncreaseVersionByTag;

class TagDeployFlow implements DeployFlowInterface
{
    /**
     * @return array
     */
    public function getDeployFlow() : array
    {
        return [
            new GitIncreaseVersionByTag,
        ];
    }
}