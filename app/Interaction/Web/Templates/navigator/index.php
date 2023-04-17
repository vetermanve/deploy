<?php
/** 
 * @var $node \Service\Node 
 */

$dirs = $node->getDirs();
?>

<style>
    .pads {
        margin: 0.1em 0.1em
    }
</style>

<div class="pure-g" style="color: #111">
    
    <div class="pure-u-1">
        <h2 style="display: inline-block"><?= __('directories') ?></h2> (<a href="?"><?= __('reset') ?></a>)</div>
    
    <form class="pure-u-1">
        <?php foreach ($node->getDirs() as $dirPath): ?>
            <label for="ch_<?= $dirPath ?>" class="pure-button pads">
                <a href="?pack=<?= $dirPath ?>"><?= $dirPath ?></a>
                <input type="checkbox" title="<?= $dirPath ?>" id="ch_<?= $dirPath ?>" name="dirs[]"
                       value="<?= $dirPath ?>"/>
            </label>
        <?php endforeach; ?>
        <input type="submit" value="Собрать Проект" class="pure-button pure-button-primary pads"/>
    </form>
    
    <?php if ($passedDirs): ?>
        <div class="pure-u-1"><h2 style="display: inline-block"><?= __('root_directories') ?> (<?=count($passedDirs) ?>)</h2> (<a
                href="./?"><?= __('reset') ?></a>)
        </div>
        <form class="pure-u-1" action="save">
                <ul><li><?=implode('</li><li>', $passedDirs) ?></li></ul>
            <input type="hidden" name="saveDirs" value='<?= implode(',', $passedDirs) ?>' title=""/>
            <input type="submit" value="<?= __('save_project') ?>" class="pure-button pure-button-primary"/>
        </form>
    <?php endif; ?>
    
    
    <div class="pure-u-1"><h2><?= __('available_repositories') ?> (<?=count($node->getRepos()) ?>)</h2></div>
    <div class="pure-u-1">
        <?php foreach ($node->getRepos() as $id => $repo): ?>
            <div class="pure-g">
                <div class="pure-u-1-3">
                    <b>ID: <?= $dirs[$id] ?></b>
                </div>
                <div class="pure-u-2-3">
                    <b><?= __('branches') ?>: <?= count($node->getBranchesByRepoId($id)) ?></b>
                    <br>
                    <div class="pure-g">
                        <?php foreach ($repo->getRemotesLastChangeTime() as $br => $time): ?>
                            <div class="pure-u-1-2" style="overflow: hidden"><?= $br ?></div>
                            <div class="pure-u-1-2"><?= @date('d.M.Y H:i', $time) ?></div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

</div>