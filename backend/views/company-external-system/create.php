<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model common\models\framework\FwCompanySystem */

?>

<div class="company-system-create">

    <?= $this->render('_form', [
        'selected_keys' => $selected_keys,
        'availableList' => $availableList,
    ]) ?>

</div>
