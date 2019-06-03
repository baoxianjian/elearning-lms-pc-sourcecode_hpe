<?php

use common\helpers\TArrayHelper;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\framework\FwTag */
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
            <td>
                <?= $form->field($model, 'tag_category_id')
                    ->dropDownList(ArrayHelper::map($tagCategoryModel,'kid', 'cate_name'),
                    ['prompt'=>Yii::t('common','select_more'),
                    ]) ?>
            </td>
        </tr>
        <tr>
            <td>
                <?= $form->field($model, 'company_id')
                    ->dropDownList(ArrayHelper::map($companyModel, 'kid', 'company_name'),
                        ['prompt'=>Yii::t('common','select_more')]) ?>
            </td>
        </tr>
        <tr>
            <td>
                <?= $form->field($model, 'tag_value')->textInput(['maxlength' => 500]) ?>
            </td>
        </tr>
    </table>


<!--    <div class="form-group">-->
<!--        --><?//= Html::submitButton($model->isNewRecord ? Yii::t('app', 'create') : Yii::t('app', 'update'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
<!--    </div>-->

    <?php ActiveForm::end(); ?>

</div>
