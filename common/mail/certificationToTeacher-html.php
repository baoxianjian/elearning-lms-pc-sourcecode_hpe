<?php
use common\models\framework\FwUser;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $teacher common\models\framework\FwUser  */
/* @var $course common\models\learning\LnCourse  */
/* @var $cert common\models\learning\LnCertification  */
/* @var $user common\models\framework\FwUser */
use common\models\learning\LnUserCertification;

Yii::$app->urlManager->suffix = ".html";
$resetLink = Yii::$app->urlManager->createAbsoluteUrl(['student/index']);
$resetLink = str_replace("/api/","/",$resetLink);
$resetLink = str_replace("/app/","/",$resetLink);
$resetLink = str_replace("/backend/","/",$resetLink);
?>
<table border="1" cellpadding="0" width="98%" style="border:solid #2990CA 1.5pt;margin:0 auto">
    <tr>
        <td style="border:none;padding:18pt 18pt 18pt 18pt">
            <table border="0" cellpadding="0" width="100%" style="width:100.0%">
                <tr style="height:80pt">
                    <td valign="top" style="padding:.75pt .75pt .75pt .75pt;">
                        <p align="right" style="text-align:right">
                            <span style="font-size:20.0pt;"><?= Yii::t('system','frontend_name')?></span>
                        </p>
                        <p>
                            <span style="font-size:18.0pt;">颁发证书通知<br></span>
                        </p>
                    </td>
                </tr>
                <tr>
                    <td valign="top" style="padding:.75pt .75pt .75pt .75pt">
                        <p>
                            <span style="font-size:10.0pt;">您好：<?= Html::encode($teacher->real_name) ?> (<?= Html::encode($teacher->user_name) ?>)，</span>
                        </p>
                        <p>
                            <span style="font-size:10.0pt;">在课程《<?=$course->course_name?>》中，您的学员已获得证书：《<?=$cert->certification_name?>》。具体如下：</span>
                        </p>
                        <table border="0" cellspacing="6" cellpadding="0" width="90%" style='width:90%'>
                            <?php
                            $number = 0;
                            foreach($userList as $userId) {
                                $number = $number + 1;
                            ?>
                            <tr>
                                <td width="38" style='width:28.5pt;background:#00558E;padding:1.5pt 1.5pt 1.5pt 1.5pt'>
                                    <p align="center" style='mso-margin-top-alt:auto;mso-margin-bottom-alt:auto;text-align:center'>
                                        <b><span style='font-size:10.0pt;font-family:"Arial","sans-serif";color:white'><?=$number?></span></b>
                                    </p>
                                </td>
                                <td width="814" style='width:610.5pt;background:#F0F0F0;padding:1.5pt 1.5pt 1.5pt 1.5pt'>
                                    <p>
                                    <?
                                    $user = FwUser::findOne($userId);
                                    $userCert = LnUserCertification::findOne(["certification_id"=>$cert->kid,"user_id"=>$user->kid,'status'=>LnUserCertification::STATUS_FLAG_NORMAL]);
                                    if ($userCert) {
                                        ?>
                                        <span style='font-size:10.0pt;font-family:"Arial","sans-serif"'><?=$user->real_name?> (<?= $user->user_name ?>)，证书编号：<?=$userCert->serial_number?></span>
                                    <? } ?>
                                    </p>
                                </td>
                            </tr>
                            <?  } ?>
                        </table>
                    </td>
                </tr>
            </table>
            <div align="center" style="text-align:center">
                <span><hr size="1" width="100%" noshade style="color:black" align="center"></span>
            </div>
            <p style='margin-bottom:12.0pt'>
                <span style='font-size:10.0pt;'>请注意：此邮件由平台自动发出，无需回复</span>
            </p>
        </td>
    </tr>
</table>