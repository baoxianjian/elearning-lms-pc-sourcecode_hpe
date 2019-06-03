<?php

/* @var $this yii\web\View */
/* @var $teacher common\models\framework\FwUser  */
/* @var $course common\models\learning\LnCourse  */
/* @var $cert common\models\learning\LnCertification  */
/* @var $user common\models\framework\FwUser */
use common\models\framework\FwUser;
use common\models\learning\LnUserCertification;


Yii::$app->urlManager->suffix = ".html";
$resetLink = Yii::$app->urlManager->createAbsoluteUrl(['student/index']);
$resetLink = str_replace("/api/","/",$resetLink);
$resetLink = str_replace("/app/","/",$resetLink);
$resetLink = str_replace("/backend/","/",$resetLink);
?>
您好：<?= $teacher->real_name ?> (<?= $teacher->user_name ?>)，

在课程《<?=$course->course_name?>》中，您的学员已获得证书：《<?=$cert->certification_name?>》。具体如下：
<?php
$number = 0;
foreach($userList as $userId) {
$number = $number + 1;
$user = FwUser::findOne($userId);
$userCert = LnUserCertification::findOne(["certification_id"=>$cert->kid,"user_id"=>$user->kid,'status'=>LnUserCertification::STATUS_FLAG_NORMAL]);
if ($userCert) {
?><?=$number?>. <?=$user->real_name?> (<?= $user->user_name ?>)，证书编号：<?=$userCert->serial_number ."\n"?>
<? } ?>
<? } ?>

请注意：此邮件是平台自动发出的，无需回复