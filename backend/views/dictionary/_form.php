<?php

use common\helpers\TArrayHelper;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\framework\FwDictionary */
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

    function GetSequenceNumber(dictionaryCategoryId)
    {
        if (dictionaryCategoryId == "")
        {
            $('#fwdictionary-sequence_number').val("");
        }
        else {
            var url = "<?=Url::toRoute(['dictionary/sequence-number']); ?>";
            url = urlreplace(url, 'dictionaryCategoryId', dictionaryCategoryId);
            var treeNodeKid=$("#selectNodeId").val();
            if (treeNodeKid) {
                url = urlreplace(url, 'treeNodeKid', treeNodeKid);
            }
            var method = 'POST';
            var dataType = 'json';
            var keys = '';
            ajaxData(url, method, keys, dataType, function (data) {

                if (data.result === 'failure') {
                    var msg = "<?=Yii::t('common','operation_confirm_warning_failure'); ?>";
                    NotyWarning(msg);
                }
                else {
                    $('#fwdictionary-sequence_number').val(data.sequenceNumber);
                }

                return false;
            });
        }
    }


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
                <?= $form->field($model, 'dictionary_category_id')->dropDownList(ArrayHelper::map($dictionaryCategoryModel,'kid', 'cate_name'),
                    ['prompt'=>Yii::t('common','select_more'),
                        'onchange'=>'GetSequenceNumber($(this).val());',
                    ]) ?>
            </td>
            <td>
                <?= $form->field($model, 'dictionary_code')->textInput(['maxlength' => 30]) ?>
            </td>
        </tr>
        <tr>
            <td>
                <?= $form->field($model, 'dictionary_name')->textInput(['maxlength' => 50]) ?>
            </td>
            <td>
                <?= $form->field($model, 'dictionary_value')->textInput(['maxlength' => 500]) ?>
            </td>
        </tr>
        <tr>
            <td>
                <?= $form->field($model, 'sequence_number')->textInput() ?>
            </td>
            <td>
                <?= $form->field($model, 'i18n_flag')->textInput(['maxlength' => 50]) ?>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <?= $form->field($model, 'description')->textarea(['rows' => 6, 'maxlength' => 5000]) ?>
            </td>
        </tr>
    </table>


<!--    <div class="form-group">-->
<!--        --><?//= Html::submitButton($model->isNewRecord ? Yii::t('app', 'create') : Yii::t('app', 'update'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
<!--    </div>-->

    <?php ActiveForm::end(); ?>

</div>
