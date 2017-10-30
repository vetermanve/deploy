<?php


namespace Commands;


use Commands\Command\Build\BuildReleaseByDirectories;
use Commands\Command\Build\RunCompile;
use Commands\Command\CommandProto;
use Commands\Command\Delivery\LinkSources;
use Commands\Command\Delivery\SourceBuildArchiveDelivery;
use Commands\Command\Delivery\SourcesBuildArchiveCreate;
use Commands\Command\Delivery\SourceTargetUnarchive;
use Commands\Command\Install\RunSetupScripts;
use Commands\Command\Install\RunStartScripts;
use Commands\Command\Install\SupervisorRestart;

class CommandFlow
{
    /**
     * @return CommandProto[]
     */
    public function getDeployFlow () 
    {
        return [
            new BuildReleaseByDirectories(),
            new RunCompile(),
            new SourcesBuildArchiveCreate(),
            new SourceBuildArchiveDelivery(),
            new SourceTargetUnarchive(),
            new LinkSources(),
            new RunSetupScripts(),
            new RunStartScripts(),
            new SupervisorRestart(),
        ];
    }
}