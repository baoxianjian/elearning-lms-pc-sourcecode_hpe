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
<input type="hidden" id="indexUrl" value="<?=Yii::$app->urlManager->createUrl(['user-info/info-index']);?>"/>
<script>

    var indexUrl = document.getElementById('indexUrl');

    if(!document.getElementById("content-body"))
    {
        window.location = indexUrl.value;
    }

    function reloadForm()
    {
//            alert("reloadForm");
//        $.pjax.reload({container:"#grid"});
//            $.pjax.reload({container:"#gridframe"});
        var ajaxUrl = "<?=Url::toRoute(['user-info/info'])?>";
//        alert('ajaxUrl:'+ajaxUrl);
        ajaxGetWithForm('searchForm', ajaxUrl,'rightList');
    }


    function loadModalFormData(modalId,url)
    {
        modalClear("updateModal");

        openModalForm(modalId, url);
    }

    function ReloadPageAfterUpdate()
    {
        var msg = "<?=Yii::t('common','operation_success')?>";
        NotyWarning(msg);
        //reloadForm();
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

                <?php if ($model->isNewRecord) :?>
                    <?= $form->field($model, 'user_name')->textInput(['maxlength' => 255]) ?>
                <?php else:?>
                    <?= $form->field($model, 'user_name')->textInput(['readonly' => 'readonly']) ?>
                <?php endif?>
            </td>
            <td>
                <?= $form->field($model, 'real_name')->textInput(['maxlength' => 255]) ?>
            </td>
        </tr>
        <tr>
            <td>
                <?= $form->field($model, 'email')->textInput(['maxlength' => 255]) ?>
            </td>
            <td>
                <?= $form->field($model, 'email_repeat')->textInput(['maxlength' => 255]) ?>
            </td>
        </tr>
        <tr>
            <td>
                <?= $form->field($model, 'nick_name')->textInput(['maxlength' => 255]) ?>
            </td>
            <td >
                <?= $form->field($model, 'gender')->radioList(ArrayHelper::map($genderModel,'dictionary_value', 'dictionary_name_i18n'),
                    ['prompt'=> Yii::t('common','select_more'),'separator'=>'&nbsp;&nbsp;']) ?>
            </td>
        </tr>
        <tr>
            <td>
                <?= $form->field($model, 'mobile_no')->textInput(['maxlength' => 30]) ?>
            </td>
            <td>
                <?= $form->field($model, 'home_phone_no')->textInput(['maxlength' => 30]) ?>
            </td>
        </tr>
        <tr>
            <td width="50%">
                <?= $form->field($model, 'birthday')->widget(TDatePicker::classname(),['readonly' => 'readonly']); ?>
            </td>
            <td>
                <?= $form->field($model, 'id_number')->textInput(['maxlength' => 50]) ?>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <?= $form->field($model, 'description')->textarea(['rows' => 6, 'maxlength' => 5000]) ?>
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