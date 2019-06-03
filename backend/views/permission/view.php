<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\framework\FwPermission */

?>
<div class="permission-view">

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            [                      // the owner name of the model
                'label' => Yii::t('backend', 'permission_type'),
                'value' => $model->getPermissionTypeText(),
            ],
            'action_url',
            'action_parameter',
            [                      // the owner name of the model
                'label' => Yii::t('backend', 'action_type'),
                'value' => $model->getActionTypeText(),
            ],
            'action_target',
            'action_class',
            'action_tip',
            'system_flag',
            'description:text',
            [
                'label' => Yii::t('backend', 'limitation'),
                'value' => $model->getLimitationText(),
            ],
            [
                'label' => Yii::t('backend', 'is_display'),
                'value' => $model->getIsDisplayText(),
            ],
            'i18n_flag',
        ],
    ]) ?>

</div>
