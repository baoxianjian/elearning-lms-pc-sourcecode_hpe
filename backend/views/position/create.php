<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model common\models\framework\Position */

?>
<div class="eln-position-create">


    <?= $this->render('_form', [
        'model' => $model,
        'formType'=>'create',
    ]) ?>

</div>
