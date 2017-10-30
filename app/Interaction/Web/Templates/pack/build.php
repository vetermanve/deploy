<?
/**
 * @var $sandbox \Service\Pack;
 */

?>

<div class="pure-g">
    <div class="pure-u-1">
        <a href="/web/pack/<?=$packId ?>">К паку</a>
    </div>
    <div class="pure-u-1">
        Песочница: <?= $sandbox->getPath() ?><br>
        Репозитории:
        <ul>
            <? foreach ($sandbox->getRepos() as $repo): ?>
                <li><?= $repo->getPath() ?></li>
            <? endforeach; ?>
        </ul>
    </div>
    
    <? if ($sandbox->getError()): ?>
        <div class="pure-u-1">
            <?= str_replace([' // ', "\n"], '<br />', $sandbox->getError()); ?>
        </div>
    <? endif; ?>
    
    <? foreach ($sandbox->getMergeResults() as $repo => $branchInfo): ?>
        <div class="pure-u-1">
            <h3><?= $repo ?></h3>
            <? foreach ($branchInfo as $branchName => $output): ?>
                <div class="pure-g">
                    <div class="pure-u-1-5"><?= $branchName ?></div>
                    <div class="pure-u-4-5">
                        <pre><?= is_array($output) ? implode('<br />', $output) : $output ?></pre>
                    </div>
                </div>
            <? endforeach; ?>
        </div>
    <? endforeach; ?>
    
    <div class="pure-u-1">
        <a href="/web/pack/<?=$packId ?>">К паку</a>
    </div>
</div>
