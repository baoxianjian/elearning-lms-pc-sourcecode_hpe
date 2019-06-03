<?php


/* @var $this yii\web\View */
/* @var $model common\models\treemanager\FwTreeType */

?>
<div class="modal-div">

    <?= $this->render('_form', [
        'model' => $model,
        'formType' => 'update'
    ]) ?>

</div>
