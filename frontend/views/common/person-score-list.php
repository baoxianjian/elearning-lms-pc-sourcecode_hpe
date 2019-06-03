<?php
/**
 * Created by PhpStorm.
 * User: Alex Liu
 * Date: 2015/12/3
 * Time: 17:51
 */
use common\models\learning\LnComponent;
use common\models\learning\LnInvestigation;
use components\widgets\TLinkPager;
use yii\helpers\Html;

?>
<table class="table table-bordered table-hover table-striped table-center">
    <tbody>
    <tr>
        <td><?= Yii::t('frontend', 'matter') ?></td>
        <td><?= Yii::t('common', 'status') ?></td>
        <td><?= Yii::t('common', 'examination_score') ?></td>
        <td><?= Yii::t('common', 'action') ?></td>
    </tr>
    <? if ($courseRes): foreach ($courseRes as $res) : ?>
        <tr>
            <td align="left">
                <?= Html::encode($res['title']) ?>
            </td>
            <td>
                <?= $res['status'] ?>
            </td>
            <td>
                <? if ($res['isRecordScore']): ?>
                    <?echo $res['score'] !== null ? $res['score'] : '--' ?>
                <? else: ?>
                    --
                <? endif; ?>
            </td>
            <td>
                <? if ($res['isCourseware']): ?>
                    --
                <? else: ?>
                    <? if ($res['resstatus']): ?>
                        <? if (!$self && $res['componentCode'] === LnComponent::COMPONENT_CODE_INVESTIGATION && $res['item']['answer_type'] === LnInvestigation::ANSWER_TYPE_ANONYMOUS): ?>
                            --
                        <? elseif ($res['componentCode'] === LnComponent::COMPONENT_CODE_HOMEWORK && !$showHomework): ?>
                            --
                        <? else: ?>
                            <a href="javascript:void(0)" onclick="LoadScoreDetail('<?= $res['componentCode'] ?>','<?= $courseModel->kid ?>','<?= $res['modRes']['mod_id'] ?>','<?= $res['modResId'] ?>','<?= $res['itemId'] ?>','<?= $res['item']['company_id'] ?>');"><?= Yii::t('common', 'view_button') ?></a>
                        <? endif; ?>
                    <? else: ?>
                        --
                    <? endif; ?>
                <? endif; ?>
            </td>
        </tr>
    <? endforeach; endif; ?>
    </tbody>
</table>
<nav class="paginationWrapper">
    <?php
    echo TLinkPager::widget([
        'id' => 'page',
        'pagination' => $pages,
    ]);
    ?>
</nav>