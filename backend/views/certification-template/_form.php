<?php

use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\learning\LnCertificationTemplate */
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

<div class="eln-certificaiton-template-form">

    <?php $form = ActiveForm::begin([
        'id' => 'clientform-' . $formType
//        'enableAjaxValidation' => false,
//        'enableClientValidation' => true,
//        'validateOnSubmit' => true
    ]); ?>
    <table width="100%" border="0">
        <tr>
            <td colspan="2">
                <?= $form->field($model, 'template_code')->textInput(['maxlength' => 50]) ?>
            </td>
            <td colspan="2">
                <?= $form->field($model, 'template_name')->textInput(['maxlength' => 50]) ?>
            </td>
            <td colspan="2">
                <?= $form->field($model, 'sequence_number')->textInput() ?>
            </td>
        </tr>
        <tr>
            <td colspan="6">
                <table width="100%">
                    <tr>
                        <td width="70%">
                            <?= $form->field($model, 'template_url')->textInput(['readonly' => 'readonly']) ?>
                        </td>
                        <td>
                            <a id="exp_upload" href="javascript:void(0);" class="btn btn-sm btn-default"><?=Yii::t('backend','select_componet')?></a>
                            <span class="upload-info" style="color:#008000;margin-left:5px;"></span>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td >
                <?= $form->field($model, 'print_type')->radioList([
                    '0'=>Yii::t('common', 'print_type_a4'),
                    '1'=>Yii::t('common', 'print_type_envolope')],
                    ['separator'=>'&nbsp;&nbsp;']) ?>
            </td>
            <td>
                <?= $form->field($model, 'is_auto_certify')->radioList([
                    '0'=>Yii::t('common', 'no'),
                    '1'=>Yii::t('common', 'yes')],
                    ['separator'=>'&nbsp;&nbsp;']) ?>
            </td>
            <td >
                <?= $form->field($model, 'is_print_score')->radioList([
                    '0'=>Yii::t('common', 'no'),
                    '1'=>Yii::t('common', 'yes')],
                    ['separator'=>'&nbsp;&nbsp;']) ?>
            </td>

            <td >
                <?= $form->field($model, 'is_display_certify_date')->radioList([
                    '0'=>Yii::t('common', 'no'),
                    '1'=>Yii::t('common', 'yes')],
                    ['separator'=>'&nbsp;&nbsp;']) ?>
            </td>
        </tr>
        <tr>
            <td >
                <?= $form->field($model, 'print_orientation')->radioList([
                    '0'=>Yii::t('common', 'print_orientation_portrait'),
                    '1'=>Yii::t('common', 'print_orientation_landscape')],
                    ['separator'=>'&nbsp;&nbsp;']) ?>
            </td>
            <td >
                <?= $form->field($model, 'is_email_user')->radioList([
                    '0'=>Yii::t('common', 'no'),
                    '1'=>Yii::t('common', 'yes')],
                    ['separator'=>'&nbsp;&nbsp;']) ?>
            </td>
            <td>
                <?= $form->field($model, 'is_email_teacher')->radioList([
                    '0'=>Yii::t('common', 'no'),
                    '1'=>Yii::t('common', 'yes')],
                    ['separator'=>'&nbsp;&nbsp;']) ?>
            </td>
        </tr>
        <tr>
            <td colspan="6">
                <?= $form->field($model, 'certification_display_name')->textInput(['maxlength' => 100]) ?>
            </td>
        </tr>
        <tr>
            <td colspan="6">
                <?= $form->field($model, 'description')->textarea(['rows' => 6, 'maxlength' => 5000]) ?>
            </td>
        </tr>
