<?php
/**
 * Created by PhpStorm.
 * User: adophper
 * Date: 2015/12/10
 * Time: 11:16
 */
use yii\helpers\Url;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use common\helpers\TStringHelper;
use common\models\learning\LnExamination;
use common\models\learning\LnExaminationQuestion;
use common\models\learning\LnExamPaperQuestion;
use common\models\learning\LnExamQuestionOption;

?>
<?= Html::hiddenInput("currentExaminationId",$modResId,['id'=>'currentExaminationId'])?>

<!-- Menu -->
<div data-am-widget="tabs" class="am-tabs am-tabs-d2 am-no-layout">
    <ul class="am-tabs-nav am-cf">
        <li class="am-active p10"><?=$examinationModel->title?></li>
    </ul>
    <div class="am-tabs-bd" style="-webkit-user-select: none; -webkit-user-drag: none;">
        <div class="timeCounter">
            <?php
            if ($examinationModel->examination_mode == LnExamination::EXAMINATION_MODE_TEST &&!empty($examinationModel->limit_time)) {
            $remainder = $examinationModel->limit_time*60 - $findResultModel->examination_duration;
            ?>
            <p>剩余时间:<strong id="examination_djs" data-date="<?=$remainder?>"><?=!empty($remainder) ? TStringHelper::timeSecondToHMS($remainder) : '--'?></strong></p>
            <?php }?>
        </div>
        <div data-tab-panel-0="" class="am-tab-panel am-active p0">
            <form id="examinationForm" name="examinationForm">
                <input type="hidden" name="result_id" id="result_id" value="<?=$findResultModel->kid?>" />
                <input type="hidden" name="examination_id" id="examination_id" value="<?=$examination_id?>" />
                <input type="hidden" name="examination_paper_user_id" id="examination_paper_user_id" value="<?=$findResultModel->examination_paper_user_id?>" />
                <input type="hidden" name="course_id" value="<?=$courseId?>" />
                <input type="hidden" name="course_reg_id" value="<?=$course_reg_id?>" />
                <input type="hidden" name="mod_id" value="<?=$mod_id?>" />
                <input type="hidden" name="mod_res_id" value="<?=$modResId?>" />
                <input type="hidden" name="courseactivity_id" value="<?=$courseactivity_id?>" />
                <input type="hidden" name="component_id" value="<?=$component_id?>" />
                <input type="hidden" name="course_complete_id" value="<?=$courseCompleteFinalId?>" />
                <input type="hidden" name="res_complete_id" value="<?=$findResultModel->res_complete_id?>" />
            <div class="am-list-item-text">
                <ul class="course-chapter">
                    <li>
                        <?php
                            if (!empty($paperQuestion)) {
                                $page = 1;
                                $j = 1;
                                foreach ($paperQuestion as $key => $item) {
                                    if (!empty($item['options']) && $item['relation_type'] == LnExamPaperQuestion::RELATION_TYPE_PAPER) {
                                        $model = new LnExaminationQuestion();
                                        $question_type = $model->getExamQuestionCategoryName($item['examination_question_type']);
                                        ?>
                                        <ul class="course-sections questionGroup_quest" data-attr="<?=$j?>" style="<?=$j>1?'display:none;':''?>">
                                            <p class="course-des"><strong><?= $j ?>.【<?= $question_type ?>】<?= $item['title'] ?></strong></p>
                                        <?php
                                        if ($item['examination_question_type'] == LnExaminationQuestion::EXAMINATION_QUESTION_TYPE_RADIO){
                                            foreach ($item['options'] as $i => $val) {
                                                ?>

                                                <li class="options">
                                                    <label>
                                                        <input name="options[<?=$item['qu_id']?>]" type="radio" value="<?=$val['kid']?>" class="<?=$item['kid']?>" <?=in_array($val['kid'], $selectOptions) ? 'checked' : ''?>> <?=chr(ord('A')+$i)?> <?=$val['option_title']?>
                                                    </label>
                                                </li>
                                                <?php
                                            }
                                        }else if ($item['examination_question_type'] == LnExaminationQuestion::EXAMINATION_QUESTION_TYPE_CHECKBOX){
                                            foreach ($item['options'] as $i => $val) {
                                            ?>
                                                <li class="options">
                                                    <label>
                                                        <input name="options[<?=$item['qu_id']?>][]" type="checkbox" value="<?=$val['kid']?>" class="<?=$item['kid']?>" <?=in_array($val['kid'], $selectOptions) ? 'checked' : ''?>> <?=chr(ord('A')+$i)?> <?=$val['option_title']?>
                                                    </label>
                                                </li>

                                                <?php
                                                }
                                        }else if ($item['examination_question_type'] == LnExaminationQuestion::EXAMINATION_QUESTION_TYPE_INPUT){
                                            /**/
                                        }else if ($item['examination_question_type'] == LnExaminationQuestion::EXAMINATION_QUESTION_TYPE_JUDGE){
                                            $check = null;
                                            foreach ($item['options'] as $val){
                                                if (in_array($val['kid'], $selectOptions)){
                                                    if ($val['is_right_option'] == LnExamQuestionOption::IS_RIGHT_OPTION_YES){
                                                        $check = 1;
                                                    }else{
                                                        $check = 0;
                                                    }
                                                }
                                            }
                                            ?>

                                            <li class="options">
                                                <label>
                                                    <input name="options[<?=$item['qu_id']?>]" type="radio" value="1" class="<?=$item['kid']?>" <?=(!is_null($check) && $check == 0) ? 'checked':''?>>正确
                                                </label>
                                            </li>
                                            <li class="options">
                                                <label>
                                                    <input name="options[<?=$item['qu_id']?>]" type="radio" value="0" class="<?=$item['kid']?>" <?=(!is_null($check) && $check == 1) ? 'checked':''?>>错误
                                                </label>
                                            </li>

                                            <?php
                                        }else if ($item['examination_question_type'] == LnExaminationQuestion::EXAMINATION_QUESTION_TYPE_QA){
                                            /**/
                                        }
                                        ?>
                                        </ul>
                                    <?php
                                        $j ++;
                                    }else{

                                        $page ++;
                                    }
                                }
                            }?>

                    </li>
                </ul>
            </div>
                <input type="hidden" id="auto_submit" value="false" />
            </form>
        </div>
    </div>
