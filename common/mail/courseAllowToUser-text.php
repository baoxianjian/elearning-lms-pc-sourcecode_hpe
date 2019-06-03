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

恭喜，您报名的课程《<?=$course->course_name?>》，已经审批通过。开课时间为：<?=date('Y-m-d', $course->open_start_time)?>，地点为：<?=$course->training_address?>，请记得到时参加，谢谢！

请注意：此邮件是平台自动发出的，无需回复