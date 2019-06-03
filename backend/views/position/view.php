<?php

use yii\bootstrap\Tabs;
use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\framework\FwPosition */

?>
<div class="eln-position-view">


    <?php

    echo DetailView::widget([
        'model' => $model,
        'attributes' => [
            'position_code',
            'position_name',
            [
                'label' => Yii::t('common','relate_{value}',['value'=>Yii::t('common','company')]),
                'value' => $model->getCompanyName(),
            ],
            [
                'label' => Yii::t('common', 'share_flag'),
                'value' => $model->getShareFlagText(),
            ],
            [
                'label' => Yii::t('common', 'limitation'),
                'value' => $model->getLimitationText(),
            ],
            [
                'label' => Yii::t('common', 'status'),
                'value' => $model->getStatusText(),
            ],
            'description:text',
        ]
    ]);

    ?>

</div>
