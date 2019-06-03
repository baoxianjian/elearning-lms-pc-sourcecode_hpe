<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\framework\FwUserPosition */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="use-position-form">
    <?php $form = ActiveForm::begin([
        'id' => 'clientform-position',
        'enableAjaxValidation' => false,
        'enableClientValidation' => true,
//        'validateOnSubmit' => true
    ]); ?>


    <table class="kv-grid-table table table-bordered table-striped">
        <thead>
            <tr>
                <th>
                    <?php echo Html::checkbox('checkAllbox',false,['onclick'=>'checkAll(this.checked,"user_position[]");'])?> <?=Yii::t('backend','post_to_choose')?>：
                </th>
                <th width="80px" align="center">
                    <?=Yii::t('backend','is_main_post')?>
                </th>
            </tr>
        </thead>
        <tbody>
            <?php
                $totalCount = count($availableList);

                if ($totalCount != 0) {
                    foreach ($availableList as $key => $value) {
                        $checked = false;
                        if (in_array($key, $selected_keys)) {
                            $checked = true;
                        }
                        echo "<tr><td>";
                        echo Html::checkbox("user_position[]", $checked, ["value" => $key]);
                        echo Html::encode($value);
                        echo "</td>";

                        $checked = false;
                        if (in_array($key, $selected_master_keys)) {
                            $checked = true;
                        }
                        echo "<td align='center'>";
                        echo Html::checkbox("user_position_master[]", $checked, ["value" => $key]);
                        echo "</td></tr>";
                    }

                }
                else {
                    echo "<tr><td></td></tr>";
                }
            ?>
        </tbody>
    </table>

    <?php ActiveForm::end(); ?>

</div>
