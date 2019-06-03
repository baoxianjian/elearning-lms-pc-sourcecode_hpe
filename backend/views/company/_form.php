<?php

use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $model common\models\framework\FwCompany */
/* @var $form yii\widgets\ActiveForm */
?>

<script>

    function validateOtherClientForm()
    {
        return true;
    }
</script>

<div class="company-form">
    <?php $form = ActiveForm::begin([
        'id' => 'clientform-other',
        'enableAjaxValidation' => false,
        'enableClientValidation' => true,
//        'validateOnSubmit' => true
    ]); ?>
    <table width="100%" border="0">
        <tr>
            <td>
                <?= $form->field($model, 'org_certificate_code')->textInput(['maxlength' => 50]) ?>
            </td>
            <td>
                <?= $form->field($model, 'representative')->textInput(['maxlength' => 50]) ?>
            </td>
        </tr>
        <tr>
            <td>
                <?= $form->field($model, 'site_url')->textInput(['maxlength' => 255]) ?>
            </td>
            <td>
                <?= $form->field($model, 'resource_url')->textInput(['maxlength' => 255]) ?>
            </td>
        </tr>
        <tr>
            <td>
                <?= $form->field($model, 'theme')->dropDownList(ArrayHelper::map($themeModel,'dictionary_value', 'dictionary_name_i18n'),
                    ['prompt'=> Yii::t('common','select_more'),'separator'=>'&nbsp;&nbsp;']) ?>
            </td>
            <td>
                <?= $form->field($model, 'language')->dropDownList(ArrayHelper::map($languageModel,'dictionary_value', 'dictionary_name_i18n'),
                    ['prompt'=> Yii::t('common','select_more'),'separator'=>'&nbsp;&nbsp;']) ?>
            </td>
        </tr>
        <tr>
            <td>
                <?= $form->field($model, 'default_portal')->radioList([
                    '0'=>Yii::t('backend', 'user_portal'),
                    '1'=>Yii::t('backend', 'company_portal')],
                    ['separator'=>'&nbsp;&nbsp;']) ?>
            </td>
            <td>
                <?= $form->field($model, 'reporting_model')->radioList(
                    ['0'=>Yii::t('backend','reporting_model_line_manager'),'1'=>Yii::t('backend','reporting_model_depart_manager')],
                    ['prompt'=> Yii::t('backend','select_more'),'separator'=>'&nbsp;&nbsp;']) ?>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <div class="form-group field-fwcompany-logo_url required">
                    <label class="control-label" for="fwcompany-logo_url"><?= Yii::t('backend','logo_url') ?></label>
                    <input type="hidden" id="logo_url" name="FwCompany[logo_url]" value="<?= $model->logo_url ?>">
                    <p style="margin: 0;">
                        <input type="button" id="upload" class="btn btn-success btn-sm" value="<?= Yii::t('backend','upload') ?>" style="max-width: 120px;">
                    </p>
                    <div class="upload-info"></div>
                    <div class="pic-display" style="<?= $model->logo_url ? '' : 'display: none;' ?> text-align: left;height: 60px; line-height: 60px;">
                        <?= $model->logo_url ? '<img src="'.$model->logo_url.'" width="50" height="50"/>' : ''?>
                    </div>
                    <div class="text-info"><?= Yii::t('backend','tip_for_img_size') ?></div>
                </div>
            </td>
        </tr>
        <tr>
            <td>
                <?= $form->field($model, 'limited_user_number')->textInput(['maxlength' => 255]) ?>
            </td>
            <td>
                <?= $form->field($model, 'limited_domain_number')->textInput(['maxlength' => 255]) ?>
            </td>
        </tr>
        <tr>
            <td>
                <?= $form->field($model, 'is_self_register')->radioList([
                    '0'=>Yii::t('backend', 'no'),
                    '1'=>Yii::t('backend', 'yes')],
                    ['separator'=>'&nbsp;&nbsp;']) ?>
            </td>
            <td>
                <?= $form->field($model, 'is_default_company')->radioList([
                    '0'=>Yii::t('backend', 'no'),
                    '1'=>Yii::t('backend', 'yes')],
                    ['separator'=>'&nbsp;&nbsp;']) ?>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <?= $form->field($model, 'second_level_domain')->textInput(['maxlength' => 255]) ?>
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
<?= Html::cssFile('/static/common/css/jquery.Jcrop.css') ?>
<?= Html::jsFile('/static/common/js/ajaxupload.js') ?>
<?= Html::jsFile('/static/common/js/jquery.Jcrop.min.js') ?>
<script>
    var ajaxUploadUrl = "<?=Url::toRoute(['/company/upload'])?>";
    var g_oJCrop = null;
    //异步上传文件
    new AjaxUpload("#upload", {
        action: ajaxUploadUrl,
        type: "POST",
        name: 'myfile',
        data: {'_csrf': '<?= Yii::$app->request->csrfToken ?>'},
        onSubmit: function (file, ext) {
//            if ($(".text-info img").length > 0) {
//                $(".upload-info").html("<div style='color:#ff0000;margin:5px;'>" + "<?//=Yii::t('common', 'file_cropped')?>//" + "</div>");
//                return false;
//            }
            $(".upload-info").html("<div style='color:#008000;margin:5px;'>" + "<?=Yii::t('backend', 'uploading')?>" + "</div>");
        },
        onComplete: function (file, response) {
            if (g_oJCrop != null) {
                g_oJCrop.destroy();
            }
            if (response == "<?=Yii::t('common', 'file_type_error')?>" || response == "<?=Yii::t('backend', 'upload_error')?>") {
                $(".upload-info").html("<div style='color:#ff0000;margin:5px;'>" + response + "</div>");
            }
            else {
                //生成元素
                $(".pic-display").show().html("<div class='thum'><img id='target' src='" + response + "' width='50' height='50'/></div>");
                //传递参数上传
                $("#logo_url").val(response);
                //更新提示信息
                $(".upload-info").html("<div style='color:#008000;margin:5px;'>" + "<?=Yii::t('backend', 'upload_completed')?>" + "</div>");
            }
        }
    });
</script>