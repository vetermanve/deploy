<?


?>
<style type="text/css">
    .menu-elipsis {
        white-space: nowrap; display: block; width: 100%; overflow-x: hidden; text-overflow: ellipsis;
    }
</style>
<div class="content">
    <? foreach ($userData as $projectId => $packs): ?>
        <div class="pure-g">
            <div class="pure-u-1-1">
                <h3 style="display: inline-block">Проект: <a href="/web/project/show/<?= $projectId ?>"><?=$projectsData[$projectId]; ?></a></h3>
                <a class="pure-button" href="/web/project/fetch/<?= $projectId ?>?return=1">Обновить репозитории</a>
            </div>
        </div>
        <!--        Ветки пользователя которые есть в паках-->
        <? foreach ($packs as $branch => $pack): ?>
            <? if(isset($branchesPackData[$projectId][$branch])): ?>
                <div class="pure-g branches-item " style="margin-top: 0.5em; margin-bottom: 0.5em">
                    <div class="pure-u-9-24"  title="<?= $branch ?>">
                        <span class="menu-elipsis"><?= $branch  ?></span>
                        <? if(isset($branchesPackData[$projectId][$branch])): ?>
                            <small>
                                <? foreach ($branchesPackData[$projectId][$branch] as $repo => $toMasterStatus): ?>
                                    <a style="cursor: pointer;" onclick="$(this).parent().find('div').toggle()">
                                        <?= $repo ?>,
                                    </a>
                                <? endforeach; ?>

                                <div style="display: none; background: #cccccc; padding: 0.2em">
                                    <? foreach ($branchesPackData[$projectId][$branch] as $repo => $toMasterStatus): ?>
                                        <?=$toMasterStatus[0] ?> < <b><?= $repo?></b> > <?=$toMasterStatus[1] ?> <br>
                                    <? endforeach; ?>
                                </div>
                            </small>
                        <? endif; ?>
                    </div>
                    <div class="pure-u-1-24">
                        <? if(isset($branchesPackData[$projectId][$branch])): ?>
                            <b><?=array_sum(array_column($branchesPackData[$projectId][$branch], 1)) ?></b>
                        <? endif; ?>
                    </div>
                    <div class="pure-u-4-24 ">
                        <? foreach ($pack as $id => $name): ?>
                            <a href="/web/pack/show/<?= $id ?>"><?= $name  ?></a>,
                        <? endforeach; ?>
                    </div>
                    <div class="pure-u-9-24">
                        <? if($primaryPackId = @array_shift(array_flip($pack))): ?>
                            <a class="pure-button" href="/web/pack/applyPack/<?= $primaryPackId ?>?bId=merge" title="Пакет:<?= $pack[$primaryPackId]  ?>">Запустить мерж веток</a>
                        <? endif; ?>
                    </div>
                </div>
            <? endif; ?>
        <? endforeach; ?>
        <!--  END      Ветки пользователя которые есть в паках-->

        <!--        Ветки пользователя которых нет в паках-->
        <? foreach ($branches[$projectId] as $branch => $repos): ?>
            <div class="pure-g">
                <div class="pure-u-9-24">
                    <div class="menu-elipsis" title="<?= $branch ?>">
                        <?= $branch ?>
                    </div>

                    <? if(isset($branchesProjData[$projectId][$branch])): ?>
                        <small>
                            <? foreach ($branchesProjData[$projectId][$branch] as $repo => $toMasterStatus): ?>
                                <a style="cursor: pointer;"
                                   onclick="$(this).parent().find('div').toggle()">
                                    <?= $repo ?>,
                                </a>
                            <? endforeach; ?>

                            <div style="display: none; background: #cccccc; padding: 0.2em">
                                <? foreach ($branchesProjData[$projectId][$branch] as $repo => $toMasterStatus): ?>
                                    <?=$toMasterStatus[0] ?> < <b><?= $repo?></b> > <?=$toMasterStatus[1] ?> <br>
                                <? endforeach; ?>
                            </div>
                        </small>
                    <? else : ?>
                        <div class="menu-elipsis">
                            <small><?=implode(', ', $repos) ?></small>
                        </div>
                    <? endif; ?>
                </div>

                <div class="pure-u-1-24">
                    <? if(isset($branchesProjData[$projectId][$branch])): ?>
                        (<?= count($repos) ?>)
                        <b><?=array_sum(array_column($branchesProjData[$projectId][$branch], 1)) ?></b>
                    <? endif; ?>
                </div>

                <div class="pure-u-14-24">
                    <? if(array_sum(array_column($branchesProjData[$projectId][$branch], 1)) !== 0): ?>
                        <form class="pure-form" action="/web/branches/save/<?= $projectId ?>?return=1" method="post">
                            <label>
                                <select size="1" name="packId">
                                    <? foreach ($packsData[$projectId] as $id => $name): ?>
                                        <option value="<?php echo $id ?>"><?php echo $name ?></option>
                                    <? endforeach; ?>
                                </select>
                            </label>
                            <input type="hidden" value="add" name="action">
                            <input type="submit" value="Добавить ветку в пакет" class="pure-button"/>
                            <input type="hidden" name="branches[]" value="<?= $branch ?>"/>
                        </form>
                    <? endif; ?>
    
                    <a href="/web/project/removeBranch/<?= $projectId ?>?branch=<?= $branch ?>">Удалить ветку</a>
                </div>

            </div>
        <? endforeach; ?>
    <? endforeach; ?>
    <div class="pure-g">
        <div class="pure-u-1-1">
            <h2>Действия</h2>
            <a class="pure-button" href="/web/user/addkey">Добавить ssh ключ</a>
        </div>
    </div>
</div>


