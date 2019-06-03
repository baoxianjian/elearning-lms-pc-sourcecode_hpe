<?php

use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\framework\FwTagCategory */
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
                submitModalForm("",formId,"addModal",false,true);
            }
            else if (operation == 'saveclose')
            {
                submitModalForm("",formId,"addModal",true,true);
            }
            else if (operation == 'update')
            {
                submitModalForm("",formId,"updateModal",true,true);
            }
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
            <td width="50%">
                <?= $form->field($model, 'cate_code')->textInput(['maxlength' => 30]) ?>
            </td>
            <td>
                <?= $form->field($model, 'cate_name')->textInput(['maxlength' => 50]) ?>
            </td>
        </tr>
        <tr>
            <td>
                <?= $form->field($model, 'limitation')->radioList([
                    'N'=>Yii::t('common', 'limitation_none'),
                    'R'=>Yii::t('common', 'limitation_readonly'),
                    'U'=>Yii::t('common', 'limitation_onlyname'),
                    'H'=>Yii::t('common', 'limitation_hidden')],
                    ['separator'=>'&nbsp;&nbsp;']) ?>
            </td>
            <td>
                <?= $form->field($model, 'sequence_number')->textInput() ?>
            </td>
        </tr>
    </table>


<!--    <div class="form-group">-->
<!--        --><?//= Html::submitButton($model->isNewRecord ? Yii::t('app', 'create') : Yii::t('app', 'update'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
<!--    </div>-->

    <?php ActiveForm::end(); ?>

</div>
