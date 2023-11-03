<?php

namespace Admin;

use eftec\bladeone\BladeOne;
use Psr\Http\Message\ResponseInterface;
use Service\Breadcrumbs\Breadcrumb;
use Service\Menu\MenuItem;
use Slim\Container;
use Slim\Http\Response;
use Slim\Views\PhpRenderer;


class DoView
{
    /** @var \Admin\App */
    protected $app;

    /** @var PhpRenderer */
    protected $renderer;

    /** @var BladeOne */
    protected $blade;

    /** @var array|Breadcrumb[] */
    protected $breadcrumbs = [];

    /** @var string */
    protected $templatesDir;

    protected $data = [];

    /**
     * @param mixed $app
     */
    public function setApp(\Admin\App $app)
    {
        $this->app = $app;
        $this->renderer = $app->getContainer()->get('renderer');

        $this->data['header'] = null;
        $this->data['title'] = null;
//        $this->renderer->addAttribute('header', null);
//        $this->renderer->addAttribute('title', null);

        $this->blade = $app->getContainer()->get('blade');
    }
    
    protected function loadMenu()
    {
        $container = $this->app->getContainer();

        $menu = [];

        $projectsItem = new MenuItem(__('menu.projects'), '/projects', [
            '/projects',
            '#/web/project/*#',
            '#/web/pack/*#',
            '#/web/branches/addBranch#',
            '#/web/branches/removeBranch#',
            '#/web/branches/forkPack#',
            '#/web/branches/createPack#',
        ]);
        $projectsItem->setIconClass('fa-solid fa-folder-tree');
        $menu[] = $projectsItem;

        if (env('ENABLE_DEPLOY')) {
            $menu[] = new MenuItem(__('menu.servers'), '/web/slot');
        }
        if (env('ENABLE_EDIT_CONFIGURATIONS')) {
            $menu[] = new MenuItem(__('menu.configurations'), '/web/scopes');
        }

        $gitItem = new MenuItem(__('menu.git'), '/web/deploy');
        $gitItem->setIconClass('fa-solid fa-code-branch');
        $menu[] = $gitItem;

        if ($container->get('auth')->isAuth()) {
            $itemProfile = new MenuItem('Profile &#128057;', '/web/user', ['#web/user/*#']);
            $itemProfile->setIconClass('fa-solid fa-user');

            $itemLogout = new MenuItem(__('logout'), '/web/auth/logout');
            $itemLogout->setIconClass('fa-solid fa-right-from-bracket');

            array_unshift($menu, $itemProfile);
            array_push($menu, $itemLogout);
        } else {
            $menu[] = new MenuItem(__('login'), '/web/auth/login');
        }
        
        $this->data['mainMenu'] = $menu;
//        $this->renderer->addAttribute('mainMenu', $menu);
    }
    
    public function setHeader($text): self
    {
        $this->data['header'] = $text;
        $this->renderer->addAttribute('header', $text);
        
        return $this;
    }
    
    public function setTitle($text): self
    {
        $this->data['title'] = $text;
        $this->renderer->addAttribute('title', $text);
        
        return $this;
    }

    public function render($template, $data = null): ResponseInterface
    {
        // TODO: попрообовать заюзать BLADE ($this->blade) для рендера шаблонов
        $container = $this->app->getContainer();

        $this->renderer->addAttribute('view', $this);
        $this->renderer->addAttribute('_identify', $this->app->getIdentify());
        $this->renderer->addAttribute('user', [
            'id' => $container->get('auth')->getUserLogin(),
            'url' => '/web/user',
        ]);

        $this->loadMenu();

//        $layout->set('_identify', $this->app->getIdentify());
//        $layout->set('content', $content);
//        $layout->set('user', [
//            'id' => $this->app->auth->getUserLogin(),
//            'url' => '/web/user',
//        ]);
//
//        $this->loadMenu();
//
//        if ($this->app->debug) {
//            $data['_logs'] = $this->app->getLogs();
//        }


        $data = array_merge($this->renderer->getAttributes(), $data, $this->data);

        $output = $this->blade->run($template, $data);

        /** @var \Slim\Http\Response Response $response */
        $response = $container->get('response');
//        var_dump($response);exit;
        $response->write($output);

        return $response;

        return $this->renderer->render(
            $container->get('response'),
            $template,
            $data
        );

//        $content = $this->subRender($template, $data);
//
//        $layout = clone $this;
//        $layout->set('_identify', $this->app->getIdentify());
//        $layout->set('content', $content);
//        $layout->set('user', [
//            'id' => $this->app->auth->getUserLogin(),
//            'url' => '/web/user',
//        ]);
//
//        $this->loadMenu();
//
//        if ($this->app->debug) {
//            $data['_logs'] = $this->app->getLogs();
//        }
//
//        return $layout->subRender('layout/main', $data);
    }

    public function oldRender($template, $data = null)
    {
        $content = $this->subRender($template, $data);

        $layout = clone $this;
        $data['_identify'] = $this->app->getIdentify();
        $data['content'] = $content;
        $data['user'] = [
            'id' => $this->app->getAuth()->getUserLogin(),
            'url' => '/web/user',
        ];
//        $layout->set('_identify', $this->app->getIdentify());
//        $layout->set('content', $content);
//        $layout->set('user', [
//            'id' => $this->app->auth->getUserLogin(),
//            'url' => '/web/user',
//        ]);

        $this->loadMenu();
        $data['mainMenu'] = $this->data['mainMenu'];
        $data['breadcrumbs'] = $this->breadcrumbs;

        if ($this->app->debug) {
            $data['_logs'] = $this->app->getLogs();
        }

        echo $layout->subRender('layout/main', $data);
    }

    public function subRender($template, $data)
    {
        if (strrpos($template, '.php') !== 0) {
            $template .= '.php';
        }

        $templatePathname = $this->templatesDir . DIRECTORY_SEPARATOR . ltrim($template, DIRECTORY_SEPARATOR);
        if (!is_file($templatePathname)) {
            throw new \RuntimeException("View cannot render `$template` because the template does not exist");
        }

        $data = array_merge($this->data, (array) $data);
        $data['data'] = $data;

        extract($data);

        $output = '';
        try {
            ob_start();
            require $templatePathname;
        } finally {
            $output = ob_get_clean();
        }

        return $output;
//        return parent::render("{$template}.php", $data);
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

    public function addBreadcrumb(Breadcrumb $breadcrumb): DoView
    {
        $this->breadcrumbs[] = $breadcrumb;

        return $this;
    }

    public function hasBreadcrumbs(): bool
    {
        return count($this->breadcrumbs) > 0;
    }

    public function getBreadcrumbs(): array
    {
        return $this->breadcrumbs;
    }

    // FOR OLD CONTROLLERS COMPATIBLE
    public function setTemplatesDirectory($dir)
    {
        $this->templatesDir = $dir;
    }
}
 