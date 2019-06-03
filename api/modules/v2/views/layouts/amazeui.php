<?php
use frontend\assets\AppAsset;
use yii\helpers\Html;
use yii\helpers\Url;
AppAsset::register($this);
?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>"/>
    <meta http-equiv="X-UA-Compatible" content="IE=Edge"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <META HTTP-EQUIV="Cache-Control" CONTENT="no-cache">
    <?= Html::csrfMetaTags() ?>
    <title>
        <?= empty($this->title) ? Yii::t('system','frontend_name') : Yii::t('system','frontend_name') . ' - ' . $this->title ?>
    </title>
    <?php echo $this->head() ?>
    <?=Html::cssFile('/static/app/css/amazeui.flat.css')?>
    <?= Html::jsFile('/static/frontend/js/underscore-min.js') ?>
    <?=Html::cssFile('/static/app/css/app.css')?>
    <?= Html::jsFile('/static/app/js/jquery.min.js') ?>
    <?= Html::jsFile('/static/app/js/amazeui.min.js')?>
    <?= Html::jsFile('/static/app/js/fastclick.js') ?>
    <?= Html::jsFile('/static/app/js/main.js')?>
</head>

<body style="padding: 0">
<?= $content ?>
</html>
<?php $this->endPage() ?>
