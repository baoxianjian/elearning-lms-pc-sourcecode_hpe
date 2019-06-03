<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model common\models\learning\LnComponent */

?>
<div class="clientform-create">

    <?= $this->render('_form', [
        'model' => $model,
        'formType' => 'create'
    ]) ?>


</div>