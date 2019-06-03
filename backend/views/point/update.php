<?php


/* @var $this yii\web\View */
/* @var $model common\models\learning\LnComponent */

?>
<div class="modal-div">

    <?= $this->render('_form', [
        'model' => $model,
        'formType' => 'update',
        'cycleRanges'=>$cycleRanges,
        'statuses'=>$statuses,
        'cycleRangeSel'=>$cycleRangeSel,
    ]) ?>

</div>
