<?php
/**
 * Created by PhpStorm.
 * User: Liu Cheng
 * Date: 2015/8/20
 * Time: 11:30
 */
use components\widgets\TLinkPager;
use common\helpers\TTimeHelper;
use common\helpers\TStringHelper;
use common\models\message\MsTask;
?>
<table class="table table-bordered table-hover table_teacher">
    <tr>
        <td><?=Yii::t('common', 'audience_code')?></td>
        <td><?= Yii::t('frontend', 'date_of_issue') ?></td>
        <td><?= Yii::t('frontend', 'contains_matters') ?></td>
        <td width="17%"><?= Yii::t('common', 'push_status') ?></td>
        <td><?= Yii::t('frontend', 'student_number') ?></td>
        <td><?= Yii::t('frontend', 'task_status') ?></td>
        <td><?= Yii::t('common', 'action') ?></td>
    </tr>
    <? foreach($data as $item):?>
    <tr>
        <td><?= $item->task_code?></td>
        <td><?= TTimeHelper::toDate( $item->created_at)?></td>
        <td><?= $item->item_count;?></td>
        <td><?= TStringHelper::TaskPushStatus($item->task_status,$item->complete_type,$item->push_prepare_at) ?></td>
        <td><?= $item->push_user_count===null?'?':$item->push_user_count ?></td>
        <td><?= $item->getStatusText() ?></td>
        <td>
            <a href="javascript:void(0);" onclick="return viewTask('<?= $item->kid ?>');" class="btn-xs icon iconfont" title="<?= Yii::t('common', 'view_button') ?>">ဇ</a>
            <? if($item->status===MsTask::STATUS_FLAG_TEMP): ?>
                <a href="javascript:void(0);" onclick="return editTask('<?= $item->kid ?>');" class="btn-xs icon iconfont" title="<?=Yii::t('frontend', 'editor_text')?>">&#x1001;</a>
                <a href="javascript:void(0);" onclick="return deleteTask('<?= $item->kid ?>');" class="btn-xs icon iconfont" title="<?= Yii::t('common', 'delete_button') ?>">ဆ</a>
            <? elseif($item->status===MsTask::STATUS_FLAG_NORMAL): ?>
                <? if($item->task_status===MsTask::TASK_STATUS_TODO && $item->push_prepare_at > 0): ?>
                    <a href="javascript:void(0);" onclick="return immediatelyPushTask('<?= $item->kid ?>','<?= $item->task_sponsor_id ?>','<?= $item->domain_id ?>');" class="btn-xs icon iconfont" title="<?= Yii::t('frontend', 'push_now') ?>">ဈ</a>
                    <a href="javascript:void(0);" onclick="return deleteTask('<?= $item->kid ?>');" class="btn-xs icon iconfont" title="<?= Yii::t('common', 'delete_button') ?>">ဆ</a>
                <? endif; ?>
                <? if($item->task_status===MsTask::TASK_STATUS_DONE &&
                    ($item->complete_type===MsTask::COMPLETE_TYPE_ALL_FAIL||
                        $item->complete_type===MsTask::COMPLETE_TYPE_PART_SUCCESS)):?>
                    <a href="javascript:void(0);" onclick="return repushTask('<?= $item->kid ?>','<?= $item->task_sponsor_id ?>','<?= $item->domain_id ?>');" class="btn-xs icon iconfont" title="<?= Yii::t('frontend', 'push_again') ?>">ဈ</a>
                <? endif; ?>
            <? endif; ?>
        </td>
    </tr>
    <? endforeach; ?>
</table>
<nav>
    <?php
    echo TLinkPager::widget([
        'id' => 'page',
        'pagination' => $pages,
        'options'=>['class'=>'pagination pull-right']
    ]);
    ?>
</nav>