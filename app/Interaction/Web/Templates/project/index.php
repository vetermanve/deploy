<?
/**
 * @var $dirSets
 * @var $branchSets
 */

use Service\Breadcrumbs\BreadcrumbsFactory;

$this->addBreadcrumb(BreadcrumbsFactory::makeProjectListBreadcrumb());
?>

<style type="text/css">
    .bset {
        height: 8em;
        overflow: hidden;
        /*background-color: #DDDDDD;*/
        border-bottom: 1px solid #CCC;
        /*padding: 0.5em 0.5em;*/
    }
    .bset li, .bset a {
        /*white-space: nowrap;*/
        display: inline-block;
        width: 100%;
        overflow: hidden; white-space: nowrap; text-overflow: ellipsis; word-break: break-all; word-wrap: break-word;
    }
    
    .dset {
        /*border-top: 1px solid silver;*/
        padding: 0.3em;
    }

    .dset a {
        margin-right: 5px;
    }
</style>

<div class="pure-g">
    <div class="pure-u-1">
        <a href="/web/navigator/" class="pure-button btn-primary"><?= __('create_project') ?></a>
    </div>

    <div class="pure-u-md-1-2 pure-u-xl-2-3">
    <?php foreach ($dirSets as $id => $dirs): ?>
        <div class="pure-u-1 project-card">
                <?php
                $dirs = $dirs ?: [];
                array_walk($dirs, function (&$val) {
                    $val = trim($val, '/');
                });
                ?>
                <h1><?= __('project') ?>: <a href="/web/project/show/<?= $id ?>"><?= implode(', ', $dirs); ?></a></h1>
                
                <div class="pure-g">
                    <?php if (isset($branchSets[$id])): ?>
                        <div class="pure-u-1">Packs:</div>
                        <?php foreach ($branchSets[$id] as $packId => $branchData): ?>
                            <div class="pure-u-1">
                                <div class="dset">
                                    <div>
                                        <a href="/web/pack/<?= $packId ?>" class="pure-button btn-secondary-outline">
                                            <?= $branchData['name'] ?? $packId; ?>
                                        </a>
                                        <?php
                                        if (array_key_exists('branches', $branchData)) {
                                            $count = count($branchData['branches']);
                                        }
                                        ?>
                                        <span class="tool" data-tip="<?= @implode("\n", @$branchData['branches']) ?>">
                                            Branches (<?= $count ?>) <i class="fa-solid fa-info-circle"></i>
                                        </span>

                                        <?php /**
                                        <div><?= __('branches') ?> (<?=@count($branchData['branches']) ?>):</div>
                                        <ul class="bset">
                                            <li><?= @implode('</li><li>', @$branchData['branches']) ?></li>
                                        </ul>
                                        */ ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
        </div>
    <?php endforeach; ?>
    </div>
</div>
