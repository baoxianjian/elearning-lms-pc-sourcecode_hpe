<?php

use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\framework\FwActionLogFilter */

?>
<div class="modal-div">



    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'filter_code',
            'filter_name',
            'controller_id',
            'action_id',
            'system_flag',
            [
                'label' => Yii::t('common', 'status'),
                'value' => $model->getStatusText(),
            ],
        ],
    ]) ?>

</div>
