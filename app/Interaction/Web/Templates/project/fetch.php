<div>
    <a href="/web/project/show/<?= $pId ?>"><?= __('back_to_project') ?></a>
    <?php foreach ($result as $path => $res): ?>
        <div><?= $path ?> : <?= $res ?></div>
    <?php endforeach; ?>
</div>
