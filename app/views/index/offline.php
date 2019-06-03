<?php
use app\assets\AppAsset;
use common\helpers\TCacheFileHelper;

AppAsset::register($this);
?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>" />
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?=Yii::t('system','app_name')?></title>
</head>
<body>
    <div class="wrap">
        <div class="container" style="text-align: center; margin-top: 120px;">
            <h2><?=Yii::t('system','site_close')?></h2>
            <p style="font-size: 30px;">
                <?=Yii::t('system','system_off')?>
            </p>
            <p>
                <?php echo $message;?>
            </p>
        </div>
    </div>
</body>
</html>