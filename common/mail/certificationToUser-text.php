<?php

/* @var $this yii\web\View */
/* @var $user common\models\framework\FwUser */

Yii::$app->urlManager->suffix = ".html";
$resetLink = Yii::$app->urlManager->createAbsoluteUrl(['student/index']);
$resetLink = str_replace("/api/","/",$resetLink);
$resetLink = str_replace("/app/","/",$resetLink);
$resetLink = str_replace("/backend/","/",$resetLink);
?>
您好：<?= $user->real_name ?> (<?= $user->user_name ?>)，

<?=$message?>根据下面的流程，您可以方便的查看此证书：
1.点击如下链接地址可以登录至学员个人门户：<?= $resetLink ?>;
2.在我的工具箱中，点击学习历程按钮;
3.点击证书即可看到此证书;

请注意：此邮件是平台自动发出的，无需回复