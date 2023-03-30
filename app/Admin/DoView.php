<?php

namespace Admin;

use \Slim\View;

class DoView extends View
{
    /**
     * @var App
     */
    private $app;
    
    /**
     * @param mixed $app
     */
    public function setApp($app)
    {
        $this->app = $app;
    }
    
    protected function loadMenu()
    {
        $menu = [];
        $menu['/web/project'] = __('menu.projects');

        if (env('ENABLE_DEPLOY')) {
            $menu['/web/slot'] = __('menu.servers');
        }
        if (env('ENABLE_EDIT_CONFIGURATIONS')) {
            $menu['/web/scopes'] = __('menu.configurations');
        }

        $menu['/web/deploy'] = __('menu.git');
        
        if ($this->app->auth->isAuth()) {
            $menu = ['/web/user' => 'Profile &#128057;'] + $menu;
            $menu['/web/auth/logout'] = __('logout');
        } else {
            $menu['/web/auth/login'] = __('login');
        }
        
        $this->set('mainMenu', $menu);
    }
    
    public function setHeader($text)
    {
        $this->set('header', $text);
        
        return $this;
    }
    
    public function setTitle($text)
    {
        $this->set('title', $text);
        
        return $this;
    }
    
    protected function render($template, $data = null)
    {
        $content = $this->subRender($template, $data);
        
        $layout = clone $this;
        $layout->set('_identify', $this->app->getIdentify());
        $layout->set('content', $content);
        $layout->set('user', [
            'id' => $this->app->auth->getUserLogin(),
            'url' => '/web/user',
        ]);
        
        $this->loadMenu();
        
        if ($this->app->debug) {
            $data['_logs'] = $this->app->getLogs();
        }
        
        return $layout->subRender('layout/main', $data);
    }
    
    public function subRender($template, $data)
    {
        return parent::render($template . '.php', $data);
    }
    
    public static function parse($data)
    {
        if (is_string($data) || is_numeric($data)) {
            return $data;
        }
        
        if (is_array($data)) {
            if (count($data) == 1 && isset($data[0])) {
                return self::parse(reset($data));
            }
            $res = [];
            
            $data = array_filter($data);
            
            $assoc = array_keys($data) !== range(0, count($data) - 1);
            foreach ($data as $k => $item) {
                $res [] = ($assoc ? "<b>{$k}</b>: " : '') . self::parse($item);
            }
            
            return $res ? implode('<br />', $res) : '';
        }
        
        if (is_object($data)) {
            return 'Object: ' . get_class($data) . self::parse(get_object_vars($data));
        }
        
        if (is_bool($data)) {
            return $data ? "Success" : "Fail"; 
        }
        
        return 'Closure';
    }
}
 