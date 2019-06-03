<?php

use yii\bootstrap\Tabs;
use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $model common\models\framework\FwUser */

?>
<?=Yii::t('backend','user')?>：<?=Html::encode($model->real_name)?>
<div class="eln-user-role-update">

    <?= $this->render('_form', [
        'model' => $model,
        'selected_keys' => $selected_keys,
        'availableList' => $availableList,
    ]) ?>

</div>
