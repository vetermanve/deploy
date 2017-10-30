<?php


namespace Commands;

use Commands\Command\CommandProto;
use Commands\Command\EmptyCommand;

class CommandConfig
{
    /* combo commands */
    const BUILD_AND_DEPLOY           = 'LocalDeploy';
    const BUILD_FOR_DOWNLOAD         = 'BuildForDownload';
    const BUILD_AND_ALL_DEPLOY       = 'SlotDeploy';
    
    /* local deploy features */
    const DEPLOY_LOCAL               = 'Install\\DeployBuildLocal';
    const SUPERVISOR_RESTART         = 'Install\\SupervisorRestart';
    
    /* deploy features */
    const BUILD_DIRECTORY            = 'Build\\BuildReleaseByDirectories';
    const BUILD_RUN_COMPILE          = 'Build\\RunCompile';
    const BUILD_RUN_SETUP            = 'Install\\RunSetupScripts';
    const BUILD_RUN_START            = 'Install\\RunStartScripts';
    
    /* pack commands */
    const PACK_CONFLICT_ANALYZE      = 'Pack\\ConflictAnalyzeCommand';
    const PACK_FETCH_PROJECT         = 'Pack\\FetchSandbox';
    const PACK_CLEAR_DATA            = 'Pack\\RemovePackWithData';
    
    /* checkpoint commands */
    const CHECKPOINT_CREATE              = 'Pack\\CheckpointCreateCommand';
    const CHECKPOINT_MERGE_BRANCHES      = 'Pack\\CheckpointMergeBranches';
    const CHECKPOINT_MERGE_TO_MASTER     = 'Pack\\GitMergeToMaster';
    const CHECKPOINT_PUSH_TO_ORIGIN      = 'Pack\\GitPushCheckpoint';
    const CHECKPOINT_DELETE              = 'Pack\\RemoveCheckpoint';
    const SOURCES_BUILD_ARCHIVE_CREATE   = 'Deploy\\SourcesBuildArchiveCreate';
    const SOURCES_BUILD_ARCHIVE_DELIVERY = 'Deploy\\SourceBuildArchiveDelivery';
    const SOURCES_BUILD_TARGET_UNARCHIVE = 'Deploy\\SourceTargetUnarchive';
    
    const PROJECT_FETCH_REPOS         = 'Project\\FetchProjectRepos';
    
    /* vars */
    const GLOBAL_WORK_DIR = 'workDir';
    
    
    /**
     * @param $commandId
     *
     * @return CommandProto
     */
    public static function getCommand($commandId)
    {
        $class = '\\Commands\\Command\\'.$commandId;
        
        if (class_exists($class)) {
            return new $class; 
        }
        
        return (new EmptyCommand())->setCommandName($commandId);
    }
    
    public static function getDeployCommands()
    {
        
    }
}