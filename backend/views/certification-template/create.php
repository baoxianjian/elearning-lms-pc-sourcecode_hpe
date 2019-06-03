<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model common\models\learning\LnCertificationTemplate */

?>
<div class="eln-certificaiton-template-create">


    <?= $this->render('_form', [
        'model' => $model,
        'formType'=>'create',
    ]) ?>

</div>
