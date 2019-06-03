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
<style>
    #playWindow{min-height: 500px}
</style>
<?= Html::hiddenInput("currentExaminationId",$modResId,['id'=>'currentExaminationId'])?>
<input type="hidden" data-type='special' id="iframe-player" />
<div class="col-md-12 col-xs-12" <?=$mode=='normal'?'style="padding-left: 0; padding-right: 0;"':''?>>
    <div class="modal-header" id="study_modal_header" style="display: none;">
        <h4 class="modal-title"><?=$examinationModel->title?></h4>
    </div>
    <div class="modal-body" style="padding: 0;">
        <div class="courseInfo">
            <div role="tabpanel" class="tab-pane active" id="teacher_info">
                <div class=" panel-default scoreList">
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
                        <input type="hidden" name="user_id" value="<?=$user_id?>" />
                        <input type="hidden" name="company_id" value="<?=$company_id?>" />
                    <div class="panel-body">
                        <div class="infoBlock pages" data-page="1">
                        <?php
                        if (!empty($paperQuestion)){
                            $page = 1;
                            $j = 1;
                            foreach ($paperQuestion as $key => $item){
                                if (!empty($item['options']) && $item['relation_type'] == LnExamPaperQuestion::RELATION_TYPE_PAPER){
                                    $model = new LnExaminationQuestion();
                                    $question_type = $model->getExamQuestionCategoryName($item['examination_question_type']);
                        ?>
                            <div class="row questionGroup_quest" data-num="<?=$j?>">
                                <div class="col-md-12 col-sm-12">
                                    <div class="form-group form-group-sm">
                                        <label class="col-sm-12 control-label">
                                            <?=$j?>.【<?=$question_type?>】<?=$item['title']?>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-12 col-sm-12">
                            <?php
                                if ($item['examination_question_type'] == LnExaminationQuestion::EXAMINATION_QUESTION_TYPE_RADIO){
                                    foreach ($item['options'] as $i => $val) {
                            ?>
                                    <div class="options">
                                        <label style="margin-right:40px;">
                                            <input name="options[<?=$item['qu_id']?>]" type="radio" value="<?=$val['kid']?>" class="<?=$item['kid']?>" <?=in_array($val['kid'], $selectOptions) ? 'checked' : ''?>> <?=chr(ord('A')+$i)?> <?=$val['option_title']?>
                                        </label>
                                    </div>
                                    <?php
                                    }
                                }else if ($item['examination_question_type'] == LnExaminationQuestion::EXAMINATION_QUESTION_TYPE_CHECKBOX){
                                    foreach ($item['options'] as $i => $val) {
                                    ?>
                                    <div class="options">
                                        <label style="margin-right:40px;">
                                            <input name="options[<?=$item['qu_id']?>][]" type="checkbox" value="<?=$val['kid']?>" class="<?=$item['kid']?>" <?=in_array($val['kid'], $selectOptions) ? 'checked' : ''?>> <?=chr(ord('A')+$i)?> <?=$val['option_title']?>
                                        </label>
                                    </div>
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
                                    <div class="options">
                                        <label style="margin-right:40px;">
                                            <input name="options[<?=$item['qu_id']?>]" type="radio" value="1" class="<?=$item['kid']?>" <?=(!is_null($check) && $check == 0) ? 'checked':''?>><?=Yii::t('common', 'right')?>
                                        </label>
                                    </div>
                                    <div class="options">
                                        <label style="margin-right:40px;">
                                            <input name="options[<?=$item['qu_id']?>]" type="radio" value="0" class="<?=$item['kid']?>" <?=(!is_null($check) && $check == 1) ? 'checked':''?>><?=Yii::t('common', 'action_status_error')?>
                                        </label>
                                    </div>
                            <?php
                                }else if ($item['examination_question_type'] == LnExaminationQuestion::EXAMINATION_QUESTION_TYPE_QA){
                                    /**/
                                }
                            ?>
                                </div>
                            </div>
                            <?php
                                $j ++;
                            }else{
                                $page ++;
                            ?>
                            <hr>
                            <div class="row">
                                <div class="centerBtnArea">
                                    <!-- 上一页下一页按钮 -->
                                    <?php
                                    if ($page > 2) {
                                        ?>
                                        <a href="javascript:;" class="btn btn-sm btn-success centerBtn prevPage" style="width:20%"><?=Yii::t('frontend', 'last_page')?></a>
                                        <?php
                                    }
                                    if ($page <= $countPage) {
                                        ?>
                                        <a href="javascript:;" class="btn btn-sm btn-success centerBtn nextPage" style="width:20%"><?=Yii::t('frontend', 'next_page')?></a>
                                        <?php
                                    }
                                    if ($page > $countPage && $mode == 'normal') {
                                    ?>
                                        <a href="javascript:;" class="btn btn-sm btn-success centerBtn" style="width:20%" id="examination_submit"><?=Yii::t('common', 'submit')?></a>
                                        <?php
                                    }
                                    ?>

                                </div>
                            </div>
                            <?php
                                if ($page <= $countPage){
                            ?>
                            </div>
                            <div class="infoBlock pages hidden" data-page="<?=$page?>">
                            <?php
                                }
                            }
                            ?>
                        <?php
                            }
                        }
                        ?>
                        </div>
                    </div>
                    <input type="hidden" id="auto_submit" value="false" />
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
if ($mode == 'normal') {
?>
<div class="col-xs-12 bd" id="result_tab">
    <?php
    if ($examinationModel->examination_mode == LnExamination::EXAMINATION_MODE_TEST &&!empty($examinationModel->limit_time)) {
        $remainder = $examinationModel->limit_time*60 - $findResultModel->examination_duration;
   ?>
    <div class="modal-header">
        <h4 class="modal-title"><?=Yii::t('frontend', 'rest_time')?>: <strong id="examination_djs" data-date="<?=$remainder?>"><?=!empty($remainder) ? TStringHelper::timeSecondToHMS($remainder) : '--'?></strong></h4>
    </div>
    <?php
    }
    ?>
    <div class="modal-body">
        <p><?=Yii::t('frontend', 'total_qa_{value1}_and_rest_qa_{value2}',['value1'=>($j-1),'value2'=>'<span id="is_answer">'.(!empty($selectQuestion) ? count($selectQuestion) : 0).'</span>'])?></p>
        <div class="answerStatu">
            <ul>
    <?php
    if (!empty($paperQuestion)){
        $m = 1;
        foreach ($paperQuestion as $val){
            if (!empty($val['options']) && $val['relation_type'] == LnExamPaperQuestion::RELATION_TYPE_PAPER){
                $keys = ArrayHelper::map($val['options'], 'kid', 'kid');
                $keys = array_keys($keys);
                $intersect = array_intersect($keys, $selectOptions);
    ?>
            <li class="<?=!empty($intersect)?'':'undone'?> answer_<?=$val['kid']?>" id="answer_<?=$m?>"><span><?=$m?></span></li>
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
<?php
}
?>
<script>
var point_is_showing=false;
function play_res_completeFinal(){
    $.get("<?=Url::toRoute(['exam-manage-main/play-res-complete'])?>?examination_process_id=<?=$findResultModel->kid?>&examination_complete_id=<?=$findFinalResult->kid?>&examination_id=<?=$examinationModel->kid?>&course_id=<?=$courseId?>&mod_res_id=<?=$modResId?>&course_complete_id=<?=$courseCompleteFinalId?>"+"&complete_type=1&course_reg_id=<?=$course_reg_id?>",function(data){
     if(point_is_showing)
     {
         app.hideLoadingMsg();
         setTimeout("scorePointEffect("+data.pointResult.show_point+",'"+data.pointResult.point_name+"',"+data.pointResult.available_point+")",1000);
     }
     else
     {
         app.hideLoadingMsg();
         scorePointEffect(data.pointResult.show_point,"'"+data.pointResult.point_name+"'",data.pointResult.available_point);
         point_is_showing=true;
     }
     });
}

function play_res_completing(){
    $.get("<?=Url::toRoute(['exam-manage-main/play-res-complete'])?>?examination_process_id=<?=$findResultModel->kid?>&examination_complete_id=<?=$findFinalResult->kid?>&examination_id=<?=$examinationModel->kid?>&course_id=<?=$courseId?>&mod_res_id=<?=$modResId?>&course_complete_id=<?=$courseCompleteProcessId?>"+"&complete_type=0&course_reg_id=<?=$course_reg_id?>",function(data){
     if(point_is_showing)
     {
         app.hideLoadingMsg();
         setTimeout("scorePointEffect("+data.pointResult.show_point+","+data.pointResult.point_name+","+data.pointResult.available_point+")",1000);
     }
     else
     {
         app.hideLoadingMsg();
         scorePointEffect(data.pointResult.show_point,data.pointResult.point_name,data.pointResult.available_point);
         point_is_showing=true;
     }
    
     });
}
var isTest = <?=$examinationModel->examination_mode?>;
var score = parseFloat('<?=$examinationModel->pass_grade?>');
var durations = 15 * 1000;
var examinationDuration = null;
$(function(){
    $("#examination_result").remove();
    $("#hideMenu").prepend($("#result_tab").clone().removeClass('hidden').attr('id', 'examination_result'));
    $("#result_tab").remove();

    $(".prevPage").on('click', function(){
        var parents = $(this).parents('.pages');
        parents.prev().removeClass('hidden');
        parents.addClass('hidden');
        $(window).scrollTop(0);
    });
    $(".nextPage").on('click', function(){
        var parents = $(this).parents('.pages');
        parents.next().removeClass('hidden');
        parents.addClass('hidden');
        $(window).scrollTop(0);
    });
    <?php
    if ($mode == 'normal'){
    ?>
    var temp_url = "<?=Url::toRoute(['/exam/temp-save'])?>?_="+Math.random();
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
                html += hour + '<?=Yii::t('common', 'time_hour')?>';
            }
            if (min > 0){
                html += min + '<?=Yii::t('frontend', 'point')?>';
            }
            if (sec > 0){
                html += sec + '<?=Yii::t('frontend', 'second')?>';
            }
            djs --;
            if (djs > 0){
                $("#examination_djs").html(html).attr('data-date', djs);
            }else{
                $("#examination_djs").parent().html('<?=Yii::t('frontend', 'end_of_exam')?>');
                clearInterval(examination_djs);
                /*自动提交*/
                app.showMsg('<?=Yii::t('frontend', 'end_of_exam_submit_answer')?>');
                $("#auto_submit").val('true');
                //$("#examination_submit").click();
                submit();
                clearInterval(examinationDuration);
            }
        }, 1000);
    }
    examinationDuration = setInterval("examinationInterval()", durations);//1000为1秒钟
    $(".questionGroup_quest").on("click", 'input[name^=options]', function(){
        var questionId = $(this).attr('class');
        if ($("."+questionId + ':checked').length > 0){
            $(".answer_"+questionId).removeClass('undone');
        }else{
            $(".answer_"+questionId).addClass('undone');
        }
        $("#is_answer").html($(".answerStatu").find('li').length - $(".answerStatu").find('.undone').length);
    });
    <?php
    }
    ?>
    /*修复onclick在ios环境下的问题*/
    $("#examination_submit").on('click', function(e){
       e.preventDefault();
        submit();
    });
});

