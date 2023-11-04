<div class="pure-g">
    <div class="pure-u-1">
        <h1><?= $msg ?? '' ?></h1>
        <form class="pure-form pure-form-aligned" method="post">
            <fieldset class="pure-group" >
                <textarea
                        class="pure-input-1-2"
                        placeholder="Pivate ssh key content"
                        name="key"
                        spellcheck="false"
                        style="min-height: 20em; font-size: small;width: 100%; font-family: monospace;">
                </textarea>
            </fieldset>

            <button type="submit" class="pure-button pure-input-1-2 pure-button-primary"><?= __('save') ?></button>
        </form>
    </div>
</div>
