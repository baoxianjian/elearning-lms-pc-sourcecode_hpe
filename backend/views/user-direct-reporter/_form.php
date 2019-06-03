<?php

use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\framework\FwUser */
/* @var $form yii\widgets\ActiveForm */
?>
<script>

    var operation = '';

    var formId = "clientform-update";

    $("#"+formId).on("submit", function(event) {
        event.preventDefault();
        var validateResult = $("#"+formId).data('yiiActiveForm').validated;
        //alert("validateResult:" + validateResult);
        if (validateResult == true) {
            // alert("validateResult:" + validateResult);

            if (operation == 'savecontinue')
            {
                submitModalFormCustomized("",formId,"addModal",false,true);
            }
            else if (operation == 'saveclose')
            {
                submitModalFormCustomized("",formId,"addModal",true,true);
            }
            else if (operation == 'update')
            {
                //alert('update');
                submitModalFormCustomized("",formId,"updateModal",true,true);
            }
        }

    });

    function FormSubmit()
    {
        $("#"+formId).submit();
    }

</script>

<div class="use-position-form">
    <?php $form = ActiveForm::begin([
        'id' => 'clientform-update',
        'enableAjaxValidation' => false,
        'enableClientValidation' => true,
//        'validateOnSubmit' => true
    ]); ?>

    <table class="kv-grid-table table table-bordered table-striped">
        <thead>
            <tr>
                <th colspan="3"><? echo Html::checkbox('checkAllbox',false,['onclick'=>'checkAll(this.checked,"reporting_manager[]");'])?> <?=Yii::t('backend','leader_to_choose')?>：</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $count = 0;
            $totalCount = count($availableList);
    
            if ($totalCount != 0)
                echo '<tr>';
            else {
                echo "<tr><td colspan='6'>".Yii::t('backend','feature_content_type_none')."</td></tr>";
            }
            $currentNumber = 0;
            foreach ($availableList as $key=>$value)
            {
                $count = $count + 1;
                $currentNumber = $currentNumber + 1;
    
                $checked = false;
                if (in_array($key,$selected_keys)) {
                    $checked = true;
                }
                echo "<td width='320'>";
                echo Html::checkbox("reporting_manager[]",$checked,["value"=>$key]);
                echo Html::encode($value);
                echo "</td>";
    
                if ($count % 3 == 0 || $count == $totalCount) {
                    for(;$currentNumber < 3; $currentNumber++) {
                        echo "<td width='320'></td>";
                    }
                    echo "</tr><tr>";
                    $currentNumber = 0;
                }
    
            }
            ?>
<!--            <tr>-->
<!--                <td>-->
<!--                    --><?//  echo Html::checkBoxList('reporting_manager[]', $selected_keys, $availableList,['separator'=>'<br>']);?>
<!--                </td>-->
<!--            </tr>-->
        </tbody>
    </table>
    <?php ActiveForm::end(); ?>

</div>
