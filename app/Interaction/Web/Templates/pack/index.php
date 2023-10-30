<?php
/**
 * @var $id
 * @var $sandboxReady
 * @var $branches
 * @var $pId
 * @var $pack \Service\Pack
 * @var $this \Admin\DoView
 */

use Service\Breadcrumbs\BreadcrumbsFactory;

$this
    ->addBreadcrumb(BreadcrumbsFactory::makeProjectListBreadcrumb())
    ->addBreadcrumb(BreadcrumbsFactory::makeProjectPageBreadcrumb($pack->getProject()))
    ->addBreadcrumb(BreadcrumbsFactory::makePackPageBreadcrumb($pack));
?>

<style>
    .pure-button {
        margin-top: 0.3em;
    }

    .btn-danger {
        margin-top: 0.8em;
    }
    
    h2 {
        padding-left: 0.7em;
    }
    
    .inactive {
        color: #888;
    }

    .separator {
        border-bottom: 1px solid #999;
        height: 7px;
        margin-bottom: 5px;
    }
</style>

<div class="pure-g">
    <div class="pure-u-1">
        <section class="top-page-nav">
            <a href="/web/project/show/<?= $pack->getProject()->getId() ?>" class="pure-button btn-secondary-outline btn-s">
                <i class="fa-solid fa-arrow-left"></i> <?= __('back_to_pack_list') ?>
            </a>
        </section>
    </div>
</div>

<div class="pure-g">
    
    <div class="pure-u-1 pure-u-md-2-3 bset">
        <h3><?= __('builds') ?></h3>
        <div class="pure-g">
            <?php foreach ($pack->getCheckPoints() as $cpId => $checkPoint): ?>
                <div class="pure-u-1 pure-u-lg-1-2 pure-u-xl-1-3">
                    <div>
                        <div><?= $cpId ?></div>
                        <div class="separator"></div>
                        <?php foreach ($checkPoint->getCommands() as $command): ?>
                            <a href="/web/command/?command=<?=$command->getId() ?>&context=<?=$command->getContext()->serialize() ?>"
                               class="pure-button <?= $command->isPrimary() ? 'btn-primary': '' ?> <?= $command->isDanger() ? 'btn-danger': '' ?> "
                               <?= $command->isConfirmRequired()
                                   ? 'onclick="return confirm(\'Are you sure to '.$command->getHumanName().'?\')"'
                                   : 'onclick="$(this).addClass(\'btn-in-action\')"'
                               ?>>
                                <?= $command->getHumanName() ?>
                            </a><br>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <?php if (env('ENABLE_DEPLOY')): ?>
    <div class="pure-u-1 pure-u-md-1-3 bset">
        <h3><?= __('deploy') ?></h3>
        <?php if ($lastCheckpoint = $pack->getLastCheckPoint()): ?>
            <div><?= $lastCheckpoint->getName() ?></div>
            <div class="separator"></div>
            <?php foreach ($pack->getDeployCommands() as $command): ?>
                <div>
                    <a href='/web/command/?command=<?=$command->getId() ?>&context=<?=$command->getContext()->serialize() ?>'
                       class="pure-button <?= $command->isPrimary() ? 'btn-primary' : '' ?>"
                    ><?= $command->getHumanName() ?></a>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    <?php endif; ?>
    
    <div class="pure-u-1 pure-u-md-2-3 bset">
        <h3><?= __('branches') ?> (<?= count($branches) ?>)</h3>
        <a href="/web/branches/addBranch/<?= $pId ?>?packId=<?= $id ?>" class="pure-button btn-primary">Add branches</a>
        <a href="/web/branches/removeBranch/<?= $pId ?>?packId=<?= $id ?>" class="pure-button ">Remove branches</a>
        <a href="/web/branches/forkPack/<?= $pId ?>?packId=<?= $id ?>" class="pure-button ">Fork pack</a>
        <ul>
            <?php foreach ($branches as $branchName => $repos): ?>
                <li class="<?= !$repos ? 'inactive' : '' ?>"><?= $branchName ?>
                    <a style="cursor: pointer;"
                       onclick="$(this).parent().find('div').toggle()">
                        (<?= count($repos) ?>) <small><?=array_sum(array_column($repos, 0)) ?> < master > <?=array_sum(array_column($repos, 1)) ?></small>
                    </a>
                    <div style="display: none; background: #cccccc; padding: 0.2em"> 
                        <?php foreach ($repos as $repo => $toMasterStatus): ?>
                            <?=$toMasterStatus[0] ?> < <b><?= $repo?></b> > <?=$toMasterStatus[1] ?> <br>
                        <?php endforeach; ?>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
    
    <div class="pure-u-1 pure-u-md-1-3 bset">
        <h3><?= __('pack_controls') ?></h3>
        <?php foreach ($pack->getPackCommands() as $command): ?>
            <div>
                <form action="/web/command/" method="get">
                    <input type="hidden" name="command" value="<?=$command->getId() ?>">
                    <input type="hidden" name="context" value="<?=$command->getContext()->serialize() ?>">
                    <?php $question = $command->isQuestion(); ?>
                    <?php if(!empty($question['field'])): ?>
                        <input type="hidden" class="js-question-<?=$question['field']?>" name="userData[<?=$question['field']?>]" value="<?=($question['placeholder'] ?? '')?>">
                    <?php endif; ?>
                    <button <?=$command->isConfirmRequired() ? 'onclick="return confirm(\'Are you sure to run '.strtolower($command->getHumanName()).'?\')"' : '' ?>
                       class="pure-button <?= $command->isDanger() ? 'btn-danger' : '' ?>"
                        <?php if(!empty($question['field']) && !empty($question['question'])): ?>
                            onclick="answer=prompt('<?= ($question['question'] ?? '')?>', '<?=($question['placeholder'] ?? '')?>');if(!answer)return false;document.getElementsByClassName('js-question-<?=$question['field']?>')[0].value=answer"
                        <?php else: ?>
                            onclick="$(this).addClass('btn-in-action')"
                        <?php endif; ?>
                    >
                        <?= $command->getHumanName() ?>
                    </button>
                </form>
            </div>
        <?php endforeach; ?>
    </div>
</div>
