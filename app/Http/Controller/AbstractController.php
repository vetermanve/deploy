<?php

namespace App\Http\Controller;

abstract class AbstractController
{
    protected $controller;
    protected $action;
    protected $template;
    protected $isJson = false;
    
    /** @var \Admin\App */
    protected $app;

    /** @var \Admin\DoView */
    protected $view;
    
    /**
     * ControllerProto constructor.
     */
    public function __construct($param = null)
    {
        $this->app = \Admin\App::getInstance();
        $this->view = $this->app->getContainer()->get('view');
    }
    
    private function _beforeAll()
    {
        $this->template = $this->action;
        
        if ($this->p('json') == 1) {
            $this->isJson = true;
        }
    }
    
    public function before()
    {
        
    }
    
    public function run()
    {
        if (method_exists($this, $this->action) && !method_exists(__CLASS__, $this->action)) {
            $this->_doRun($this->action);
            return;
        }
        
        $methodWithPostfix = $this->action . 'Action';
        if (method_exists($this, $methodWithPostfix)) {
            $this->_doRun($methodWithPostfix);
            return;
        }
        
        $this->notFound($this->action);
    }
    
    private function _doRun($method)
    {
        $this->_beforeAll();
        $this->before();
        $this->$method();
        $this->after();
    }
    
    
    public function after()
    {
    }
    
    public function response($data = [], $tpl = null)
    {
        if ($this->isJson) {
            $this->app->json($data);
            return;
        }
        
        $tpl = $tpl ?: $this->template;

        $this->view->render($this->controller . '/' . $tpl, $data);
        return;
    }
    
    public function notFound($method = '')
    {
        echo $method;
        $this->app->notFound();
    }
    
    /**
     * @return mixed
     */
    public function getApp()
    {
        return $this->app;
    }
    
    /**
     * @param mixed $app
     *
     * @return $this
     */
    public function setApp($app)
    {
        $this->app = $app;
        
        return $this;
    }
    
    public function p($name, $default = null)
    {
//        var_dump($this->app->request());exit;
        $res = $this->app->request->get($name, null);
        if ($res !== null) {
            return $res;
        }
        
        return $this->app->request->post($name, $default);
    }
    
    /**
     * @param mixed $controller
     */
    public function setController($controller)
    {
        $this->controller = $controller;
    }
    
    /**
     * @param mixed $action
     */
    public function setAction($action)
    {
        $this->action = $action;
    }
    
    public function setTitle($title)
    {
        $this->view->setHeader($title);
    }
    
    public function setSubTitle($subTitle)
    {
        $this->view->setTitle($subTitle);
    }
}