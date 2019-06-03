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
                <th colspan="2"><?=Yii::t('backend','leader_choosed')?>：</th>
            </tr>
        </thead>
        <tbody>

            <?php
                $count = 0;

                foreach ($selected_keys as $key=>$value)
                {
                    $count = $count + 1;
                    echo "<tr><td width=20px>". strval($count) ."</td><td>" . Html::encode($value) . "</td></tr>";
                }

                if ($count == 0)
                    echo "<tr><td colspan='2'>".Yii::t('backend','feature_content_type_none')."</td></tr>";
            ?>

        </tbody>
    </table>


</div>
