<?php
/**
 * User: GROOT (pzyme@outlook.com)
 * Date: 2016/4/8
 * Time: 10:44
 */
use yii\bootstrap\ActiveForm;
?>
<header data-am-widget="header" class="am-header am-header-default">
    <h1 class="am-header-title">
        <a href="#title-link">找回密码</a>
    </h1>
</header>

<div class="am-onePic">
    <img src="/static/mobile/proto/assets/i/login_head.jpeg">
</div>
<div class="am-g">
    <div class="am-u-lg-6 am-u-md-8 am-u-sm-centered">
        <br>
        <?php $form = ActiveForm::begin(['id' => 'request-password-reset-form','options' => ['class' => 'am-form']]); ?>

        <?= $form->field($model, 'email')->textInput(['id' => 'txtEmail',"placeholder" => ""]) ?>
        <br>
        <div class="lesson-btn am-cf m0">
            <button type="submit" id="sendEmailBtn" class="am-btn am-btn-primary am-btn-xs fr" ><?=Yii::t('common', 'send_email')?></button>
        </div>
        <?php ActiveForm::end(); ?>
        <hr>
        <p>© 2016 惠普大学版权所有</p>
    </div>
</div>