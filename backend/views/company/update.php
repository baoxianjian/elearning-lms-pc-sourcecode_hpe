<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\framework\FwCompany */

?>

<div class="company-update">

    <?= $this->render('_form', [
        'model' => $model,
        'themeModel'=>$themeModel,
        'languageModel'=>$languageModel
    ]) ?>

</div>
