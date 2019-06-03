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
            [
                'label' => Yii::t('common', 'component_type'),
                'value' => $model->getComponentTypeText(),
            ],
            [
                'label' => Yii::t('common', 'component_category'),
                'value' => $model->getComponentCategoryText(),
            ],
            'file_type',
            [
                'label' => Yii::t('common', 'is_display_pc'),
                'value' => $model->getDisplayPCText(),
            ],
            [
                'label' => Yii::t('common', 'is_display_mobile'),
                'value' => $model->getDisplayMobileText(),
            ],
            [
                'label' => Yii::t('common', 'is_allow_download'),
                'value' => $model->getAllowDownloadText(),
            ],
            [
                'label' => Yii::t('common', 'transfer_type'),
                'value' => $model->getTransferTypeText(),
            ],
            [
                'label' => Yii::t('common', 'is_need_upload'),
                'value' => $model->getNeedUploadText(),
            ],
            [
                'label' => Yii::t('common', 'is_allow_reuse'),
                'value' => $model->getAllowReuseText(),
            ],
            [
                'label' => Yii::t('common', 'feature_content_type'),
                'value' => $model->getFeatureContentTypeText(),
            ],
            [
                'label' => Yii::t('common', 'window_mode'),
                'value' => $model->getWindowModeText(),
            ],
            [
                'label' => Yii::t('common', 'is_record_score'),
                'value' => $model->getIsRecordScoreText(),
            ],
            [
                'label' => Yii::t('common', 'complete_rule'),
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
