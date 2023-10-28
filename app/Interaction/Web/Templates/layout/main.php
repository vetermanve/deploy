<?php
    $data = $this->data;
    $currentPath = request()->getPathInfo();
/**
 * @var $data \Slim\Helper\Set
 * @var $user array
 */
?><!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $_identify ?? '' ?> Config Server</title>

    <link href="/fontawesome/css/fontawesome.css" rel="stylesheet">
    <link href="/fontawesome/css/solid.css" rel="stylesheet">

    <link rel="stylesheet" href="/css/pure-min.css">
    <link rel="stylesheet" href="/css/side-menu.css">
    <link rel="stylesheet" href="/css/girds-min.css">
    <link rel="stylesheet" href="/css/custom.css">
    <link rel="stylesheet" href="/css/custom-buttons.css">
    <link rel="icon" type="image/png" sizes="32x32" href="/rocket_32.png">
    <script src="/js/jquery-2.1.1.min.js"></script>
</head>

<body>
<div id="layout">
    <!-- Menu toggle -->
    <a href="#menu" id="menuLink" class="menu-link">
        <!-- Hamburger icon -->
        <span></span>
    </a>
    <div id="menu">
<!--        <span id="loader"></span>-->
        <div class="pure-menu pure-menu-open">
            <a class="pure-menu-heading" href="<?= $user['url'] ?>"><?= $user['id'] ?></a>
            <ul>
                <?php foreach ( $data['mainMenu'] as $menuItem): ?>
                <?php /** @var $menuItem \Service\Menu\MenuItem */ ?>
                <li <?= $menuItem->isSelected() ? 'class="pure-menu-selected"' : '' ?>>
                    <a href="<?=$menuItem->route ?>">
                        <?php if ($menuItem->iconClass): ?>
                            <i class="<?= $menuItem->iconClass ?> icon"></i>
                        <?php else: ?>
                            <i class="fa-solid icon"></i>
                        <?php endif; ?>
                        <span><?=$menuItem->title ?></span>
                    </a>
                </li>
                <?php endforeach; ?>
                <?php if(0): ?>
                <li class="menu-item-divided pure-menu-selected">
                    <a href="#">Services</a>
                </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
    <div id="main">

        <div class="breadcrumbs pure-g">
            <?php if ( $data['breadcrumbs'] ): ?>
            <div class="pure-u-4-5">
                <ul>
                    <?php foreach ($data['breadcrumbs'] as $item): ?>
                    <?php /** @var $item \Service\Breadcrumbs\Breadcrumb */ ?>
                    <li>
                        <?= $item->url !== null && $item->url !== request()->getPathInfo() ? "<a href=\"{$item->url}\">" : '<span>' ?>
                            <?php if ($item->iconClass): ?>
                            <i class="<?= $item->iconClass ?> icon"></i>
                            <?php endif; ?>
                            <p><?= $item->title ?></p>
                        <?= $item->url !== null ? '</a>' : '<span>' ?>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>

            <div class="pure-u">
                <div id="loader"></div>
            </div>
        </div>
        <div class="breadcrumbs-placeholder pure-u-1"></div>


        <?php if( $data['header'] ||  $data['title']): ?>
        <div class="header">
            <?php if( $data['header']): ?>
                <h1><?= $data['header'] ?></h1>
            <?php endif; ?>
            <?php if( $data['title']): ?>
                <h2><?= $data['title'] ?></h2>
            <?php endif; ?>
        </div>
        <?php else : ?>
            <br/>
        <?php endif; ?>
        <div class="content" style="color:#111111;">
            <?= $data['content']; ?>
            
            <?php if (isset($_logs)): ?>
                <button id="logs-toggle-button">
                    Show Debug Logs
                </button>
                <div class="pure-g logs-cont" id="logs-container">
                    <?php foreach ($_logs as $info): ?>
                        <div class="pure-u-1-3">
                             <div style="word-break: break-all; padding: 0.3em">
                                 <?= $info[0] ?: '_' ?>
                             </div>
                        </div><div class="pure-u-2-3"><?= \Admin\DoView::parse($info[1]) ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            <?php if(0): ?>
            <h2 class="content-subhead">How to use this layout</h2>
            
            <h2 class="content-subhead">Now Let's Speak Some Latin</h2>
            <p>
                Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.
            </p>
            <div class="pure-g">
                <div class="pure-u-1-4">
                    <img class="pure-img-responsive" src="http://farm3.staticflickr.com/2875/9069037713_1752f5daeb.jpg" alt="Peyto Lake">
                </div>
                <div class="pure-u-1-4">
                    <img class="pure-img-responsive" src="http://farm3.staticflickr.com/2813/9069585985_80da8db54f.jpg" alt="Train">
                </div>
                <div class="pure-u-1-4">
                    <img class="pure-img-responsive" src="http://farm6.staticflickr.com/5456/9121446012_c1640e42d0.jpg" alt="T-Shirt Store">
                </div>
                <div class="pure-u-1-4">
                    <img class="pure-img-responsive" src="http://farm8.staticflickr.com/7357/9086701425_fda3024927.jpg" alt="Mountain">
                </div>
            </div>
            <h2 class="content-subhead">Try Resizing your Browser</h2>
            <p>
                Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.
            </p>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="/js/ui.js"></script>
</body>
</html>
