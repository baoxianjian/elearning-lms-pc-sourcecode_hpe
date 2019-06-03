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

    function GetSequenceNumber(componentType)
    {
        var url = "<?=Url::toRoute(['component/sequence-number']); ?>";
        url = urlreplace(url,'componentType',componentType);
        var method = 'POST';
        var dataType = 'json';
        var keys = '';
        ajaxData(url, method, keys, dataType, function(data){

            if (data.result === 'failure') {
                var msg = "<?=Yii::t('backend','operation_confirm_warning_failure'); ?>";
                NotyWarning(msg);
            }
            else
            {
                $('#sequence_number').val(data.sequenceNumber);
            }

            return false;
        });
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
    ]); ?>
    <table width="100%" border="0">
        <tr>
            <td>
                <?= $form->field($model, 'title')->textInput(['maxlength' => 50]) ?>
            </td>
            <td>
                <?= $form->field($model, 'component_type')->dropDownList([
                    '0'=>Yii::t('backend', 'resource'),
                    '1'=>Yii::t('backend', 'active'),
                    ],['onchange'=>'GetSequenceNumber($(this).val());',
                ]) ?>
            </td>
        </tr>
        <tr>
            <td>
                <?= $form->field($model, 'component_code')->textInput(['maxlength' => 50]) ?>
            </td>
            <!--<td>?= $form->field($model, 'component_model')->textInput(['maxlength' => 50]) ?</td>-->
            <td>
                <?= $form->field($model, 'component_category')->dropDownList([
                    '0'=>Yii::t('backend', 'component_course'),
                    '1'=>Yii::t('backend', 'component_media'),
                    '2'=>Yii::t('backend', 'component_activity'),
                ]) ?>
            </td>
        </tr>
        <tr>
            <td>
                <?= $form->field($model, 'file_type')->textInput(['maxlength' => 255]) ?>
            </td>
            <td>
                <?= $form->field($model, 'is_allow_download')->radioList(
                    ['0'=>Yii::t('backend','no'),'1'=>Yii::t('backend','yes')],
                    ['prompt'=> Yii::t('backend','select_more'),'separator'=>'&nbsp;&nbsp;']) ?>
            </td>
        </tr>
        <tr>
            <td>
                <?= $form->field($model, 'is_display_pc')->radioList(
                    ['0'=>Yii::t('backend','no'),'1'=>Yii::t('backend','yes')],
                    ['prompt'=> Yii::t('common','select_more'),'separator'=>'&nbsp;&nbsp;']) ?>
            </td>
            <td>
                <?= $form->field($model, 'is_display_mobile')->radioList(
                    ['0'=>Yii::t('backend','no'),'1'=>Yii::t('backend','yes')],
                    ['prompt'=> Yii::t('backend','select_more'),'separator'=>'&nbsp;&nbsp;']) ?>
            </td>
        </tr>
        <tr>
            <td>
                <?= $form->field($model, 'is_need_upload')->radioList(
                    ['0'=>Yii::t('backend','no'),'1'=>Yii::t('backend','yes')],
                    ['prompt'=> Yii::t('backend','select_more'),'separator'=>'&nbsp;&nbsp;']) ?>
            </td>
            <td>
                <?= $form->field($model, 'is_allow_reuse')->radioList(
                    ['0'=>Yii::t('backend','no'),'1'=>Yii::t('backend','yes')],
                    ['prompt'=> Yii::t('backend','select_more'),'separator'=>'&nbsp;&nbsp;']) ?>
            </td>
        </tr>
        <tr>
            <td>
                <?= $form->field($model, 'is_use_vendor')->radioList(
                    ['0'=>Yii::t('backend','no'),'1'=>Yii::t('backend','yes')],
                    ['prompt'=> Yii::t('backend','select_more'),'separator'=>'&nbsp;&nbsp;']) ?>
            </td>
            <td>

            </td>
        </tr>
        <tr>
            <td>
                <?= $form->field($model, 'feature_content_type')->radioList(
                    [
                        '0'=>Yii::t('backend','feature_content_type_none'),
                        '1'=>Yii::t('backend','feature_content_type_filename'),
                        '2'=>Yii::t('backend','feature_content_type_extension')
                    ],
                    ['prompt'=> Yii::t('backend','select_more'),'separator'=>'&nbsp;&nbsp;']) ?>
            </td>
            <td>
                <?= $form->field($model, 'feature_content')->textInput(['id' => 'feature_content','maxlength' => 500]) ?>
            </td>
        </tr>
        <tr>
            <td>
                <?= $form->field($model, 'transfer_type')->radioList(
                    ['0'=>Yii::t('backend','transfer_type_normal'),'1'=>Yii::t('backend','transfer_type_rtmp')],
                    ['prompt'=> Yii::t('backend','select_more'),'separator'=>'&nbsp;&nbsp;']) ?>
            </td>
            <td>
                <?= $form->field($model, 'window_mode')->radioList(
                    ['0'=>Yii::t('backend','window_mode_small'),'1'=>Yii::t('backend','window_mode_big')],
                    ['prompt'=> Yii::t('backend','select_more'),'separator'=>'&nbsp;&nbsp;']) ?>
            </td>
        </tr>
        <tr>
            <td><?= $form->field($model, 'default_time')->textInput(['id' => 'default_time']) ?></td>
            <td><?= $form->field($model, 'default_credit')->textInput(['id' => 'default_credit']) ?></td>
        </tr>
        <tr>
            <td><?= $form->field($model, 'icon')->textInput(['maxlength' => 500]) ?></td>
            <td><?= $form->field($model, 'sequence_number')->textInput(['id' => 'sequence_number']) ?></td>
        </tr>
        <tr>
            <td>
                <?= $form->field($model, 'is_record_score')->radioList(
                    ['0'=>Yii::t('backend','no'),'1'=>Yii::t('backend','yes')],
                    ['prompt'=> Yii::t('backend','select_more'),'separator'=>'&nbsp;&nbsp;']) ?>
            </td>
            <td>
                <?= $form->field($model, 'complete_rule')->radioList(
                    [0=>Yii::t('backend','complete_rule_browse'),1=>Yii::t('backend','complete_rule_score')],
                    ['prompt'=> Yii::t('backend','select_more'),'separator'=>'&nbsp;&nbsp;']) ?>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <?= $form->field($model, 'description')->textarea(['rows' => 6, 'maxlength' => 5000]) ?>
            </td>
        </tr>
    </table>
    <?php ActiveForm::end(); ?>

</div>
<script>
    var editor;
    KindEditor.ready(function (K) {
        editor = K.create('#componentservice-description', {
            allowFileManager: false,
            width:'100%',
            afterCreate: function () {
                this.sync();
            },
            afterBlur: function () {
                this.sync();
            }
        });
    });
</script>
