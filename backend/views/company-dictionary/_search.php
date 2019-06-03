<?php

use yii\helpers\ArrayHelper;
use yii\helpers\Html;
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
    <table>
        <tr>
            <td>
                <?= $form->field($model, 'dictionary_code')->textInput() ?>
            </td>
            <td>
                <?= $form->field($model, 'dictionary_name')->textInput() ?>
            </td>
            <td>
                <?= $form->field($model, 'dictionary_category_id')->dropDownList(ArrayHelper::map($dictionaryCategoryModel,'kid', 'cate_name'),
                    ['prompt'=>Yii::t('common','select_more')]) ?>
            </td>
            <td>
                <?= $form->field($model, 'status')->dropDownList([
                    '1'=>Yii::t('common','status_normal'),
                    '2'=>Yii::t('common','status_stop')],
                    ['prompt'=>Yii::t('common','all_data')]) ?>
            </td>
            <td>
                <?php if ($includeSubNode == '1') :?>
                    <?= Html::checkbox('includeSubNode',true) ?><?= Yii::t('common','include_sub_node')?>
                <?php else: ?>
                    <?= Html::checkbox('includeSubNode',false) ?><?= Yii::t('common','include_sub_node')?>
                <?php endif ?>
            </td>
            <td>
                <?= Html::button(Yii::t('common', 'search'), ['class' => 'btn btn-primary', 'id'=>'search']) ?>
                <?= Html::button(Yii::t('common', 'reset'), ['class' => 'btn btn-default', 'id'=>'reset']) ?>
            </td>
        </tr>
    </table>


    <?php ActiveForm::end(); ?>

</div>