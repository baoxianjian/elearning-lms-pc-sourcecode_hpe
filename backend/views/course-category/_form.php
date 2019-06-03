<?php

use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\learning\LnCourseCategory */
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

<div class="course-category-form">

    <?php $form = ActiveForm::begin([
        'id' => 'clientform-other',
        'enableAjaxValidation' => false,
        'enableClientValidation' => true,
//        'validateOnSubmit' => true
    ]); ?>


    <?= $form->field($model, 'company_id')
        ->label(Yii::t('common','relate_{value}',['value'=>Yii::t('common','company')]))
        ->dropDownList(ArrayHelper::map($companyModel,'kid', 'fwTreeNode.tree_node_name'),
        ['prompt'=>Yii::t('common','select_more'),'id'=>'company_id']) ?>

    <?= $form->field($model, 'description')->textarea(['rows' => 6, 'maxlength' => 5000]) ?>


    <?php ActiveForm::end(); ?>

</div>
