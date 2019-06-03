<?php
use api\assets\AppAsset;
use common\helpers\TCacheFileHelper;

AppAsset::register($this);
?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>" />
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo TCacheFileHelper::getCachedConfigValue('api_name');?></title>
</head>
<body>
    <div class="wrap">
        <div class="container" style="text-align: center; margin-top: 120px;">
            <h2>站点关闭</h2>
            <p style="font-size: 30px;">
                <?php echo TCacheFileHelper::getCachedConfigValue('api_status_message');?>
            </p>
            <p>
                <?php echo $message;?>
            </p>
        </div>
    </div>
</body>
</html>