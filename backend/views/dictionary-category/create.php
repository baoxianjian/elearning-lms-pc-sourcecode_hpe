<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model common\models\framework\FwDictionaryCategory */

?>
<div class="clientform-create">

    <?= $this->render('_form', [
        'model' => $model,
        'formType' => 'create'
    ]) ?>


</div>