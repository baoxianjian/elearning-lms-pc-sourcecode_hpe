<?php

use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\framework\Position */
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

<div class="eln-position-form">

    <?php $form = ActiveForm::begin([
        'id' => 'clientform-' . $formType,
//        'enableAjaxValidation' => false,
//        'enableClientValidation' => true,
//        'validateOnSubmit' => true
    ]); ?>

    <table width="100%" border="0">
        <tr>
            <td>
                <?php if ($model->limitation == 'U') :?>
                    <?= $form->field($model, 'position_code')->textInput(['readonly' => 'readonly'])?>
                <?php else:?>
                    <?= $form->field($model, 'position_code')->textInput(['maxlength' => 50]) ?>
                <?php endif?>
            </td>
            <td>
                <?= $form->field($model, 'position_name')->textInput(['maxlength' => 50]) ?>
            </td>
        </tr>
        <tr>
            <td>
                <?php if ($model->limitation == 'U') :?>
                    <?= $form->field($model, 'position_type')->textInput(['readonly' => 'readonly'])?>
                <?php else:?>
                    <?= $form->field($model, 'position_type')->textInput(['maxlength' => 50]) ?>
                <?php endif?>
            </td>
            <td>
                <?php if ($model->limitation == 'U') :?>
                    <?= $form->field($model, 'position_level')->textInput(['readonly' => 'readonly'])?>
                <?php else:?>
                    <?= $form->field($model, 'position_level')->textInput(['maxlength' => 50]) ?>
                <?php endif?>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <?php if ($model->limitation == 'U') :?>
                    <?= $form->field($model, 'responsibilities')->textarea(['readonly' => 'readonly', 'rows' => 6, 'maxlength' => 5000]) ?>
                <?php else:?>
                    <?= $form->field($model, 'responsibilities')->textarea(['rows' => 6, 'maxlength' => 5000]) ?>
                <?php endif?>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <?php if ($model->limitation == 'U') :?>
                    <?= $form->field($model, 'capabilities')->textarea(['readonly' => 'readonly', 'rows' => 6, 'maxlength' => 5000]) ?>
                <?php else:?>
                    <?= $form->field($model, 'capabilities')->textarea(['rows' => 6, 'maxlength' => 5000]) ?>
                <?php endif?>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <?php if ($model->limitation == 'U') :?>
                    <?= $form->field($model, 'description')->textarea(['readonly' => 'readonly', 'rows' => 6, 'maxlength' => 5000]) ?>
                <?php else:?>
                    <?= $form->field($model, 'description')->textarea(['rows' => 6, 'maxlength' => 5000]) ?>
                <?php endif?>
            </td>
        </tr>
    </table>

    <?php ActiveForm::end(); ?>

</div>
