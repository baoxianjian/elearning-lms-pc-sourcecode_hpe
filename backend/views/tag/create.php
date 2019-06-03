<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model common\models\framework\FwTag */

?>
<div class="clientform-create">

    <?= $this->render('_form', [
        'model' => $model,
        'formType' => 'create',
        'tagCategoryModel' => $tagCategoryModel,
        'companyModel' => $companyModel
    ]) ?>


</div>