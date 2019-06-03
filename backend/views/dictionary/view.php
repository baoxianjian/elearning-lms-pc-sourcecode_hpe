<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\framework\FwDictionary */

?>
<div class="modal-div">



    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            [
                'label' => Yii::t('common', 'dictionary_cate_name'),
                'value' => $model->getDictionaryCategoryName(),
            ],
            'dictionary_code',
            'dictionary_name',
            'dictionary_value',
            [
                'label' => Yii::t('common', 'status'),
                'value' => $model->getStatusText(),
            ],
            'description:text',
            'sequence_number',
            'i18n_flag',
        ],
    ]) ?>

</div>
