<?php
/**
 * Created by PhpStorm.
 * User: LiuCheng
 * Date: 2015/6/25
 * Time: 17:17
 */
use yii\widgets\ActiveForm;
use yii\helpers\Html;

?>
<style>
    #changepasswordform .control-label {
        float: left;
    }

    #changepasswordform .help-block {
        float: left;
        margin-bottom:0px;
        position: relative;
        top: 10px;
        left: 10px;
    }
</style>
<div class=" panel-default scoreList">
    <div class="panel-body courseInfoInput">
        <p style="text-align:center"><?= Yii::t('common', 'rule') ?>：<?= Yii::t('frontend', 'warning_for_password') ?></p>
        <?php $form = ActiveForm::begin([
            'id' => 'changepasswordform',
            'method' => 'post',
        ]); ?>
        <div class="uploadFileTablePW">
            <table class="table">
                <tr>
                    <td><?= Yii::t('frontend', 'password_origin') ?></td>
                    <td>
                        <?= $form->field($model, 'password_old')->passwordInput(['maxlength' => 255])->label('') ?>
                    </td>
                </tr>
                <tr>
                    <td><?= Yii::t('frontend', 'password_new') ?></td>
                    <td>
                        <?= $form->field($model, 'password_hash')->passwordInput(['maxlength' => 255])->label('') ?>
                    </td>
                </tr>
                <tr>
                    <td><?= Yii::t('frontend', 'password_repeat') ?></td>
                    <td>
                        <?= $form->field($model, 'password_repeat')->passwordInput(['maxlength' => 255])->label('') ?>
                    </td>
                </tr>
            </table>
        </div>
        <hr/>
        <div class="centerBtnArea">
        <?=
        Html::submitButton(Yii::t('frontend', 'update'),
            ['id' => 'updateBtn', 'class' => 'btn btn-success pull-right centerBtn','style'=>'width:30%;'])
        ?>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
</div>
<script>
    $("#changepasswordform").on("submit", function (event) {
        event.preventDefault();
        var $form = $(this);
        var validateResult = $form.data('yiiActiveForm').validated;

        var password=$("#fwuser-password_hash").val();

        if (validateResult == true) {
            if(checkPass(password)<2){
                app.showMsg('<?= Yii::t('common', 'password_strength_less') ?>',1500);
                return false ;
            }

            submitModalForm("", "changepasswordform", "", true, false, null, null);
        }
    });

    function checkPass(pass){
        if(pass.length < 6){
            return 0;
        }
        var ls = 0;

        if(pass.match(/([a-zA-z])+/)){
            ls++;
        }

        if(pass.match(/([0-9])+/)){
            ls++;
        }

        if(pass.match(/([\~!@#$\%\^&\*\(\)\_\+\=\-`/.,<>;:'"|\[\]{}\\])+/)){
            ls++;
        }
        return ls
    }
</script>