<?php

use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\treemanager\FwTreeType */

?>
<div class="modal-div">



    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'tree_type_code',
            'tree_type_name',
//            [
//                'attribute'=>'code_gen_way',
//                'format'=>'text',
//                'value'=> function ($model, $key, $index, $cloumn){
//                    return $model->code_gen_way=='0' ? Yii::t('common', 'code_gen_way_system') :
//                        Yii::t('common', 'code_gen_way_manual');
//                },
//            ],
            [                      // the owner name of the model
                'label' => Yii::t('backend', 'code_gen_way'),
                'value' => $model->getCodeGenWayText(),
            ],
            'code_prefix',
            [
                'label' => Yii::t('backend', 'limitation'),
                'value' => $model->getLimitationText(),
            ],
            'max_level',
            'sequence_number',
            'i18n_flag',
        ],
    ]) ?>

</div>
