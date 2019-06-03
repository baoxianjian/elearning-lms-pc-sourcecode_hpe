<?php

use common\helpers\TStringHelper;
use yii\bootstrap\Tabs;
use yii\helpers\Html;
use yii\widgets\DetailView;
use common\helpers\TTimeHelper;

/* @var $this yii\web\View */
/* @var $model common\models\learning\LnCertificationTemplate */

?>
<div class="eln-certificaiton-template-view">
    <table width="100%" border="0" class="table table-striped table-bordered">
        <tr>
            <th>
                <?= Yii::t('common', 'service_name');?>
            </th>
            <td>
                <?= $model->fwService->service_name ?>
            </td>
        </tr>
        <tr>
            <th>
                <?= Yii::t('common', 'action_status');?>
            </th>
            <td>
                <?= $model->getActionStatusText() ?>
            </td>
        </tr>
        <tr>
            <th>
                <?= Yii::t('common', 'action_time');?>
            </th>
            <td>
                <?= TTimeHelper::toDateTime($model->created_at) ?>
            </td>
        </tr>
        <tr>
            <th>
                <?= Yii::t('common', 'service_log');?>
            </th>
            <td>
                <div style="width:700px;word-wrap:break-word;">
                    <?= TStringHelper::OutPutBr($model->service_log) ?>
                </div>
            </td>
        </tr>
   </table>
</div>
