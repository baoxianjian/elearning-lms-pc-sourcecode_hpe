<?php
/**
 * Created by PhpStorm.
 * User: TangMingQiang
 * Date: 3/8/15
 * Time: 2:41 PM
 */
use yii\helpers\Html;

?>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>"/>
    <meta http-equiv="X-UA-Compatible" content="IE=Edge"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <?= Html::csrfMetaTags() ?>
    <title>
        <?= empty($this->title) ? Yii::t('system','frontend_name') : Yii::t('system','frontend_name') . ' - ' . $this->title ?>
    </title>
    <script type="text/javascript" src="/static/frontend/js/lang.zh-CN.js"></script>
    <?= 'zh-CN' !== Yii::$app->language ? '<script type="text/javascript" src="/static/frontend/js/lang.' . Yii::$app->language . '.js"></script>' : '' ?>
    <?php $this->head() ?>
</head>
<body>
<?= $content ?>
</body>
</html>