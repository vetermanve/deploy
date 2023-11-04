<?php

namespace App\Http\Controller;

use Admin\App;
use Commands\Command\Pack\CheckpointCreateCommand;
use Commands\Command\Project\FetchProjectRepos;
use Commands\CommandContext;
use Service\Pack;
use Service\Project;
use Service\Node;
use Service\Data;

class PacksController extends AbstractAuthController
{
    /** @var Pack */
    protected $pack;

    public function show(int $id)
    {
        $pack = new Pack();
        $pack->setId($id);
        $pack->init();
        $pack->getNode()->loadBranches();

        $this->pack = $pack;

        $this->setTitle('<i class="fa-solid fa-file-lines"></i>' . __('pack') . " '{$this->pack->getName()}'");
        $node = $this->pack->getNode();
        $packReposByBranches = $node->getToMasterStatus($this->pack->getBranches());

        try {
            $this->pack->cloneMissedRepos();
            $this->pack->loadCheckpoints();


            if (!$this->pack->getCheckPoints()) {
                $this->pack->runCommand(new CheckpointCreateCommand());
                $this->pack->loadCheckpoints();
            }

        } catch (\Exception $e) {
            App::i()->log($e->getMessage().' at '.$e->getFile().':'.$e->getLine());
        }


        $this->pack->loadCheckpoints();

        $dirs = array_intersect_key($node->getDirs(), $node->getRepos());

        $this->view->render('packs/show.blade.php', [
            'packData'     => $this->pack->getData(),
            'pId'          => $this->pack->getProject()->getId(),
            'id'           => $this->pack->getId(),
            'branches'     => $packReposByBranches,
            'dirs'         => $dirs,
            'pack'         => $this->pack,
            'sandboxReady' => !$this->pack->getDirsToInit(),
        ]);
    }
}
