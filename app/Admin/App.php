<?php

namespace Admin;

use \Slim\App as SlimApp;

/**
 * Class App
 * @property array $logs
 *
 * @package Admin
 */
class App extends SlimApp
{
    private static $instance;

    public $itemId;
    
    private $logs;

    public $debug = true;
    
    private $identify;

    /** @var array */
    protected $lang = [];
       
    const DATA_PROJECTS      = 'projects';
    const DATA_PACKS         = 'packs';
    const DATA_PROJECT_NAMES = 'project_names';
    const DATA_PACK_NAMES    = 'pack_names';
    const DATA_PACK_BUILDS   = 'pack_builds';
    const DATA_SLOTS         = 'slots';

    /**
     * @return null|\Admin\App|\Slim\App
     */
    public static function i () 
    {
        return self::getInstance();
    }

    public static function getInstance(): self
    {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct($container = [])
    {
        parent::__construct($container);

        // FIXME: just a hack to make old flow works
        self::$instance = $this;
    }

    /**
     * @return \Admin\DoView|\Slim\View;
     */
    public function view(): \Admin\DoView
    {
        return $this->getContainer()->get('view');
    }

    public function getRequest(): \Slim\Http\Request
    {
        return $this->getContainer()->get('request');
    }

    public function getResponse(): \Slim\Http\Response
    {
        return $this->getContainer()->get('response');
    }

    public function getAuth(): \User\Auth
    {
        return $this->getContainer()->get('auth');
    }
    
    public function json($dataArray, $code = 200)
    {
        /** @var \Slim\Http\Response $response */
        $response = $this->getContainer()->get('response');
        $response->withHeader('Content-Type', 'application/json')
            ->withStatus($code)
            ->write(json_encode($dataArray));
    }
    
    private $directory;
    
    /**
     * @return Directory
     */
    public function directory()
    {
        if (!$this->directory) {
            $this->directory = new Directory();
            $this->directory->setSitesDir(REPOS_DIR . '/');
        }
        
        return $this->directory;
    }
    
    public function log ($info, $form = null, $starTime = null) 
    {
        if ($starTime) {
            $form.=' ('.round(microtime(1)-$starTime,4).')';
        }
        
        $this->logs[] = [$form, $info];
        
        if (count($this->logs) > 1000) {
            array_pop($this->logs);
        }
    }
    
    public function doRoute ($module = 'web', $controller = 'user', $action = 'index', $id = 0)
    {
        $module     = ucfirst($module);
        $controller = ucfirst($controller);
        
        $this->itemId = $id;
        if (is_numeric($action) && $action) {
            $this->itemId = $action;
            $action = 'index';
        }

        $class = "\\Interaction\\{$module}\\Controller\\{$controller}";
        if (!class_exists($class)) {
            $class .= 'Controller';
            if (!class_exists($class)) {
                $this->notFound();
            }
        }
        
        $this->view()->setTemplatesDirectory("app/Interaction/{$module}/Templates");
        
        /* @var $controllerModel \Interaction\Base\Controller\ControllerProto */
        $controllerModel = new $class;
        $controllerModel->setApp($this);
        $controllerModel->setController(lcfirst($controller));
        $controllerModel->setAction($action);
        
        $this->log('Routing to: '.get_class($controllerModel).'->'.$action.'()', __METHOD__);
        
        return $controllerModel->run();
    }
    
    public function getIdentify () 
    {
        if (!$this->identify) {
            $this->identify = @gethostname(); 
        }
        
        return $this->identify;
    }
    
    /**
     * @return mixed
     */
    public function getLogs()
    {
        return $this->logs;
    }

    public function getLangStringForKey($key, $lang = 'en')
    {
        if (!array_key_exists($lang, $this->lang)) {
            $langFile = ROOT_DIR . "/lang/{$lang}.php";
            if (!file_exists($langFile) || !is_readable($langFile)) {
                throw new \Exception("Lang file not exists or not readable for '{$lang}' language");
            }

            $this->lang[$lang] = require_once $langFile;
        }

        return $this->lang[$lang][$key] ?? null;
    }
}