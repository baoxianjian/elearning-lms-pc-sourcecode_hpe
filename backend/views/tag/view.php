<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\framework\FwTag */

?>
<div class="modal-div">



    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            [
                'label' => Yii::t('common', 'tag_cate_name'),
                'value' => $model->getTagCategoryName(),
            ],
            [
                'label' => Yii::t('common','relate_{value}',['value'=>Yii::t('common','company')]),
                'value' => $model->getCompanyName(),
            ],
            'tag_value',
            'reference_count'
        ],
    ]) ?>

</div>
