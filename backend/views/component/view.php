<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\learning\LnComponent */

$this->title = $model->title;
$this->params['breadcrumbs'][] = ['label' => Yii::t('common', 'Ln Components'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="ln-component-view">

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'title',
            'component_code',
            'icon',
            [
                'label' => Yii::t('backend', 'component_type'),
                'value' => $model->getComponentTypeText(),
            ],
            [
                'label' => Yii::t('backend', 'component_category'),
                'value' => $model->getComponentCategoryText(),
            ],
            'file_type',
            [
                'label' => Yii::t('backend', 'is_display_pc'),
                'value' => $model->getDisplayPCText(),
            ],
            [
                'label' => Yii::t('backend', 'is_display_mobile'),
                'value' => $model->getDisplayMobileText(),
            ],
            [
                'label' => Yii::t('backend', 'is_allow_download'),
                'value' => $model->getAllowDownloadText(),
            ],
            [
                'label' => Yii::t('backend', 'transfer_type'),
                'value' => $model->getTransferTypeText(),
            ],
            [
                'label' => Yii::t('backend', 'is_need_upload'),
                'value' => $model->getNeedUploadText(),
            ],
            [
                'label' => Yii::t('backend', 'is_allow_reuse'),
                'value' => $model->getAllowReuseText(),
            ],
            [
                'label' => Yii::t('backend', 'feature_content_type'),
                'value' => $model->getFeatureContentTypeText(),
            ],
            [
                'label' => Yii::t('backend', 'window_mode'),
                'value' => $model->getWindowModeText(),
            ],
            [
                'label' => Yii::t('backend', 'is_record_score'),
                'value' => $model->getIsRecordScoreText(),
            ],
            [
                'label' => Yii::t('backend', 'is_use_vendor'),
                'value' => $model->getIsUseVendorText(),
            ],
            [
                'label' => Yii::t('backend', 'complete_rule'),
                'value' => $model->getCompleteRuleText(),
            ],
            'feature_content',
            'default_time',
            'default_credit',
            'description:text',
            'sequence_number',
        ],
    ]) ?>

</div>
