<?php

use common\helpers\TArrayHelper;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\framework\FwCompanySetting */
/* @var $form yii\widgets\ActiveForm */
?>
<script>
    function GetDefaultValue(dictionaryCode)
    {
        if (dictionaryCode == "")
        {
            $('#fwcompanysetting-value').val("");
        }
        else {
            var url = "<?=Url::toRoute(['company-setting/default-value']); ?>";
            url = urlreplace(url, 'dictionaryCode', dictionaryCode);
            var method = 'POST';
            var dataType = 'json';
            var keys = '';
            ajaxData(url, method, keys, dataType, function (data) {

                if (data.result === 'failure') {
                    var msg = "<?=Yii::t('common','operation_confirm_warning_failure'); ?>";
                    NotyWarning(msg);
                }
                else {
                    $('#fwcompanysetting-value').val(data.defaultValue);
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
        //alert("validateResult:" + validateResult);
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

<div class="company-setting-form">
    <?php $form = ActiveForm::begin([
        'id'=>'clientform-'. $formType,
//        'action' => 'a',
//        'enableAjaxValidation' => false,
//        'enableClientValidation' => true,
    ]); ?>

    <table width="100%" border="0">
        <tr>
            <td>
                <?= $form->field($model, 'code')
                    ->dropDownList(ArrayHelper::map($dictionaryModel,'dictionary_code', 'dictionary_name_i18n'),
                    ['prompt'=>Yii::t('common','select_more'),
                        'onchange'=>'GetDefaultValue($(this).val());',
                    ]) ?>
            </td>
        </tr>
        <tr>
            <td>
                <?= $form->field($model, 'value')->textInput(['maxlength' => 500]) ?>
            </td>
        </tr>
    </table>
    <?php ActiveForm::end(); ?>

</div>
