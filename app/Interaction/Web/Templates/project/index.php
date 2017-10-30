<?
/**
 * @var $dirSets
 * @var $branchSets
 *
 */

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
        <a href="/web/navigator/" class="pure-button pure-button-primary">Создать новый проект</a>
    </div>
    <? foreach ($dirSets as $id => $dirs): ?>
        <div class="pure-u-1">
                <?
                $dirs = $dirs ?: [];
                array_walk($dirs, function (&$val) {
                    $val = trim($val, '/');
                });
                ?>
                <h1>Проект: <a href="/web/project/show/<?= $id ?>"><?= implode(', ', $dirs); ?></a></h1>
                
                <div class="pure-g">
                    <? if (isset($branchSets[$id])): ?>
                        <? foreach ($branchSets[$id] as $bsId => $branchData): ?>
                            <div class="pure-u-1 pure-u-md-1-3 bset">
                                <div class="dset">
                                    <div><a href="/web/pack/<?= $bsId ?>"><?= isset($branchData['name'])
                                            && $branchData['name'] ? $branchData['name'] : $bsId; ?></a></div>
                                    <div>Ветки (<?=@count($branchData['branches']) ?>):</div>
                                    <ul class="bset">
                                        <li><?= @implode('</li><li>', @$branchData['branches']) ?></li>
                                    </ul>
                                </div>
                            </div>
                        <? endforeach; ?>
                    <? endif; ?>
                </div>
        </div>
    <? endforeach; ?>
</div>