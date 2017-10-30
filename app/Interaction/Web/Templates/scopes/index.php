<h1>Уравление конфигами</h1>
<!--<a href="/web/scopes/create">Создать набор</a>-->
<form action="/web/scopes/edit" class="pure-form" method="get">
    <label for="scope-name">Новый конфиг</label>
    <input type="text" id="scope-name" name="scope"/>
    <input type="submit" value="Создать" class="pure-button"/>
</form>

<h2>Есть такие конфиги:</h2>
<ul>
    <? foreach ($scopes as $scope): ?>
        <li><a href="/web/scopes/edit?scope=<?= $scope ?>"><?= $scope ?></a></li>
    <? endforeach; ?>
</ul>
