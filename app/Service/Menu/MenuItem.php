<?php

namespace Service\Menu;

class MenuItem
{
    public $route;
    public $title;
    public $patterns;

    public function __construct(string $title, string $route, array $patterns = [])
    {
        $this->title = $title;
        $this->route = $route;
        $this->patterns = $patterns;
    }

    public function isSelected(): bool
    {
        $isSelected = false;
        $currentPath = request()->getPathInfo();
        if ($currentPath === $this->route) {
            return true;
        }

        foreach ($this->patterns as $pattern) {
            if ($currentPath === $pattern || strpos($pattern, $currentPath) === 0) {
                $isSelected = true;
                break;
            }

            // check pattern as regex
            if (preg_match($pattern, $currentPath)) {
                $isSelected = true;
                break;
            }
        }

        return $isSelected;
    }
}