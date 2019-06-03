<?php
/**
 * Created by PhpStorm.
 * User: Liu Cheng
 * Date: 2016/1/6
 * Time: 20:49
 */
use common\models\learning\LnInvestigation;
use yii\helpers\Html;

?>
<div class="actionBar"></div>
<table class="table table-bordered table-hover table-center">
    <tbody>
    <tr>
        <td><?= Yii::t('common', 'real_name') ?></td>
        <td><?= Yii::t('frontend', 'department') ?></td>
        <td><?= Yii::t('frontend', 'position') ?></td>
        <td><?= Yii::t('common', 'examination_submit_at') ?></td>
        <td><?= Yii::t('frontend', 'result') ?></td>
    </tr>
    <? foreach ($data as $pinfo): ?>
        <tr>
            <td><?=Html::encode($pinfo['real_name']) ?></td>
            <td><?=Html::encode($pinfo['orgnization_name']) ?></td>
            <td><?=Html::encode($pinfo['position_name']) ?></td>
            <td><?=$pinfo['created_at'] ?></td>
            <? if($type===LnInvestigation::INVESTIGATION_TYPE_SURVEY):?>
                <td><a href="###" onclick='view_survey_show("<?=$pinfo['kid'] ?>")' class="btn-xs"><?= Yii::t('common', 'view_button') ?></a></td>
            <? else: ?>
                <td><span class="preview" title="<?=Html::encode($pinfo['option_title']) ?>"><?=Html::encode($pinfo['option_title']) ?></span></td>
            <? endif; ?>
        </tr>
    <? endforeach; ?>
    </tbody>
</table>
