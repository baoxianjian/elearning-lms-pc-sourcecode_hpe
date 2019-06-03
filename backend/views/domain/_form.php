<?php

use common\helpers\TArrayHelper;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\framework\FwDomain */
/* @var $form yii\widgets\ActiveForm */
?>
<script>

    function validateOtherClientForm()
    {
        if ($('#company_id').val() == '')
        {
            NotyWarning('<?=Yii::t('backend','please_choose_company')?>');
            TabShow('1');
            return false;
        }
        else {
            return true;
        }
    }
</script>

<div class="domain-form">

    <?php $form = ActiveForm::begin([
        'id' => 'clientform-other',
        'enableAjaxValidation' => false,
        'enableClientValidation' => true,
//        'validateOnSubmit' => true
    ]); ?>


    <?= $form->field($model, 'company_id')
        ->label(Yii::t('backend','relate_{value}',['value'=>Yii::t('backend','company')]))
        ->dropDownList(ArrayHelper::map($companyModel,'kid', 'fwTreeNode.tree_node_name'),
        ['prompt'=>Yii::t('backend','select_more'),'id'=>'company_id']) ?>

    <?= $form->field($model, 'share_flag')->radioList([
        '0'=>Yii::t('backend', 'share_flag_exclusive'),
        '1'=>Yii::t('backend', 'share_flag_share')],
        ['separator'=>'&nbsp;&nbsp;']) ?>

    <?= $form->field($model, 'description')->textarea(['rows' => 6, 'maxlength' => 5000]) ?>


    <?php ActiveForm::end(); ?>

</div>
