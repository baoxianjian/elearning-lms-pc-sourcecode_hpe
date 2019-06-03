<?php

use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\framework\FwCompanyMenu */
/* @var $form yii\widgets\ActiveForm */
?>

<script>

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

    });

    function FormSubmit()
    {
        $("#"+formId).submit();
    }

</script>

<div class="eln-wechat-template-form">

    <?php $form = ActiveForm::begin([
        'id' => 'clientform-' . $formType
//        'enableAjaxValidation' => false,
//        'enableClientValidation' => true,
//        'validateOnSubmit' => true
    ]); ?>
    <table width="100%" border="0">
        <tr>
            <td>
                <?= $form->field($model, 'menu_code')->textInput(['maxlength' => 50]) ?>
            </td>
            <td>
                <?= $form->field($model, 'menu_name')->textInput(['maxlength' => 50]) ?>
            </td>
        </tr>
        <tr>
            <td>
                <?= $form->field($model, 'parent_menu_id')->textInput(['maxlength' => 50]) ?>
            </td>
            <td>
                <?= $form->field($model, 'menu_type')->dropDownList([
                    'portal'=>Yii::t('common', 'menu_type_portal'),
                    'report'=>Yii::t('common', 'menu_type_report'),
                    'tool-box'=>Yii::t('common','menu_type_tool_box'),
                    'portal-menu'=>Yii::t('common','menu_type_portal_menu')],
                    ['separator'=>'&nbsp;&nbsp;']) ?>
            </td>
        </tr>
        <tr>
            <td>
                <?= $form->field($model, 'action_url')->textInput(['maxlength' => 500]) ?>
            </td>
            <td>
                <?= $form->field($model, 'action_parameter')->textInput(['maxlength' => 500]) ?>
            </td>
        </tr>
        <tr>
            <td>
                <?= $form->field($model, 'action_type')->radioList([
                    '0'=>Yii::t('common', 'action_type_url'),
                    '1'=>Yii::t('common', 'action_type_action')],
                    ['separator'=>'&nbsp;&nbsp;']) ?>
            </td>
            <td>
                <?= $form->field($model, 'action_target')->textInput(['maxlength' => 50]) ?>
            </td>
        </tr>
        <tr>
            <td>
                <?= $form->field($model, 'action_icon')->textInput(['maxlength' => 50]) ?>
            </td>
            <td>
                <?= $form->field($model, 'action_class')->textInput(['maxlength' => 50]) ?>
            </td>
        </tr>
        <tr>
            <td>
                <?= $form->field($model, 'action_tip')->textInput(['maxlength' => 50]) ?>
            </td>
            <td>
                <?= $form->field($model, 'sequence_number')->textInput() ?>
            </td>
        </tr>
        <tr>
            <td>
                <?= $form->field($model, 'i18n_flag')->textInput(['maxlength' => 50]) ?>
            </td>
            <td>
               
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <?= $form->field($model, 'description')->textarea(['rows' => 6, 'maxlength' => 5000]) ?>
            </td>
        </tr>
    </table>

    <?php ActiveForm::end(); ?>

</div>

