<?
/**
 * @var $node \Service\Node
 */
$dirs = $node->getDirs();
?>

<style type="text/css">
    .pure-u-1 h2 {
        display: inline-block;
    }
</style>

<div class="pure-g" style="color: #111">
    <div class="pure-u-1"><h2>Выберите пакет</h2> (<a href="/web/deployPack/dirs">Создать</a>)</div>
    <div class="pure-u-1">
        <? foreach ($dirPacks as $dirId => $dirPath): ?>
            <div class="pure-g">
                <div class="pure-u-1-3">
                    Id: <?= $dirId ?>
                    <a href="/web/deployPack/pack?load=<?= $dirId ?>" class="pure-button">Выбрать</a>
                    <br>
                    <?= implode(', ', $dirPath) ?>
                </div>
                <div class="pure-u-2-3">
                    <? if (isset($sets[$dirId])): ?>
                        <div class="pure-g">
                            <? foreach ($sets[$dirId] as $setId => $info): ?>
                            <div class="pure-u-1">
                                #<?=$setId ?>: <?= @implode(', ', @$info['branches']) ?>
                            </div>
                            <? endforeach; ?>
                        </div>
                    <? endif; ?>
                </div>
            </div>
        <? endforeach; ?>
    </div>
</div>
