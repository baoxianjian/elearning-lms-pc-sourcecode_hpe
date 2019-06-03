<?php

use yii\bootstrap\Tabs;
use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\framework\FwCompanyWechat */

?>
<div class="company-dictionary-view">

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'template_code',
            'template_name',
            'wechat_template_id',
            'wechat_template_id_short',
            'wechat_template_content:text',
            'description:text',
            [
                'label' => Yii::t('common','status'),
                'value' => $model->getStatusText(),
            ],
            [
                'label' => Yii::t('common','relate_{value}',['value'=>Yii::t('common','company')]),
                'value' => $model->getCompanyName(),
            ],
            'sequence_number'
        ],
    ]) ?>


</div>
