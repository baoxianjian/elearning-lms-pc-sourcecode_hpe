<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\treemanager\FwTreeNode */
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
                submitModalFormWithOther("",formId,"addModal",false,true);
            }
            else if (operation == 'saveclose')
            {
                submitModalFormWithOther("",formId,"addModal",true,true);
            }
            else if (operation == 'update')
            {
                submitModalFormWithOther("",formId,"updateModal",true,true);
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
        'id' => 'clientform-' . $formType,
//        'enableAjaxValidation' => false,
//        'enableClientValidation' => true,
//        'validateOnSubmit' => true
    ]); ?>

<!--    --><?php //echo $form->errorSummary($model); ?>

<!--    --><?//= $form->field($model, 'kid')->textInput() ?>
<!--    --><?//= Html::hiddenInput("treeNodeId",$model->kid,['id'=>'treeNodeId'])?>
<!--        --><?//= $form->field($model, 'kid')->hiddenInput()->label('')?>


    <?php if ($model->fwTreeType->limitation == 'U' || $model->fwTreeType->code_gen_way == "0") :?>
        <?= $form->field($model, 'tree_node_code')->textInput(['readonly' => 'readonly'])?>
    <?php else:?>
        <?= $form->field($model, 'tree_node_code')->textInput(['maxlength' => 30]) ?>
    <?php endif?>

    <?= $form->field($model, 'tree_node_name')->textInput(['maxlength' => 50]) ?>

    <?= $form->field($model, 'display_number')->textInput() ?>

    <?php if ($model->fwTreeType->limitation == 'U' || !$isSpecialUser) :?>
<!--        --><?//= $form->field($model, 'sequence_number')->hiddenInput()?>
    <?php else:?>
        <?= $form->field($model, 'sequence_number')->textInput() ?>
    <?php endif?>

<!--    <div class="form-group">-->
<!--        --><?//= Html::submitButton($model->isNewRecord ? Yii::t('app', 'create') : Yii::t('app', 'update'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
<!--    </div>-->

    <?php ActiveForm::end(); ?>

</div>
