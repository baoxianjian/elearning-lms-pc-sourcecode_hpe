<?php
use mobile\assets\AppAsset;
use yii\helpers\Html;


/* @var $this \yii\web\View */
/* @var $content string */

AppAsset::register($this);
/*
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>"/>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>

</head>
<body>
    <?php $this->beginBody() ?>
    <?= $content ?>

    <?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
*/
?><!DOCTYPE html>
<html class="no-js">
	<head>
	    <meta charset="utf-8">
	    <?= Html::csrfMetaTags() ?>
	    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no">
	    <title><?= $this->context->title ?></title>
	    <link rel="stylesheet" href="<?= $this->context->static_root ?>/proto/assets/css/amazeui.flat.css">
	    <link rel="stylesheet" type="text/css" href="<?= $this->context->static_root ?>/proto/assets/css/app.css">
	    <link rel="stylesheet" type="text/css" href="<?= $this->context->static_root ?>/assets/css/we.css">
	</head>
	<body data-module="staple">
		<script>var STATIC_ROOT = '<?= $this->context->static_root ?>', TOKEN = '<?=$this->context->token?>', HASH = '<?=$this->context->hash?>';</script>
		<?= $content ?>
	</body>
</html>