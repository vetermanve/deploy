<?php if (isset($error)): ?>
    <div>
        <h3>УПС! Что-то пошло не так... :</h3>
        <p><?= $error?></p>
    </div>
<?php endif; ?>
<div>
    <a href="/web/project/show/<?= $pId ?>"><?= __('back_to_project') ?></a>
    <?php foreach ($result as $path => $res): ?>
        <div><?= $path ?> : <?= $res ?></div>
    <?php endforeach; ?>
</div>
