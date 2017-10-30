<?
/**
 * @var $runtime \Commands\CommandRuntime
 * @var $context \Commands\CommandContext
 * @var $packId
 */

?>


<div class="pure-g">
    <div class="pure-u-1">
        <? if ($context->getProject()): ?>
            <a href="/web/project/show/<?= $context->getProject()->getId() ?>">К проекту</a>
        <? endif; ?>
        <? if ($context->getPack()): ?>
            <a href="/web/pack/<?= $context->getPack()->getId() ?>">К паку</a>
        <? endif; ?>
    </div>
    <? if ($exceptionsBySection = $runtime->getExceptions()): ?>
        <? foreach ($exceptionsBySection as $sectionId => $exceptions): ?>
            <div class="pure-u-1" style="padding-bottom: 20px; box-sizing: border-box">
                <h1>Exceptions at <?= $runtime->getSectionName($sectionId) ?>:</h1>
                <? foreach ($exceptions as $exception): ?>
                    <? /* @var $exception \Exception */ ?>
                    <div>
                        <?= $exception->getMessage() ?><br>
                        <b>File:</b> <?= $exception->getFile() ?>:<?= $exception->getLine() ?><br>
                    </div>
                <? endforeach; ?>
            </div>
        <? endforeach; ?>
    <? endif; ?>
    
    <? if ($errorsBySection = $runtime->getErrors()): ?>
        <? foreach ($errorsBySection as $sectionId => $errors): ?>
            <div class="pure-u-1" style="padding-bottom: 20px; box-sizing: border-box">
                <h1>Ошибки в <?= $runtime->getSectionName($sectionId) ?>:</h1>
                <? foreach ($errors as $error): ?>
                    <div class="pure-u-1">
                        <?= \Admin\DoView::parse($error) ?>
                    </div>
                <? endforeach; ?>
            </div>
        <? endforeach; ?>
    <? endif; ?>
    
    <? foreach ($runtime->getLogs() as $sectionId => $sectionLogs): ?>
        <div class="pure-u-1">
            <h2><?= $runtime->getSectionName($sectionId) ?></h2>
            <div class="pure-g">
                <? foreach ($sectionLogs as $key => $result): ?>
                    <div class="pure-u-1" style="font-weight: bold"><?= $key ?></div>
                    <div class="pure-u-1 pure-u-md-1-5 pure-u-sm-1"></div>
                    <div class="pure-u-1 pure-u-md-4-5 pure-u-sm-1">
                        <pre style="margin: 0.7em"><?= \Admin\DoView::parse($result); ?></pre>
                    </div>
                <? endforeach; ?>
            </div>
        </div>
    <? endforeach; ?>
    <div class="pure-u-1">
        <a href="/web/pack/<?= $packId ?>">К паку</a>
    </div>
</div>