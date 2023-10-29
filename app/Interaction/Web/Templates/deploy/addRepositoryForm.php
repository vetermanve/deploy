<div class="pure-g">
    <div class="pure-u" style="margin-left: auto; margin-right: auto; width: 300px;">

        <form class="pure-form pure-form-stacked"
              method="post"
              action="/web/deploy/addRepository"
        >
            <fieldset>
                <div class="pure-control-group">
                    <label for="repository_path">Repository Path</label>
                    <span class="pure-help-inline">(HTTPS url or SSH link)</span>
                    <input required type="text" id="repository_path" name="repository_path" class="pure-input-1"/>
                </div>

                <div class="pure-controls">
                    <button type="submit" class="pure-button pure-button-primary"><?= __('save') ?></button>
                </div>
            </fieldset>
        </form>

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
 