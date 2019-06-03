<?php
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model \common\viewmodels\framework\ResetPasswordForm */

?>

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
                        <h3 class="panel-title"><?= Yii::t('backend','reset_pass_button')?></h3>
                    </div>
                    <div class="panel-body">
                        <p><?= $model->user->real_name?>（<?= $model->user->user_name?>）<?= Yii::t('backend','hello_to_write_new_password')?> 。</p>
                        <?php $form = ActiveForm::begin(['id' => 'reset-password-form']); ?>
                        <table width="100%">
                            <tr>
                                <td >
                                    <?= $form->field($model, 'password_new')->passwordInput(["placeholder" => Yii::t('common','password_new')]) ?>
                                </td>
                            </tr>
                            <tr>
                                <td >
                                    <?= $form->field($model, 'password_repeat')->passwordInput(["placeholder" => Yii::t('common','password_repeat')]) ?>
                                </td>
                            </tr>
                            <tr>
                                <td >
                                    <?= Html::SubmitButton(Yii::t('backend', 'update'),
                                        ['id'=>'updateBtn','class'=>'btn btn-primary'])?>
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