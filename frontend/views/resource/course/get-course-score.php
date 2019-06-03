<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/11/17
 * Time: 16:58
 */
use common\models\learning\LnComponent;
use common\models\learning\LnCourseactivity;
use common\models\learning\LnCourseware;
use common\models\learning\LnModRes;
use common\helpers\TTimeHelper;
use components\widgets\TLinkPager;
use yii\helpers\Html;

?>
<table class="table table-bordered table-hover table-striped table-center" style="margin-top:20px;">
    <tbody>
    <tr>
        <td width="40%"><?= Yii::t('common', 'scorm_course_unit') ?></td>
        <td width="15%"><?= Yii::t('common', 'status') ?></td>
        <td width="25%"><?= Yii::t('frontend', 'page_info_publish') ?></td>
        <td width="20%"><?= Yii::t('common', 'action') ?></td>
    </tr>
    <?php
    if($courseMods) {
        foreach ($courseMods as $mod){
            if ($mod->res_type == LnModRes::RES_TYPE_COURSEACTIVITY){
                $model = LnCourseactivity::findOne($mod->courseactivity_id);
                $itemId = $model->object_id;
            }else{
                $model = LnCourseware::findOne($mod->courseware_id);
                $itemId = $model->kid;
            }
            $modResId = $mod->kid;
            $resourceName = $mod->getResourceName();
    ?>
        <tr>
            <td align="left">【<?= Html::encode($mod->lnComponent->title) ?>】<?= Html::encode($resourceName) ?></td>
            <td><?= $mod->getPublishStatusText(); ?></td>
            <td><?= TTimeHelper::toDateTime($mod->updated_at, TTimeHelper::DATE_FORMAT_1) ?></td>
            <td>
                <? if ($mod->publish_status === LnModRes::PUBLIC_STATUS_YES): ?>
                    <a href="javascript:void(0);" onclick="LoadCompleteInfo(this, '<?=$courseId?>', '<?= $modResId ?>', '<?= $itemId ?>', '<?= Html::encode($resourceName) ?>', '<?= $mod->lnComponent->component_code ?>')"><?= Yii::t('frontend', 'transcript_detail') ?></a>
                <? endif; ?>
            </td>
        </tr>
    <?php
        }
    }else{
        ?>
        <tr>
            <td colspan="4"><?= Yii::t('frontend', 'temp_no_record') ?></td>
        </tr>
    <?php
    }
    ?>
    </tbody>
</table>
<nav class="paginationWrapper" style="text-align: right;">
    <?php
    echo TLinkPager::widget([
        'id' => 'page-msg',
        'pagination' => $pages,
    ]);
    ?>
</nav>
<script>
    $(".pagination").on('click', 'a', function (e) {
        e.preventDefault();
        var parent = $(this).parents('.tab-pane').attr('id');
        $.get($(this).attr('href'),function(r){
            $("#"+parent).html(r);
        });
    });
</script>