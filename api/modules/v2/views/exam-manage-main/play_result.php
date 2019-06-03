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

<script src="/static/app/js/amazeui.min.js"></script>

<div class="am-onePic">
    <img src="/static/app/i/course/Unknown-12.jpeg">
    <h2 class="lessWord"><?=$examination['title']?></h2>
</div>
<div class="lesson-btn am-cf m10">
    <?php
    if ($mode == 'exam'){
        ?>
        <?php
        if (!empty($next->kid)) {
            ?>
            <!-- 独立考试 -->
            <a href="<?= Url::toRoute(['/v2/exam/player', 'id' => $next->kid,'access_token'=>$access_token,'system_key'=>$system_key]) ?>"  class="am-btn am-btn-primary am-btn-xs fr">再考一次</a>
            <?php
        }
    }else{
        ?>
        <?php
        if (!empty($next->kid)) {
            ?>
            <!-- 课程内考试 -->
            <a data-uri="<?= Url::toRoute(['player/examination-study-player','system_key'=>$system_key,'access_token'=>$access_token, 'result_id' => $next->kid, 'examination_id' => $next->examination_id, 'modResId' => $next->mod_res_id, 'courseId' => $next->course_id, 'courseRegId' => $next->course_reg_id, 'mod_id' => $next->mod_id, 'attempt' => $next->examination_attempt_number, 'coursewareId' => $next->courseactivity_id, 'courseCompleteFinalId' => $next->course_complete_id, 'mode' => 'normal']) ?>" class="am-btn am-btn-primary am-btn-xs fr" id="start_study">再考一次</a>
            <?php
        }
    }
    ?>
<!--    <button type="button" class="am-btn am-btn-primary am-btn-xs fr" onclick="location.href='task_list.html'">返回列表页</button>-->
</div>


<div data-am-widget="tabs" class="am-tabs am-tabs-d2 am-no-layout">
    <ul class="am-tabs-nav am-cf">
        <li class="am-active"><a href="[data-tab-panel-0]">详情</a></li>
        <li class=""><a href="[data-tab-panel-1]">
                历史记录
                <?php
                if ($examination['examination_mode'] == LnExamination::EXAMINATION_MODE_TEST) {
                    ?>
                    (<?=!empty($userResultAll)?count($userResultAll):0?>/<?=$examination['limit_attempt_number']?>)
                    <?php
                }
                ?>
            </a></li>
    </ul>
    <div class="am-tabs-bd" style="-webkit-user-select: none; -webkit-user-drag: none;">
        <div data-tab-panel-0="" class="am-tab-panel am-active am-in">
<!--            <p><strong>考试说明:</strong></p>-->
<!--            <div class="am-list-item-text">您已完成本次考试，成绩将在10月10日公布，请注意查看。</div>-->
            <br>
            <table class="am-table">
                <tbody>
                <tr>
                    <td class="table-fc">完成日期</td>
                    <td><?=!empty($userResult->end_at)?date('Y年m月d日 H:i', $userResult->end_at):''?></td>
                </tr>
                <tr>
                    <td class="table-fc">用时</td>
                    <td><?=!empty($userResult->examination_duration) ? TStringHelper::timeSecondToHMS($userResult->examination_duration) : '--'?></td>
                </tr>
                <?php
                if ($examination['examination_mode'] == LnExamination::EXAMINATION_MODE_TEST){
                    ?>
                    <tr>
                        <td class="table-fc">尝试次数</td>
                        <td><?=$examination['limit_attempt_number'] ? $examination['limit_attempt_number'].'次' : '不限'?></td>
                    </tr>
                    <tr>
                        <td class="table-fc">本次成绩</td>
                        <td><?=$userResult->examination_score?>分</td>
                    </tr>
                    <?php
                }else{
                    ?>
                    <tr>
                        <td class="table-fc">正确率</td>
                        <td><?=sprintf("%.2f", $userResult->correct_rate).'%'?>(<?=$userResult->correct_number?>/<?=$userResult->all_number?>)</td>
                    </tr>
                    <?php
                }
                ?>
                </tbody>
            </table>
        </div>
        <div data-tab-panel-1="" class="am-tab-panel">
            <table class="am-table">
                <tbody>
                <?php
                if (!empty($userResultAll)){
                    foreach ($userResultAll as $item){
                        ?>
                        <tr>
                            <td class="breakword"><?=date('Y年m月d日 H:i', $item['end_at'])?></td>
                            <td><?=!empty($item['examination_duration']) ? TStringHelper::timeSecondToHMS($item['examination_duration']) : '--'?></td>
                            <?php
                            if ($examination['examination_mode'] == LnExamination::EXAMINATION_MODE_TEST){
                                ?>
                                <td><?=$item['examination_score'] ? $item['examination_score'].' 分' : '--'?></td>
                                <td>
                                    <?php
                                    if ($item['examination_status'] == LnExaminationResultUser::EXAMINATION_STATUS_END){
                                        if ($mode == 'exam') {
                                            ?>
                                            <a href="<?= Url::toRoute(['/v2/exam/play-view','system_key'=>$system_key,'access_token'=>$access_token, 'id' => $item['kid']]) ?>" class="btn-xs icon iconfont play_view" title="查看">&#x1007;</a>
                                            <?php
                                        }else{
                                            ?>
                                            <a href="<?= Url::toRoute(['/v2/exam-manage-main/play-view','system_key'=>$system_key,'access_token'=>$access_token, 'id' => $item['kid'], 'modResId' => $modResId]) ?>" class="btn-xs icon iconfont play_view" title="查看">&#x1007;</a>
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
<!--                <tr>-->
<!--                    <td class="table-wc">2015-09-1 15:00</td>-->
<!--                    <td class="table-c-b">90分</td>-->
<!--                    <td class="table-c-b"><a href="###">查看</a></td>-->
<!--                </tr>-->
<!--                <tr>-->
<!--                    <td class="table-wc">2015-09-1 15:00</td>-->
<!--                    <td class="table-c-b">90分</td>-->
<!--                    <td class="table-c-b"><a href="###">查看</a></td>-->
<!--                </tr>-->
<!--                <tr>-->
<!--                    <td class="table-wc">2015-09-1 15:00</td>-->
<!--                    <td class="table-c-b">90分</td>-->
<!--                    <td class="table-c-b"><a href="###">查看</a></td>-->
<!--                </tr>-->
<!--                <tr>-->
<!--                    <td class="table-wc">2015-09-1 15:00</td>-->
<!--                    <td class="table-c-b">90分</td>-->
<!--                    <td class="table-c-b"><a href="###">查看</a></td>-->
<!--                </tr>-->
<!--                <tr>-->
<!--                    <td class="table-wc">2015-09-1 15:00</td>-->
<!--                    <td class="table-c-b">90分</td>-->
<!--                    <td class="table-c-b"><a href="###">查看</a></td>-->
<!--                </tr>-->
<!--                <tr>-->
<!--                    <td class="table-wc">2015-09-1 15:00</td>-->
<!--                    <td class="table-c-b">90分</td>-->
<!--                    <td class="table-c-b"><a href="###">查看</a></td>-->
<!--                </tr>-->
                </tbody>
            </table>
        </div>
    </div>
</div>




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
