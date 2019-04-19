<?php

namespace Commands\Command\DeployFlow;

interface DeployFlowInterface
{
    /**
     * @return \Commands\Command\CommandProto[]
     */
    public function getDeployFlow () :array;
}