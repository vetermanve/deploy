<div>
    <a href="/web/project/show/<?= $pId ?>">К проекту</a>
    <? foreach ($result as $path => $res): ?>
        <div><?= $path ?> : <?= $res ?></div>
    <? endforeach; ?>
</div>
