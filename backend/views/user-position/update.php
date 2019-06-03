<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\framework\FwUserPosition */

?>

<div class="use-position-update">

    <?= $this->render('_form', [
        'selected_keys' => $selected_keys,
        'availableList' => $availableList,
//        'availableIsMasterList' => $availableIsMasterList,
        'selected_master_keys'=>$selected_master_keys,
    ]) ?>

</div>
