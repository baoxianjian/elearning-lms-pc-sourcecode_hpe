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
        <a href="#title-link">登 录</a>
    </h1>
</header>

<div class="am-onePic">
    <img src="/static/mobile/proto/assets/i/login_head.jpeg">
</div>
<div class="am-g">
    <div class="am-u-lg-6 am-u-md-8 am-u-sm-centered">
        <br>
        <?php $form = ActiveForm::begin(['id' => 'login-form','options' => ['class' => 'am-form'],
            'method' => 'post',
        ]); ?>
        <?if($company):?>
            <?= $form->field($model, 'company_id')->hiddenInput(['value'=>$company->kid]); ?>
        <?endif;?>
        <?= $form->field($model, 'user_name')->textInput(["placeholder" => Yii::t('common','user_name'),'maxlength' => 255]); ?>
            <br>
        <?= $form->field($model, 'password')->passwordInput(['placeholder' => Yii::t('common','password'),'maxlength' => 255]); ?>
            <br>
            <div class="lesson-btn am-cf m0">
                <button class='am-btn am-btn-primary am-btn-xs fr' wechat="hide" name="submit-button" type="submit" value="button"><?=Yii::t('common','login')?></button>
                <button type="submit" class="am-btn am-btn-success am-btn-xs fr" wechat="show"  style="margin-top:10px;"><?=Yii::t('common','wechat_bind_login')?></button>
            </div>
            <br />
            <div class="am-cf">
                <label for="remember-me" style="display: none;">
                    <input id="loginform-remember_time" class="form-control" type="hidden" value="1" name="LoginForm[remember_time]">
                    <input type="hidden" value="0" name="LoginForm[remember_me]">
                    <input id="loginform-remember_me" type="checkbox" value="1" name="LoginForm[remember_me]">
                    &nbsp;<?=Yii::t('common','remember_me_one_day')?>
                </label>
                <a href="<?=Yii::$app->urlManager->createUrl('wechat/auth/find-password')?>" class="am-btn am-btn-default am-btn-sm am-fr"><?= Yii::t('common','forget_password')?></a>
            </div>
        <?php ActiveForm::end(); ?>
        <hr>
        <p>© 2016 惠普大学版权所有</p>
    </div>
</div>