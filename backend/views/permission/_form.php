<?php

use common\models\framework\FwPermission;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\framework\FwPermission */
/* @var $form yii\widgets\ActiveForm */
?>
<script>

    function validateOtherClientForm()
    {
        return true;
    }
</script>

<div class="permission-form">

    <?php $form = ActiveForm::begin([
        'id' => 'clientform-other',
        'enableAjaxValidation' => false,
        'enableClientValidation' => true,
//        'validateOnSubmit' => true
    ]); ?>


    <table width="100%" border="0">
        <tr>
            <td>
                <?= $form->field($model, 'permission_type')->radioList(
                    [FwPermission::PERMISSION_TYPE_MENU=>Yii::t('backend', 'permission_type_menu'),
                        FwPermission::PERMISSION_TYPE_FUNCTION=>Yii::t('backend', 'permission_type_function')],
                    ['separator'=>'&nbsp;&nbsp;']) ?>
            </td>
            <td>
                <?= $form->field($model, 'action_type')->radioList(
                    [FwPermission::ACTION_TYPE_ACTION=>Yii::t('backend', 'action_type_action'),
                        FwPermission::ACTION_TYPE_URL=>Yii::t('backend', 'action_type_url')],
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
                <?= $form->field($model, 'action_target')->textInput(['maxlength' => 50]) ?>
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
                <?= $form->field($model, 'system_flag')->textInput(['maxlength' => 30]) ?>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <?= $form->field($model, 'description')->textarea(['rows' => 6, 'maxlength' => 5000]) ?>
            </td>
        </tr>
        <tr>
            <td >
                <?= $form->field($model, 'limitation')->radioList([
                    'N'=>Yii::t('backend', 'limitation_none'),
                    'R'=>Yii::t('backend', 'limitation_readonly'),
                    'U'=>Yii::t('backend', 'limitation_onlyname')],
//                    'H'=>Yii::t('backend', 'limitation_hidden'),
                    ['separator'=>'&nbsp;&nbsp;']) ?>
            </td>
            <td>
                <?= $form->field($model, 'is_display')->radioList([
                    '0'=>Yii::t('backend', 'no'),
                    '1'=>Yii::t('backend', 'yes')],
                    ['separator'=>'&nbsp;&nbsp;']) ?>
            </td>
        </tr>
        <tr>
            <td>
                <?= $form->field($model, 'i18n_flag')->textInput(['maxlength' => 50]) ?>
            </td>
            <td>

            </td>
        </tr>
    </table>

    <?php ActiveForm::end(); ?>

</div>
