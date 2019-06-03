<?php
/**
 * Created by PhpStorm.
 * User: Alex
 * Date: 2016/4/15
 * Time: 13:19
 */
?>
<!DOCTYPE html>
<html lang="zh-cn">
<head>
    <title><?=Yii::t('frontend','home_welcome_text')?><?=Yii::t('system','frontend_name')?></title>
    <meta http-equiv="X-UA-Compatible" content="IE=Edge,chrome=1">
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="stylesheet" href="/vendor/bower/bootstrap/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="/static/frontend/css/loginPageBig_zh.css">
</head>
<body class="upgradeBack">
<div class="container" style="width: 1150px !important;">
    <div class="row header">
<!--      <span class="logo" title="企业学习平台" style="float: left;">-->
<!--        <img src="dist/images/loginPageBig_logo_solo.jpeg" height="90" width="90">-->
<!--      </span>-->
        <h1 style="color:#0697d8"><?=Yii::t('system','frontend_name')?></h1>
    </div>
    <div class="row content" style="text-align: center;">
        <div class='loginPanel mainContent' style="position: static !important; margin: 130px auto; padding: 0; width: 600px; opacity: 0.94; height: 260px;">
            <h2><?=Yii::t('system','upgrade_browser');?></h2>
            <hr/>
            <div class="linkContainer" style=" display: block; margin-top: 75px; ">
                <a href="http://windows.microsoft.com/zh-cn/windows/downloads" style="color: #fff; background: #0078d7; padding: 15px 60px; border-radius: 5px; font-size: 20px;"><?=Yii::t('frontend','update_ie11')?></a>
                <a href="http://www.google.cn/chrome/browser/" style="color: #fff; background: #449b46; padding: 15px 60px; border-radius: 5px; font-size: 20px;"><?=Yii::t('frontend','download_chrome')?></a>
            </div>
        </div>
    </div>
    <div class="row footer">
        <div class="foot_info">
            <p style="padding-top: 15px"><?=Yii::t('system','version_info');?> <?=Yii::t('system','version_no')?>：<?= Yii::$app->version ?></p>
        </div>
    </div>
</div>
</body>
</html>
