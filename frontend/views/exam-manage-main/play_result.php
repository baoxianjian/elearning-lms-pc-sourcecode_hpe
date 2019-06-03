<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/12/21
 * Time: 13:57
 */
use yii\helpers\Url;
use common\helpers\TStringHelper;
use components\widgets\TBreadcrumbs;
use common\models\learning\LnExamination;
use common\models\learning\LnExaminationResultUser;

if ($mode == 'course') {
    /*$this->params['breadcrumbs'][] = ['label' => $course->course_name, 'url' => ['/resource/course/view', 'id' => $course->kid]];
    $this->params['breadcrumbs'][] = Yii::t('common', 'examination_course_submit');
    $this->params['breadcrumbs'][] = Yii::t('common', 'examination_course_submit');*/
}else{
    $this->params['breadcrumbs'][] = ['label' => Yii::t('common', 'examination_view'), 'url' => ['/exam/view', 'id' => $examination['kid']]];
    $this->params['breadcrumbs'][] = Yii::t('common', 'examination_submit');
    $this->params['breadcrumbs'][] = Yii::t('common', 'examination_submit');
}
?>
<?php
if ($mode == 'exam'){
?>
<div class="container">
    <div class="row">
        <?= TBreadcrumbs::widget([
            'tag' => 'ol',
            'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
        ]) ?>
        <div class="col-md-12 bd">
            <div class="modal-header exam_header">
                <h4 class="modal-title"><?= $examination['title'] ?>
                    <?php
                    if ($examination['examination_question_number']) {
                        ?>
                        &nbsp;&nbsp;&nbsp;&nbsp;<strong><?=Yii::t('frontend', 'exam_gong')?><?= $examination['examination_question_number'] ?><?=Yii::t('frontend', 'exam_ti')?></strong>
                        <?php
                    }
                    ?>
                    <?php
                    if ($examination['examination_range'] == LnExamination::EXAMINATION_RANGE_SELF && $examination['examination_mode'] == LnExamination::EXAMINATION_MODE_TEST) {
                        ?>
                        <span class="pull-right"><?= date('Y-m-d', $examination['start_at']) ?>
                            - <?= date('m-d', $examination['end_at']) ?></span>
                        <?php
                    }
                    ?>
                </h4>
            </div>
<?php
}
?>
<div role="tabpanel" class="tab-pane active" id="teacher_info">
    <div class=" panel-default scoreList">
        <div class="panel-body">
            <div class="infoBlock" style="text-align:center">
                <h3 class="examDes"><?=$examination['after_description']?></h3>
                <ul class="exam_info">
                    <p><?=Yii::t('frontend', 'exam_choose_wanchengriqi')?>：<?=!empty($userResult->end_at)?date('Y-m-d H:i', $userResult->end_at):''?></p>
                    <p><?=Yii::t('frontend', 'exam_yongshi')?>：<?=!empty($userResult->examination_duration) ? TStringHelper::timeSecondToHMS($userResult->examination_duration) : '--'?></p>
                    <?php
                    if ($examination['examination_mode'] == LnExamination::EXAMINATION_MODE_TEST){
                    ?>
                    <p><?=Yii::t('frontend', 'exam_t_times')?>：<?=$examination['limit_attempt_number'] ? $examination['limit_attempt_number'].Yii::t('frontend', 'exam_times') : Yii::t('frontend', 'exam_na')?></p>
                    <p>
                    <?=Yii::t('frontend', 'exam_choose_bencichengji')?>: <strong><?=$userResult->examination_score?></strong>
                    </p>
                    <?php
                    }else{
                    ?>
                    <p>
                    <?=Yii::t('frontend', 'exam_zhengquelv')?>: <strong><?=sprintf("%.2f", $userResult->correct_rate).'%'?>(<?=$userResult->correct_number?>/<?=$userResult->all_number?>)</strong></p>
                    <?php
                    }
                    ?>
                </ul>
                <div class="centerBtnArea">
                    <?php
                    if ($mode == 'exam'){
                    ?>
                        <?php
                        if (!empty($next->kid)) {
                        ?>
                        <a href="<?= Url::toRoute(['/exam/player', 'id' => $next->kid]) ?>"  class="btn btn-sm btn-success centerBtn" style="width:auto;"><?=Yii::t('frontend', 'exam_choose_zaikaoyici')?></a>
                        <?php
                        }else{
                        ?>
                        <a href="<?=Url::toRoute(['/student/index'])?>" class="btn btn-sm btn-success centerBtn" style="width:auto;"><?=Yii::t('frontend', 'exam_choose_fanhuishouye')?></a>
                        <?php
                        }
                    }else{
                    ?>
                    <!--<a href="<?=Url::toRoute(['/resource/course/view', 'id' => $userResult->course_id])?>" class="btn btn-sm btn-success centerBtn" style="width:auto;"><?=Yii::t('frontend', 'exam_choose_fanhuishouye')?></a>-->
                    <?php
                        if (!empty($next->kid)) {
                    ?>
                        <a data-uri="<?= Url::toRoute(['resource/player/examination-study-player', 'result_id' => $next->kid, 'examination_id' => $next->examination_id, 'modResId' => $next->mod_res_id, 'courseId' => $next->course_id, 'courseRegId' => $next->course_reg_id, 'mod_id' => $next->mod_id, 'attempt' => $next->examination_attempt_number, 'coursewareId' => $next->courseactivity_id, 'courseCompleteFinalId' => $next->course_complete_id, 'mode' => 'normal']) ?>" class="btn btn-sm btn-success centerBtn" style="width:auto;" id="start_study"><?=Yii::t('frontend', 'exam_choose_zaikaoyici')?></a>
                    <?php
                        }
                    }
                    ?>
                </div>
            </div>
            <div class="col-sm-12" style="border-top:1px solid #ccc; margin:30px 0 0 0; float:left">
                <p><?=Yii::t('frontend', 'exam_choose_lishicaozuo')?>
                    <?php
                    if ($examination['examination_mode'] == LnExamination::EXAMINATION_MODE_TEST && $examination['limit_attempt_number'] > 0) {
                    ?>
                    (<?=!empty($userResultAll)?count($userResultAll):0?>/<?=$examination['limit_attempt_number']?>)
                    <?php
                    }
                    ?>
                </p>
                <table class="table table-bordered table-hover table-striped table-center" id="kr_examination">
                    <tbody>
                    <tr>
                        <td><?=Yii::t('frontend', 'exam_choose_wanchengshijian')?></td>
                        <td><?=Yii::t('frontend', 'exam_yongshi')?></td>
                        <?php
                        if ($examination['examination_mode'] == LnExamination::EXAMINATION_MODE_TEST) {
                        ?>
                        <td><?=Yii::t('frontend', 'exam_score')?></td>
                        <td><?=Yii::t('frontend', 'exam_choose_caozuo')?></td>
                        <?php
                        }else{
                        ?>
                        <td><?=Yii::t('frontend', 'exam_zhengquelv')?></td>
                        <?php
                        }
                        ?>
                    </tr>
                    <?php
                    if (!empty($userResultAll)){
                        foreach ($userResultAll as $item){
                            ?>
                            <tr>
                                <td class="breakword"><?=date('Y-m-d H:i', $item['end_at'])?></td>
                                <td><?=!empty($item['examination_duration']) ? TStringHelper::timeSecondToHMS($item['examination_duration']) : '--'?></td>
                                <?php
                                if ($examination['examination_mode'] == LnExamination::EXAMINATION_MODE_TEST){
                                ?>
                                <td><?=$item['examination_score'] ? $item['examination_score'] : '--'?></td>
                                <td>
                                    <?php
                                    if ($item['examination_status'] == LnExaminationResultUser::EXAMINATION_STATUS_END){
                                        if ($mode == 'exam') {
                                            ?>
                                            <a href="<?= Url::toRoute(['/exam/play-view', 'id' => $item['kid']]) ?>" class="btn-xs icon iconfont play_view" title="<?=Yii::t('frontend', 'vender_view')?>">&#x1007;</a>
                                            <?php
                                        }else{
                                            ?>
                                            <a href="<?= Url::toRoute(['/exam-manage-main/play-view', 'id' => $item['kid'], 'modResId' => $modResId]) ?>" class="btn-xs icon iconfont play_view" title="<?=Yii::t('frontend', 'vender_view')?>">&#x1007;</a>
                                            <?php
                                        }
                                    }else{
                                        echo '--';
                                    }
                                    ?>
                                </td>
                                <?php
                                }else{
                                ?>
                                <td><?=$item['correct_rate'].'%'?>(<?=$item['correct_number']?>/<?=$item['all_number']?>)</td>
                                <?php
                                }
                                ?>
                            </tr>
                            <?php
                        }
                    }else{
                        ?>
                        <tr>
                            <td colspan="<?=$examination['examination_mode'] == LnExamination::EXAMINATION_MODE_TEST?4:3?>"><?=Yii::t('frontend', 'exam_choose_wushuju')?>!</td>
                        </tr>
                        <?php
                    }
                    ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php
if ($mode == 'exam') {
?>
        </div>
    </div>
</div>
<?php
}
?>
<script>
    $(function(){
        $("#start_study").on('click', function(e){
            e.preventDefault();
            $.get($(this).attr('data-uri'), function(html){
                if (html){
                    $("#player-frame").html(html);
                }
            }) ;
        });
        <?php
        if ($mode == 'course'){
        ?>
        loadCatalog();
        openMenu();
        <?php
        }
        ?>
    });

    function loadCatalog(){
        $("#catalog-frame").empty();
        var scoId = "<?=$scoId?>";
        var ajaxUrl = "<?=Url::toRoute(['resource/course/catalog','modResId'=>$userResult->mod_res_id, 'courseId'=>$course->kid,'courseRegId'=>$userResult->course_reg_id,
        'courseCompleteFinalId'=>$userResult->course_complete_id,'courseCompleteProcessId'=>$courseCompleteProcessId,'modType'=>0,'courseType'=>$course->course_type,
        'attempt'=>$attempt, 'mode' => 'normal'])?>";

        if (scoId != "")
            ajaxUrl = urlreplace(ajaxUrl, 'scoId', scoId);
        ajaxGet(ajaxUrl, "catalog-frame");
    }
</script>
