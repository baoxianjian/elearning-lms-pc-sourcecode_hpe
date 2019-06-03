<?php
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model \common\viewmodels\framework\PasswordResetRequestForm */

?>
<script>


    function BackButton()
    {
        var url = "<?=Yii::$app->urlManager->createAbsoluteUrl('index/login')?>";

        window.location.href = url;
    }
</script>

<div id="wrapper">
    <!-- Navigation -->
    <nav class="navbar navbar-default navbar-static-top" role="navigation" style="margin-bottom: 0">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse"><span
                    class="sr-only"><?=Yii::t('common','toggle_menu')?></span> <span class="icon-bar"></span> <span
                    class="icon-bar"></span> <span class="icon-bar"></span></button>
            <a class="navbar-brand" href="<?=Yii::$app->urlManager->createUrl('index/index')?>"><?= Yii::t('common','backend_name')?></a>
        </div>
    </nav>
    <!-- /.row -->
    <div class="container">
        <div class="row">
            <div class="col-md-6 col-md-offset-3">
                <div class="login-panel-without-animation panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title"><?=Yii::t('backend','please_reset_password')?></h3>
                    </div>
                    <div class="panel-body">
                        <p><?=Yii::t('backend','please_write_down_your_email')?></p>
                        <?php $form = ActiveForm::begin(['id' => 'request-password-reset-form']); ?>
                        <table width="100%">
                            <tr>
                                <td >
                                    <?= $form->field($model, 'email')->textInput(['id' => 'txtEmail',"placeholder" => Yii::t('common','email')]) ?>
                                </td>
                            </tr>

                            <tr>
                                <td >
                                    <?= Html::Submitbutton(Yii::t('common', 'send_email'),
                                    ['id'=>'sendEmailBtn','class'=>'btn btn-primary'])?>
                                    <?= Html::button(Yii::t('common', 'back_button'),
                                        ['id'=>'backBtn','class'=>'btn btn-default','onclick'=>'BackButton();'])?>
                                </td>
                            </tr>
                            <tr>
                                <td align="center" height="30px" >
                                    <div style="color:red"><?= $model->error_message ?></div>
                                </td>
                            </tr>
                        </table>
                        <?php ActiveForm::end(); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
