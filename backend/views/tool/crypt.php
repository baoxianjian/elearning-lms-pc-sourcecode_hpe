<?php
/**
 * Created by PhpStorm.
 * User: tangming
 * Date: 8/22/2015
 * Time: 9:52 PM
 */
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

$ContentTanslateName =  Yii::t('common', 'encrypt_decrypt') ;

$this->params['breadcrumbs'][] =  $ContentTanslateName .  Yii::t('common', 'tool');
?>
<head>
    <?=Html::jsFile('/vendor/bower/bootstrap/dist/js/bootstrap.min.js')?>
    <script>
        $(document).ready(function() {

        });

    </script>
</head>

<div id="content-body">
    <?php $form = ActiveForm::begin([
        'method' => 'post',
        'id'=>'clientform-'. $formType,
    ]); ?>

    <table width="100%" border="0">
        <tr>
            <td>
                <?= $form->field($model, 'system_id')->dropDownList(ArrayHelper::map($externalSystemModel,'kid', 'system_name')) ?>
            </td>
        </tr>
        <tr>
            <td>
                <?= $form->field($model, 'decrypt_message')->textarea(['rows'=>'10']) ?>
            </td>
        </tr>
        <tr>
            <td>
                <?= $form->field($model, 'encrypt_message')->textarea(['rows'=>'10']) ?>
            </td>
        </tr>
        <tr>
            <td >
                <?= $form->field($model, 'mode')->radioList(
                    ['0'=>Yii::t('common','encrypt'),'1'=>Yii::t('common','decrypt')],
                    ['prompt'=> Yii::t('common','select_more'),'separator'=>'&nbsp;&nbsp;']) ?>
            </td>
        </tr>
        <tr>
            <td>
                <?=
                Html::submitButton(Yii::t('common', 'submit'),
                    ['class'=>'btn btn-primary'])
                ?>
            </td>
        </tr>
    </table>
    <?php ActiveForm::end(); ?>
</div>