<?php
use yii\helpers\Url;
use common\helpers\TStringHelper;
?>
<div class="am-onePic">
    <img src="/static/mobile/proto/assets/i/course/Unknown-12.jpeg">
    <h2 class="lessWord" data-fill="title"><?=$examination['title']?></h2>
</div>
<div class="lesson-btn am-cf m10">
    <button type="button" class="am-btn am-btn-primary am-btn-xs fr">返回列表页</button>
</div>
<div data-am-widget="tabs" class="am-tabs am-tabs-d2">
    <ul class="am-tabs-nav am-cf">
        <li class="am-active"><a href="[data-tab-panel-0]">详情</a></li>
        <li class=""><a href="[data-tab-panel-1]">历史记录</a></li>
    </ul>
    <div class="am-tabs-bd">
        <div data-tab-panel-0 class="am-tab-panel am-active">
            <p><strong>考试说明:</strong></p>
            <div class="am-list-item-text"><?=$examination['after_description']?></div>
            <br/>
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
                <tr>
                    <td class="table-fc">本次成绩</td>
                    <td><?=$userResult->examination_score?>分</td>
                </tr>
                </tbody>
            </table>

            <div class="am-tabs-bd">
                <div data-tab-panel-0 class="am-tab-panel am-active p0">
                    <div class="am-list-item-text">
                        <ul class="course-chapter" id="container">

                        </ul>
                    </div>
                </div>
            </div>

        </div>
        <div data-tab-panel-1 class="am-tab-panel">
            <table class="am-table">
                <tbody>
                <?php foreach($userResultAll as $item){?>
                <tr>
                    <td class="table-wc"><?=date('Y年m月d日 H:i', $item['end_at'])?></td>
                    <td class="table-c-b"><?=$item['examination_score'] ? $item['examination_score'].' 分' : '--'?></td>
                    <td><?=sprintf("%.2f", $item['correct_rate']).'%'?>(<?=$item['correct_number']?>/<?=$item['all_number']?>)</td>
                    <?php if($examination['examination_mode'] == 0){?>
                    <td class="table-c-b"><a href="javascript:previewResult('<?php echo $item['kid']?>')">查看</a></td>
                    <?php }?>
                </tr>
                <?php }?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="/static/mobile/assets/js/exam.js"></script>
<script type="text/template" id="item-radio">
    <li style="display:<%=display%>" data-page_position="<%=calcPage%>" data-item_type="radio" data-question_title="<%=item.title%>" data-question_id="<%=item.kid%>" data-question_type="<%=item.examination_question_type%>">
        <ul class="course-sections">
            <p class="course-des"><strong><%= index%>. <%= item.title%></strong></p>
            <%_.each(item.options,function(option){%>
            <li class="options <%= option.is_checked == 1 && option.is_right_option == 0 ? 'exam-option-error' : (option.is_right_option == 1 ? 'exam-option-right':'')%>">
                <label for="<%=option.kid%>">
                    <input <%=debug && option.is_right_option==1?'checked':''%> disabled name="options[<%=item.qu_id%>]" id="<%=option.kid%>" type="radio" value="<%=option.kid%>"  data-title="<%= option.option_title%>"><%= option.option_title%>
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
            <li class="options <%= option.is_checked == 1 && option.is_right_option == 0 ? 'exam-option-error' : (option.is_right_option == 1 ? 'exam-option-right':'')%>">
                <label for="<%=option.kid%>">
                    <input <%=debug && option.is_right_option==1?'checked':''%> disabled type="checkbox" value="<%=option.kid%>" id="<%=option.kid%>" name="options[<%=item.qu_id%>]" data-title="<%= option.option_title%>"><%= option.option_title%>
                </label>
            </li>
            <%});%>
        </ul>
    </li>
</script>
<script>
    function previewResult(id) {
        location.href = '<?php echo Url::toRoute(['exam/result'])?>?access_token=<?php echo $access_token?>&system_key=<?php echo $system_key?>&result_id='+id;
    }
    $(document).ready(function () {
        var dom = {
            container : $("#container")
        };
        var exam = new Exam("<?php echo $access_token;?>","<?php echo $system_key;?>","<?php echo $result_id;?>");
        exam.setDebug(false);
        exam.previewMode(true);
        exam.setQuestionUrl("<?php echo Url::toRoute(['exam/history'])?>");
        exam.setTemplate(
            dom.container,
            $("#item-radio").html(),
            $("#item-checkbox").html()
        );
        exam.questions("<?php echo $result_id;?>");
    });
</script>