</div>

<div class="lesson-btn am-cf m10">
    <button type="button" class="am-btn am-btn-default am-btn-xs" data-am-modal="{target: '#my-popup'}">进度(<span id="current_page">1</span>/<?= $j-1 ?>)</button>
    <button  type="button" class="am-btn am-btn-half am-btn-primary am-btn-xs fr next_page" >下一题</button>
    <button type="button" class="am-btn am-btn-half am-btn-success am-btn-xs fr prev_page" disabled="disabled" >上一题</button>

</div>
<!-- 调查内导航 -->
<div class="am-popup" id="my-popup" style="display: none;">
    <div class="am-popup-inner">
        <div class="am-popup-hd">
            <h4 class="am-popup-title">题目导航</h4>
            <span data-am-modal-close="" class="am-close">×</span>
        </div>
        <div class="am-list-news-bd">
            <div class="am-u-sm-12 am-u-md-10">
                <div class="navPanel">
                    <ul class="panel">
                        <?php
                        if (!empty($paperQuestion)){
                            $m = 1;
                            foreach ($paperQuestion as $val){
                                if (!empty($val['options']) && $val['relation_type'] == LnExamPaperQuestion::RELATION_TYPE_PAPER){
                                    $keys = ArrayHelper::map($val['options'], 'kid', 'kid');
                                    $keys = array_keys($keys);
                                    $intersect = array_intersect($keys, $selectOptions);
                        ?>
                            <li data-attr="label_<?=$m?>"><a class="<?=!empty($intersect)?"done":"undone"?> answer_<?=$val['kid']?> serv" id="answer_<?=$m?>"><?=$m?></a></li>
                            <?php
                            $m++;
                             }
                            }
                        }
                        ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>





<script>
    //每隔15秒记录一次时间
    var durations = 15 * 1000;
    var examinationDuration = setInterval("examinationInterval()", durations);//1000为1秒钟




function play_res_completeFinal(){
    $.get("<?=Url::toRoute(['exam-manage-main/play-res-complete'])?>?system_key=<?= $system_key?>&access_token=<?=$access_token?>&examination_process_id=<?=$findResultModel->kid?>&examination_complete_id=<?=$findFinalResult->kid?>&examination_id=<?=$examinationModel->kid?>&course_id=<?=$courseId?>&mod_res_id=<?=$modResId?>&course_complete_id=<?=$courseCompleteFinalId?>"+"&complete_type=1&course_reg_id=<?=$course_reg_id?>",function(data){

    });
}

