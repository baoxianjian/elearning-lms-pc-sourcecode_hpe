<?php
/**
 * User: GROOT (pzyme@outlook.com)
 * Date: 2016/7/6
 * Time: 17:11
 */
use yii\helpers\Html;
?>
<!DOCTYPE html>
<html class="no-js">
<head>
    <meta charset="utf-8">
    <?= Html::csrfMetaTags() ?>
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no">
    <title><?= $this->context->title ?></title>
    <link rel="stylesheet" href="/static/mobile/proto/assets/css/amazeui.flat.css">
    <link rel="stylesheet" type="text/css" href="/static/mobile/proto/assets/css/app.css">
    <script src="/static/mobile/lib/jquery.js,t3.js,react-0.14.7.js,react-dom.js.merge().0.2.js"></script>
    <script src="/static/mobile/lib/fastclick.js"></script>
    <script src="/static/mobile/lib/babel-core-browser.min.js"></script>
    <script src="/static/mobile/lib/jweixin-1.0.0.js"></script>
    <script src="/static/mobile/proto/assets/js/jquery.min.js"></script>
    <script src="/static/mobile/proto/assets/js/amazeui.min.js"></script>
    <script src="/static/mobile/proto/assets/js/main.js"></script>
</head>
<body>
<header data-am-widget="header" class="am-header am-header-default">
    <a href="javascript:history.back()" class="goBack testCase">退出</a>
    <h1 class="am-header-title">
        <a href="#title-link"><?= $this->context->title ?></a>
    </h1>
</header>
<!-- Menu -->
<nav data-am-widget="menu" class="am-menu  am-menu-offcanvas1" data-am-menu-offcanvas>
    <a href="javascript: void(0)" class="am-menu-toggle">
        <i class="am-menu-toggle-icon am-icon-bars"></i>
    </a>
    <div class="am-offcanvas">
        <div class="am-offcanvas-bar">
            <ul class="am-menu-nav sm-block-grid-1">
                <li>
                    <a href="/mobile/index/pending_list.html">待完成</a>
                </li>
                <li>
                    <a href="/mobile/wechat/voice/index.html">面授助理</a>
                </li>
                <li>
                    <a href="/mobile/index/lesson_list.html">已完成</a>
                </li>
                <li>
                    <a href="/mobile/index/lesson_list.html">浏览课程</a>
                </li>
                <li>
                    <a href="javascript:history.back()">返回</a>
                </li>
            </ul>
        </div>
    </div>
</nav>
<?= $content ?>
</body>
</html>
