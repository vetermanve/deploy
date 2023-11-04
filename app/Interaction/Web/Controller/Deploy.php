<?php

namespace Interaction\Web\Controller;

class Deploy extends AuthControllerProto
{

    public function index()
    {
        $this->app->view()->setHeader(__('deploy'));
        
        $this->response(array(
            'list' => $this->app->directory()->allData(),
        ));
    }
    
    public function getgit()
    {
        $dir = $this->app->getRequest()->getParam('dir');
        
        $this->app->json(array('data' => $this->app->directory()->getBranch($dir),));
    }
    
    public function fixgit()
    {
        $dir = $this->app->getRequest()->getParam('dir');
        
        $this->app->json(array(
            'data' => $this->app->directory()
                ->fix($dir, $this->app->request->get('doClean', false))
        ));
    }
    
    public function checkout()
    {
        $dir = $this->app->getRequest()->getParam('dir');
        $branch = $this->app->getRequest()->getParam('branch', '');
        
        $this->app->json(array('data' => $this->app->directory()->checkout($dir, $branch)));
    }
    
    public function update()
    {
        $dir = $this->app->getRequest()->getParam('dir');
        
        $this->app->json(array('data' => $this->app->directory()->update($dir),));
    }

    public function showAddRepositoryForm()
    {
        $this->setTitle(__('deploy'));
        $this->setSubTitle(__('add_repository'));

        $this->app->view()->oldRender('deploy/addRepositoryForm');
    }

    public function addRepository()
    {
        // SSH link: git@github.com:janson-git/deploy.git
        // HTTPS url: https://github.com/janson-git/deploy.git

        $repoPath = $this->p('repository_path');
        $repoPath = preg_replace('#[^a-zA-Z0-9:@./\-]#', '', $repoPath);

        $repoNameFull = mb_substr($repoPath, strrpos($repoPath, '/') + 1);
        $dirName = str_replace('.git', '', $repoNameFull);

        try {
            $output = $this->app->directory()->cloneRepository($repoPath, $dirName);
        } catch (\Exception $e) {
            $output = $e->getMessage();
            return $this->app->json(['data' => $output], 500);
        }

        return $this->app->json(['data' => $output]);
    }
}
