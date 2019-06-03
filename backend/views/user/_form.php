<?php

use common\helpers\TArrayHelper;
use components\widgets\TDatePicker;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\framework\FwUser */
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
        else{
            TabShow(0);
        }
    });

    function FormSubmit()
    {
        $("#"+formId).submit();
    }

</script>

<div class="eln-user-form">

    <?php $form = ActiveForm::begin([
        'id' => 'clientform-' . $formType,
//        'enableAjaxValidation' => false,
//        'enableClientValidation' => true,
//        'validateOnSubmit' => true
    ]); ?>

    <table width="100%" border="0">
        <tr>
            <td>

                <?php if ($model->isNewRecord) :?>
                    <?= $form->field($model, 'user_name')->textInput(['maxlength' => 255]) ?>
                <?php else:?>
                    <?= $form->field($model, 'user_name')->textInput(['readonly' => 'readonly']) ?>
                <?php endif?>
            </td>
            <td>
                <?= $form->field($model, 'real_name')->textInput(['maxlength' => 255]) ?>
            </td>
        </tr>
        <tr>
            <td>
                <?= $form->field($model, 'password_hash')->passwordInput(['maxlength' => 255]) ?>
            </td>
            <td>
                <?= $form->field($model, 'password_repeat')->passwordInput(['maxlength' => 255]) ?>
            </td>
        </tr>
        <tr>
            <td>
                <?= $form->field($model, 'email')->textInput(['maxlength' => 255]) ?>
            </td>
            <td>
                <?= $form->field($model, 'email_repeat')->textInput(['maxlength' => 255]) ?>
            </td>
        </tr>
        <tr>
            <td>
                <?= $form->field($model, 'nick_name')->textInput(['maxlength' => 255]) ?>
            </td>
            <td >
                <?= $form->field($model, 'gender')->radioList(ArrayHelper::map($genderModel,'dictionary_value', 'dictionary_name_i18n'),
                    ['prompt'=> Yii::t('backend','select_more'),'separator'=>'&nbsp;&nbsp;']) ?>
            </td>
        </tr>
        <tr>
            <td width="50%">
                <?= $form->field($model, 'birthday')->widget(TDatePicker::classname(),['readonly' => 'readonly']); ?>
            </td>
            <td>
                <?= $form->field($model, 'id_number')->textInput(['maxlength' => 50]) ?>
            </td>
        </tr>
        <tr>
            <td>
                <?= $form->field($model, 'domain_id')
//                    ->label(Yii::t('common','relate_{value}',['value'=>Yii::t('common','domain')]))
                    ->dropDownList(ArrayHelper::map($domainModel,'kid', 'fwTreeNode.tree_node_name'),
                        ['prompt'=>Yii::t('backend','select_more'),'id'=>'domain_id']) ?>
            </td>
            <td>
                <?= $form->field($model, 'manager_flag')->radioList(
                    ['0'=>Yii::t('backend','no'),'1'=>Yii::t('backend','yes')],
                    ['prompt'=> Yii::t('backend','select_more'),'separator'=>'&nbsp;&nbsp;']) ?>
            </td>
        </tr>
        <tr>
            <td>
                <?= $form->field($model, 'mobile_no')->textInput(['maxlength' => 30]) ?>
            </td>
            <td>
                <?= $form->field($model, 'home_phone_no')->textInput(['maxlength' => 30]) ?>
            </td>
        </tr>
        <tr>
            <td>
                <?= $form->field($model, 'telephone_no')->textInput(['maxlength' => 30]) ?>
            </td>
            <td>
                <?= $form->field($model, 'employee_status')->dropDownList(ArrayHelper::map($employeeStatusModel,'dictionary_value', 'dictionary_name'),
                    ['prompt'=> Yii::t('backend','select_more'),'separator'=>'&nbsp;&nbsp;']) ?>
            </td>
        </tr>
        <tr>
            <td width="50%">
                <?= $form->field($model, 'onboard_day')->widget(TDatePicker::classname(),['readonly' => 'readonly']); ?>
            </td>
            <td>
                <?= $form->field($model, 'work_place')->dropDownList(ArrayHelper::map($workPlaceModel,'dictionary_value', 'dictionary_name'),
                    ['prompt'=> Yii::t('backend','select_more'),'separator'=>'&nbsp;&nbsp;']) ?>
            </td>
        </tr>
        <tr>
            <td>
                <?= $form->field($model, 'rank')->textInput(['maxlength' => 30]) ?>
            </td>
            <td>
                <?= $form->field($model, 'position_mgr_level')->dropDownList(ArrayHelper::map($positionMgrLevelModel,'dictionary_value', 'dictionary_name'),
                    ['prompt'=> Yii::t('backend','select_more'),'separator'=>'&nbsp;&nbsp;']) ?>
            </td>
        </tr>
        <tr>
            <td>
                <?= $form->field($model, 'location')->dropDownList(ArrayHelper::map($locationModel,'dictionary_code', 'dictionary_name_i18n'),
                    ['prompt'=> Yii::t('backend','select_more'),'separator'=>'&nbsp;&nbsp;']) ?>
            </td>
            <td>
                <?= $form->field($model, 'user_no')->textInput(['maxlength' => 30]) ?>
            </td>
        </tr>
        <tr>
            <td>
                <?= $form->field($model, 'valid_start_at')->widget(TDatePicker::classname(),['readonly' => 'readonly']); ?>
            </td>
            <td>
                <?= $form->field($model, 'valid_end_at')->widget(TDatePicker::classname(),['readonly' => 'readonly']); ?>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <?= $form->field($model, 'description')->textarea(['rows' => 6, 'maxlength' => 5000]) ?>
            </td>
        </tr>
        <tr>
            <td>
                <?= $form->field($model, 'theme')->dropDownList(ArrayHelper::map($themeModel, 'dictionary_value', 'dictionary_name_i18n'),
                    ['prompt' => Yii::t('backend', 'select_more')]) ?>
            </td>
            <td>
                <?= $form->field($model, 'language')->dropDownList(ArrayHelper::map($languageModel, 'dictionary_value', 'dictionary_name_i18n'),
                    ['prompt' => Yii::t('backend', 'select_more')]) ?>
            </td>
        </tr>
        <tr>
            <td>
                <?= $form->field($model, 'sequence_number')->textInput() ?>
            </td>
        </tr>
<!--        <tr>-->
<!--            <td>-->
<!--                --><?//= $form->field($model, 'timezone')->dropDownList(ArrayHelper::map($timezoneModel, 'dictionary_value', 'dictionary_name_i18n'),
//                    ['prompt' => Yii::t('common', 'select_more')]) ?>
<!--            </td>-->
<!--            <td>-->
<!--                &nbsp;-->
<!--            </td>-->
<!--        </tr>-->
    </table>

    <?php ActiveForm::end(); ?>

</div>
