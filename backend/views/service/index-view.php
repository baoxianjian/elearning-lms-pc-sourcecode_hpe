<?php

use yii\bootstrap\Tabs;
use yii\helpers\Html;
use yii\widgets\DetailView;
use common\helpers\TTimeHelper;

/* @var $this yii\web\View */
/* @var $model common\models\framework\FwService */

?>
<div class="eln-certificaiton-template-view">

    <?php
    echo DetailView::widget([
        'model' => $model,
        'attributes' => [
            'service_code',
            'service_name',
            [
                'label' => Yii::t('common', 'is_log'),
                'value' => $model->getIsLogText(),
            ],
            [
                'label' => Yii::t('common', 'service_status'),
                'value' => $model->getServiceStatusText(),
            ],
            [
                'label' => Yii::t('common', 'is_allow_restart'),
                'value' => $model->getIsAllowRestartText(),
            ],
            [
                'label' => Yii::t('common', 'service_type'),
                'value' => $model->getServiceTypeText(),
            ],
            [
                'label' => Yii::t('common', 'restart_cycle'),
                'value' => $model->getRestartCycleText(),
            ],
            'run_at',
            'description'
        ]
    ]);

    ?>
</div>
