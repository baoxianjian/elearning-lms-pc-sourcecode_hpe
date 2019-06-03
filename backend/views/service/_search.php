<?php

use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use components\widgets\TDatePicker;

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
                <?= $form->field($model, 'action_start_at')
                    ->widget(TDatePicker::classname(),['readonly' => 'readonly']); ?>
            </td>
            <td>
                <?= $form->field($model, 'action_end_at')
                    ->widget(TDatePicker::classname(),['readonly' => 'readonly']); ?>
            </td>
            <td>
                <?= $form->field($model, 'service_log')->textInput() ?>
            </td>
            <td>
                <?= $form->field($model, 'service_id')
                    ->dropDownList(ArrayHelper::map($serviceList,'kid', 'service_name'),
                        ['prompt'=>Yii::t('common','select_more')]) ?>
            </td>
            <td>
                <?= $form->field($model, 'action_status')
                    ->dropDownList(['0'=>Yii::t('backend', 'action_status_normal'),'1'=>Yii::t('backend', 'action_status_error')],
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