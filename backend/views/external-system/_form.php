<?php

use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\framework\FwExternalSystem */
/* @var $form yii\widgets\ActiveForm */
?>
<script type="text/javascript">
    $(document).ready(function(){
//        $("#clientform").submit(function() {
//            var url = $("#clientform").attr("action");
//            var method = $("#clientform").attr("method");
//            //alert(url);
//            $.ajax({
//                url: url,
//                cache: true,
//                type: method,
//                dataType: 'json',
//                data: null,
//                async: false,
//                success: function(data)
//                {
//                    alert(data.result); // show response from the php script.
//                }
//            });
//            return false;
//        });
    });

    var operation = '';

    var formId = "clientform-"+"<?=$formType?>";

    $("#"+formId).on("submit", function(event) {
        event.preventDefault();
        var validateResult = $("#"+formId).data('yiiActiveForm').validated;
        if (validateResult == true) {
           // alert("validateResult:" + validateResult);
            if (operation == 'savecontinue')
            {
                submitModalFormCustomized("",formId,"addModal",false,true);
            }
            else if (operation == 'saveclose')
            {
                submitModalFormCustomized("",formId,"addModal",true,true);
            }
            else if (operation == 'update')
            {
                submitModalFormCustomized("",formId,"updateModal",true,true);
            }
        }
        else{
            TabShow(0);
        }

    });

    function FormSubmit()
    {
        $("#"+formId).submit();
    }



</script>
<div class="clientform-div">

    <?php $form = ActiveForm::begin([
        'id'=>'clientform-'. $formType,
//        'action' => 'a',
//        'enableAjaxValidation' => false,
//        'enableClientValidation' => true,
    ]); ?>

<!--    --><?php //echo $form->errorSummary($model); ?>
    <table width="100%" border="0">
        <tr>
            <td>
                <?= $form->field($model, 'system_code')->textInput(['maxlength' => 50]) ?>
            </td>
            <td>
                <?= $form->field($model, 'system_name')->textInput(['maxlength' => 50]) ?>
            </td>
        </tr>
        <tr>
            <td>
                <?= $form->field($model, 'system_key_is_single')->radioList([
                    '0'=>Yii::t('common', 'no'),
                    '1'=>Yii::t('common', 'yes')],
                    ['separator'=>'&nbsp;&nbsp;']) ?>
            </td>
            <td>
                <?= $form->field($model, 'system_key')->textInput(['maxlength' => 50]) ?>
            </td>
        </tr>
        <tr>
            <td>
                <?= $form->field($model, 'encoding_key')->textInput(['encoding_key' => 50]) ?>
            </td>
            <td>
                <?= $form->field($model, 'service_mode')->radioList([
                    '0'=>Yii::t('common', 'service_mode_server'),
                    '1'=>Yii::t('common', 'service_mode_client'),
                    '2'=>Yii::t('common', 'service_mode_both')],
                    ['separator'=>'&nbsp;&nbsp;']) ?>
            </td>
        </tr>
        <tr>
            <td>
                <?= $form->field($model, 'security_mode')->radioList([
                    '0'=>Yii::t('common', 'security_mode_plain'),
                    '1'=>Yii::t('common', 'security_mode_encrypt')],
                    ['separator'=>'&nbsp;&nbsp;']) ?>
            </td>
            <td>
                <?= $form->field($model, 'encrypt_mode')->radioList([
                    '0'=>Yii::t('common', 'encrypt_mode_none'),
                    '1'=>Yii::t('common', 'encrypt_mode_aes'),
                    '2'=>Yii::t('common', 'encrypt_mode_des')],
                    ['separator'=>'&nbsp;&nbsp;']) ?>
            </td>
        </tr>
        <tr>
            <td>
                <?= $form->field($model, 'user_name')->textInput(['maxlength' => 50]) ?>
            </td>
            <td>
                <?= $form->field($model, 'password')->textInput(['maxlength' => 50]) ?>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <?= $form->field($model, 'api_address')->textInput(['api_address' => 255]) ?>
            </td>
        </tr>
        <tr>
            <td>
                <?= $form->field($model, 'status')->radioList([
                    '1'=>Yii::t('common', 'status_normal'),
                    '2'=>Yii::t('common', 'status_stop')],
                    ['separator'=>'&nbsp;&nbsp;']) ?>
            </td>
            <td>
                <?= $form->field($model, 'token_expire')->textInput() ?>
            </td>
        </tr>
        <tr>
            <td>
                <?= $form->field($model, 'duration')->textInput() ?>
            </td>
            <td>
                <?= $form->field($model, 'limit_count')->textInput() ?>
            </td>
        </tr>
        <tr>
            <td>
                <?= $form->field($model, 'memo1')->textInput(['maxlength' => 50]) ?>
            </td>
            <td>
                <?= $form->field($model, 'memo2')->textInput(['maxlength' => 50]) ?>
            </td>
        </tr>
        <tr>
            <td>
                <?= $form->field($model, 'memo3')->textInput(['maxlength' => 50]) ?>
            </td>
            <td>
            </td>
        </tr>
    </table>

    <?php ActiveForm::end(); ?>

</div>
