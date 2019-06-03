<?php


/* @var $this yii\web\View */
/* @var $model common\models\framework\FwDictionary */

?>
<div class="modal-div">

    <?= $this->render('_form', [
        'model' => $model,
        'formType' => 'update',
        'dictionaryCategoryModel' => $dictionaryCategoryModel
    ]) ?>

</div>
