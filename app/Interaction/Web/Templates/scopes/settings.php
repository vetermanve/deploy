<div class="pure-g">
    <div class="pure-u-1">
        <a href="/web/scopes">Все конфиги</a>
        <h1>Редактирование конфига "<?= $scope ?>"</h1>
        <? if ($is_exists): ?>
            <form action="/web/scopes/settings" class="pure-form" method="post">
                <input type="hidden" name="scope" value="<?= $scope ?>"/>
                <input type="hidden" name="action" value="changeName"/>
                <label for="scope-name">Название конфига</label>
                <input type="text" id="scope-name" name="name" value="<?= $scope ?>"/>
                <input type="submit" class="pure-button" value="Изменить"/>
            </form>
            <form action="/web/scopes/settings" class="pure-form" method="post">
                <input type="hidden" name="scope" value="<?= $scope ?>"/>
                <input type="hidden" name="action" value="remove"/>
                <label for="scope-name">Удаелние конфига</label>
                <input type="submit" class="pure-button" value="Удалить"/>
            </form>
        <? else : ?>
            <h2>Данной конфигурации больше не существует</h2>
        <? endif; ?>
    </div>
</div>
