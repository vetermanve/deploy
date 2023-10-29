<?php

namespace Interaction\Web\Controller;

use Interaction\Base\Controller\ControllerProto;

class Deploy extends AuthControllerProto
{

    public function indexAction()
    {
        $this->app->view()->setHeader(__('deploy'));
        
        $this->app->render('deploy/index', array(
            'list' => $this->app->directory()->allData(),
        ));
    }
    
    public function getgitAction()
    {
        $dir = $this->app->request->get('dir');
        
        $this->app->json(array('data' => $this->app->directory()->getBranch($dir),));
    }
    
    public function fixgitAction()
    {
        $dir = $this->app->request->get('dir');
        
        $this->app->json(array(
            'data' => $this->app->directory()
                ->fix($dir, $this->app->request->get('doClean', false))
        ));
    }
    
    public function checkoutAction()
    {
        $dir = $this->app->request->get('dir');
        $branch = $this->app->request()->get('branch', '');
        
        $this->app->json(array('data' => $this->app->directory()->checkout($dir, $branch)));
    }
    
    public function updateAction()
    {
        $dir = $this->app->request->get('dir');
        
        $this->app->json(array('data' => $this->app->directory()->update($dir),));
    }

    public function showAddRepositoryFormAction()
    {
        $this->setTitle(__('deploy'));
        $this->setSubTitle(__('add_repository'));

        $this->app->render('deploy/addRepositoryForm');
    }

    public function addRepositoryAction()
    {
        // SSH link: git@github.com:janson-git/deploy.git
        // HTTPS url: https://github.com/janson-git/deploy.git

        $repoPath = $this->p('repository_path');
        $repoPath = preg_replace('#[^a-zA-Z0-9:@./\-]#', '', $repoPath);

        $repoNameFull = mb_substr($repoPath, strrpos($repoPath, '/') + 1);
        $dirName = str_replace('.git', '', $repoNameFull);

        $output = $this->app->directory()->cloneRepository($repoPath, $dirName);

        $this->app->json(['data' => $output]);
        var_dump($repoPath);exit;
    }
}
