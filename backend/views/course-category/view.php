<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\learning\LnCourseCategory */

?>
<div class="course-category-view">

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            [
                'label' => Yii::t('common','relate_{value}',['value'=>Yii::t('common','company')]),
                'value' => $model->getCompanyName(),
            ],
            'description:text',
        ],
    ]) ?>

</div>
