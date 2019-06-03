<?php
/**
 * Created by PhpStorm.
 * User: adophper
 * Date: 2015/12/10
 * Time: 11:16
 */
use yii\helpers\Url;
use common\helpers\TStringHelper;
use common\models\learning\LnExamination;
use common\models\learning\LnExaminationResultUser;

?>
<style>
    #playWindow{min-height: 500px}
</style>
<div id="iframe-player" data-type="exam" class="col-md-12" <?=$mode=='normal'?'style="padding-left: 0; padding-right: 0;"':''?>>
    <div class="modal-header" style="display: none;">
        <h4 class="modal-title hidden" id="exam_header"><?=$examination['title']?>  &nbsp;&nbsp;&nbsp;&nbsp;<strong><?= Yii::t('frontend', 'total_{value}',['value'=>$examination['examination_question_number']]) ?></strong>
        </h4>
    </div>
    <div class="modal-body" style="padding: 0;">
        <div class="">
            <div role="tabpanel" class="tab-pane active" id="teacher_info">
                <div class=" panel-default scoreList">
                    <div class="panel-body">
                        <div class="infoBlock" style="text-align:center">
                            <h3 class="examDes"><?=$examination['pre_description']?></h3>
                            <ul class="exam_info">
                                <?php
                                if ($examination['examination_mode'] == LnExamination::EXAMINATION_MODE_TEST) {
                                    if (!empty($examination['limit_time'])) {
                                    ?>
                                    <li><?=Yii::t('common', 'courseware_time')?>:
                                        <?=$examination['limit_time']?><?=Yii::t('frontend', 'time_minute')?>
                                    </li>
                                    <?php
                                    }
                                    ?>
                                    <li><?=Yii::t('frontend', 'exam_t_times')?>:
                                        <?= $examination['limit_attempt_number'] == 0 ? Yii::t('frontend', 'exam_na') : $examination['limit_attempt_number'] . Yii::t('frontend', 'exam_times') ?>
                                    </li>
                                    <li><?=Yii::t('frontend', 'exam_score_way')?>:
                                        <?php
                                        if ($examination['attempt_strategy'] == LnExamination::ATTEMPT_STRATEGY_FIRST) {
                                            echo Yii::t('frontend', 'exam_first');
                                        } else if ($examination['attempt_strategy'] == LnExamination::ATTEMPT_STRATEGY_AVG) {
                                            echo Yii::t('frontend', 'exam_ap');
                                        } else if ($examination['attempt_strategy'] == LnExamination::ATTEMPT_STRATEGY_LAST) {
                                            echo Yii::t('frontend', 'exam_last');
                                        } else if ($examination['attempt_strategy'] == LnExamination::ATTEMPT_STRATEGY_TOP) {
                                            echo Yii::t('frontend', 'exam_hs');
                                        }
                                        ?>
                                    </li>
                                    <?php
                                    if (!empty($examination['pass_grade'])) {
                                    ?>
                                    <li><?=Yii::t('frontend', 'pass_grade')?>: <?=$examination['pass_grade']?>%</li>
                                    <?php
                                    }
                                }else{
                                ?>
                                <p><?=Yii::t('common', 'courseware_time')?>: <?=Yii::t('frontend', 'exam_na')?></p>
                                <?php
                                }
                                ?>
                            </ul>
                            <div class="centerBtnArea">
                                <?php
                                if ($mode == 'normal') {
                                    if (!empty($examinationLast) && $generate) {
                                    ?>
                                    <a data-uri="<?= Url::toRoute(['resource/player/examination-study-player', 'result_id' => $examinationLast->kid, 'examination_id' => $examination_id, 'modResId' => $modResId, 'courseId' => $courseId, 'courseRegId' => $course_reg_id, 'mod_id' => $mod_id, 'attempt' => $attempt, 'coursewareId' => $courseactivity_id, 'courseCompleteFinalId' => $courseCompleteFinalId, 'courseCompleteProcessId' => $courseCompleteProcessId, 'mode' => $mode]) ?>" class="btn btn-sm btn-success centerBtn" style="width:auto;" id="start_study"><?=Yii::t('frontend', 'exam_start')?></a>
                                    <?php
                                    } else if (!$generate) {
                                    ?>
                                    <p><?=$errMessage?></p>
                                    <?php
                                    }else{
                                    ?>
                                    <a href="javascript:;" class="btn btn-sm btn-success centerBtn disabled" style="width:auto;"><?= Yii::t('frontend', 'exam_exam_done') ?>!</a>
                                    <?php
                                    }
                                }else{
                                ?>
                                    <a data-uri="<?= Url::toRoute(['resource/player/examination-study-player', 'result_id' => $examinationLast->kid, 'examination_id' => $examination_id, 'modResId' => $modResId, 'courseId' => $courseId, 'courseRegId' => $course_reg_id, 'mod_id' => $mod_id, 'attempt' => $attempt, 'coursewareId' => $courseactivity_id, 'courseCompleteFinalId' => $courseCompleteFinalId, 'courseCompleteProcessId' => $courseCompleteProcessId, 'mode' => $mode]) ?>" class="btn btn-sm btn-success centerBtn" style="width:auto;" id="start_study"><?=Yii::t('frontend', 'exam_start')?></a>
                                <?php
                                }
                                ?>
                            </div>
                        </div>
                        <?php
                        if ($mode == 'normal'){
                        ?>
                        <div class="col-sm-12" style="border-top:1px solid #ccc; margin:30px 0 0 0; float:left">
                            <p><?=Yii::t('frontend', 'exam_choose_lishicaozuo')?>
                                <?php
                                if ($examination['examination_mode'] == LnExamination::EXAMINATION_MODE_TEST && $examination['limit_attempt_number'] > 0) {
                                ?>(<?=!empty($resultUser)?count($resultUser):0?>/<?=$examination['limit_attempt_number']?>)
                                <?php
                                }
                                ?>
                            </p>
                            <table class="table table-bordered table-hover table-striped table-center" id="kr_examination">
                                <tbody>
                                <tr>
                                    <td><?=Yii::t('common', 'complete_end_at')?></td>
                                    <td><?=Yii::t('frontend', 'exam_choose_yongshi')?></td>
                                    <?php
                                    if ($examination['examination_mode'] == LnExamination::EXAMINATION_MODE_TEST) {
                                    ?>
                                    <td><?= Yii::t('common', 'examination_score') ?></td>
                                    <td><?= Yii::t('common', 'action') ?></td>
                                    <?php
                                    }else{
                                    ?>
                                    <td><?=Yii::t('frontend', 'exam_zhengquelv')?></td>
                                    <?php
                                    }
                                    ?>
                                </tr>
                                <?php
                                if (!empty($resultUser)){
                                    foreach ($resultUser as $item){
                                ?>
                                <tr>
                                    <td class="breakword"><?=!empty($item['end_at']) ? date('Y年m月d日 H:i', $item['end_at']) : "--"?></td>
                                    <td><?=!empty($item['examination_duration']) ? TStringHelper::timeSecondToHMS($item['examination_duration']) : '--'?></td>
                                    <?php
                                    if ($examination['examination_mode'] == LnExamination::EXAMINATION_MODE_TEST){
                                    ?>
                                    <td><?=$item['examination_score'] ? $item['examination_score'] : '--'?></td>
                                    <td>
                                        <?php
                                        if ($item['examination_status'] == LnExaminationResultUser::EXAMINATION_STATUS_END){
                                        ?>
                                        <a href="<?=Url::toRoute(['/exam-manage-main/play-view', 'id' => $item['kid'], 'modResId' => $modResId])?>" class="btn-xs icon iconfont play_view" title="<?= Yii::t('common', 'view_button') ?>">&#x1007;</a>
                                        <?php
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
                                    <td colspan="<?=$examination['examination_mode'] == LnExamination::EXAMINATION_MODE_TEST?4:3?>"><?=Yii::t('frontend', 'exam_no_data')?></td>
                                </tr>
                                <?php
                                }
                                ?>
                                </tbody>
                            </table>
                        </div>
                        <?php
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    $(function(){
        LoadiFramePlayer();
        $("#start_study").on('click', function(e){
            e.preventDefault();
            $.get($(this).attr('data-uri'), function(html){
                if (html){
                    $("#player-frame").html(html);
                }
            }) ;
        });
    });

    function LoadiFramePlayer(){
        miniScreen();
        if (typeof diffTemp == 'function') {
            diffTemp();
        }
    }
</script>
