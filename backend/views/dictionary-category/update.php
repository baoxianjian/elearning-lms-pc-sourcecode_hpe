<?php


/* @var $this yii\web\View */
/* @var $model common\models\framework\FwDictionaryCategory */

?>
<div class="modal-div">

    <?= $this->render('_form', [
        'model' => $model,
        'formType' => 'update'
    ]) ?>

</div>
