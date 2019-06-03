<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\framework\FwOrgnization */

?>
<div class="orgnization-view">

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            [
                'label' => Yii::t('backend','relate_{value}',['value'=>Yii::t('backend','company')]),
                'value' => $model->getCompanyName(),
            ],
            [
                'label' => Yii::t('backend','relate_{value}',['value'=>Yii::t('backend','domain')]),
                'value' => $model->getDomainName(),
            ],
            [
                'label' => Yii::t('backend','is_default_orgnization'),
                'value' => $model->getIsDefaultOrgnizationText(),
            ],
            [
                'label' => Yii::t('backend','is_make_org'),
                'value' => $model->getIsMakeOrgText(),
            ],
            [
                'label' => Yii::t('backend','is_service_site'),
                'value' => $model->getIsServiceSiteText(),
            ],
            [
                'label' => Yii::t('backend','orgnization_level'),
                'value' => $model->getOrgnizationLevelName(),
            ],
            [
                'label' => Yii::t('backend','orgnization_manager_id'),
                'value' => $model->getOrgnizationManagerName(),
            ],
            'description:text',
        ],
    ]) ?>

</div>
