<?
/**
 * @var $view \Admin\DoView
 * @var $dirSets
 * @var $branchSets
 */

use Service\Breadcrumbs\BreadcrumbsFactory;

$view->addBreadcrumb(BreadcrumbsFactory::makeProjectListBreadcrumb());
?>

@extends('./layout.blade.php')

@section('content')
<div class="pure-g">
    <div class="pure-u-1">
        <a href="/web/navigator/" class="pure-button btn-primary-outline"><?= __('create_project') ?></a>
    </div>

    <div class="pure-u-md-1-2 pure-u-xl-2-3">
    <?php foreach ($dirSets as $id => $dirs): ?>
        <div class="pure-u-1 project-card">
                <?php
                $dirs = $dirs ?: [];
                array_walk($dirs, function (&$val) {
                    $val = trim($val, '/');
                });
                ?>
                <h1><i class="fa-solid fa-folder"></i> <a href="/web/project/show/{{ $id }}">{{ implode(', ', $dirs) }}</a></h1>
                
                <div class="pure-g">
                <?php if (isset($branchSets[$id])): ?>
                    <div class="pure-u-1">Packs:</div>

                    <?php foreach ($branchSets[$id] as $packId => $branchData): ?>
                    <div class="pure-u-1 dataset-item">
                        <div>
                            <a href="/web/pack/{{ $packId }}" class="pack-link">
                                <span class="icon-border"><i class="fa-regular fa-file-lines"></i></span> {{ $branchData['name'] ?? $packId }}
                            </a>
                            <?php $count = isset($branchData['branches']) ? count($branchData['branches']) : 0; ?>

                            @if($count > 0)
                            <span class="tool" data-tip="<?= @implode("\n", @$branchData['branches']) ?>">
                                Branches ({{ $count }}) <i class="fa-solid fa-info-circle"></i>
                            </span>
                            @else
                            <span class="empty"><i>No branches added</i></span>
                            @endif
                        </div>
                    </div>
                    <?php endforeach; ?>

                <?php endif; ?>
                </div>
        </div>
    <?php endforeach; ?>
    </div>
</div>
@endsection