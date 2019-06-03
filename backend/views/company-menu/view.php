<?php

use yii\bootstrap\Tabs;
use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\framework\FwCompanyMenu */

?>
<div class="eln-company-menu-view">

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'menu_code',
            'menu_name',
            'parent_menu_id',
            [
                'label' => Yii::t('common','menu_type'),
                'value' => $model->getMenuTypeName(),
            ],
            'action_url',
            'action_parameter',
            [
                'label' => Yii::t('common','action_type'),
                'value' => $model->getActionTypeName(),
            ],
            'action_target',
            'action_icon',
            'action_class',
            'action_tip',
            'description:text',
            [
                'label' => Yii::t('common','share_flag'),
                'value' => $model->getShareFlagText(),
            ],
            [
                'label' => Yii::t('common','status'),
                'value' => $model->getStatusText(),
            ],
            [
                'label' => Yii::t('common','relate_{value}',['value'=>Yii::t('common','company')]),
                'value' => $model->getCompanyName(),
            ],
            'i18n_flag',
            'sequence_number'
        ],
    ]) ?>


</div>
