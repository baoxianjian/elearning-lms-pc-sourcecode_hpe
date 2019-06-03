<?php
use app\assets\AppAsset;
use yii\helpers\Html;


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
    <title><?= Html::encode($this->title) ?></title>
    <?= html::cssFile('/static/app/css/bootstrap.css') ?>
    <?= Html::cssFile('/static/app/css/index.css') ?>
    <?= html::cssFile('/static/app/css/mobileStyle.css') ?>



    <?= html::jsFile('/static/app/js/jquery.min.js')?>
    <?= html::jsFile('/static/app/js/bootstrap.min.js')?>
    <?= html::jsFile('/static/app/js/jquery-ui.min.js')?>

</head>
<body>
    <?php $this->beginBody() ?>
    <?= $content ?>

    <?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>


