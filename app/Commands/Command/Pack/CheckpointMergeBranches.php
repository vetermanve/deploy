<?php


namespace Commands\Command\Pack;


use Commands\Command\CommandProto;
use Commands\CommandConfig;
use Git\GitRepository;

class CheckpointMergeBranches extends CommandProto
{
    public function prepare()
    {
        
    }
    
    public function run()
    {
        $pack           = $this->context->getPack();
        $checkpointName = $this->context->getCheckpoint()->getName();
        $branches       = $pack->getBranches();
        
        array_unshift($branches, 'master'); // всегда подтягиваем последний мастер
        
        foreach ($pack->getRepos() as $id => $repo) {
            $repo->fetch();
            $repo->fullReset();
            $repo->checkout($checkpointName);
            $this->_mergeBranches($repo, $branches);
        }
        
        return $this->runtime;
    }
    
    /**
     * @param GitRepository $repo
     * @param               $branches
     * @param int           $loop
     *
     * @return array|mixed
     */
    private function _mergeBranches($repo, $branches, $loop = 1)
    {
        $unmerged    = [];
        $results     = [];
        $mergedCount = 0;
        
        foreach ($branches as $branch) {
            try {
                $result = $repo->mergeRemoteIfHas($branch);
                if ($result !== false) {
                    $results[$branch] = $result;
                    $mergedCount++;
                }
            } catch (\Exception $e) {
                $results[$branch] = 'Error: ' . $e->getMessage();
                $this->runtime->exception($e);
                $repo->fullReset();
                $unmerged[] = $branch;
            }
        }
        
        $this->runtime->log($results, $repo->getPath());
        
        $mergedCount && $loop < 5 && $this->_mergeBranches($repo, $unmerged, ++$loop);
    }
    
    public function getId()
    {
        return CommandConfig::CHECKPOINT_MERGE_BRANCHES;
    }
    
    public function getHumanName()
    {
        return 'Запустить мерж веток';
    }
    
    public function isPrimary()
    {
        return true;
    }
}