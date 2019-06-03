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

抱歉，您报名的课程《<?=$course->course_name?>》，审批未获通过。请继续报名学习其它课程，谢谢！

请注意：此邮件是平台自动发出的，无需回复