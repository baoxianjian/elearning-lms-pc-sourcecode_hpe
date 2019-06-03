<?php
use yii\helpers\Html;
?>
<!-- Bootstrap Core CSS -->
<?//= Html::cssFile('/vendor/bower/bootstrap/dist/css/bootstrap.min.css')?>

<!-- MetisMenu CSS -->
<?=Html::cssFile('/vendor/bower/metisMenu/dist/metisMenu.min.css')?>

<!-- Timeline CSS -->
<!--<link href="../dist/css/timeline.css" rel="stylesheet">-->
<?=Html::cssFile('/static/backend/css/timeline.css')?>

<!-- Morris Charts CSS -->
<?=Html::cssFile('/vendor/bower/morrisjs/morris.css')?>
<!-- Custom Fonts -->
<?=Html::cssFile('/vendor/bower/font-awesome/css/font-awesome.min.css')?>

<!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
<!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
<!--[if lt IE 9]>
<?= Html::jsFile('/static/backend/js/html5shiv.js')?>
<?= Html::jsFile('/static/backend/js/respond.min.js')?>
<![endif]-->

<?//=Html::jsFile('/vendor/bower/jquery/dist/jquery.min.js')?>
<?//=Html::jsFile('/vendor/bower/bootstrap/dist/js/bootstrap.min.js')?>
<?= Html::jsFile('/static/common/js/d.modal.js')?>

