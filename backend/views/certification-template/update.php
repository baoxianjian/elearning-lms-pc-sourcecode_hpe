<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\learning\LnCertificationTemplate */

?>
<div class="eln-certificaiton-template-update">

    <?= $this->render('_form', [
        'model' => $model,
        'formType'=>'update',
    ]) ?>

</div>
