<?
/**
 * @var $dirSets
 * @var $branchSets
 */

use Service\Breadcrumbs\BreadcrumbsFactory;

$this->addBreadcrumb(BreadcrumbsFactory::makeProjectListBreadcrumb());
?>

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
                    <div class="pure-u-1 dataset-item">
                        <div>
                            <a href="/web/pack/<?= $packId ?>" class="pure-button btn-secondary-outline">
                                <?= $branchData['name'] ?? $packId; ?>
                            </a>
                            <?php $count = isset($branchData['branches']) ? count($branchData['branches']) : 0; ?>
                            <?php if ($count > 0): ?>
                            <span class="tool" data-tip="<?= @implode("\n", @$branchData['branches']) ?>">
                                Branches (<?= $count ?>) <i class="fa-solid fa-info-circle"></i>
                            </span>
                            <?php else: ?>
                            <span>Branches (0)</span>
                            <?php endif; ?>

                        </div>
                    </div>
                    <?php endforeach; ?>

                <?php endif; ?>
                </div>
        </div>
    <?php endforeach; ?>
    </div>
</div>
