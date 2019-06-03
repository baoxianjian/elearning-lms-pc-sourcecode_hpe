<?php
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use yii\captcha\Captcha;
use yii\helpers\ArrayHelper;

$this->title = Yii::t('system','frontend_name');
$lang = 'en';

if (strpos(Yii::$app->language, 'zh') === 0) {
    $lang = 'zh';
} elseif (strpos(Yii::$app->language, 'en') === 0) {
    $lang = 'en';
}
?>

<!--<link href="/vendor/bower/bootstrap/dist/css/bootstrap-theme.min.css" rel="stylesheet" />-->
<link rel="stylesheet" href="/static/frontend/css/loginPageBig_<?= $lang ?>.css">
<style>
   *{box-sizing: border-box !important;}
   #loginform-remember_me {
       float: left;
       margin: 0 !important;
       position: static;
   }
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
   .content .barCode span{
       height: 220px !important;
   }
   .content .loginPanel{
       height: 360px !important;
   }
   label.control-label{
    display: none;
   }
</style>
<div class="container">
    <div class="row header">
        <input type="hidden" id="hostname" value="<?=$hostName?>">
        <?if($company && $company->logo_url):?>
            <span class="logo" title="<?=$company->company_name?>">
                <img src="<?=$company->logo_url?>" style="margin: 20px 0px 20px 20px" height="45">
            </span>
        <?endif;?>
        <h1 style="color:#0697d8;margin-left:20px"><?=Yii::t('system','frontend_name')?></h1>
        <div class="clientDL">
            <a href="###" class="clientBar" title="<?=Yii::t('frontend','click_for_download')?>"><?=Yii::t('frontend','{value}_download',['value'=>Yii::t('frontend','client')])?>
                <div class="centerBtnArea">
                    <div class="barCodeLeft">
                        <img src="<?= Yii::$app->urlManager->createUrl(['common/download-code']) ?>" height="128" width="128">
                        <p style="color:#c6c6c6;"><?=Yii::t('frontend','{value}_client',['value'=>Yii::t('frontend','mobile_learning')])?></p>
                    </div>
                </div>
            </a>
        </div>
    </div>
    <div class="row content">
        <div class='loginPanel mainContent <?php if ($mailon==1)echo 'hide';?>'>
            <h2><?=Yii::t('frontend','welcome_{value}',['value'=>Yii::t('system','frontend_name')])?></h2>
            <div class="barCode">
                <span><img src="<?=Yii::$app->urlManager->createUrl('site/qr-code')?>?url=<?=base64_encode($hostUrl)?>" height="128" width="128"></span>
            </div>
            <hr/>
            <?php $form = ActiveForm::begin(['id' => 'login-form',
                'method' => 'post',
            ]); ?>
            <?if($company):?>
                <?= $form->field($model, 'company_id')->hiddenInput(['value'=>$company->kid]); ?>
            <?endif;?>
            <?= $form->field($model, 'user_name')->textInput(["placeholder" => Yii::t('common','user_name'),'maxlength' => 255]); ?>
            <?= $form->field($model, 'password')->passwordInput(['placeholder' => Yii::t('common','password'),'maxlength' => 255]); ?>
            <button class='btn btn-info' name="submit-button" type="submit" value="button"><?=Yii::t('common','login')?></button>
            <input type="hidden" value="0" name="LoginForm[remember_me]">
            <div class="userOption">
                <a id="forgetPassword" href="#" class="passwordFG pull-right"><?= Yii::t('common','forget_password')?></a>
                <? if ($enableRegister == "1") {?>
                    <a href="<?=Yii::$app->urlManager->createUrl('site/signup')?>" class="passwordFG pull-right" style="padding-right: 5px"><?= Yii::t('common','signup') . Yii::t('common','new_user')?></a>
                <? }?>

                <label class="passwordRM pull-left">
                    <!--label class="control-label" for="loginform-remember_time">Remember Time</label-->
                    <input id="loginform-remember_time" class="form-control" type="hidden" value="1" name="LoginForm[remember_time]">
                    <input type="hidden" value="0" name="LoginForm[remember_me]">
                    <input id="loginform-remember_me" type="checkbox" value="1" name="LoginForm[remember_me]">
                    &nbsp;<?=Yii::t('common','remember_me_one_day')?>
                </label>

            </div>
            <!--                --><?//= $form->field($model, 'remember_time')->dropDownList(array('1' => '保存一天', '7' => '保存一周', '30' => '保存一月')); ?>

            <?php ActiveForm::end(); ?>

        </div>
        <div class='loginPanel passwordPanel <?php if ($mailon==0)echo 'hide';?>'>
            <h2><?=Yii::t('frontend','please_reset_password')?></h2>
            <hr/>
            <?php $form = ActiveForm::begin(['id' => 'request-password-reset-form']); ?>
            <? $emailmsg= Yii::t('frontend','please_write_down_your_email')?>
            <?= $form->field($passwordresetmodel, 'email')->textInput(['id' => 'txtEmail','style'=>"margin-top:50px;","placeholder" => "$emailmsg"]) ?>
            <?= Html::Submitbutton(Yii::t('common', 'send_email'),
                ['id'=>'sendEmailBtn','class'=>'btn btn-info'])?>

            <?php ActiveForm::end(); ?>
            <a href="#" class="pull-right" id="returnLogin"><?=Yii::t('common','back_button')?></a>
         </div>
    </div>
    <div class="row footer">
        <div class="foot_info">
            <p style="padding-top: 15px"><?=Yii::t('system','version_info');?> <?=Yii::t('system','version_no')?>：<?= Yii::$app->version ?></p>
        </div>
        <? if(count($languageModel)>1){?>

        <select class="lang_select" onchange="window.location=this.value;" name="select" style="position: relative; top: 9px;">
            <? foreach($languageModel as $k=>$v){?>
            <? if($v->status == 1){?>
                <option value="<?= Yii::$app->urlManager->createUrl(['site/change-lang','lang'=>$v->dictionary_code,'url'=>'http://'.$_SERVER['HTTP_HOST'].'/site/login.html']); ?>" <? if(!empty($_REQUEST['lang'])){if($_REQUEST['lang'] == $v->dictionary_code){echo 'selected=""';}}?> ><?=Yii::t('data',$v->i18n_flag)?></option>
            <?}?>
            <?}?>
        </select>
        <?}?>

    </div>
</div>
<script src='/static/api/dist/js/jquery.min.js'></script>
<script type="text/javascript">
    $(document).ready(function () {
        $('#forgetPassword').bind('click', function () {
            $('.mainContent').addClass('hide')
            $('.passwordPanel').removeClass('hide')
        });

        $('#returnLogin').bind('click', function () {
            $('.mainContent').removeClass('hide')
            $('.passwordPanel').addClass('hide')
        });

        var version = navigator.userAgent;
        if (version.indexOf('iPhone') > -1 || version.indexOf('iPad') > -1 || version.indexOf('Android') > -1) {
            console.log('<?=Yii::t('frontend','ios_android_platform')?>');
            $('.clientBar').attr('href', '/install/index.html');
        } else {
            console.log("<?=Yii::t('frontend','other_platform')?>");
            $('.clientBar').attr('href', '###');
        }

    });

</script>

