<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

?>
<style>
    .form-inline .form-group{float: left;}
</style>
<script>
    $(document).ready(function(){
        $("#searchForm").submit(function() {
            reloadForm();
            return false;
        });
    });
    function resetForm(){
        $("#searchText").val('');
        $("#courseservice-course_type>option").attr("selected",false).eq(0).attr("selected",true);
    }
</script>
<div class="form-inline pull-right">
    <?php $form = ActiveForm::begin([
        'id' => 'searchForm',
        'action'=>['list'],
        'method' => 'get',
    ]); ?>
    
    <?= $form->field($model, 'title')->textInput(['id'=>'searchText','placeholder'=>Yii::t('frontend', 'input_keyword')])->label(false) ?>
    <div class="form-group field-courseservice-course_type has-success">
       
        <div class="help-block"></div>
    </div>
    <?= Html::submitButton(Yii::t('common', 'search'), ['class' => 'btn btn-primary']) ?>
    <?= Html::Button(Yii::t('common', 'reset'), ['onclick'=>'resetForm()','class' => 'btn btn-default']) ?>
    <?php ActiveForm::end(); ?>
</div>