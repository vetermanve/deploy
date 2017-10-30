<?php


namespace Interaction\Api\Controller;


use Admin\App;
use Commands\Command\MakeBuildForDownload;
use Commands\CommandConfig;
use Commands\CommandRunner;
use Service\Data;
use Service\Pack;
use Service\Project;

class LastbuildController extends ApiProto
{
    public function index () 
    {
        $this->response($this->_getProjectsByName());
    }
    
    public function dev () 
    {
        $projectName = $this->p('p', '');
        $projects = $this->_getProjectsByName();
        if(!isset($projects[$projectName])) {
            return $this->error('no project '.$projectName.' found');
        }
        
        $projectId = $projects[$projectName];
        
        $project = new Project($projectId);
        $project->init();
        $project->initPacks();
        
        $packs = $project->getPacks();
    
        $pack = null;
        $packId = null;
        foreach ($packs as $packItem) {
            if (strpos($packItem->getName(), 'dev_') === 0) {
                $pack = $packItem;
                break;
            }
        }
        
        if (!$pack) {
            return $this->error('no dev packs found');
        }
        
        $pack->loadCheckpoints();
        
        $checkpoint = $pack->getLastCheckPoint();
        
        if (!$checkpoint) {
            return $this->error('no checkpoints in pack'); 
        }
        
        $cpName = $pack->getLastCheckPoint()->getName();
        
        $command = new MakeBuildForDownload();
        $context = $command->getContext();
        $context->setPack($pack);
        $context->setCheckpoint($checkpoint);
        
        $runner = new CommandRunner();
        $runner->setContext($context);
        $runner->runCommand($command);
        
        $logs = $runner->getRuntime()->getLogs();
        
        $buildPlacement = @$logs[CommandConfig::SOURCES_BUILD_ARCHIVE_CREATE]['name'];
        
        $response = [
            'project_id' => $projectId,
            'pack_id' => $packId,
            'pack' => $packs[$packId],
            'checkpoint' => $cpName,
            'target' => $buildPlacement,
            'success' => (bool)$buildPlacement,
        ];
        
        if ($this->p('logs')) {
            $response['logs'] = $logs;
        }
        
        $this->response($response);
    }
    
    private function _getProjectsByName () {
        $projects = new Data(App::DATA_PROJECTS);
        $d = $projects->read();
    
        $res = [];
        foreach ($d as $key => $folders) {
            $res[str_replace('/','', implode('', $folders))] = $key;
        }
        
        return $res;
    }
    
}