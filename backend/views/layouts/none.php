<?php
/**
 * Created by PhpStorm.
 * User: TangMingQiang
 * Date: 3/8/15
 * Time: 2:41 PM
 */
use yii\helpers\Html;

?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
    <head>
        <meta charset="<?= Yii::$app->charset ?>"/>
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <?= Html::csrfMetaTags() ?>
        <title><?= Yii::t('system','backend_name')?></title>
    </head>
<body>
<?= $content ?>

<?=Html::jsFile('/vendor/bower/jquery/dist/jquery.min.js')?>

<?=Html::jsFile('/vendor/bower/bootstrap/dist/js/bootstrap.min.js')?>
</body>
</html>
