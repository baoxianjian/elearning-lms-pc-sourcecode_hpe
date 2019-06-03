<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\framework\FwCompanySystem */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="company-system-form">
    <?php $form = ActiveForm::begin([
        'id' => 'clientform-system',
        'enableAjaxValidation' => false,
        'enableClientValidation' => true,
//        'validateOnSubmit' => true
    ]); ?>


    <table class="kv-grid-table table table-bordered table-striped">
        <thead>
            <tr>
                <th><?php echo Html::checkbox('checkAllbox',false,['onclick'=>'checkAll(this.checked,"company_system[]");'])?> <?= Yii::t('backend','company_to_choose')?>：</th>
            </tr>
        </thead>
        <tbody>
        <tr>
            <td>
                <?php  echo Html::checkBoxList('company_system[]', $selected_keys, $availableList,['separator'=>'</br>']);?>
            </td>
        </tr>
        </tbody>
    </table>

    <?php ActiveForm::end(); ?>

</div>
