<?php

namespace Interaction\Base\Controller;

abstract class ControllerProto
{
    protected $controller;
    protected $action;
    protected $template;
    protected $isJson = false;
    
    /**
     * @var \Admin\App
     */
    protected $app;
    
    /**
     * ControllerProto constructor.
     */
    public function __construct()
    {
        $this->app = \Admin\App::getInstance();
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
            return $this->_doRun($this->action);
        }
        
        $methodWithPostfix = $this->action . 'Action';
        if (method_exists($this, $methodWithPostfix)) {
            return $this->_doRun($methodWithPostfix);
        }
        
        $this->notFound($this->action);
    }
    
    private function _doRun($method)
    {
        $this->_beforeAll();
        $this->before();
        $response = $this->$method();
        $this->after();

        return $response;
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

//        $this->app->view()->setTemplatesDirectory();
        $this->app->view()->oldRender($this->controller . '/' . $tpl, $data);
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
        if ($name == 'id' && $this->app->itemId) {
            return $this->app->itemId;
        }

        return $this->app->getRequest()->getParam($name, $default);
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
        $this->app->view()->setHeader('<span style="color: red; margin: 0 10px;" title="THIS PAGE HANLDED IN OBSOLETE CONTROLLER!">!</span>' . $title);
    }
    
    public function setSubTitle($subTitle)
    {
        $this->app->view()->setTitle('OLD!' . $subTitle);
    }
}