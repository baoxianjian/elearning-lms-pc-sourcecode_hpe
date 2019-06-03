<?php

use yii\helpers\ArrayHelper;
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
    <table>
        <tr>
            <td>
                <?= $form->field($model, 'title')->textInput() ?>
            </td>
            <td>
                <?= $form->field($model, 'component_code')->textInput() ?>
            </td>
            <td>
                <?= $form->field($model, 'component_type')->dropDownList([
                    ''=>Yii::t('backend','select_more'),
                    '0'=>Yii::t('backend', 'resource'),
                    '1'=>Yii::t('backend', 'active'),
                ]) ?>
            </td>
            <td>
                <?= Html::button(Yii::t('backend', 'search'), ['class' => 'btn btn-primary', 'id'=>'search']) ?>
                <?= Html::button(Yii::t('backend', 'reset'), ['class' => 'btn btn-default', 'id'=>'reset']) ?>
            </td>
        </tr>
    </table>


    <?php ActiveForm::end(); ?>

</div>