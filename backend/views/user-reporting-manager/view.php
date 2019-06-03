<?php

use yii\bootstrap\Tabs;
use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\framework\FwUser */

?>
<?=Yii::t('backend','user')?>：<?=Html::encode($model->real_name)?>
<div class="eln-user-role-view">

    <table class="kv-grid-table table table-bordered table-striped">
        <thead>
            <tr>
                <th colspan="6"><?=Yii::t('backend','subordinate_choosed')?>：</th>
            </tr>
        </thead>
        <tbody>

            <?php
                $count = 0;
                $totalCount = count($selected_keys);

                if ($totalCount != 0)
                    echo '<tr>';
                else {
                    echo "<tr><td colspan='6'>".Yii::t('backend','feature_content_type_none')."</td></tr>";
                }
                $currentNumber = 0;
                foreach ($selected_keys as $key=>$value)
                {
                    $count = $count + 1;
                    $currentNumber = $currentNumber + 1;

                    echo "<td width=20px>" . strval($count) . "</td><td width=300px>" . Html::encode($value) . "</td>";

                    if ($count % 3 == 0 || $count == $totalCount) {
                        for(;$currentNumber < 3; $currentNumber++) {
                            echo "<td colspan='2' width='320px'></td>";
                        }
                        echo "</tr><tr>";
                        $currentNumber = 0;
                    }

                }

            ?>

        </tbody>
    </table>


</div>
