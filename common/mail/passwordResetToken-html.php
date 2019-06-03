<?php
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $user common\models\framework\FwUser */

Yii::$app->urlManager->suffix = ".html";
$resetLink = Yii::$app->urlManager->createAbsoluteUrl(['site/reset-password', 'token' => $user->password_reset_token]);
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
                            <span style="font-size:18.0pt;">重置系统密码<br></span>
                        </p>
                    </td>
                </tr>
                <tr>
                    <td valign="top" style="padding:.75pt .75pt .75pt .75pt">
                        <p>
                            <span style="font-size:10.0pt;">您好：<?= Html::encode($user->real_name) ?> (<?= Html::encode($user->user_name) ?>)，</span>
                        </p>
                        <p>
                            <span style="font-size:10.0pt;">根据下面的流程，您可以方便的重新设置您的系统密码：</span>
                        </p>
                        <table border="0" cellspacing="6" cellpadding="0" width="90%" style='width:90%'>
                            <tr>
                                <td width="38" style='width:28.5pt;background:#00558E;padding:1.5pt 1.5pt 1.5pt 1.5pt'>
                                    <p align="center" style='mso-margin-top-alt:auto;mso-margin-bottom-alt:auto;text-align:center'>
                                        <b><span style='font-size:10.0pt;font-family:"Arial","sans-serif";color:white'>1</span></b>
                                    </p>
                                </td>
                                <td width="814" style='width:610.5pt;background:#F0F0F0;padding:1.5pt 1.5pt 1.5pt 1.5pt'>
                                    <p>
                                        <span style='font-size:10.0pt;font-family:"Arial","sans-serif"'>点击如下链接地址可以登录至密码重置页面：
                                            <br>
                                            <?= Html::a(Html::encode($resetLink), $resetLink) ?>
                                            <br>
                                        </span>
                                    </p>
                                </td>
                            </tr>
                            <tr>
                                <td width="38" style='width:28.5pt;background:#00558E;padding:1.5pt 1.5pt 1.5pt 1.5pt'>
                                    <p align=center style='mso-margin-top-alt:auto;mso-margin-bottom-alt:auto;text-align:center'>
                                        <b><span style='font-size:10.0pt;font-family:"Arial","sans-serif";color:white'>2</span></b>
                                    </p>
                                </td>
                                <td width="814" style='width:610.5pt;background:#F0F0F0;padding:1.5pt 1.5pt 1.5pt 1.5pt'>
                                    <p>
                                        <span style='font-size:10.0pt;font-family:"Arial","sans-serif"'>填写完您期望的新密码后，点击<b>更新数据</b>按钮<br></span>
                                    </p>
                                </td>
                            </tr>
                            <tr>
                                <td width="38" style='width:28.5pt;background:#00558E;padding:1.5pt 1.5pt 1.5pt 1.5pt'>
                                    <p align=center style='mso-margin-top-alt:auto;mso-margin-bottom-alt:auto;text-align:center'>
                                        <b><span style='font-size:10.0pt;font-family:"Arial","sans-serif";color:white'>3</span></b>
                                    </p>
                                </td>
                                <td width="814" style='width:610.5pt;background:#F0F0F0;padding:1.5pt 1.5pt 1.5pt 1.5pt'>
                                    <p>
                                        <span style='font-size:10.0pt;font-family:"Arial","sans-serif"'>至此，您的新密码已经设置完成，请重新登录即可<br></span>
                                    </p>
                                </td>
                            </tr>
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