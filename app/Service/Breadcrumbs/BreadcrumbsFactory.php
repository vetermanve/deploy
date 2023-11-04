<?php

namespace Service\Breadcrumbs;

use Service\Pack;
use Service\Project;

class BreadcrumbsFactory
{
    public static function makeProjectListBreadcrumb(): Breadcrumb
    {
        return new Breadcrumb(
            __('projects'),
            'fa-solid fa-folder-tree',
            '/projects'
        );
    }

    public static function makeProjectPageBreadcrumb(Project $project): Breadcrumb
    {
        return new Breadcrumb(
            $project->getName(),
            'fa-solid fa-folder-open',
            '/projects/' . $project->getId()
        );
    }

    public static function makePackPageBreadcrumb(Pack $pack): Breadcrumb
    {
        return new Breadcrumb(
            $pack->getName(),
            'fa-solid fa-file-lines',
            '/packs/' . $pack->getId()
        );
    }
}