<?php
use api\assets\AppAsset;
use yii\helpers\Html;
use yii\helpers\Url;
/* @var $this yii\web\View */
/* @var $content string */

AppAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>"/>
    <meta http-equiv="X-UA-Compatible" content="IE=edge"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <META HTTP-EQUIV="Cache-Control" CONTENT="no-cache">
    <?= Html::csrfMetaTags() ?>
    <title></title>
    <?php $this->head() ?>
</head>
<body>
<?php $this->beginBody() ?>
<?php $this->endBody() ?>
<?= $content ?>
</body>
</html>
<?php $this->endPage() ?>
