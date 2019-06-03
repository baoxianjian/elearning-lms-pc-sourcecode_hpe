<?php

use yii\bootstrap\Tabs;
use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\framework\FwCompanySetting */

?>
<div class="company-setting-view">

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            [
                'label' => Yii::t('common','setting_parameter'),
                'value' => $model->getSettingName(),
            ],
            'value',
            [
                'label' => Yii::t('common','relate_{value}',['value'=>Yii::t('common','company')]),
                'value' => $model->getCompanyName(),
            ],
        ],
    ]) ?>


</div>
