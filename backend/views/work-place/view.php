<?php

use yii\bootstrap\Tabs;
use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\framework\FwCompanyWechat */

?>
<div class="eln-wechat-template-view">

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            [
                'label' => Yii::t('backend', '{value}_tree_node_code',['value'=>Yii::t('backend','work_place')]),
                'value' => $model->dictionary_code,
            ],
            [
                'label' => Yii::t('backend', '{value}_tree_node_name',['value'=>Yii::t('backend','work_place')]),
                'value' => $model->dictionary_name,
            ],
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