var submitHandlerUrl = '<?=Url::toRoute(['/exam-manage-main/submit-result','course_complete_id'=> $courseCompleteFinalId, 'course_reg_id' => $course_reg_id, 'mod_res_id' => $modResId, 'courseId' => $courseId, 'examinationId'=> $id, 'mod_id' => $mod_id])?>';
var play_click = true;
function submit(){
    if (!play_click) {
        app.showMsg('<?=Yii::t('frontend', 'data_submiting')?>...');
        return false;
    }
    if (play_click == 'reply') {
        return false;
    }
    var auto = $("#auto_submit").val();
    if ($(".answerStatu").find(".undone").length > 0 && auto == 'false'){
        app.showMsg('<?=Yii::t('frontend', 'finish_the_{value}',['value'=>''])?>'+ $(".answerStatu").find(".undone").eq(0).find('span').html() + '<?=Yii::t('frontend','option')?>');
        return false;
    }
    play_click = false;
    app.showLoadingMsg('<?=Yii::t('frontend', 'operation_is_in_progress')?>');
    $.ajax({
        url: submitHandlerUrl,
        type: 'POST',
        data: $("#examinationForm").serialize(),
        async: false,
        success: function(html){
            var alerted = false;
            if (html.result == 'success') {
                play_res_completing();
                play_res_completeFinal();
                clearInterval(examinationDuration);
                examinationDuration = null;
                $("#examination_djs").parent().html('<?=Yii::t('frontend', 'end_of_exam')?>').removeAttr('data-date');
                setTimeout(function(){
                    app.hideLoadingMsg();
                    reloadCatalog("<?=\common\models\learning\LnComponent::COMPONENT_CODE_EXAMINATION?>","<?=$modResId?>", "");
                    location.href = '<?=Url::toRoute(['/resource/course/play'])?>?modResId=<?=$modResId?>&iframe=examination&resultUserId='+ html.result_id;
                }, 3000);
            }else{
                play_click = true;
                app.hideLoadingMsg();
                app.showMsg('<?=Yii::t('frontend', 'data_submiting_failed')?>');
                return false;
            }
        },
        error: function(e){
            app.hideLoadingMsg();
            app.showMsg('<?=Yii::t('frontend', 'page_exception')?>，<?=Yii::t('frontend', 'refresh')?>！');
            return false;
        }
    });
}

function examinationInterval(){
    $.post('<?=Url::toRoute(['/exam-manage-main/update-duration'])?>', {
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
</script>