<!--        <tr>-->
<!--            <td>-->
<!--                --><?//= $form->field($model, 'name_top')->textInput() ?>
<!--            </td>-->
<!--            <td>-->
<!--                --><?//= $form->field($model, 'name_left')->textInput() ?>
<!--            </td>-->
<!--            <td>-->
<!--                --><?//= $form->field($model, 'name_size')->textInput() ?>
<!--            </td>-->
<!--            <td>-->
<!--                --><?//= $form->field($model, 'name_font')->textInput() ?>
<!--            </td>-->
<!--            <td>-->
<!--                --><?//= $form->field($model, 'name_color')->textInput() ?>
<!--            </td>-->
<!--        </tr>-->
<!--        <tr>-->
<!--            <td>-->
<!--                --><?//= $form->field($model, 'serial_number_top')->textInput() ?>
<!--            </td>-->
<!--            <td>-->
<!--                --><?//= $form->field($model, 'serial_number_left')->textInput() ?>
<!--            </td>-->
<!--            <td>-->
<!--                --><?//= $form->field($model, 'serial_number_size')->textInput() ?>
<!--            </td>-->
<!--            <td>-->
<!--                --><?//= $form->field($model, 'serial_number_font')->textInput() ?>
<!--            </td>-->
<!--            <td>-->
<!--                --><?//= $form->field($model, 'serial_number_color')->textInput() ?>
<!--            </td>-->
<!--        </tr>-->
<!--        <tr>-->
<!--            <td>-->
<!--                --><?//= $form->field($model, 'score_top')->textInput() ?>
<!--            </td>-->
<!--            <td>-->
<!--                --><?//= $form->field($model, 'score_left')->textInput() ?>
<!--            </td>-->
<!--            <td>-->
<!--                --><?//= $form->field($model, 'score_size')->textInput() ?>
<!--            </td>-->
<!--            <td>-->
<!--                --><?//= $form->field($model, 'score_font')->textInput() ?>
<!--            </td>-->
<!--            <td>-->
<!--                --><?//= $form->field($model, 'score_color')->textInput() ?>
<!--            </td>-->
<!--        </tr>-->
<!--        <tr>-->
<!--            <td>-->
<!--                --><?//= $form->field($model, 'certify_date_top')->textInput() ?>
<!--            </td>-->
<!--            <td>-->
<!--                --><?//= $form->field($model, 'certify_date_left')->textInput() ?>
<!--            </td>-->
<!--            <td>-->
<!--                --><?//= $form->field($model, 'certify_date_size')->textInput() ?>
<!--            </td>-->
<!--            <td>-->
<!--                --><?//= $form->field($model, 'certify_date_font')->textInput() ?>
<!--            </td>-->
<!--            <td>-->
<!--                --><?//= $form->field($model, 'certify_date_color')->textInput() ?>
<!--            </td>-->
<!--        </tr>-->
    </table>

    <?php ActiveForm::end(); ?>

</div>

<?= Html::jsFile('/static/common/js/ajaxupload.js') ?>
<script>

    var ajaxUploadUrl = "<?=Url::toRoute(['certification-template/upload'])?>";


    //异步上传文件
    new AjaxUpload("#exp_upload", {
        action: ajaxUploadUrl,
        type: "POST",
        name: 'myfile',
        data: {'_csrf': '<?= Yii::$app->request->csrfToken ?>'},
        onSubmit: function (file, ext) {
//            if ($(".text-info img").length > 0) {
//                $(".upload-info").html("<div style='color:#E3583B;margin:5px;'>" + "<?//=Yii::t('common', 'file_cropped')?>//" + "</div>");
//                return false;
//            }
//            alert(ajaxUploadUrl);
            $(".upload-info").html("<?=Yii::t('common', 'uploading')?>");
        },
        onComplete: function (file, response) {
            var result = JSON.parse(response);

            if (result.info == "<?=Yii::t('common', 'file_type_error')?>" || result.info == "<?=Yii::t('common', 'upload_error')?>") {
                $(".upload-info").html(result.info);
            }
            else {
                //生成元素
//                $("#exp_upload").html(result.filename);
                //传递参数上传
//                $("#"+formId+" #sorecord-attach_original_filename").val(result.filename);
                //$("#imgTemplateUrl").attr('src', result.info);

                $("#lncertificationtemplate-template_url").val(result.info);

                //更新提示信息
                $(".upload-info").html("<?=Yii::t('common', 'upload_completed')?>");
            }
        }
    });
</script>
