<?php

namespace Service\Menu;

class MenuItem
{
    public $route;
    public $title;
    public $patterns;
    public $iconClass;

    public function __construct(string $title, string $route, array $patterns = [])
    {
        $this->title = $title;
        $this->route = $route;
        $this->patterns = $patterns;
    }

    public function setIconClass(string $iconClass)
    {
        $this->iconClass = $iconClass;
    }

    public function isSelected(): bool
    {
        $isSelected = false;
        $currentPath = \Admin\App::getInstance()->getRequest()->getUri()->getPath();
        if ($currentPath === $this->route) {
            return true;
        }

        foreach ($this->patterns as $pattern) {
            if ($currentPath === $pattern || strpos($pattern, $currentPath) === 0) {
                $isSelected = true;
                break;
            }

            // check pattern as regex
            if (substr($pattern, 0, 1) !== substr($pattern, -1)) {
                // this is not regex string. Skip it!
                continue;
            }
            if (preg_match($pattern, $currentPath)) {
                $isSelected = true;
                break;
            }
        }

        return $isSelected;
    }
}