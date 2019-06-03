<?php

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
        'method' => 'post',
    ]); ?>
    <table>
        <tr>
            <td>
                <?= $form->field($model, 'tree_type_code')->textInput() ?>
            </td>
            <td>
                <?= $form->field($model, 'tree_type_name')->textInput() ?>
            </td>
            <td>
                <?= Html::button(Yii::t('backend', 'search'), ['class' => 'btn btn-primary', 'id'=>'search']) ?>
                <?= Html::button(Yii::t('backend', 'reset'), ['class' => 'btn btn-default', 'id'=>'reset']) ?>
            </td>
        </tr>
    </table>


    <?php ActiveForm::end(); ?>

</div>