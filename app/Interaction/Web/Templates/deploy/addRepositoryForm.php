<?php
/** @var $this \Admin\DoView */
use Service\Breadcrumbs\Breadcrumb;

$this
    ->addBreadcrumb(new Breadcrumb('Git', 'fa-solid fa-code-branch', '/web/deploy'))
    ->addBreadcrumb(new Breadcrumb('Add repository'));
?>

<div class="pure-g">
    <div class="pure-u-1">
        <section class="top-page-nav">
            <a href="/web/deploy" class="pure-button btn-secondary-outline btn-s">
                <i class="fa-solid fa-arrow-left"></i> <?= __('back_to_git') ?>
            </a>
        </section>
    </div>
</div>

<div class="pure-g">
    <div class="pure-u" style="margin-left: auto; margin-right: auto; width: 300px;">

        <div class="pure-form pure-form-stacked">
            <fieldset>
                <div class="pure-control-group">
                    <label for="repository_path">Repository Path</label>
                    <span class="pure-help-inline">(HTTPS url or SSH link)</span>
                    <input required type="text" id="repository_path" name="repository_path" class="pure-input-1"/>
                </div>

                <div class="pure-controls">
                    <button onclick="admin.addRepository(this)" class="pure-button pure-button-primary"><?= __('save') ?></button>
                </div>
            </fieldset>
        </div>

    </div>

    <div class="pure-u-1">
        <p id="doneLog" style="white-space: pre-wrap;"></p>
    </div>
</div>

<? if(isset($result)): ?>
    <h2 >Result</h2>
    <table class="pure-table">
        <? foreach ($result as $command): ?>
        <tr>
            <td ><?=$command['com'] ?></td>
            <td ><?=$command['res'] ?></td>
        </tr>
        <? endforeach; ?>
    </table>
<? endif; ?>


<script type="text/javascript">

    const admin = {
        rootPath : '/web/deploy',
        addRepository : function (btn) {
            let $repositoryPath = $('#repository_path');
            let repoPath = $repositoryPath.val();

            if (repoPath.length === 0) {
                return false;
            }

            let _this = this;
            spinnerOn(btn)
            $.getJSON(_this.rootPath + '/addRepository/',
                {
                    repository_path: repoPath
                },
                function (res) {
                    window.location.href = '/web/deploy';
                }
            )
                .fail(function (jqxhr, textStatus, error) {
                    console.log(jqxhr, textStatus, error)
                    _this.log(
                        jqxhr && jqxhr.responseJSON
                            ? jqxhr.responseJSON.data
                            : (textStatus + ' ' + error)
                    );
                    spinnerOff(btn)
                });
        },
        log : function (data, el) {
            $('#doneLog').html(data);
        }
    }
</script>
