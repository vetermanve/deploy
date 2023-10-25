<?
/**
 * @var $project \Service\Project
 * @var $selected
 * @var $action
 * @var $packBranches
 * @var $branches
 * @var $branchesData
 * @var $pack \Service\Pack
 * @var $this \Admin\DoView
 * @var $title string
 */

use Interaction\Web\Controller\Branches;
use Service\Breadcrumbs\BreadcrumbsFactory;

$this
    ->addBreadcrumb(BreadcrumbsFactory::makeProjectListBreadcrumb())
    ->addBreadcrumb(BreadcrumbsFactory::makeProjectPageBreadcrumb($project));

if ($pack) {
    $this->addBreadcrumb(BreadcrumbsFactory::makePackPageBreadcrumb($pack));
}

$this->addBreadcrumb(new \Service\Breadcrumbs\Breadcrumb($title));

?>

<style>
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

<?php if ($pack): ?>
<div class="pure-g">
    <div class="pure-u-1">
        <section class="top-page-nav">
            <a href="/web/pack/show/<?= $pack->getId() ?>" class="pure-button btn-secondary-outline btn-s">
                <i class="fa-solid fa-arrow-left"></i> <?= __('back_to_pack') ?>
            </a>
        </section>
    </div>
</div>
<?php else: ?>
<div class="pure-g">
    <div class="pure-u-1">
        <section class="top-page-nav">
            <a href="/web/project/show/<?= $project->getId() ?>" class="pure-button btn-secondary-outline btn-s">
                <i class="fa-solid fa-arrow-left"></i> <?= __('back_to_project') ?>
            </a>
        </section>
    </div>
</div>
<?php endif; ?>

<div class="pure-g">
    <div class="pure-u-1">
        <div>
            <h2 style="display: inline-block"><?= __('branches') ?> (<?= count($branches) ?>)</h2>
            <a href="/web/project/fetch/<?= $project->getId() ?>?return=1" class="pure-button">
                <?= __('refetch_repositories') ?>
            </a>
        </div>
        <form class="pure-form" action="/web/branches/save/<?= $project->getId() ?>" method="post"
              onsubmit="return aFilter.checkForm(this);" >
            <input type="hidden" name="action" value="<?= $action ?>"/>
            <? if ($action == Branches::ACTION_PACK_CREATE || $action == Branches::ACTION_PACK_FORK): ?>
                <input type="text" value="" name="name" placeholder="<?= __('set_pack_name') ?>" id="pack-name"/>
                <input type="submit" value="<?= __('save_pack') ?>" class="pure-button btn-primary"/>
            <? elseif ($action == Branches::ACTION_PACK_ADD_BRANCH
                || $action == Branches::ACTION_PACK_CHANGE_BRANCHES
            ) : ?>
                <input type="submit" value="<?= __('accept_branches') ?>" class="pure-button btn-primary"/>
                <input type="hidden" name="packId" value="<?= $pack->getId() ?>"/>
            <? endif; ?>
            
            <? if ($action == Branches::ACTION_PACK_CHANGE_BRANCHES): ?>
                <input type="hidden" name="oldBranches" value='<?= json_encode($packBranches) ?>'/>
            <? endif; ?>
            
            <h1><input type="text" placeholder="<?= __('filter_branches') ?>" onkeydown="aFilter.filter()" class="mainInput"
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
        <?= __('if_no_branches_found') ?>
        <a href="/web/project/fetch/<?= $project->getId() ?>?return=1" class="pure-button">
            <?= __('refetch_repositories_and_return') ?></a>
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
                alert("Enter pack name, please");
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