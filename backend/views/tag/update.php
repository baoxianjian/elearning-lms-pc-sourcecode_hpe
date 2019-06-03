<?php


/* @var $this yii\web\View */
/* @var $model common\models\framework\FwTag */

?>
<div class="modal-div">

    <?= $this->render('_form', [
        'model' => $model,
        'formType' => 'update',
        'tagCategoryModel' => $tagCategoryModel,
        'companyModel' => $companyModel
    ]) ?>

</div>
