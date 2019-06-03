<?php

use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\framework\FwTagCategory */

?>
<div class="modal-div">



    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'cate_code',
            'cate_name',
//            [
//                'attribute'=>'code_gen_way',
//                'format'=>'text',
//                'value'=> function ($model, $key, $index, $cloumn){
//                    return $model->code_gen_way=='0' ? Yii::t('common', 'code_gen_way_system') :
//                        Yii::t('common', 'code_gen_way_manual');
//                },
//            ],
            [
                'label' => Yii::t('common', 'limitation'),
                'value' => $model->getLimitationText(),
            ],
            'sequence_number',
        ],
    ]) ?>

</div>
