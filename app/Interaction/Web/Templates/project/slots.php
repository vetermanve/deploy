<? 
/**
 * @var $id int - project id
 * @var $slots \Service\Slot\SlotProto[]
 */

?>

<div class="pure-g">
    <div class="pure-u-1">
        <a href="/web/slot/create/?pId=<?=$id ?>" class="pure-button pure-button-primary">Новый релизный сервер</a>
        <a href="/web/slot/list/?pId=<?=$id ?>" class="pure-button">Скоприровать резилзный сервер</a>
        <a href="/web/project/show/<?=$id ?>">Перейти к проекту</a>
    </div>
    <div class="pure-u-1">
        <? foreach ($slots as $slot): ?>
            <div class="pure-u-1 pure-u-md-1-3">
                <div style="margin: 1em">
                    <h2><?=$slot->getName() ?></h2>
                    <div><?=$slot->getHost() ?>:<?=$slot->getPath() ?></div>
                    <div><?=$slot->getState() ?></div>
                </div>
            </div>
        <? endforeach; ?>
    </div>
</div>
