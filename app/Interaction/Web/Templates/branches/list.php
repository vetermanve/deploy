<?
/**
 * @var $project \Service\Project
 * @var $selected
 * @var $action
 * @var $packId
 * @var $packBranches
 */

use Interaction\Web\Controller\Branches;

/* @var $branches */
/* @var $branchesData */

?>

<style type="text/css">
    .h {
        display: none; 
    }
    
    .repos {
        display: block; width: 100%; overflow: hidden; text-overflow: ellipsis;
    }
    
    .repos small {
        white-space: nowrap;
    }
</style>


<div class="pure-g">
    <div class="pure-u-1">
        <div>
            <h2 style="display: inline-block">Ветки (<?= count($branches) ?>)</h2>
            <a href="/web/pack/show/<?= $packId ?>">Вернуться к паку</a>
            <a href="/web/project/fetch/<?= $project->getId() ?>?return=1">Обновить репозитории</a>
        </div>
        <form class="pure-form" action="/web/branches/save/<?= $project->getId() ?>" method="post"
              onsubmit="return aFilter.checkForm(this);" >
            <input type="hidden" name="action" value="<?= $action ?>"/>
            <? if ($action == Branches::ACTION_PACK_CREATE || $action == Branches::ACTION_PACK_FORK): ?>
                <input type="text" value="" name="name" placeholder="Обязательно задайте имя пакета" id="pack-name"/>
                <input type="submit" value="Сохранить Пак" class="pure-button pure-button-primary"/>
            <? elseif ($action == Branches::ACTION_PACK_ADD_BRANCH
                || $action == Branches::ACTION_PACK_CHANGE_BRANCHES
            ) : ?>
                <input type="submit" value="Принять ветки в пакет" class="pure-button pure-button-primary"/>
                <input type="hidden" name="packId" value="<?= $packId ?>"/>
            <? endif; ?>
            
            <? if ($action == Branches::ACTION_PACK_CHANGE_BRANCHES): ?>
                <input type="hidden" name="oldBranches" value='<?= json_encode($packBranches) ?>'/>
            <? endif; ?>
            
            <h1><input type="text" placeholder="Фильтр по веткам" onkeydown="aFilter.filter()" class="mainInput"
                       onkeyup="aFilter.filter()" autofocus/></h1>
            <? foreach ($branches as $branch => $repos): ?>
                <? if (!$selected || ($selected && isset($selected[$branch]))): ?>
                    <div class="pure-g branches-item" style="margin-top: 0.5em">
                        <div class="pure-u-1-2">
                            <input type="checkbox" name="branches[]" id="br_<?= $branch ?>" value="<?= $branch ?>"
                                   title=""
                                <?= isset($selected[$branch]) ? 'checked' : '' ?>
                                   class="checkbox-item"/>
                            <label for="br_<?= $branch ?>" class="branch-name">
                                <?= $branch ?> (<?= count($repos) ?>) </label>
                            <? if(isset($branchesData[$branch])): ?>
                                  <b><?=array_sum(array_column($branchesData[$branch], 1)) ?></b>
                            <? endif; ?>
                        </div>

                        <div class="pure-u-1-2">
                            <? if(isset($branchesData[$branch])): ?>
                                <small>
                                    <? foreach ($branchesData[$branch] as $repo => $toMasterStatus): ?>
                                        <a style="cursor: pointer;"
                                           onclick="$(this).parent().find('div').toggle()">
                                            <?= $repo ?>,
                                        </a>
                                    <? endforeach; ?>

                                    <div style="display: none; background: #cccccc; padding: 0.2em">
                                    <? foreach ($branchesData[$branch] as $repo => $toMasterStatus): ?>
                                        <?=$toMasterStatus[0] ?> < <b><?= $repo?></b> > <?=$toMasterStatus[1] ?> <br>
                                    <? endforeach; ?>
                                    </div>
                                </small>
                            <? else : ?>
                                <div class="repos">
                                    <small><?=implode(', ', $repos) ?></small>
                                </div>
                            <? endif; ?>
                        </div>
                        
                    </div>
                <? endif; ?>
            <? endforeach; ?>
        </form>
    </div>
    <div class="pure-u-1-1">
        Если ветка не нашлась
        <a href="/web/project/fetch/<?= $project->getId() ?>?return=1" class="pure-button">
            Обновить репозитории и вернуться</a>
    </div>
</div>


<script type="text/javascript">
    var aFilter = {
        prevSearch  : '', 
        items: $('.branches-item'),
        input: {},
        version : 1,
        
        filter: function () {
            var self = this;
            
            var search = this.input.val().trim();
            
            if (search == self.prevSearch) {
                return ;
            }
            
            localStorage.setItem('search', search);
            
            var curVersion = ++self.version;
            self.prevSearch = search;
            
            var searchArray = search.split(' ').map(function (val) {
                return new RegExp(val.trim(), 'ig');
            });
            
            var text;
            var line;
            var matched = false;
            
            this.items.each(function (idx, obj) {
                if (curVersion !== self.version) {
                    return;
                }
                line = $(obj);
                text = line.find('.branch-name').first().text().trim();
                
                matched = false;
                var lineMatched = false;
                
                for (var id in searchArray) {
                    lineMatched = (text.match(searchArray[id]) || line.find('.checkbox-item:checked').length);
                    matched = matched || lineMatched ;
//                    if (lineMatched) {
//                        console.log(searchArray[id], text, matched); 
//                    }
                }
                
                if (matched)  {
                    line.removeClass('h');
                } else {
                    line.addClass('h');
                }
            })
        }, 
        checkForm: function (form) {
            var formObj = $(form);
            if (formObj.find('#pack-name').length && !formObj.find('#pack-name').val()) {
                alert("Введи имя пакета пожалуйста");
                return false;
            }
            
            return true;
        },
        init : function () {
            var self = this;
            self.input = $('.mainInput');
            self.input.val(localStorage.getItem('search'));
            self.filter();
        },
        checkAll : function () {
            this.items.not('.closedTab').each(function (idx, obj) {
                obj.attr('checked', true);
            });
        }
    }
    
    aFilter.init();
</script>