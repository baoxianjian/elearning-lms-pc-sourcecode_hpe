<?php
use yii\helpers\Url;
?>
<div data-am-widget="tabs" class="am-tabs am-tabs-d2">
    <ul class="am-tabs-nav am-cf">
        <li class="am-active p10"><a href="[data-tab-panel-0]" data-fill="title"></a></li>
    </ul>
    <div class="am-tabs-bd">
        <div class="timeCounter">
            <p>剩余时间:<span id="timer"></span></p>
        </div>
        <div data-tab-panel-0 class="am-tab-panel am-active p0">
            <div class="am-list-item-text">
                <form id="exam-form">
                    <input type="hidden" name="examination_id" value="" />
                    <input type="hidden" name="examination_paper_user_id" value="" />
                    <input type="hidden" name="course_id" value="<?php echo $extra['courseId']?>" />
                    <input type="hidden" name="course_reg_id" value="<?php echo $extra['courseRegId']?>" />
                    <input type="hidden" name="mod_id" value="<?php echo $extra['mod_id']?>" />
                    <input type="hidden" name="mod_res_id" value="<?php echo $extra['modResId']?>" />
                    <input type="hidden" name="courseactivity_id" value="<?php echo $extra['courseactivity_id']?>" />
                    <input type="hidden" name="course_complete_id" value="<?php echo $extra['courseCompleteFinalId']?>" />
                <ul class="course-chapter" id="container">

                </ul>
                </form>
            </div>
        </div>
    </div>
</div>
<div class="lesson-btn am-cf m10">
    <button type="button" class="am-btn am-btn-default am-btn-xs" disabled>进度<span id="process"></span></button>
    <button type="button" class="am-btn am-btn-half am-btn-success am-btn-xs fr" id="nextPage">下一页</button>
    <button type="button" class="am-btn am-btn-half am-btn-primary am-btn-xs fr" id="prevPage">上一页</button>
    <button type="button" class="am-btn am-btn-primary am-btn-xs fr" style="display: none" id="submit">提交</button>
</div>
<script src="/static/mobile/assets/js/exam.js"></script>
<script type="text/template" id="item-radio">
    <li style="display:<%=display%>" data-page_position="<%=calcPage%>" data-item_type="radio" data-question_title="<%=item.title%>" data-question_id="<%=item.kid%>" data-question_type="<%=item.examination_question_type%>">
        <ul class="course-sections">
            <p class="course-des"><strong><%= index%>. <%= item.title%></strong></p>
            <%_.each(item.options,function(option){%>
            <li class="options">
                <label for="<%=option.kid%>">
                    <input <%=debug && option.is_right_option==1?'checked':''%> name="options[<%=item.qu_id%>]" id="<%=option.kid%>" type="radio" value="<%=option.kid%>"  data-title="<%= option.option_title%>"><%= option.option_title%>
                </label>
            </li>
            <%});%>
        </ul>
    </li>
</script>

<script type="text/template" id="item-checkbox">
    <li style="display: <%=display%>" data-page_position="<%=calcPage%>" data-item_type="checkbox" data-question_title="<%=item.title%>" data-question_id="<%=item.kid%>" data-question_type="<%=item.examination_question_type%>">
        <ul class="course-sections">
            <p class="course-des"><strong><%= index%>. <%= item.title%></strong></p>
            <%_.each(item.options,function(option){%>
            <li class="options">
                <label for="<%=option.kid%>">
                    <input <%=debug && option.is_right_option==1?'checked':''%> type="checkbox" value="<%=option.kid%>" id="<%=option.kid%>" name="options[<%=item.qu_id%>]" data-title="<%= option.option_title%>"><%= option.option_title%>
                </label>
            </li>
            <%});%>
        </ul>
    </li>
</script>
<script>
    $(document).ready(function () {
        var dom = {
            container : $("#container"),
            nextPage : $("#nextPage"),
            prevPage : $("#prevPage"),
            submitBtn : $("#submit"),
            processDiv : $("#process"),
            timer : $("#timer"),
            form : $("#exam-form")
        };
        var exam = new Exam("<?php echo $access_token;?>","<?php echo $system_key;?>","<?php echo $id;?>");
        exam.setDebug(false);
        exam.setExtraParam(<?php echo json_encode($extra)?>);
        exam.setStandMode(<?php echo $stand ? 'true':'false'?>);
        exam.setQuestionUrl("<?php echo Url::toRoute(['exam/questions'])?>");
        exam.setRecordUrl("<?php echo Url::toRoute(['exam/update-duration'])?>");
        exam.setSubmitUrl("<?php echo Url::toRoute(['exam-manage-main/submit-result'])?>");
        exam.setMarkCompleteUrl("<?php echo Url::toRoute(['exam-manage-main/play-res-complete'])?>");
        exam.setTemplate(
            dom.container,
            $("#item-radio").html(),
            $("#item-checkbox").html(),
            dom.nextPage,
            dom.prevPage,
            dom.submitBtn,
            dom.processDiv,
            dom.timer,
            dom.form
        );
        exam.onComplete(complete);
        exam.questions("<?php echo $kid;?>");

        dom.nextPage.on("click",function(){
            exam.nextPage();
        });
        dom.prevPage.on('click',function(){
            exam.prevPage()
        });
        dom.submitBtn.on('click',function(){
            $(this).attr("disabled",true);
            exam.complete(false);
        });

        function complete(res) {
            if(res.result == 'success') {
                exam.markComplete();
                setTimeout(function(){
                    location.href = '<?php echo Url::toRoute(['exam/result'])?>'+ exam.queryString({result_id:res.result_id});
                },500);
            } else {
                alert(res.errmsg);
            }
            dom.submitBtn.attr('disabled',false);
        }
    });
</script>