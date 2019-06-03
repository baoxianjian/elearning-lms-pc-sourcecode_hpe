<?php
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use yii\captcha\Captcha;


?>
<div id="wrapper">
    <!-- Navigation -->
    <nav class="navbar navbar-default navbar-static-top" role="navigation" style="margin-bottom: 0">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
                <span class="sr-only"><?=Yii::t('common','toggle_menu')?></span>
<!--                <span class="icon-bar"></span> -->
<!--                <span class="icon-bar"></span> -->
<!--                <span class="icon-bar"></span> -->
            </button>
            <a class="navbar-brand" href="<?=Yii::$app->urlManager->createUrl('index/index')?>"><?= Yii::t('system','backend_name')?></a>
        </div>
    </nav>
    <!-- /.row -->
    <div class="container">
        <div class="row">
            <div class="col-md-6 col-md-offset-3">
                <div class="login-panel panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title"><?=Yii::t('common','login')?></h3>
                    </div>
                    <div class="panel-body">
                        <?php $form = ActiveForm::begin(['id' => 'login-form',
                            'method' => 'post',
                        ]); ?>
                        <?= $form->field($model, 'remember_time')->hiddenInput(array('value' => '1')); ?>
                        <table width="100%">
                            <tr>
                                <td colspan="3">
                                    <?= $form->field($model, 'user_name')->textInput(["placeholder" => Yii::t('common','user_name'),'maxlength' => 255]); ?>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="3">
                                    <?= $form->field($model, 'password')->passwordInput(['placeholder' => Yii::t('common','password'),'maxlength' => 255]); ?>
                                </td>
                            </tr>
                            <tr>
                                <td width="120px">
<!--                -->             <?//= $form->field($model, 'remember_time')->dropDownList(array('1' => '保存一天', '7' => '保存一周', '30' => '保存一月')); ?>
                                    <?= $form->field($model, 'remember_me')->checkbox()->label(Yii::t('common','remember_me_one_day')); ?>
                                </td>
                                <td valign="top" align="right">
                                   <!--
                                    <div style="margin-top: 18px">
                                        <a href="<?=Yii::$app->urlManager->createUrl('index/request-password-reset')?>"><?= Yii::t('common','forget_password')?></a>
                                    </div>
                                    -->
                                </td>
                            </tr>
                            <tr>
                                <td colspan="3">
                                    <?= Html::submitButton(Yii::t('common','login'), ['class' => 'btn btn-lg btn-success btn-block', 'name' => 'submit-button']) ?>
                                </td>
                            </tr>
                        </table>

                        <?php ActiveForm::end(); ?>
                    </div>
                    <div width="100%" align="right" style="padding-right: 5px">
                        <span id="lblSystemVersion" style="color: grey"><?= Yii::$app->version ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>