<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\framework\FwDomain */

?>
<div class="domain-view">

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            [
                'label' => Yii::t('backend','relate_{value}',['value'=>Yii::t('common','company')]),
                'value' => $model->getCompanyName(),
            ],
            [
                'label' => Yii::t('backend', 'share_flag'),
                'value' => $model->getShareFlagText(),
            ],
            'description:text',
        ],
    ]) ?>

</div>
