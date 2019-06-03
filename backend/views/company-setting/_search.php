<?php

use common\helpers\TArrayHelper;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

?>

<style>
    /*label{display: none;}*/
</style>

<script>
//    $(document).ready(function(){
//        $("#searchForm").submit(function() {
////            alert('1');
//            reloadForm();
//            return false;
//        });
//    });

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
                <?= $form->field($model, 'code')
                    ->dropDownList(ArrayHelper::map($dictionaryModel,'dictionary_code', 'dictionary_name_i18n'),
                        ['prompt'=>Yii::t('common','select_more')]) ?>
            </td>
            <td>
                <?= $form->field($model, 'value')->textInput() ?>
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