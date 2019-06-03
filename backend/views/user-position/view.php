<?php

use common\models\framework\FwUserPosition;
use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\framework\FwUser */

?>

<div class="use-position-view">



    <table class="kv-grid-table table table-bordered table-striped">
        <thead>
        <tr>
            <th colspan="2"><?=Html::encode($model->real_name)?>，<?=Yii::t('backend','post_choosed')?>：</th>
            <th width="80px" align="center">
                <?=Yii::t('backend','is_main_post')?>
            </th>
        </tr>
        </thead>
        <tbody>

        <?php
        $count = 0;

        foreach ($selected_keys as $key=>$value)
        {
            $count = $count + 1;
            echo "<tr>";
            echo "<td width=20px>". strval($count) ."</td><td>" . Html::encode($value) . "</td>";

            $isMaster = FwUserPosition::NO;
            foreach ($selected_master_keys as $single) {
                if ($single == $key) {
                    $isMaster = FwUserPosition::YES;
                    break;
                }
            }
//                    $isMaster = array_key_exists($key,$userPositionMaster) ? FwUserPosition::YES:FwUserPosition::NO;
            $isMasterStr = $isMaster == FwUserPosition::YES ? Yii::t('common','yes') : Yii::t('common','no');
            echo "<td>". $isMasterStr ."</td>";
            echo "</tr>";
        }

        if ($count == 0)
            echo "<tr><td colspan='3'>".Yii::t('common', 'encrypt_mode_none')."</td></tr>";
        ?>

        </tbody>
    </table>

</div>
