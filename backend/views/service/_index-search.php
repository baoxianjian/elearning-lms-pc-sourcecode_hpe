<?php

use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

?>
<script>
    $("#search").on('click', function(){
//        alert('reset');
        reloadForm();
    });

    $("#reset").on('click', function(){
        //        alert('reset');
        clearForm("searchForm");
    });
</script>
<div class="list-search-form">
    <?php $form = ActiveForm::begin([
        'id' => 'searchForm',
        'action' => ['list'],
        'method' => 'get',
    ]); ?>
    <table  border="0">
        <tr>
            <td>
                <?= $form->field($model, 'service_code')->textInput() ?>
            </td>
            <td>
                <?= $form->field($model, 'service_name')->textInput() ?>
            </td>
            <td>
                <?= $form->field($model, 'service_status')
                    ->dropDownList([
                        '1'=>Yii::t('common','change_status_start'),
                        '0'=>Yii::t('common','change_status_stop')],
                        ['prompt'=>Yii::t('common','select_more')]) ?>
            </td>
            <td>
                <?= Html::button(Yii::t('common', 'search'), ['class' => 'btn btn-primary', 'id'=>'search']) ?>
                <?= Html::button(Yii::t('common', 'reset'), ['class' => 'btn btn-default', 'id'=>'reset']) ?>
            </td>
        </tr>
    </table>
    <?php ActiveForm::end(); ?>
</div>