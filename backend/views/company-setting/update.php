<?php

use yii\bootstrap\Tabs;
use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $model common\models\framework\FwCompanySetting */

?>
<div class="company-setting-update">

    <?= $this->render('_form', [
        'model' => $model,
        'formType' => 'create',
        'dictionaryModel' => $dictionaryModel
    ]) ?>

</div>
