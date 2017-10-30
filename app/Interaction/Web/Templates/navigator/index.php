<? 
/** 
 * @var $node \Service\Node 
 */

$dirs = $node->getDirs();
?>

<style type="text/css">
    .pads {
        margin: 0.1em 0.1em
    }
</style>

<div class="pure-g" style="color: #111">
    
    <div class="pure-u-1"><h2 style="display: inline-block">Директории</h2> (<a href="?">Сбросить</a>) (<a href="./?">Закончить</a>)</div>
    
    <form class="pure-u-1">
        <? foreach ($node->getDirs() as $dirPath): ?>
            <label for="ch_<?= $dirPath ?>" class="pure-button pads">
                <a href="?pack=<?= $dirPath ?>"><?= $dirPath ?></a>
                <input type="checkbox" title="<?= $dirPath ?>" id="ch_<?= $dirPath ?>" name="dirs[]"
                       value="<?= $dirPath ?>"/>
            </label>
        <? endforeach; ?>
        <input type="submit" value="Собрать Проект" class="pure-button pure-button-primary pads"/>
    </form>
    
    <? if ($passedDirs): ?>
        <div class="pure-u-1"><h2 style="display: inline-block">Корневые директории (<?=count($passedDirs) ?>)</h2> (<a
                href="./?">Сбросить</a>)
        </div>
        <form class="pure-u-1" action="save">
                <ul><li><?=implode('</li><li>', $passedDirs) ?></li></ul>
            <input type="hidden" name="saveDirs" value='<?= implode(',', $passedDirs) ?>' title=""/>
            <input type="submit" value="Сохранить Проект" class="pure-button pure-button-primary"/>
        </form>
    <? endif; ?>
    
    
    <div class="pure-u-1"><h2>Репозитории проекта (<?=count($node->getRepos()) ?>)</h2></div>
    <div class="pure-u-1">
        <? foreach ($node->getRepos() as $id => $repo): ?>
            <div class="pure-g">
                <div class="pure-u-1-3">
                    Id: <?= $dirs[$id] ?>
                </div>
                <div class="pure-u-2-3">
                    Веток: <?= count($node->getBranchesByRepoDir($id)) ?><br>
                    <div class="pure-g">
                        <? foreach ($repo->getRemotesLastChangeTime() as $br => $time): ?>
                            <div class="pure-u-1-2" style="overflow: hidden"><?= $br ?></div>
                            <div class="pure-u-1-2"><?= @date('d.M.Y H:i', $time) ?></div>
                        <? endforeach; ?>
                    </div>
                </div>
            </div>
        <? endforeach; ?>
    </div>

</div>