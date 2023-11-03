<?php

namespace App\Http\Controller;

use Slim\Container;
use Slim\Http\Request;
use Slim\Http\Response;

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

    /** @var \Slim\Http\Request */
    protected $request;

    /** @var \Slim\Http\Response */
    protected $response;

    private $container;

    /**
     * ControllerProto constructor.
     */
    public function __construct(
        Container $container,
        Request $request,
        Response $response
    ) {
        $this->container = $container;

        $this->app = \Admin\App::getInstance();
        $this->view = $this->app->getContainer()->get('view');

        $this->request = $request;
        $this->response = $response;
    }

    public function getContainer(): Container
    {
        return $this->container;
    }

    public function before() {}
    
    public function after() {}
    
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
        return $this->app->getRequest()->getParam($name, null);
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