<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\framework\FwWechatTemplate */

?>
<div class="company-dictionary-update">

    <?= $this->render('_form', [
        'model' => $model,
        'formType'=>'update',
    ]) ?>

</div>
