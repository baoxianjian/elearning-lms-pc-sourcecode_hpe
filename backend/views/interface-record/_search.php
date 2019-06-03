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
       		  <?= $form->field($model, 'kid')->textInput() ?>
           </td>  
            <td>
          
               <?= $form->field($model, 'bo_type')
                    ->dropDownList($model->getBoTypeSelects(),
                        ['prompt'=>Yii::t('common','select_more')]) ?>
            </td>
           
            <td>
                <?= $form->field($model, 'change_type')
                    ->dropDownList($model->getChangeTypeSelects(),
                        ['prompt'=>Yii::t('common','select_more')]) ?>
            </td>
            
             <td>
                <?= $form->field($model, 'handle_result')
                    ->dropDownList($model->getHandleResultSelects(),
                        ['prompt'=>Yii::t('common','select_more')]) ?>
            </td>
            
            <td>
                <?= $form->field($model, 'operate_time')
                    ->widget(TDatePicker::classname(),['readonly' => 'readonly']); ?>
            </td>
            <td>
                <?= Html::button(Yii::t('common', 'search'), ['class' => 'btn btn-primary', 'id'=>'search']) ?>
                <?= Html::resetButton(Yii::t('common', 'reset'), ['class' => 'btn btn-default','id'=>'reset']) ?>
            </td>
        </tr>
    </table>
    <?php ActiveForm::end(); ?>
</div>