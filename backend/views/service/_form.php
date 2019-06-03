<?php
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
/* @var $this yii\web\View */
/* @var $model common\models\learning\LnComponent */
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
    ]); ?>
    <table width="100%" border="0">
        <tr>
            <td>
                <?= $form->field($model, 'service_status')->radioList([
                    '0'=>Yii::t('common', 'change_status_stop'),
                    '1'=>Yii::t('common', 'change_status_start')],
                    ['separator'=>'&nbsp;&nbsp;']) ?>
            </td>
        </tr>
        <tr>
            <td>
                <?= $form->field($model, 'service_name')->textInput(['maxlength' => 50]) ?>
            </td>

        </tr>
        <tr>
            <td>
                <?= $form->field($model, 'service_code')->textInput(['maxlength' => 50]) ?>
            </td>
            <!--<td>?= $form->field($model, 'component_model')->textInput(['maxlength' => 50]) ?</td>-->
        </tr>
        <tr>
            <td>
                <?= $form->field($model, 'is_log')->radioList([
                    '0'=>Yii::t('common', 'no'),
                    '1'=>Yii::t('common', 'yes')],
                    ['separator'=>'&nbsp;&nbsp;']) ?>
            </td>
        </tr>
        <tr>
            <td>
                <?= $form->field($model, 'is_allow_restart')->radioList([
                    '0'=>Yii::t('common', 'no'),
                    '1'=>Yii::t('common', 'yes')],
                    ['separator'=>'&nbsp;&nbsp;']) ?>
            </td>
        </tr>
        <tr>
            <td>
                <?= $form->field($model, 'restart_cycle')->radioList([
                    '0'=>Yii::t('common', 'restart_cycle_none'),
                    '1'=>Yii::t('common', 'restart_cycle_year'),
                    '2'=>Yii::t('common', 'restart_cycle_month'),
                    '3'=>Yii::t('common', 'restart_cycle_day')],
                    ['separator'=>'&nbsp;&nbsp;']) ?>
            </td>
        </tr>
        <tr>
            <td>
                <?= $form->field($model, 'service_type')->radioList([
                    '0'=>Yii::t('common', 'service_type_normal'),
                    '1'=>Yii::t('common', 'service_type_report')],
                    ['separator'=>'&nbsp;&nbsp;']) ?>
            </td>
        </tr>
        <tr>
            <td>
                <?= $form->field($model, 'run_at')->textInput(['maxlength' => 100]) ?>
            </td>
            <!--<td>?= $form->field($model, 'component_model')->textInput(['maxlength' => 50]) ?</td>-->
        </tr>
        <tr>
            <td>
                <?= $form->field($model, 'description')->textarea(['rows' => 6, 'maxlength' => 5000]) ?>
            </td>
        </tr>
    </table>
    <?php ActiveForm::end(); ?>

</div>