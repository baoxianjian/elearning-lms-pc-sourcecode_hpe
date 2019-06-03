<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

?>

<script>
    function resetForm(){
        $("#searchText").val('');
        $("#searchSelect>option").attr("selected",false).eq(0).attr("selected",true);
        $("#domain_id>option").attr("selected",false).eq(0).attr("selected",true);
    }
</script>
<div class="form-inline pull-right">
    <?php $form = ActiveForm::begin([
        'id' => 'searchForm',
        'action'=>['list'],
        'method' => 'get',
    ]); ?>
    <?= $form->field($model, 'courseware_name')->textInput(['id'=>'searchText','placeholder'=>Yii::t('common', 'audience_code').'/'.Yii::t('common', 'audience_name').'/'.Yii::t('common', 'file_name').'/'.Yii::t('common', 'supplier')])->label(false) ?>
    <?= $form->field($model, 'component_id')->dropDownList($componentArray,['id'=>'searchSelect'])->label(false) ?>
    <div class="form-group field-coursewareservice-domain_id">
        <select name="domain_id" class="form-control" id="domain_id">
            <option value=""><?=Yii::t('common', 'select_{value}',['value'=>Yii::t('common','domain')])?></option>
            <?php
            foreach ($domain as $k=>$v){
            ?>
                <option value="<?=$k?>" <?=!empty($_REQUEST['domain_id']) && $_REQUEST['domain_id'] == $k ? 'selected' : ''?> label="<?=$v?>"></option>
            <?php
            }
            ?>
        </select>
    </div>
    <input type="hidden" name="TreeNodeKid" id="TreeNodeKid" value="<?=$TreeNodeKid?>">
    <?= Html::button(Yii::t('common', 'reset'), ['onclick'=>'resetForm()', 'class' => 'btn btn-default pull-right']) ?>
    <?= Html::button(Yii::t('common', 'search'), ['id'=>'searchSubmit', 'class' => 'btn btn-primary pull-right']) ?>
    <?php ActiveForm::end(); ?>
</div>