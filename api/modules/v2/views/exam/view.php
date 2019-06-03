<?php
use yii\helpers\Url;
?>
<div class="am-onePic">
    <img src="/static/mobile/proto/assets/i/course/Unknown-12.jpeg">
    <h2 class="lessWord" data-fill="title"></h2>
</div>
<div class="lesson-btn am-cf m10">
    <button type="button" class="am-btn am-btn-primary am-btn-xs fr" data-fill="state" onclick="redirect(this)">
    </button>
</div>
<div data-am-widget="tabs" class="am-tabs am-tabs-d2">
    <ul class="am-tabs-nav am-cf">
        <li class="am-active"><a href="[data-tab-panel-0]">详情</a></li>
        <li class=""><a href="[data-tab-panel-1]">历史记录</a></li>
    </ul>
    <div class="am-tabs-bd">
        <div data-tab-panel-0 class="am-tab-panel am-active">
            <p><strong>考试说明:</strong></p>
            <div class="am-list-item-text" data-fill="description"></div>
            <br/>
            <table class="am-table">
                <tbody>
                <tr>
                    <td class="table-fc">考试时间</td>
                    <td data-fill="date_range"></td>
                </tr>
                <tr>
                    <td class="table-fc">时长</td>
                    <td data-fill="limit_time"></td>
                </tr>
                <tr>
                    <td class="table-fc">试题总数</td>
                    <td data-fill="all_number"></td>
                </tr>
                <tr>
                    <td class="table-fc">尝试次数</td>
                    <td data-fill="try_limits"></td>
                </tr>
                <tr>
                    <td class="table-fc">评分方法</td>
                    <td data-fill="strategy"></td>
                </tr>
                <tr>
                    <td class="table-fc">及格分数</td>
                    <td data-fill="pass_grade"></td>
                </tr>
                </tbody>
            </table>
        </div>
        <div data-tab-panel-1 class="am-tab-panel">
            <table class="am-table">
                <tbody id="history">

                </tbody>
            </table>
        </div>
    </div>
</div>
<script src="/static/mobile/assets/js/exam.js"></script>
<script type="text/template" id="history-item">
    <tr>
        <td class="table-wc"><%=item.human_date%></td>
        <td class="table-c-b"><%=item.examination_score%>分</td>
        <td class="table-c-b"><a href="#" onclick="previewResult('<%= testMode ? item.kid : 0 %>')"><%=testMode?'查看':''%></a></td>
    </tr>
</script>
<script>
    $(document).ready(function () {
        var exam = new Exam("<?php echo $access_token;?>","<?php echo $system_key;?>","<?php echo $id;?>");
        exam.setExtraParam(<?php echo json_encode($extra)?>);
        exam.setStandMode(<?php echo $stand ? 'true':'false'?>);
        exam.setDetailUrl("<?php echo Url::toRoute(['exam/detail'])?>");
        exam.setPlayerUrl("<?php echo Url::toRoute(['exam/player'])?>");
        exam.getDetail(['title','date_range','limit_time','try_limits','strategy','pass_grade','description','state','all_number']);
    });
    function redirect(self) {
        location.href = $(self).attr("data-url");
    }
    function previewResult(id) {
        if(id == 0) return;
        location.href = '<?php echo Url::toRoute(['exam/result'])?>?access_token=<?php echo $access_token?>&system_key=<?php echo $system_key?>&result_id='+id;
    }
</script>