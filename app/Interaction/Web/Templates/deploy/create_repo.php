<form class="pure-form pure-form-aligend" method="post">
    <fieldset style="text-align: center">
        <input type="text" name="name"/>
        <button type="submit" class="pure-button pure-button-primary">Cоздать</button>
    </fieldset>
</form>

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
 