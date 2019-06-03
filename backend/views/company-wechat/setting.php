<?php
/**
 * Created by PhpStorm.
 * User: tangming
 * Date: 8/22/2015
 * Time: 9:52 PM
 */
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

?>
<input type="hidden" id="indexUrl" value="<?=Yii::$app->urlManager->createUrl(['company-wechat/index']);?>"/>
<script>

    var indexUrl = document.getElementById('indexUrl');

    if(!document.getElementById("content-body"))
    {
        window.location = indexUrl.value;
    }


    function ReloadPageAfterUpdate()
    {
        var msg = "<?=Yii::t('common','operation_success')?>";
        NotyWarning(msg);
    }



</script>
<div id="company-wechat-form">
    <?php $form = ActiveForm::begin([
        'id' => 'updateform',
        'method' => 'post',
        'enableAjaxValidation' => false,
        'enableClientValidation' => true,
//        'options' => ['enctype' => 'multipart/form-data'],
//        'validateOnSubmit' => true
    ]); ?>
    <table width="100%" border="0">
        <tr>
            <td>
                <?= $form->field($model, 'mp_name')->textInput(['maxlength' => 50]) ?>
            </td>
            <td>
                <?= $form->field($model, 'mp_type')->radioList(
                    [
                        '0'=>Yii::t('common','mp_type_subscribe'),
                        '1'=>Yii::t('common','mp_type_service'),
                        '2'=>Yii::t('common','mp_type_company')
                    ],
                    [
                        'prompt'=> Yii::t('common','select_more'),
                        'separator'=>'&nbsp;&nbsp;'
                    ]) ?>
            </td>
        </tr>
        <tr>
            <td>
                <?= $form->field($model, 'is_authenticated')->radioList([
                    '0'=>Yii::t('common', 'no'),
                    '1'=>Yii::t('common', 'yes')],
                    ['separator'=>'&nbsp;&nbsp;']) ?>

            </td>
            <td>
                <?= $form->field($model, 'status')->radioList([
                    '0'=>Yii::t('common', 'change_status_stop'),
                    '1'=>Yii::t('common', 'change_status_start')],
                    ['separator'=>'&nbsp;&nbsp;']) ?>
            </td>
        </tr>
        <tr>
            <td >
                <?= $form->field($model, 'wechat_name')->textInput(['maxlength' => 50]) ?>
            </td>
            <td>
                <?= $form->field($model, 'original_id')->textInput(['maxlength' => 50]) ?>
            </td>
        </tr>
        <tr>
            <td>
                <?= $form->field($model, 'action_token')->textInput(['maxlength' => 50]) ?>
            </td>
            <td>
                <?= $form->field($model, 'server_url')->textInput(['maxlength' => 50, 'readonly' => 'readonly']) ?>
            </td>
        </tr>
        <tr>
            <td>
                <?= $form->field($model, 'app_id')->textInput(['maxlength' => 50]) ?>
            </td>
            <td>
                <?= $form->field($model, 'app_secret')->textInput(['maxlength' => 50]) ?>
            </td>
        </tr>
        <tr>
            <td>
                <?= $form->field($model, 'security_mode')->radioList(
                    [
                        '0'=>Yii::t('common','security_mode_plain'),
                        '1'=>Yii::t('common','security_mode_compatible'),
                        '2'=>Yii::t('common','security_mode_encrypt')
                    ],
                    [
                        'prompt'=> Yii::t('common','select_more'),
                        'separator'=>'&nbsp;&nbsp;'
                    ]) ?>
            </td>
            <td>
                <?= $form->field($model, 'encoding_aes_key')->textInput(['maxlength' => 50]) ?>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <?=
                Html::button(Yii::t('backend', 'update'),
                    ['id'=>'updateBtn','class'=>'btn btn-primary','onclick'=>'submitForm("updateform");'])
                ?>
                <?= Html::resetButton(Yii::t('common', 'reset'), ['class' => 'btn btn-default']) ?>
            </td>
        </tr>
    </table>

    <?php ActiveForm::end(); ?>

</div>