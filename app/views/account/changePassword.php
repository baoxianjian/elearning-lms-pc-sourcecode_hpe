<?php
/**
 * Created by PhpStorm.
 * User: chenli
 * Date: 7/9/15
 * Time: 10:44 AM
 */
use yii\helpers\Html;
use yii\widgets\ActiveForm;
?>

<div class="container">
    <div class="row">
        <div class="courseInfo">
            <div class="tab-content">
                <div role="tabpanel" class="tab-pane active" id="course_info">
                    <div class=" panel-default scoreList">
                        <div class="panel-body">
                            <div class="infoBlock newStyle">
                                <h4>密码信息</h4>
                                <hr/>
                                <?php $form = ActiveForm::begin([
                                    'id' => 'changepasswordform',
                                    'method' => 'post',
                                    'enableAjaxValidation' => false,
                                    'enableClientValidation' => true,
                                    'action' => Yii::$app->urlManager->createUrl('account/change-password'),]);
                                ?>
                                <div class="row">
                                    <div class="col-md-6 col-sm-6">
                                        <div class="form-group form-group-sm">
                                            <label class="col-sm-3 control-label">旧密码</label>
                                            <div class="col-sm-9">
                                                <?= $form->field($changeModel, 'password_origin')->passwordInput(['maxlength' => 255])->label('')->textInput(['class'=>'form-control'],['id'=>'formGroupInputSmall']) ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-sm-6">
                                        <div class="form-group form-group-sm">
                                            <label class="col-sm-3 control-label">新密码</label>
                                            <div class="col-sm-9">
                                                <?= $form->field($changeModel, 'password_new')->passwordInput(['maxlength' => 255])->label('')->textInput(['class'=>'form-control'],['id'=>'formGroupInputSmall']) ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 col-sm-6">
                                        <div class="form-group form-group-sm">
                                            <label class="col-sm-3 control-label">确认密码</label>
                                            <div class="col-sm-9">
                                                <?= $form->field($changeModel, 'password_repeat')->passwordInput(['maxlength' => 255])->label('')->textInput(['class'=>'form-control'],['id'=>'formGroupInputSmall']) ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row centerContainer">
                                    <?=
                                    Html::submitButton(Yii::t('frontend', '提交'),
                                        ['id' => 'updateBtn', 'class' => 'btn btn-success btn-md centerBtn'])
                                    ?>
                                    <?php ActiveForm::end(); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>