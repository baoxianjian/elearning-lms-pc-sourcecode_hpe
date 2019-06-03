<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/11/18
 * Time: 11:47
 */
use common\models\learning\LnResComplete;
use common\helpers\TTimeHelper;
use components\widgets\TLinkPager;
use yii\helpers\Html;

?>
<table class="table table-bordered table-hover table-striped table-center" style="margin-top:20px;">
    <tbody>
    <tr>
        <td width="15%"><?= Yii::t('common', 'real_name') ?></td>
        <td width="25%"><?= Yii::t('common', 'user_email') ?></td>
        <td width="15%"><?= Yii::t('common', 'mobile') ?></td>
        <td width="10%"><?= Yii::t('common', 'status') ?></td>
        <td width="25%"><?= Yii::t('common', 'complete_end_at') ?></td>
        <td width="10%"><?= Yii::t('common', 'complete_grade') ?></td>
    </tr>
    <? if($resCompletes): foreach ($resCompletes as $res): ?>
        <tr>
            <td><?=Html::encode($res['real_name'])?></td>
            <td><?=Html::encode($res['email'])?></td>
            <td><?=$res['mobile_no']?></td>
            <td>
                <?=Yii::t('common', 'complete_status_'.intval($res['complete_status']))?>
            </td>
            <td><? if($res['end_at']): ?><?=TTimeHelper::toDateTime($res['end_at'],TTimeHelper::DATE_FORMAT_1) ?><? endif; ?></td>
            <td><? if($res['complete_score']): ?><?=$res['complete_score']?><? endif; ?></td>
        </tr>
    <? endforeach; else: ?>
        <tr>
            <td colspan="6"><?= Yii::t('frontend', 'temp_no_record') ?></td>
        </tr>
    <? endif; ?>
    </tbody>
</table>
<nav class="paginationWrapper">
    <?php
    if (!empty($pages)) {
        echo TLinkPager::widget([
            'id' => 'page-msg',
            'pagination' => $pages,
            'displayPageSizeSelect' => false
        ]);
    }
    ?>
</nav>