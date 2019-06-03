<?php
use frontend\assets\DefaultAppAsset;
use yii\helpers\Html;

/* @var $this \yii\web\View */
/* @var $content string */

DefaultAppAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <title><?= Yii::t('system','frontend_name')?></title>
    <meta http-equiv="X-UA-Compatible" content="IE=Edge,chrome=1">
    <meta charset="<?= Yii::$app->charset ?>"/>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?= Html::csrfMetaTags() ?>
    <script type="text/javascript" src="/static/frontend/js/lang.zh-CN.js"></script>
    <?= 'zh-CN' !== Yii::$app->language ? '<script type="text/javascript" src="/static/frontend/js/lang.' . Yii::$app->language . '.js"></script>' : '' ?>
    <?php $this->head() ?>
</head>
<body id="loginBack">
<?php $this->beginBody() ?>
<?php $this->endBody() ?>
<script type="text/javascript" src="/static/frontend/js/elearning.js"></script>
<?= html::jsFile('/static/common/js/common.js') ?>
<?= $content ?>
</body>
</html>
<?php $this->endPage() ?>