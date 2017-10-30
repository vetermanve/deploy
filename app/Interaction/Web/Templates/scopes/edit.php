<style type="text/css">
    .config-input {
        width: 100%;
    }
    
    .pure-config-row {
        padding: 0.1em 0em;
    }
</style>

<div class="pure-g">
    <div class="pure-u-1">
        <a href="/web/scopes">Все конфиги</a> | <a href="/web/scopes/settings?scope=<?= $scope ?>">Редактировать этот конфиг</a>
        <h1>Конфиг: <?= $scope ?></h1>
        <form action="/web/scopes/edit" class="pure-form" method="post">
            <input type="hidden" name="scope" value="<?= $scope ?>"/>
            <input type="submit" value="Сохранить" class="pure-button"/>
            <a href="#" onclick="addField(true); return false;">Добавить поле</a>
            
            <div id="edit-from" class="pure-g">
                <? foreach ($data as $key => $item): ?>
                    <div class="pure-u-1-2 pure-config-row">
                        <input type="text" placeholder="Marker" value="<?= $key ?>" class="config-input"
                               name="data_key[]"/>
                    </div>
                    <div class="pure-u-1-2 pure-config-row">
                        <input type="text" placeholder="Value" value='<?= is_array($item) ? json_encode($item) : $item ?>' class="config-input"
                               name="data_value[]"/>
                    </div>
                <? endforeach; ?>
            </div>
            <input type="submit" value="Сохранить" class="pure-button"/>
            <a href="#" onclick="addField(); return false;">Добавить поле</a>
        </form>
    </div>
    
    <div style="display: none" id="items-template">
            <div class="pure-u-1-2 pure-config-row">
                <input type="text" placeholder="Marker" value="" class="config-input"
                       name="data_key[]"/>
            </div>
            <div class="pure-u-1-2 pure-config-row">
                <input type="text" placeholder="Value" value="" class="config-input"
                       name="data_value[]"/>
            </div>
    </div>
    
    <script type="text/javascript">
        var addField = function (prepend) {
            if (prepend) {
                $('#edit-from').prepend($('#items-template').html());
            } else {
                $('#edit-from').append($('#items-template').html());
            }
        }
    </script>
</div>
