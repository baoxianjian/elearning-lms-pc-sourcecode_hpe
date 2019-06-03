<?php
/**
 * Created by PhpStorm.
 * User: LiuCheng
 * Date: 2015/6/15
 * Time: 13:18
 */
use common\models\learning\LnCourse;
use common\models\message\MsMessageUser;
use components\widgets\TLinkPager;
use yii\helpers\Html;
use yii\helpers\Url;
use common\helpers\TTimeHelper;

?>
<style>
    #pageSizeSelect_page-msg{ display: none; }
</style>
<div class="header">
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
            aria-hidden="true">&times;</span></button>
    <h4 class="modal-title" id="myModalLabel"><?= Yii::t('frontend', 'tab_btn_news') ?></h4>
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
                <? if($val['course_type']==LnCourse::COURSE_TYPE_ONLINE):?>
                    <td>
                        <? if(!$val['receive_status']): ?>
                            <a href="<?= Yii::$app->urlManager->createUrl(['resource/course/view', 'id' => $val['kid'], 'from' => 'message','msg_id' => $val['kid'],'msg_type' => \common\models\message\MsMessageUser::TYPE_SPECIAL]) ?>"><?= Html::encode($val['course_name']) ?></a>
                        <? else: ?>
                            <a href="<?= Yii::$app->urlManager->createUrl(['resource/course/view', 'id' => $val['kid']]) ?>"><?= $val['course_name'] ?></a>
                        <? endif; ?>
                    </td>
                <? elseif($val['course_type']==LnCourse::COURSE_TYPE_FACETOFACE): ?>
                    <td>
                        <?if(!$val['receive_status']): ?>
                            <a href="<?= Yii::$app->urlManager->createUrl(['resource/course/view', 'id' => $val['kid'], 'from' => 'message','msg_id' => $val['kid'],'msg_type' => \common\models\message\MsMessageUser::TYPE_SPECIAL]) ?>">【<?= Yii::t('common', 'face_to_face') ?><?= Yii::t('common', 'course') ?>】<?= Html::encode($val['course_name']) ?></a>
                        <? else: ?>
                            <a href="<?= Yii::$app->urlManager->createUrl(['resource/course/view', 'id' => $val['kid']]) ?>">【<?= Yii::t('common', 'face_to_face') ?><?= Yii::t('common', 'course') ?>】<?= Html::encode($val['course_name']) ?></a>
                        <? endif; ?>
                    </td>
                <? endif; ?>
                <td><?= TTimeHelper::toDate($val['created_at']) ?></td>
                <td><?= Yii::t('frontend', 'system') ?></td>
                <td>
                    <? if(!$val['receive_status']): ?>
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
        $.post('<?= Url::toRoute(['common/news-mark-read']) ?>', {"id": id},
            function (data) {
                var result = data.result;
                if (result === 'failure') {
                    app.showMsg('<?= Yii::t('common', 'operation_confirm_warning_failure') ?>', 1500);
                }
                else {
                    app.showMsg('<?= Yii::t('common', 'operation_success') ?>', 1500);
                    loadMessage('<?= Url::toRoute(['common/pop-message-news']) ?>', 'task1');
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

        $.post('<?= Url::toRoute(['common/news-mark-read']) ?>', {"id": kids},
            function (data) {
                var result = data.result;
                if (result === 'failure') {
                    app.showMsg('<?= Yii::t('common', 'operation_confirm_warning_failure') ?>', 1500);
                }
                else {
                    app.showMsg('<?= Yii::t('common', 'operation_success') ?>', 1500);
                    loadMessage('<?= Url::toRoute(['common/pop-message-news']) ?>', 'task1');
                }
            }, "json");
    }
</script>