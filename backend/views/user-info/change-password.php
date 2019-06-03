<?php
/**
 * Created by PhpStorm.
 * User: TangMingQiang
 * Date: 3/23/15
 * Time: 1:16 AM
 */
use components\widgets\TGridView;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use components\widgets\TDatePicker;
?>
<input type="hidden" id="indexUrl" value="<?=Yii::$app->urlManager->createUrl(['user-info/change-password-index']);?>"/>
<script>

    var indexUrl = document.getElementById('indexUrl');

    if(!document.getElementById("content-body"))
    {
        window.location = indexUrl.value;
    }

    function loadModalFormData(modalId,url)
    {
        modalClear("updateModal");

        openModalForm(modalId, url);
    }

    function ReloadPageAfterUpdate()
    {
        document.getElementById("fwuser-password_old").value = "";
        document.getElementById("fwuser-password_hash").value = "";
        document.getElementById("fwuser-password_repeat").value = "";

        var msg = "<?=Yii::t('common','operation_success')?>";
        NotyWarning(msg);
        //reloadForm();

//        var url = "<?//=Yii::$app->urlManager->createAbsoluteUrl('index/index')?>//";
//
////        alert(url);
//
//        window.location.href = url;
    }



</script>
<div class="eln-user-info-form">

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
                <?= $form->field($model, 'user_name')->textInput(['readonly' => 'readonly']) ?>
            </td>
        </tr>
        <tr>
            <td>
                <?= $form->field($model, 'password_old')->passwordInput(['maxlength' => 255]) ?>
            </td>
        </tr>
        <tr>
            <td>
                <?= $form->field($model, 'password_hash')->passwordInput(['maxlength' => 255]) ?>
            </td>
        </tr>
        <tr>
            <td>
                <?= $form->field($model, 'password_repeat')->passwordInput(['maxlength' => 255]) ?>
            </td>
        </tr>
        <tr>
            <td>
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