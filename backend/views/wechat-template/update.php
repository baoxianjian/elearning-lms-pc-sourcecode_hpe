<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\framework\FwWechatTemplate */

?>
<div class="eln-wechat-template-update">

    <?= $this->render('_form', [
        'model' => $model,
        'formType'=>'update',
    ]) ?>

</div>
