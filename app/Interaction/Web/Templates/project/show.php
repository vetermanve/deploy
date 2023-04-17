<?php
/* @var $slots \Service\Slot\SlotProto[]
 * @var $fetchCommand \Commands\Command\Project\FetchProjectRepos
 * @var $packs \Service\Pack[]
 */ 
?>

<style>
    .bset {
        overflow: scroll;
        border: 1px solid #111111;
        padding: 0.5em 0.5em;
    }
    
    .dset {
        padding: 0.5em 0.5em;
    }
    
    .vmenu .pure-button {
        margin-bottom: 0.2em;
    }
</style>

<div class="pure-g">
    <div class="pure-u-1">
        <a href="/web/branches/createPack/<?=$id ?>" class="pure-button pure-button-primary"><?= __('create_pack') ?></a>
        <a href="/web/command/?command=<?=$fetchCommand->getId() ?>&context=<?=$fetchCommand->getContext()->serialize() ?>"
           class="pure-button <?= $fetchCommand->isPrimary() ? 'pure-button-primary'
               : '' ?>"><?= $fetchCommand->getHumanName() ?>
        </a>
        <a href="/web/project"><?= __('back_to_projects') ?> </a>
    </div>
    <div class="pure-u-1 dset">
        <div class="pure-g">
            <div class="pure-u-md-1-2 pure-u-xl-2-3">
                <h3><?= __('packs') ?></h3>
                <?php foreach ($packs as $pack): ?>
                    <div class="pure-u-1 ">
                        <div class="dset">
                            <div><a href="/web/pack/<?=$pack->getId() ?>"><?=$pack->getName(); ?></a>
                                <a href="<?=$pack->prepareCommand(new \Commands\Command\Pack\RemovePackWithData)->getLink() ?>"
                                   style="float: right"
                                   onclick="return confirm('Do you really want delete pack?')"><?= __('delete') ?></a></div>
                            <ul class="bset">
                                <li><?= @implode('</li><li>', $pack->getBranches()) ?></li>
                            </ul>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <?php if (env('ENABLE_DEPLOY')): ?>
            <div class="pure-u-md-1-2 pure-u-xl-1-3 vmenu">
                <h3><?= __('servers') ?></h3>
                <a href="/web/slot/create/?pId=<?=$id ?>" class="pure-button pure-button-primary"><?= __('add_release_server') ?></a>
                <a href="/web/project/slots/<?=$id ?>" class="pure-button"><?= __('release_servers_list') ?></a>
                <ul>
                    <?php foreach ($slots as $slot): ?>
                    <li><?=$slot->getName() ?>, <?=$slot->getHost().':'.$slot->getPath() ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>
            
        </div>
    </div>
</div>