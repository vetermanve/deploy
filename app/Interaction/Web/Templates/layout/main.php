<?
    $data = $this->data;
/**
 * @var $data \Slim\Helper\Set
 */
?><!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
<!--    <meta name="description" content="A layout example with a side menu that hides on mobile, just like the Pure website.">-->
    <title><?=isset($_identify) ? $_identify : '' ?> Config Server</title>
    <link rel="stylesheet" href="/css/pure-min.css">
    <link rel="stylesheet" href="/css/side-menu.css">
    <link rel="stylesheet" href="/css/girds-min.css">
    <script src="/js/jquery-2.1.1.min.js"></script>
    <style type="text/css">
        .blink_me {
            -webkit-animation-name: blinker;
            -webkit-animation-duration: 1s;
            -webkit-animation-timing-function: linear;
            -webkit-animation-iteration-count: infinite;
        
            -moz-animation-name: blinker;
            -moz-animation-duration: 1s;
            -moz-animation-timing-function: linear;
            -moz-animation-iteration-count: infinite;
        
            animation-name: blinker;
            animation-duration: 1s;
            animation-timing-function: linear;
            animation-iteration-count: infinite;
        }
    
        @-moz-keyframes blinker {
            0% { opacity: 1.0; }
            50% { opacity: 0.0; }
            100% { opacity: 1.0; }
        }
    
        @-webkit-keyframes blinker {
            0% { opacity: 1.0; }
            50% { opacity: 0.0; }
            100% { opacity: 1.0; }
        }
    
        @keyframes blinker {
            0% { opacity: 1.0; }
            50% { opacity: 0.0; }
            100% { opacity: 1.0; }
        }
    </style>
</head>
<body>
<div id="layout">
    <!-- Menu toggle -->
    <a href="#menu" id="menuLink" class="menu-link">
        <!-- Hamburger icon -->
        <span></span>
    </a>
    <div id="menu">
        <span id="loader"></span>
        <div class="pure-menu pure-menu-open">
<!--            <a class="pure-menu-heading" href="#" id="mainTitle">DeploYo</a>-->
            <a class="pure-menu-heading" href="<?php echo ($user['url'])?>"><?php echo $user['id'] ?></a>
            <ul>
<!--                <li class="pure-menu-heading"></li>-->
                <? foreach ( $data['mainMenu'] as $url => $title): ?>
                <li><a href="<?=$url ?>"><?=$title ?></a></li>
                <? endforeach; ?>
                <? if(0): ?>
                <li class="menu-item-divided pure-menu-selected">
                    <a href="#">Services</a>
                </li>
                <? endif; ?>
            </ul>
        </div>
    </div>
    <div id="main">
        <? if( $data['header'] ||  $data['title']): ?>
        <div class="header">
            <? if( $data['header']): ?>
                <h1><?= $data['header'] ?></h1>
            <? endif; ?>
            <? if( $data['title']): ?>
                <h2><?= $data['title'] ?></h2>
            <? endif; ?>
        </div>
        <? else : ?>
            <br/>
        <? endif; ?>
        <div class="content" style="color:#111111;">
            <?= $data['content']; ?>
            
            <? if(isset($_logs)): ?>
                <div onclick="$('.logs-cont').toggle()" style="cursor: pointer; color: #555; padding-top: 2em">
                    Show Debug Logs
                </div>
                <div class="pure-g logs-cont" style="display: none">
                    <? foreach ($_logs as $info): ?>
                        <div class="pure-u-1-3">
                             <div style="word-break: break-all; padding: 0.3em">
                                 <?=$info[0] ? $info[0] : '_' ?>     
                             </div>
                        </div><div class="pure-u-2-3"><?= \Admin\DoView::parse($info[1]) ?></div>
                    <? endforeach; ?>
                </div>
            <? endif; ?>
            <? if(0): ?>
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
            <? endif; ?>
        </div>
    </div>
</div>
<script src="/js/ui.js"></script>
</body>
</html>