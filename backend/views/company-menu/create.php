<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model common\models\framework\FwCompanyMenu */

?>
<div class="eln-company-menu-create">


    <?= $this->render('_form', [
        'model' => $model,
        'formType'=>'create',
    ]) ?>

</div>
