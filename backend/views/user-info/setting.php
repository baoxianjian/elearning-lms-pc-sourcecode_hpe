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

?>
<input type="hidden" id="indexUrl" value="<?=Yii::$app->urlManager->createUrl(['user-info/setting-index']);?>"/>
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
        var msg = "<?=Yii::t('common','operation_success')?>";
        NotyWarning(msg);
       // reloadForm();
    }



</script>
<div class="eln-user-setting-form">

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
            <td >
                <?= $form->field($model, 'theme')->dropDownList(ArrayHelper::map($themeModel,'dictionary_value', 'dictionary_name_i18n'),
                    ['prompt'=> Yii::t('common','select_more')]) ?>
            </td>
        </tr>
        <tr>
            <td>
                <?= $form->field($model, 'timezone')->dropDownList(ArrayHelper::map($timezoneModel,'dictionary_value', 'dictionary_name_i18n'),
                    ['prompt'=> Yii::t('common','select_more')]) ?>
            </td>
        </tr>
        <tr>
            <td>
                <?= $form->field($model, 'language')->dropDownList(ArrayHelper::map($languageModel,'dictionary_value', 'dictionary_name_i18n'),
                    ['prompt'=> Yii::t('common','select_more')]) ?>
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