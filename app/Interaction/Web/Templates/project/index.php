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
        height: 20em;
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
</style>
<div class="pure-g">
    <div class="pure-u-1">
        <a href="/web/navigator/" class="pure-button btn-primary"><?= __('create_project') ?></a>
    </div>
    <?php foreach ($dirSets as $id => $dirs): ?>
        <div class="pure-u-1">
                <?php
                $dirs = $dirs ?: [];
                array_walk($dirs, function (&$val) {
                    $val = trim($val, '/');
                });
                ?>
                <h1><?= __('project') ?>: <a href="/web/project/show/<?= $id ?>"><?= implode(', ', $dirs); ?></a></h1>
                
                <div class="pure-g">
                    <?php if (isset($branchSets[$id])): ?>
                        <?php foreach ($branchSets[$id] as $bsId => $branchData): ?>
                            <div class="pure-u-1 pure-u-md-1-3 bset">
                                <div class="dset">
                                    <div><a href="/web/pack/<?= $bsId ?>"><?= isset($branchData['name'])
                                            && $branchData['name'] ? $branchData['name'] : $bsId; ?></a></div>
                                    <div><?= __('branches') ?> (<?=@count($branchData['branches']) ?>):</div>
                                    <ul class="bset">
                                        <li><?= @implode('</li><li>', @$branchData['branches']) ?></li>
                                    </ul>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
        </div>
    <?php endforeach; ?>
</div>
