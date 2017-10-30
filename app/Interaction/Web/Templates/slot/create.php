<?
use Service\SlotsPool;

/**
 * @var $slotData array
 */
 
?>
<div class="pure-g">
    <div class="pure-u-1">
        <form class="pure-form pure-form-aligned" method="post">
            <fieldset>
                <div class="pure-control-group">
                    <label for="name">Имя сервера</label>
                    <input id="name" name="name" type="text" placeholder="App1" value="<?=isset($slotData['name']) ? $slotData['name'] : '' ?>">
                </div>
                
                <div class="pure-control-group">
                    <label for="host">Адрес сервера</label>
                    <input id="host" type="text" name="host" value="<?=isset($slotData['host']) ? $slotData['host'] : 'localhost' ?>" placeholder="localhost|dev.alol.io">
                </div>
    
                <div class="pure-control-group">
                    <label for="path">Путь для релиза</label>
                    <input id="path" type="text" name="path" placeholder="/var/www/" value="<?=isset($slotData['path']) ? $slotData['path'] : '/var/www/' ?>">
                </div>
                
                <div class="pure-control-group">
                    <label for="type">Тип серерва</label>
                    <select name="type" id="type">
                        <option value="<?= SlotsPool::SLOT_TYPE_LOCAL ?>"  <?=isset($slotData['type']) && $slotData['type'] == SlotsPool::SLOT_TYPE_LOCAL ? 'checked' : '' ?>>Local</option>
                        <option value="<?= SlotsPool::SLOT_TYPE_REMOTE ?>" <?=isset($slotData['type']) && $slotData['type'] == SlotsPool::SLOT_TYPE_LOCAL ? 'checked' : '' ?>>Remote</option>
                    </select>
                </div>
    
                <div class="pure-control-group">
                    <label for="projectId">Для проекта</label>
                    <input id="projectId" type="text" name="projectId" placeholder="Id проекта" 
                           value="<?= isset($slotData['projectId']) ? $slotData['projectId'] : '' ?>">
                </div>
                
                <div class="pure-controls">
                    <input type="hidden" value="<?=isset($slotData['id']) &&  $slotData['id'] ? $slotData['id'] : ''?>" name="id"/>
                    <button type="submit" class="pure-button pure-button-primary">Добавить релизный сервер</button>
                </div>
            </fieldset>
        </form>
    </div>
</div>
