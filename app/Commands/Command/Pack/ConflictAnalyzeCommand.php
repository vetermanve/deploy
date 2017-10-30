<?php


namespace Commands\Command\Pack;


use Commands\Command\CommandProto;
use Commands\CommandConfig;
use Git\GitRepository;
use Git\GitException;

class ConflictAnalyzeCommand extends CommandProto
{
    private $knownPairs = [];
    private $troubles = 0;
    
    public function prepare()
    {
        foreach ($this->context->getPack()->getRepos() as $id => $repo) {
            $repo->fetch();
        }
    }
    
    public function run()
    {
        $sandbox = $this->context->getPack();
        $branches   = $sandbox->getBranches();
        
        foreach ($sandbox->getRepos() as $id => $repo) {
            $this->runtime->startSection($id, $repo->getPath());
            $conflict = $this->_findConflictBranches($repo, $branches);
            
            if ($conflict) {
                $testBranches = $branches;
                array_unshift($testBranches, 'master');
    
                foreach ($conflict as $conflictBranch) {
                    $this->_findConflictPairs($repo, $conflictBranch, $testBranches);
                }
    
                $this->runtime->log($conflict, 'CONFLICTED');
            }
        }
    }
    
    /**
     * @param GitRepository    $repo
     * @param     $branches
     * @param int $loop
     *
     * @return array|mixed
     */
    private function _findConflictBranches($repo, $branches) {
        $mergeTestBranch = 'merge-test-'.date('Y.m.d\a\tH-i-s');
        $repoPath = $repo->getPath();
        
        $repo->fullReset();
        $repo->checkout('master');
        $repo->checkoutToNewBranch('origin/master', $mergeTestBranch);
        
        $conflict = [];
        $results = [];
        
        foreach ($branches as $branch) {
            try {
                $result = $repo->mergeRemoteIfHas($branch);
                if ($result !== false) {
                    $results[$branch] = ['ok'];
                }
            } catch (GitException $e) {
                $repo->fullReset();
                $conflict[] = $branch;
//                $this->results[$repo->getPath()][1][$branch] = 'Conflicted: '.implode("\n", $e->getOutput());
            }
        }
    
        $this->runtime->log($results, $repoPath);
    
        $repo->checkout('master');
        $repo->removeBranch($mergeTestBranch);
        
        return $conflict;
    }
    
    /**
     * @param $repo GitRepository
     * @param $conflictBranch
     * @param $testBranches
     *
     * @return array
     */
    private function _findConflictPairs($repo, $conflictBranch, $testBranches)
    {
        $repo->fullReset();
        $troubles = [];    
        foreach ($testBranches as $testBranch) {
            if ($testBranch === $conflictBranch) {
                continue;
            }
            $repo->checkout('master');
            
            $mergeTestBranch = 'merge-test-find-'.$conflictBranch.microtime(1);
            $repo->checkoutToNewBranch('origin/'.$conflictBranch, $mergeTestBranch);
            
            try {
                $result = $repo->mergeRemoteIfHas($testBranch);
                if ($result !== false) {
//                    $this->results[$repo->getPath()][$conflictBranch.' TO '.$testBranch] = ['ok'];
                }
            } catch (GitException $e) {
                $this->knownPairs[$testBranch][$conflictBranch] = 1;
                $this->knownPairs[$conflictBranch][$testBranch] = 1;
                $this->troubles++;
    
                $troubles['#'.$this->troubles] = [
                    'TROUBLE' => $conflictBranch.' TO '.$testBranch,
                    'MERGE_BRANCH' => 'merge-'.date('md').'-'.$conflictBranch.'-to-'.$testBranch,
                    'DESC' => $e->getOutput(),
                    'DIFF' => $repo->diff(),
                ];
                $repo->fullReset();
            }
            
            $repo->checkout('master');
            $repo->removeBranch($mergeTestBranch);
        }
        
        $this->runtime->log($troubles, $repo->getPath());
    }
    
    public function getId()
    {
        return CommandConfig::PACK_CONFLICT_ANALYZE;
    }
    
    public function getHumanName()
    {
        return 'Найти конфликтующие ветки';
    }
    
}