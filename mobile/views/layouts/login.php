<?php
use mobile\assets\AppAsset;
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
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?= Html::csrfMetaTags() ?>
    <title><?php echo '企业学习平台 登陆';?></title>

    <?= Html::jsFile('/static/frontend/js/underscore-min.js') ?>
    <?= Html::jsFile('/static/mobile/proto/assets/js/jquery.min.js') ?>
    <?= Html::jsFile('/static/mobile/proto/assets/js/amazeui.min.js') ?>
    <?= Html::jsFile('/static/mobile/proto/assets/js/fastclick.js') ?>
    <?= Html::jsFile('/static/mobile/proto/assets/js/main.js') ?>
    <?= Html::cssFile('/static/mobile/proto/assets/css/amazeui.flat.css') ?>
    <?= Html::cssFile('/static/mobile/proto/assets/css/app.css') ?>
</head>
<body>
    <?= $content ?>
</body>
</html>
<?php $this->endPage() ?>


