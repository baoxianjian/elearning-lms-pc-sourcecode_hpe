<?php

/* @var $this yii\web\View */
/* @var $user common\models\framework\FwUser */

Yii::$app->urlManager->suffix = ".html";
$resetLink = Yii::$app->urlManager->createAbsoluteUrl(['site/reset-password', 'token' => $user->password_reset_token]);
$resetLink = str_replace("/api/","/",$resetLink);
$resetLink = str_replace("/app/","/",$resetLink);
$resetLink = str_replace("/backend/","/",$resetLink);
?>
您好：<?= $user->real_name ?> (<?= $user->user_name ?>)，

根据下面的流程，您可以方便的重新设置您的系统密码：
1.点击如下链接地址可以登录至密码重置页面：<?= $resetLink ?>;
2.填写完您期望的新密码后，点击更新数据按钮;
3.至此，您的新密码已经设置完成，请重新登录即可;

请注意：此邮件是平台自动发出的，无需回复