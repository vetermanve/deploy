<?
/**
 * @var $id
 * @var $sandboxReady
 * @var $branches
 * @var $pId
 * @var $pack \Service\Pack
 *
 */
?>

<style type="text/css">
    a.pure-button {
        margin-top: 0.3em;
    }
    
    h2 {
        padding-left: 0.7em;
    }
    
    .inactive {
        color: #888;
    }
</style>

<div class="pure-g">
    
    <div class="pure-u-1 pure-u-md-2-3 bset">
        <h3>Сборки</h3>
        <div class="pure-g">
            <? foreach ($pack->getCheckPoints() as $cpId => $checkPoint): ?>
                <div class="pure-u-1 pure-u-lg-1-2 pure-u-xl-1-3">
                    <div>
                        <div><?= $cpId ?></div>
                        <? foreach ($checkPoint->getCommands() as $command): ?>
                            <a href="/web/command/?command=<?=$command->getId() ?>&context=<?=$command->getContext()->serialize() ?>"
                               class="pure-button <?= $command->isPrimary() ? 'pure-button-primary'
                                   : '' ?>"><?= $command->getHumanName() ?>
                            </a><br>
                        <? endforeach; ?>
                    </div>
                </div>
            <? endforeach; ?>
        </div>
    </div>
    
    <div class="pure-u-1 pure-u-md-1-3 bset">
        <h3>Разливка</h3>
        <? if ($lastCheckpoint = $pack->getLastCheckPoint()): ?>
            <div><?= $lastCheckpoint->getName() ?></div>
            <? foreach ($pack->getDeployCommands() as $command): ?>
                <div>
                    <a href='/web/command/?command=<?=$command->getId() ?>&context=<?=$command->getContext()->serialize() ?>'
                       class="pure-button <?= $command->isPrimary() ? 'pure-button-primary' : '' ?>"
                    ><?= $command->getHumanName() ?></a>
                </div>
            <? endforeach; ?>
        <? endif; ?>
    </div>
    
    <div class="pure-u-1 pure-u-md-2-3 bset">
        <h3>Ветки (<?= count($branches) ?>)</h3>
        <a href="/web/branches/addBranch/<?= $pId ?>?packId=<?= $id ?>" class="pure-button pure-button-primary">
            Добавить ветки</a>
        <a href="/web/branches/removeBranch/<?= $pId ?>?packId=<?= $id ?>" class="pure-button ">Убрать ветки</a>
        <a href="/web/branches/forkPack/<?= $pId ?>?packId=<?= $id ?>" class="pure-button ">Форкнуть пак</a>
        <ul>
            <? foreach ($branches as $branchName => $repos): ?>
                <li class="<?= !$repos ? 'inactive' : '' ?>"><?= $branchName ?>
                    <a style="cursor: pointer;"
                       onclick="$(this).parent().find('div').toggle()">
                        (<?= count($repos) ?>) <small><?=array_sum(array_column($repos, 0)) ?> < master > <?=array_sum(array_column($repos, 1)) ?></small>
                    </a>
                    <div style="display: none; background: #cccccc; padding: 0.2em"> 
                        <? foreach ($repos as $repo => $toMasterStatus): ?>
                            <?=$toMasterStatus[0] ?> < <b><?= $repo?></b> > <?=$toMasterStatus[1] ?> <br>
                        <? endforeach; ?>
                    </div>
                </li>
            <? endforeach; ?>
        </ul>
    </div>
    
    <div class="pure-u-1 pure-u-md-1-3 bset">
        <h3>Управление паком</h3>
        <? foreach ($pack->getPackCommands() as $command): ?>
            <div>
                <a href="/web/command/?command=<?=$command->getId() ?>&context=<?=$command->getContext()->serialize() ?>" <?=$command->isConfirmRequired() ? 'onclick="return confirm(\'Точно хочешь '.$command->getHumanName().'\')"?' : '' ?>
                   class="pure-button"><?= $command->getHumanName() ?></a>
            </div>
        <? endforeach; ?>
    </div>
</div>
