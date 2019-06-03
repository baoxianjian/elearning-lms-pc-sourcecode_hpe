<?php

use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\framework\FwUser */
/* @var $form yii\widgets\ActiveForm */
?>

<script>

    var operation = '';

    var formId = "clientform-update";

    $("#"+formId).on("submit", function(event) {
        event.preventDefault();
        var validateResult = $("#"+formId).data('yiiActiveForm').validated;
        //alert("validateResult:" + validateResult);
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
                //alert('update');
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

<div class="use-position-form">
    <?php $form = ActiveForm::begin([
        'id' => 'clientform-update',
        'enableAjaxValidation' => false,
        'enableClientValidation' => true,
//        'validateOnSubmit' => true
    ]); ?>

    <table class="kv-grid-table table table-bordered table-striped">
        <thead>
            <tr>
                <th><? echo Html::checkbox('checkAllbox',false,['onclick'=>'checkAll(this.checked,"user_role[]");'])?> <?=Yii::t('backend','role_to_choose')?>：</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    <?  echo Html::checkBoxList('user_role[]', $selected_keys, $availableList,['separator'=>'</br>']);?>
                </td>
            </tr>
        </tbody>
    </table>
    <?php ActiveForm::end(); ?>

</div>
