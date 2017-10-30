<? if(isset($error)): ?>
    <div>
        <h3>УПС! Что-то пошло не так... :</h3>
        <p><?= $error?></p>
    </div>
<? endif; ?>
<div>
    <a href="/web/project/show/<?= $pId ?>">К проекту</a>
    <? foreach ($result as $path => $res): ?>
        <div><?= $path ?> : <?= $res ?></div>
    <? endforeach; ?>
</div>
