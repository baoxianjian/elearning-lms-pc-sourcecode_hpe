<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

?>

<style>
    /*label{display: none;}*/
</style>

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
                <?= $form->field($model, 'user_name')->textInput() ?>
            </td>
            <td>
                <?= $form->field($model, 'real_name')->textInput() ?>
            </td>
            <td>
                <?= $form->field($model, 'status')->dropDownList([
                    '1'=>Yii::t('backend','status_normal'),
                    '2'=>Yii::t('backend','status_stop')],
                    ['prompt'=>Yii::t('common','all_data')]) ?>
            </td>
            <td>
                <?php if ($includeSubNode == '1') :?>
                    <?= Html::checkbox('includeSubNode',true) ?><?= Yii::t('backend','include_sub_node')?>
                <?php else: ?>
                    <?= Html::checkbox('includeSubNode',false) ?><?= Yii::t('backend','include_sub_node')?>
                <?php endif ?>
            </td>
            <td>
                <?= Html::button(Yii::t('backend', 'search'), ['class' => 'btn btn-primary', 'id'=>'search']) ?>
                <?= Html::button(Yii::t('backend', 'reset'), ['class' => 'btn btn-default', 'id'=>'reset']) ?>
            </td>
        </tr>
    </table>


    <?php ActiveForm::end(); ?>

</div>