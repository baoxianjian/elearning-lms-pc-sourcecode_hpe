<?php
use backend\assets\AppAsset;
use yii\helpers\Html;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\widgets\Breadcrumbs;

/* @var $this \yii\web\View */
/* @var $content string */

AppAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>"/>
	<meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?= Html::csrfMetaTags() ?>
    <title><?= Yii::t('system','backend_name')?></title>
    <?php $this->head() ?>
<!--    <link rel="shortcut icon" href="/favicon.ico"/>-->
<!--    <link rel="bookmark" href="/favicon.ico"/>-->
    <?= Html::cssFile('/static/backend/css/sb-admin-2.css') ?>
    <?= Html::cssFile('/static/backend/css/miniNav.css') ?>
    <?= Html::cssFile('/static/common/css/common.css')?>
<!--    --><?//= Html::jsFile('/vendor/bower/jquery/dist/jquery.min.js')?>
    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <?= Html::jsFile('/static/backend/js/html5shiv.js')?>
    <?= Html::jsFile('/static/backend/js/respond.min.js')?>

    <![endif]-->

    <style>
        .control-label{display: none;}
    </style>
</head>
<body id="loginBack">
<?php $this->beginBody() ?>


<?= $content ?>


<?php $this->endBody() ?>


<?//= Html::jsFile('/static/common/js/common.js')?>
<?//= Html::cssFile('/static/common/css/animate.css')?>
<?//= Html::jsFile('/components/noty/packaged/jquery.noty.packaged.min.js')?>
</body>
</html>
<?php $this->endPage() ?>

