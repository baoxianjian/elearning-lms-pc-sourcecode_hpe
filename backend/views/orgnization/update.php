<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\framework\FwOrgnization */
/* @var $companyModel common\models\framework\FwCompany */
/* @var $domainModel common\models\framework\FwDomain */

?>
<div class="orgnization-update">

<!--    <h1>--><?//= Html::encode($this->title) ?><!--</h1>-->

    <?= $this->render('_form', [
        'model' => $model,
        'companyModel' => $companyModel,
        'domainModel' => $domainModel,
        'orgnizationLevelModel' => $orgnizationLevelModel
    ]) ?>

</div>
