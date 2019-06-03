<?php
use yii\helpers\Html;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\widgets\Breadcrumbs;
use frontend\assets\AppAsset;
use frontend\widgets\Alert;
use yii\db\Query;

/* @var $this \yii\web\View */
/* @var $content string */

AppAsset::register($this);
$context = $this->context;
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>"/>
    <meta http-equiv="X-UA-Compatible" content="IE=edge"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <!-- Bootstrap -->
    <?= html::cssFile('/static/frontend/css/bootstrap.css') ?>
    <?= html::cssFile('/static/frontend/css/index.css') ?>

</head>
<body>
<?= $content ?>
</body>
</html>
<?php $this->endPage() ?>
