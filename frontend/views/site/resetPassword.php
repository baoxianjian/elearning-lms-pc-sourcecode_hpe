<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model \common\viewmodels\framework\ResetPasswordForm */

?>
<style>
    body {
        font: normal 14px/1.5 "TIBch","Classic Grotesque W01","Helvetica Neue",Arial,"Hiragino Sans GB","STHeiti","Microsoft YaHei","WenQuanYi Micro Hei",SimSun,sans-serif
    }
    .help-block{
        margin-top: -4px !important;
        margin-bottom: -2px !important;
    }
    *{box-sizing: border-box !important;}
    html{
        background-image:none !important;
        background-color: white !important;
    }
    .footer .foot_info ul {
        /* margin-left: 25px; */
        display: block;
        margin: 0 auto;
        width: 325px;
    }
</style>
<link rel="stylesheet" href="/static/frontend/css/loginPageBig_zh.css">
<div class="container">
    <div class="row header">
      <span class="logo" title="<?=Yii::t('system','frontend_name')?>">
        <img src="/static/frontend/images/loginPageBig_logo.jpeg" height="90" width="250">
      </span>
    </div>
    <div class="row content">
        <div class='loginPanel mainContent editPassword' style="height: auto">
            <h2 style="font-size: 14px;"><strong style="font-size: 16px;"><?= $model->user->real_name?>（<?= $model->user->user_name?>）</strong><?=Yii::t('frontend','hello_to_write_new_password')?></h2>
            <hr/>
            <p style="text-align: left;font-size: 1rem;background-color: #f7e0a5;padding: 2px 5px;"><?=Yii::t('frontend','warning_for_password')?></p>
            <?php $form = ActiveForm::begin(['id' => 'reset-password-form']); ?>
            <?= $form->field($model, 'password_new')->passwordInput([ "placeholder" => Yii::t('common','password_new'),'class'=>'inputWrap_user']) ?>
            <?= $form->field($model, 'password_repeat')->passwordInput(["placeholder" => Yii::t('common','password_repeat'),'class'=>'inputWrap_password']) ?>
            <div style="color:red"><?= $model->error_message ?></div>
            <?= Html::SubmitButton(Yii::t('common', 'update_{value}',['value'=>Yii::t('common', 'password')]),
                ['id'=>'updateBtn','class'=>'btn btn-info'])?>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
    <div class="row footer">
        <div class="foot_info">
            <ul>
                <li><a href=""><?=Yii::t('frontend','nav_home_text')?></a></li>
                <li><a href=""><?=Yii::t('frontend','email_regist')?></a></li>
                <li><a href=""><?=Yii::t('frontend','web_map')?></a></li>
                <li><a href=""><?=Yii::t('frontend','private')?></a></li>
                <li><a href=""><?=Yii::t('frontend','ad_strategy')?></a></li>
                <li><a href=""><?=Yii::t('frontend','terms_of_use')?></a></li>
                <li><a href=""><?=Yii::t('frontend','help')?></a></li>
            </ul>
            <p><?=Yii::t('system','version_info');?> <?=Yii::t('system','version_no')?>：<?= Yii::$app->version ?></p>
        </div>
    </div>
</div>