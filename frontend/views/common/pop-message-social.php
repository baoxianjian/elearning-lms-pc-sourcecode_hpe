<?php
/**
 * Created by PhpStorm.
 * User: LiuCheng
 * Date: 2015/6/15
 * Time: 13:18
 */
use components\widgets\TLinkPager;
use yii\helpers\Html;
use yii\helpers\Url;
use common\helpers\TTimeHelper;
use common\models\message\MsMessage;
use common\models\message\MsMessageUser;
?>
<style>
    #pageSizeSelect_page-msg{ display: none; }
</style>
<div class="header">
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
            aria-hidden="true">&times;</span></button>
    <h4 class="modal-title" id="myModalLabel"><?= Yii::t('frontend', 'tab_btn_social') ?></h4>
</div>
<div class="content">
    <table class="table table_teacher" style="margin-top:0; text-align:left;">
        <tbody>
        <tr>
            <td width="50%"><?= Yii::t('frontend', 'question_content') ?></td>
            <td width="20%"><?= Yii::t('common', 'time') ?></td>
            <td width="15%"><?= Yii::t('common', 'art_from') ?></td>
            <td width="15%"><?= Yii::t('common', 'status') ?></td>
        </tr>
        <? foreach ($data as $val): ?>
            <tr>
                <? if($val['object_type']==MsMessage::OBJECT_TYPE_QUESTION):?>
                    <td>
                        <? if($val['receive_status']===MsMessageUser::STATUS_UNRECEIVE): ?>
                            <a href="<?= Yii::$app->urlManager->createUrl(['question/detail', 'id' => $val['object_id'], 'from' => 'message','msg_id' => $val['kid'],'msg_type' => MsMessageUser::TYPE_NORMAL]) ?>"><?= Html::encode($val['title']) ?></a>
                        <? else: ?>
                            <a href="<?= Yii::$app->urlManager->createUrl(['question/detail', 'id' => $val['object_id']]) ?>"><?= Html::encode($val['title']) ?></a>
                        <? endif; ?>
                    </td>
                <? elseif($val['object_type']==MsMessage::OBJECT_TYPE_COURSE):?>
                    <td>
                        <? if($val['receive_status']===MsMessageUser::STATUS_UNRECEIVE): ?>
                            <a href="<?= Yii::$app->urlManager->createUrl(['resource/course/view', 'id' => $val['object_id'], 'from' => 'message','msg_id' => $val['kid'],'msg_type' => MsMessageUser::TYPE_NORMAL]) ?>"><?= Html::encode($val['title']) ?></a>
                        <? else: ?>
                            <a href="<?= Yii::$app->urlManager->createUrl(['resource/course/view', 'id' => $val['object_id']]) ?>"><?= Html::encode($val['title']) ?></a>
                        <? endif; ?>
                    </td>
                <? else: ?>
                    <td><?= Html::encode($val['title']) ?></td>
                <? endif; ?>
                <td><?= TTimeHelper::toDate($val['created_at']) ?></td>
                <td><?=Html::encode($val->getFromText())?></td>
                <td>
                    <? if($val['receive_status']===MsMessageUser::STATUS_UNRECEIVE): ?>
                        <a href="javascript:void(0)" onclick="markRead('<?= $val['kid'] ?>')"><?= Yii::t('frontend', '{value1}_mask_{value2}',['value1'=>'','value2'=>Yii::t('frontend','mask_read')]) ?></a>
                        <input type="hidden" name="kid" value="<?= $val['kid'] ?>"/>
                    <? else: ?>
                        <?= Yii::t('frontend', 'mask_read') ?>
                    <? endif; ?>
                </td>
            </tr>
        <? endforeach; ?>
        <tr>
            <td colspan="4"><a href="javascript:void(0)" onclick="markReadAll()"><?= Yii::t('frontend', '{value1}_mask_{value2}',['value1'=>Yii::t('frontend','all'),'value2'=>Yii::t('frontend','mask_read')]) ?></a></td>
        </tr>
        <tr>
            <td colspan="4">
                <nav class="paginationWrapper">
                    <?php
                    echo TLinkPager::widget([
                        'id' => 'page-msg',
                        'pagination' => $pages,
                    ]);
                    ?>
                </nav>
            </td>
        </tr>
        </tbody>
    </table>
    <div class="c"></div>
</div>
<script>
    function markRead(id) {
        $.post('<?= Url::toRoute(['common/mark-read']) ?>', {"id": id},
            function (data) {
                var result = data.result;
                if (result === 'failure') {
                    app.showMsg('<?= Yii::t('common', 'operation_confirm_warning_failure') ?>', 1500);
                }
                else {
                    app.showMsg('<?= Yii::t('common', 'operation_success') ?>', 1500);
                    loadMessage('<?= Url::toRoute(['common/pop-message-social']) ?>', 'task1');
                }
            }, "json");
    }

    function markReadAll() {
        var inputs = $("input[name='kid']");
        var kids = '';

        $.each(inputs, function (i, n) {
            kids = kids + "," + $(this).val();
        });

        if (kids != '') {
            kids = kids.substr(1);
        }

        $.post('<?= Url::toRoute(['common/mark-read']) ?>', {"id": kids},
            function (data) {
                var result = data.result;
                if (result === 'failure') {
                    app.showMsg('<?= Yii::t('common', 'operation_confirm_warning_failure') ?>', 1500);
                }
                else {
                    app.showMsg('<?= Yii::t('common', 'operation_success') ?>', 1500);
                    loadMessage('<?= Url::toRoute(['common/pop-message-social']) ?>', 'task1');
                }
            }, "json");
    }
</script>