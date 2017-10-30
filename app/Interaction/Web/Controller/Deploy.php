<?php

namespace Interaction\Web\Controller;

use Interaction\Base\Controller\ControllerProto;

class Deploy extends AuthControllerProto
{
    
    public function indexAction()
    {
        $this->app->view()->setHeader('Deploy');
        
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
    
}
