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
<script src="/static/app/js/amazeui.min.js"></script>

<div class="am-onePic">
    <img src="/static/app/i/course/Unknown-12.jpeg">
    <h2 class="lessWord"><?= $title ?></h2>
</div>
<!-- startbutton -->
<div class="lesson-btn am-cf m10">
<!--    <button type="button" class="am-btn am-btn-primary am-btn-xs fr" onclick="location.href='exam_examing_spa.html'">开 始</button>-->
    <?php
    if ($mode == 'normal') {
        if (!empty($examinationLast)) {
            ?>
            <a data-uri="<?= Url::toRoute(['player/examination-study-player','system_key'=>$system_key,'access_token'=>$access_token, 'result_id' => $examinationLast->kid, 'examination_id' => $examination_id, 'modResId' => $modResId, 'courseId' => $courseId, 'courseRegId' => $course_reg_id, 'mod_id' => $mod_id, 'attempt' => $attempt, 'coursewareId' => $courseactivity_id, 'courseCompleteFinalId' => $courseCompleteFinalId, 'courseCompleteProcessId' => $courseCompleteProcessId, 'mode' => $mode]) ?>" class="am-btn am-btn-primary am-btn-xs fr" id="start_study">开始</a>
            <?php
        }else {
            ?>
            <a href="javascript:;" class="btn btn-sm btn-success centerBtn disabled" style="width:auto;">您已经完成此项考试!</a>
            <?php
        }
    }else{
        ?>
        <a data-uri="<?= Url::toRoute(['player/examination-study-player','system_key'=>$system_key,'access_token'=>$access_token, 'result_id' => $examinationLast->kid, 'examination_id' => $examination_id, 'modResId' => $modResId, 'courseId' => $courseId, 'courseRegId' => $course_reg_id, 'mod_id' => $mod_id, 'attempt' => $attempt, 'coursewareId' => $courseactivity_id, 'courseCompleteFinalId' => $courseCompleteFinalId, 'courseCompleteProcessId' => $courseCompleteProcessId, 'mode' => $mode]) ?>" class="am-btn am-btn-primary am-btn-xs fr" id="start_study">开始</a>
        <?php
    }
    ?>
</div>

<div data-am-widget="tabs" class="am-tabs am-tabs-d2 am-no-layout">
    <ul class="am-tabs-nav am-cf">
        <li class="am-active"><a href="[data-tab-panel-0]">详情</a></li>
        <li class=""><a href="[data-tab-panel-1]">历史记录</a></li>
    </ul>
    <div class="am-tabs-bd" style="-webkit-user-select: none; -webkit-user-drag: none;">
        <div data-tab-panel-0="" class="am-tab-panel am-active am-in">
            <p><strong>考试说明:</strong></p>
            <div class="am-list-item-text"><?=$examination['pre_description']?></div>
            <br>
            <table class="am-table">
                <tbody>

                <?php
                if ($examination['examination_mode'] == LnExamination::EXAMINATION_MODE_TEST) {
                    if (!empty($examination['limit_time'])) {
                        ?>
                        <tr>
                            <td class="table-fc">时长</td>
                            <td><?=$examination['limit_time']?>分钟</td>
                        </tr>
                        <?php
                    }
                    ?>
                    <tr>
                        <td class="table-fc">尝试次数</td>
                        <td><?= $examination['limit_attempt_number'] == 0 ? '不限制' : $examination['limit_attempt_number'] . '次' ?></td>
                    </tr>

                    <tr>
                        <td class="table-fc">评分方法</td>
                        <td><?php
                            if ($examination['attempt_strategy'] == LnExamination::ATTEMPT_STRATEGY_FIRST) {
                                echo '第一次';
                            } else if ($examination['attempt_strategy'] == LnExamination::ATTEMPT_STRATEGY_AVG) {
                                echo '平均分';
                            } else if ($examination['attempt_strategy'] == LnExamination::ATTEMPT_STRATEGY_LAST) {
                                echo '最后一次';
                            } else if ($examination['attempt_strategy'] == LnExamination::ATTEMPT_STRATEGY_TOP) {
                                echo '最高分';
                            }
                            ?>
                        </td>
                    </tr>
                    <?php
                    if (!empty($examination['pass_grade'])) {
                        ?>
                        <tr>
                            <td class="table-fc">及格分数</td>
                            <td><?=$examination['pass_grade']?>分</td>
                        </tr>
                        <?php
                    }
                }
                else{
                    ?>
                    <p>时长: 不限</p>
                    <?php
                }
                ?>

                </tbody>
            </table>
        </div>
        <div data-tab-panel-1="" class="am-tab-panel">
            <table class="am-table">
                <tbody>
                <tr>
                    <td>完成时间</td>
                    <td>用时</td>
                    <?php
                    if ($examination['examination_mode'] == LnExamination::EXAMINATION_MODE_TEST) {
                        ?>
                        <td>成绩</td>
                        <td>操作</td>
                        <?php
                    }else{
                        ?>
                        <td>正确率</td>
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
                                <td><?=$item['examination_score'] ? $item['examination_score'].' 分' : '--'?></td>
                                <td>
                                    <?php
                                    if ($item['examination_status'] == LnExaminationResultUser::EXAMINATION_STATUS_END){
                                        ?>
                                        <a href="<?=Url::toRoute(['exam-manage-main/play-view', 'id' => $item['kid'], 'modResId' => $modResId,'system_key'=>$system_key,'access_token'=>$access_token])?>" class="btn-xs icon iconfont play_view" title="查看">&#x1007;</a>
                                        <?php
                                    }else{
                                        echo '--';
                                    }
                                    ?>
                                </td>
                                <?php
                            }else{
                                ?>
                                <td><?=sprintf("%.2f", $item['correct_rate']).'%'?>(<?=$item['correct_number']?>/<?=$item['all_number']?>)</td>
                                <?php
                            }
                            ?>
                        </tr>
                        <?php
                    }
                }else{
                    ?>
                    <tr>
                        <td colspan="<?=$examination['examination_mode'] == LnExamination::EXAMINATION_MODE_TEST?4:3?>">无数据!</td>
                    </tr>
                    <?php
                }
                ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!--<div class="col-md-12" <?=$mode=='normal'?'style="padding-left: 0; padding-right: 0;"':''?>>
    <div class="modal-header" style="display: none;">
        <h4 class="modal-title hidden" id="exam_header"><?=$examination['title']?>  &nbsp;&nbsp;&nbsp;&nbsp;<strong>共<?=$examination['examination_question_number']?>题</strong>
        </h4>
    </div>
    <div class="modal-body" style="padding: 0;">
        <div class="">
            <div role="tabpanel" class="tab-pane active" id="teacher_info">
                <div class=" panel-default scoreList">
                    <div class="panel-body">
                        <?php
                        if ($mode == 'normal'){
                        ?>
                        <div class="col-sm-12" style="border-top:1px solid #ccc; margin:30px 0 0 0; float:left">
                            <p>历史记录

                            </p>
                            <table class="table table-bordered table-hover table-striped table-center" id="kr_examination">
                                <tbody>

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
</div> -->
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
    })
</script>
