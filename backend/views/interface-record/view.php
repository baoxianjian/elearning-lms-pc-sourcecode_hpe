<?php

use common\helpers\TStringHelper;
use yii\bootstrap\Tabs;
use yii\helpers\Html;
use yii\widgets\DetailView;
use common\helpers\TTimeHelper;

/* @var $this yii\web\View */
/* @var $model common\models\learning\LnCertificationTemplate */

header("Content-type: text/xml");
?>

<div class="eln-certificaiton-template-view">
    <table width="100%" border="0" class="table table-striped table-bordered">
   	    <tr>
            <th>
                <?= Yii::t('common', 'record_id');?>
            </th>
            <td>
                <?= $model->kid ?>
            </td>
        </tr>
        <tr>
            <th>
                <?= Yii::t('common', 'bo_type');?>
            </th>
            <td>
                <?= $model->getBoType() ?>
            </td>
        </tr>
        <tr>
            <th>
                <?= Yii::t('common', 'change_type');?>
            </th>
            <td>
                <?= $model->getChangeType() ?>
            </td>
        </tr>
        <tr>
            <th>
                <?= Yii::t('common', 'handle_result');?>
            </th>
            <td>
                <?=  $model->getHandleResult()  ?>
            </td>
        </tr>
        <tr>
            <th>
                <?= Yii::t('common', 'operate_time');?>
            </th>
            <td>
                <?=  $model->operate_time  ?>
            </td>
        </tr>
        <tr>
            <th>
                <?= Yii::t('common', 'request_soap');?>
            </th>
            <td>
                <div style="width:700px;word-wrap:break-word;">
                   
                    <pre> <?= htmlspecialchars($model->request_soap)?></pre>
                </div>
            </td>
        </tr>
        <tr>
            <th>
                <?= Yii::t('common', 'response_soap');?>
            </th>
            <td>
                <div style="width:700px;word-wrap:break-word;">
                 <pre> <?= htmlspecialchars($model->response_soap)?></pre>
                </div>
            </td>
        </tr>
        <tr>
            <th>
                <?= Yii::t('common', 'error_message');?>
            </th>
            <td>
                <div style="width:700px;word-wrap:break-word;">
                    <?= TStringHelper::OutPutBr($model->error_message) ?>
                </div>
            </td>
        </tr>
   </table>
</div>
