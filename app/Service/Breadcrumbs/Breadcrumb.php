<?php

namespace Service\Breadcrumbs;

class Breadcrumb
{
    public $title;
    public $iconClass;
    public $url;

    public function __construct(string $title, ?string $iconClass = null, ?string $url = null)
    {
        $this->title = $title;
        $this->iconClass = $iconClass;
        $this->url = $url;
    }
}