function play_res_completing(){
    $.get("<?=Url::toRoute(['exam-manage-main/play-res-complete'])?>?system_key=<?= $system_key?>&access_token=<?=$access_token?>&examination_process_id=<?=$findResultModel->kid?>&examination_complete_id=<?=$findFinalResult->kid?>&examination_id=<?=$examinationModel->kid?>&course_id=<?=$courseId?>&mod_res_id=<?=$modResId?>&course_complete_id=<?=$courseCompleteProcessId?>"+"&complete_type=0&course_reg_id=<?=$course_reg_id?>",function(data){

    });
}
var isTest = <?=$examinationModel->examination_mode?>;
var score = parseFloat('<?=$examinationModel->pass_grade?>');
$(function(){
    var total = $('.course-sections').length;
    var currentIndex = 1;

    //下一题
    $(".next_page").click(function(){
        currentIndex++;
        if(currentIndex <= total){
            $('.course-sections').hide();
            $(".course-sections[data-attr='"+currentIndex+"']").show();

            if(currentIndex >1){
                $(".prev_page").removeAttr("disabled");
            }

            if(currentIndex == total){
                $(this).attr('onClick','submitButtonClick()');
                $(this).html('提交');

            }
        }else{
            currentIndex = total;
            return false;
        }

        $("#current_page").html(currentIndex);
    });

    //上一题
    $(".prev_page").click(function(){
        currentIndex--;
        if(currentIndex<total){
            $(".next_page").removeAttr('onClick');
            $(".next_page").html('下一题');
        }
        if(currentIndex == 1){
            $(this).attr('disabled','disabled');
        }
        else if(currentIndex < 1){
            currentIndex = 1;
        }

        $('.course-sections').hide();
        $(".course-sections[data-attr='"+currentIndex+"']").show();

        $("#current_page").html(currentIndex);
    });

    //题目导航点击
    $(".serv").click(function(){
        j = $(this).parent().attr('data-attr').split('_')[1];
        currentIndex = j;

        $('.course-sections').hide();
        $(".course-sections[data-attr='"+currentIndex+"']").show();
        $(".am-close").trigger('click');
        $("#current_page").html(currentIndex);




        if(currentIndex>1 && currentIndex < total){
            $(".prev_page").removeAttr("disabled");
            $(".next_page").removeAttr('onClick').html('下一题');

        }else if(currentIndex == 1){
            $(".prev_page").attr('disabled','disabled');
            $(".next_page").removeAttr('onClick').html('下一题');

        }
        else if(currentIndex == total){
            $(".prev_page").removeAttr("disabled");
            $(".next_page").attr('onClick','submitButtonClick()').html('提交');
        }

    })

    <?php
    if ($mode == 'normal'){
    ?>

    var temp_url = "<?=Url::toRoute(['exam/temp-save','system_key'=>$system_key,'access_token'=>$access_token])?>&_="+Math.random();
    $("input[name^=options]").on('click', function(){


        var result_id = $("#result_id").val();
        var examination_paper_user_id = $("#examination_paper_user_id").val();
        var examination_id = $("#examination_id").val();
        var node = $(this).attr('name');
        var question_id = node.replace('options','').replace(/\[/g,'').replace(/\]/g,'');
        var options_id = $(this).val();
        //var checked_val = $("input[name^='options["+question_id+"]']:checked").val();
        var checked = 'False';
        if ($(this).is(":checked")){
            checked = 'True';
        }
        $.ajax({
            url: temp_url,
            data: {
                checked: checked,
                result_id: result_id,
                examination_paper_user_id: examination_paper_user_id,
                examination_id: examination_id,
                question_id: question_id,
                options_id: options_id,
                course_id: '<?=$courseId?>',
                mod_id: '<?=$mod_id?>',
                course_reg_id: '<?=$course_reg_id?>',
                mod_res_id: '<?=$modResId?>',
                courseactivity_id: '<?=$courseactivity_id?>',
                component_id: '<?=$component_id?>',
                course_complete_id: '<?=$courseCompleteFinalId?>',
                res_complete_id: '<?=$findResultModel->res_complete_id?>'
            },
            dataType: 'json',
            type: 'POST',
            successs: function(data) {
                /**/
            },
            error: function(res){

            }
        });
    });


    if ($("#examination_djs").length > 0){
        var examination_djs = setInterval(function(){
            var html = "";
            var djs = $("#examination_djs").attr('data-date');
            if (typeof djs == 'undefined') return false;
            var hour = Math.floor(djs / 3600);
            var min = Math.floor((djs - hour * 3600) / 60);
            var sec = Math.floor((djs - hour * 3600 - min * 60));
            if (hour > 0){
                html += hour + '小时';
            }
            if (min > 0){
                html += min + '分';
            }
            if (sec > 0){
                html += sec + '秒';
            }
            djs --;
            if (djs > 0){
                $("#examination_djs").html(html).attr('data-date', djs);
            }else{
                $("#examination_djs").parent().html('考试结束');
                clearInterval(examination_djs);
                /*自动提交*/
                alert('考试时间已到，正在自动提交试卷!');
                $("#auto_submit").val('true');
//                $("#examination_submit").click();
                clearInterval(examinationDuration);
            }
        }, 1000);
    }



    $(".questionGroup_quest").on("click", 'input[name^=options]', function(){

        var questionId = $(this).attr('class');
        if ($("."+questionId + ':checked').length > 0){
            $(".answer_"+questionId).addClass('done');
            $(".answer_"+questionId).removeClass('undone');
        }else{
            $(".answer_"+questionId).removeClass('done');
            $(".answer_"+questionId).addClass('undone');
        }
    });
    <?php
    }
    ?>
});
function examinationInterval(){
    $.post('<?=Url::toRoute(['exam-manage-main/update-duration','system_key'=>$system_key,'access_token'=>$access_token])?>', {
        course_id: '<?=$courseId?>',
        mod_res_id: '<?=$modResId?>',
        course_complete_id: '<?=$courseCompleteFinalId?>',
        course_reg_id: '<?=$course_reg_id?>',
        examination_id: '<?=$examination_id?>',
        mod_id: '<?=$mod_id?>',
        courseactivity_id: '<?=$courseactivity_id?>',
        res_complete_id: '<?=$findResultModel->res_complete_id?>',
        examination_paper_user_id: '<?=$findResultModel->examination_paper_user_id?>'
    });
}
    var play_click = true;
    function submitButtonClick(e){
//        e.preventDefault();
        if (!play_click) {
//            app.showMsg('数据提交中...');
            return false;
        }
        if (play_click == 'reply') {
            return false;
        }
        var auto = $("#auto_submit").val();


        if ($(".panel").find("li").find(".undone").length > 0 && auto == 'false'){
            alert('请完善第'+ $(".panel").find("li").find(".undone").html() + '选项');
            return false;
        }
        play_click = false;
        $.ajax({
            url:"<?=Url::toRoute(['exam-manage-main/submit-result','system_key'=>$system_key,'access_token'=>$access_token,'course_complete_id'=> $courseCompleteFinalId, 'course_reg_id' => $course_reg_id, 'mod_res_id' => $modResId, 'courseId' => $courseId, 'examinationId'=> $id, 'mod_id' => $mod_id])?>",
            type: 'POST',
            data: $("#examinationForm").serialize(),
            async: false,
            success: function(html){
                if (html.data.result == 'success'){
                    //if (isTest == 0) {
                    play_res_completing();
                    play_res_completeFinal();
                    //}
                    clearInterval(examinationDuration);
                    examinationDuration = null;
                    $("#examination_djs").parent().html('考试结束').removeAttr('data-date');
                    location.href = '<?=Url::toRoute(['play/play-preview'])?>?system_key=<?=$system_key?>&access_token=<?=$access_token?>&modResId=<?=$modResId?>&iframe=examination&resultUserId='+ html.data.result_id;
//                    $.get('<?//=Url::toRoute(['/exam-manage-main/play-result'])?>//', {id: html.result_id}, function(data){
//                        if (data){
//                            $(".courseInfo").html(data);
//                        }else{
//                            app.showMsg('网络异常');
//                        }
//                    });
//                    $(window).scrollTop(0);
//                    play_click = 'reply';
                }else{
                    play_click = true;
                    alert('数据提交失败');

                    return false;
                }
            },
            error: function(e){
                alert('页面异常，请刷新重试！');
                return false;
            }
        });
    }
</script>
