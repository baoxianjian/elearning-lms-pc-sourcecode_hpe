<?php
/**
 * Created by PhpStorm.
 * User: Liu Cheng
 * Date: 2015/8/27
 * Time: 15:05
 */
use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

?>

<div class="header">
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    <h4 class="modal-title" id="new_task_title"><?=Yii::t('frontend', 'new_task')?></h4>
</div>
<div class="content" id="newTask">
    <?php $form = ActiveForm::begin([
        'id' => 'pushForm',
        'method' => 'post',
        'action' => Yii::$app->urlManager->createUrl('task/task-push'),
    ]); ?>
    <input type="hidden" id="domain_id" name="TaskPushForm[domain]" value="" />
    <input type="hidden" id="is_temp" name="TaskPushForm[is_temp]" value="yes" />
    <div class="col-md-8 col-sm-8">
        <label style="font-weight:600;"><?= Yii::t('frontend', 'task_code') ?>: <?= $model->getTaskCode(Yii::$app->user->identity->company_id) ?></label>
        <a href="javascript:void(0);" class="btn btn-sm btn-default pull-right selectBtn"><?= Yii::t('frontend', 'push_task_add') ?></a>
        <div class="selectPanel selectPanel_task hide">
            <div class="panel panel-default">
                <div class="panel-body">
                    <div class="input-group">
                        <input id="keyword" type="text" class="form-control" placeholder="<?= Yii::t('frontend', 'input_course_exm_invest_fuzzy_search') ?>" aria-describedby="basic-addon2">
                        <a class="btn input-group-addon" id="basic-addon2" onclick="searchItem()"><?= Yii::t('frontend', 'top_search_text') ?></a>
                    </div>
                    <div role="tabpanel">
                        <!-- Nav tabs -->
                        <ul class="nav nav-tabs" role="tablist">
                            <li role="presentation" class="active"><a href="#addNewCourse" aria-controls="addNewCourse" role="tab" data-toggle="tab" onclick="changeType('course')"><?= Yii::t('common', 'course') ?></a></li>
                            <li role="presentation"><a href="#addNewExam" aria-controls="addNewExam" role="tab" data-toggle="tab" onclick="changeType('exam')"><?= Yii::t('frontend', 'exam') ?></a></li>
                            <li role="presentation"><a href="#addNewSurvey" aria-controls="addNewSurvey" role="tab" data-toggle="tab" onclick="changeType('survey')"><?= Yii::t('common', 'investigation') ?></a></li>
                        </ul>
                        <!-- Tab panes -->
                        <div class="tab-content">
                            <div role="tabpanel" class="tab-pane active" id="addNewCourse">
                                <div class="AddtaskList" id="courseList">
                                </div>
                            </div>
                            <div role="tabpanel" class="tab-pane" id="addNewExam">
                                <div class="AddtaskList" id="examList">
                                </div>
                            </div>
                            <div role="tabpanel" class="tab-pane" id="addNewSurvey">
                                <div class="AddtaskList" id="surveyList">
                                </div>
                            </div>
                            <div class="loadingWaiting hide">
                                <span></span>
                                <span></span>
                                <span></span>
                                <span></span>
                                <span></span>
                                <p><?=Yii::t('frontend', 'loading')?>...</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="panel-footer">
                    <label><?=Yii::t('common', 'select_{value}',['value'=>Yii::t('frontend','task')])?></label>
                    <a href="#" class="btn btn-xs btn-success pull-right btnComfirm showList"><?=Yii::t('frontend', 'be_sure')?></a>
                </div>
            </div>
        </div>
        <ul id="taskList" class="taskList theShownList" style="margin: 15px 0;">
        </ul>
    </div>
    <div class="col-md-4 col-sm-4 myGroupList_mini">
        <div class="input-group ">
            <input id="search_object_key" type="text" class="form-control search_people" style="height: 30px;" placeholder="<?=Yii::t('frontend', 'input_position_people')?>" />
              <span class="input-group-btn">
                  <button class="btn btn-success btn-sm" type="button" onclick="searchObject()"><?= Yii::t('frontend', 'top_search_text') ?></button>
                </span>
        </div>
        <ul id="objects" class="list_people hide" style="min-width: 256px">
        </ul>
        <ul id="object_list" class="thumbList">
            <li>
                <input name="TaskPushForm[objects][]" id="all_domain" type="checkbox" />
                <img src="/static/common/images/woman.jpeg" alt="scoreList1" width="99" height="99" />
                <p class="name"><?= Yii::t('frontend', 'push_all_domain') ?></p>
            </li>
        </ul>
    </div>
    <div class="col-md-12" style="float: left;">
        <div class="btn-group" data-toggle="buttons">
            <label style="margin-right:25px;">
                <input id="is_time_push" name="TaskPushForm[time_push]" type="checkbox" value="1" /><?= Yii::t('frontend', 'push_task_at_time') ?>
            </label>
            <label><?=Yii::t('common', 'action_start_at')?>:
                <input id="push_time" name="TaskPushForm[push_prepare_at]" data-type='rili' data-full="1" data-hms="20:00:00" type="text" placeholder="<?=Yii::t('common', 'action_start_at')?>" readonly />
            </label>
        </div>
    </div>
    <div class="col-md-12 centerBtnArea">
        <button id="saveBtn" type="button" class="btn btn-sm btn-success centerBtn" style="margin-bottom:20px;"><?= Yii::t('common', 'save_temp') ?></button>
        <button id="subBtn" type="button" class="btn btn-sm btn-success centerBtn " style="margin-bottom:20px;"><?= Yii::t('frontend', 'push_start') ?></button>
    </div>
    <?php ActiveForm::end(); ?>
    <div class="c"></div>
</div>
<script>
    var currentDate="<?= date('Y-m-d H:i:s') ?>";
    var auFlag = '0';

    $("#subBtn").bind("click", function () {
        disabledBtn();

        var domain = $("#pushForm #domain_id").val();
        var items = $("#pushForm .task_type").length;
        var objects = $("#pushForm input:checkbox[name='TaskPushForm[objects][]']:checked").length;
        var is_time_push = document.getElementById('is_time_push').checked;
        var push_time = $("#push_time").val();

        if (domain == '') {
            app.showMsg('<?= Yii::t('frontend', 'select_domain_again') ?>', 1500);
            enabledBtn();
            return false;
        }
        if (items == 0) {
            app.showMsg('<?= Yii::t('frontend', 'push_task_add_alert') ?>', 1500);
            enabledBtn();
            return false;
        }
        var flag = false;
        $("#pushForm .dateInput").each(function () {
            var val = $(this).val();
            if (val == '') {
                $(this).focus();
                flag = true;
                return;
            }
        });

        if (flag) {
            app.showMsg('<?= Yii::t('frontend', 'set_finish_time') ?>', 1500);
            enabledBtn();
            return false;
        }

        var flag = false;
        $("#pushForm .dateInput").each(function () {
            var val = $(this).val();
            if (val < currentDate) {
                $(this).val(null);
                $(this).focus();
                flag = true;
                return;
            }
        });

        if (flag) {
            app.showMsg('<?= Yii::t('frontend', 'alert_warning_time6') ?>', 1500);
            enabledBtn();
            return false;
        }

        if (auFlag !== '2' && objects == 0) {
            app.showMsg('<?= Yii::t('common', 'select_{value}', ['value' => Yii::t('common', 'push_object')]) ?>', 1500);
            enabledBtn();
            return false;
        }

        if (is_time_push && push_time==='') {
            app.showMsg('<?= Yii::t('frontend', 'set_start_time') ?>', 1500);
            $("#push_time").focus();
            enabledBtn();
            return false;
        }
        if (is_time_push && push_time <= currentDate) {
            app.showMsg('<?= Yii::t('frontend', 'alert_warning_time7') ?>', 1500);
            $("#push_time").focus();
            enabledBtn();
            return false;
        }

        $("#is_temp").val("no");
        submitModalForm('none', 'pushForm', 'none', true, false, null, null);
    });

    $("#saveBtn").bind("click", function () {
        disabledBtn();
        var domain = $("#pushForm #domain_id").val();
        var items = $("#pushForm .task_type").length;
        var objects = $("#pushForm input:checkbox[name='TaskPushForm[objects][]']:checked").length;
        var is_time_push = document.getElementById('is_time_push').checked;
        var push_time = $("#push_time").val();

        if (domain == '') {
            app.showMsg('<?= Yii::t('frontend', 'select_domain_again') ?>', 1500);
            enabledBtn();
            return false;
        }
        if (items == 0) {
            app.showMsg('<?= Yii::t('frontend', 'push_task_add_alert') ?>', 1500);
            enabledBtn();
            return false;
        }
        var flag = false;
        $("#pushForm .dateInput").each(function () {
            var val = $(this).val();
            if (val == '') {
                $(this).focus();
                flag = true;
                return;
            }
        });

        if (flag) {
            app.showMsg('<?= Yii::t('frontend', 'set_finish_time') ?>', 1500);
            enabledBtn();
            return false;
        }

        var flag = false;
        $("#pushForm .dateInput").each(function () {
            var val = $(this).val();
            if (val < currentDate) {
                $(this).val(null);
                $(this).focus();
                flag = true;
                return;
            }
        });

        if (flag) {
            app.showMsg('<?= Yii::t('frontend', 'alert_warning_time6') ?>', 1500);
            enabledBtn();
            return false;
        }

        if (objects == 0) {
            app.showMsg('<?= Yii::t('common', 'select_{value}',['value'=>Yii::t('common','push_object')]) ?>', 1500);
            enabledBtn();
            return false;
        }

        if (is_time_push && push_time==='') {
            app.showMsg('<?= Yii::t('frontend', 'set_start_time') ?>', 1500);
            $("#push_time").focus();
            enabledBtn();
            return false;
        }
        if (is_time_push && push_time <= currentDate) {
            app.showMsg('<?= Yii::t('frontend', 'alert_warning_time7') ?>', 1500);
            $("#push_time").focus();
            enabledBtn();
            return false;
        }

        $("#is_temp").val("yes");
        submitModalForm('none', 'pushForm', 'none', true, false, null, null);
    });

    function disabledBtn() {
        $("#saveBtn").attr({"disabled": "disabled"});
        $("#subBtn").attr({"disabled": "disabled"});
    }
    function enabledBtn() {
        $("#saveBtn").removeAttr("disabled");
        $("#subBtn").removeAttr("disabled");
    }
</script>
<script>
    Array.prototype.remove = function (obj) {
        if (!obj || this.length === 0) {
            return false;
        }
        for (var i = 0, n = 0; i < this.length; i++) {
            if (this[i] != obj) {
                this[n++] = this[i]
            }
        }
        this.length -= 1;
    };

    Array.prototype.indexOf = function(e){
        for(var i=0,j; j=this[i]; i++){
            if(j==e){
                return true;
            }
        }
        return false;
    };


    var search_object_flag = false;

    var currentItemType = 'course';

    var temp_select_course_arr = new Array();
    var temp_select_course_id_arr = new Array();

    var temp_select_exam_arr = new Array();
    var temp_select_exam_id_arr = new Array();

    var temp_select_survey_arr = new Array();
    var temp_select_survey_id_arr = new Array();

    var temp_select_object_id_arr = new Array();

    var selected = '';
    var selectBtn = $(".selectBtn"),
        selectPanel = $(".selectPanel"),
        btnComfirm = $(".btnComfirm");

    selectBtn.bind("click", function () {
        if (selectPanel.hasClass("hide")) {
            selectPanel.removeClass("hide");
        } else {
            selectPanel.addClass("hide");
        }
    })

    btnComfirm.bind("click", function () {
        if (selectPanel.hasClass("hide")) {
            selectPanel.removeClass("hide");
        } else {
            selectPanel.addClass("hide");
        }
    });

    var objects = $("#objects");

    $(function () {
        app.genCalendar();

        $('#search_object_key').keyup(
            function (event) {
                var myevent = event || window.event;
                var mykeyCode = myevent.keyCode;
                //字母，退格，删除，空格
                if (mykeyCode >= 48 && mykeyCode <= 57 || mykeyCode >= 65 && mykeyCode <= 90 || mykeyCode == 8 || mykeyCode == 46 || mykeyCode == 32) {
                    //获得文本框内容
                    var word = $("#search_object_key").val().trim();
                    var domain = $("#domain_id").val();
                    var timeDelay;
                    if (word != "") {
                        //取消上次提交
                        window.clearTimeout(timeDelay);
                        //延迟提交，这边设置的为500ms
                        timeDelay = window.setTimeout(searchObject, 500);
                    } else {
                        if (!$('.list_people').hasClass('hide')) {
                            $('.list_people').addClass('hide');
                        }
                    }
                }
            });

        $(document).bind('click', function () {
            $('.list_people').addClass('hide');
        });
    });

    function changeType(type) {
        currentItemType = type;
    }

    function searchObject() {
        objects.empty();
        //获得文本框内容
        var word = $("#search_object_key").val().trim();
        var domain = $("#domain_id").val();
        if (word != "" && !search_object_flag) {
            search_object_flag = true;
            var url = "<?=Yii::$app->urlManager->createUrl('task/search-object')?>";
            $.post(url, {'key': word, 'domain': domain},
                function (data) {
                    //将返回数据转换为JQuery对象
                    var wordNodes = $(data);
                    wordNodes.each(
                        function (i, n) {
                            var newli = $("<li>").addClass('lessWord');
                            if (n.type === '4') {
                                newli.html(htmlencode(n.name)).attr('title', htmlencode(n.name));
                            }
                            else {
                                var temp = htmlencode(n.name + "(" + n.code + ")");
                                newli.html(temp).attr('title', temp);
                            }
                            newli.attr('data-kid', n.kid).attr('data-type', n.type).click(function () {
                                var obj = $(this);
                                var id = obj.attr('data-kid');
                                var type = obj.attr('data-type');
                                var name = obj.html();

                                var lists = [
                                    {
                                        kid: id,
                                        type: type,
                                        name: name
                                    }];
                                addObjectArrays(lists);
                            });
                            newli.appendTo(objects);
                        }
                    );

                    //当返回的数据长度大于0才显示
                    if (wordNodes.length > 0) {
                        $('.list_people').removeClass('hide').css({
                            width: $('.search_people').width()
                        });
                    } else {
                        if (!$('.list_people').hasClass('hide')) {
                            $('.list_people').addClass('hide');
                        }
                    }
                    search_object_flag = false;
                }
                , "json");
        } else {
            if (!$('.list_people').hasClass('hide')) {
                $('.list_people').addClass('hide');
            }
        }
    }
    function htmlencode(html) {
        var temp = document.createElement("div");
        (temp.textContent != null) ? (temp.textContent = html) : (temp.innerText = html);
        var output = temp.innerHTML;
        temp = null;
        return output;
    }
    function htmldecode(text) {
        var temp = document.createElement("div");
        temp.innerHTML = text;
        var output = temp.innerText || temp.textContent;
        temp = null;
        return output;
    }

    function searchItem() {
        var domain = $("#domain_id").val();
        var keyword = $("#keyword").val().trim();

        if (currentItemType === 'course') {
            var inputdata = {selected: temp_select_course_arr, keyword: keyword, domain: domain};
            loadData("<?=Url::toRoute(['task/search-item'])?>", "courseList", inputdata);
        }
        else if (currentItemType === 'exam') {
            var inputdata = {selected: temp_select_exam_arr, keyword: keyword};
            loadData("<?=Url::toRoute(['task/search-exam'])?>", "examList", inputdata);
        }
        else if (currentItemType === 'survey') {
            var inputdata = {selected: temp_select_survey_arr, keyword: keyword};
            loadData("<?=Url::toRoute(['task/search-survey'])?>", "surveyList", inputdata);
        }
    }

    function addTaskArrays(lists) {
        var temp;
        if (currentItemType === 'course') {
            temp = _.template($("#t2").html(), {variable: 'data'})({datas: lists});
        }
        else if (currentItemType === 'exam') {
            temp = _.template($("#t3").html(), {variable: 'data'})({datas: lists});
        }
        else if (currentItemType === 'survey') {
            temp = _.template($("#t4").html(), {variable: 'data'})({datas: lists});
        }
        $("#taskList").append(temp);
        app.genCalendar();
    }

    function addObjectArrays(lists) {
        if (!temp_select_object_id_arr.indexOf(lists[0].kid)) {
            temp_select_object_id_arr.push(lists[0].kid);
            var temp = _.template($("#t1").html(), {variable: 'data'})({datas: lists});
            $("#object_list").append(temp);
        }
    }

    function loadData(ajaxUrl, container,inputdata) {
        $("#" + container).empty();
        ajaxGet(ajaxUrl, container, bindData,inputdata);
        $(".tab-content > .loadingWaiting").removeClass('hide');
    }
    function bindData(target, data) {
        $(".tab-content > .loadingWaiting").addClass('hide');
        $("#" + target).html(data);
        $("#" + target + ' .pagination a').bind('click', function () {
            var url = $(this).attr('href');
            loadData(url, target, null);
            return false;
        });
    }
    function delTaskItem(obj, kid) {
        $("#" + kid).removeClass('btn-success').html('<?=Yii::t('frontend', 'select')?>').removeAttr('disabled');
        $(obj).parent().remove();

        if (currentItemType === 'course') {
            temp_select_course_arr.remove(kid);
            temp_select_course_id_arr.remove(kid);

            if ((auFlag === '1' && temp_select_course_id_arr.length === 0) || auFlag === '2') {
                auFlag = '0';
            }
        }
        else if (currentItemType === 'exam') {
            temp_select_exam_arr.remove(kid);
            temp_select_exam_id_arr.remove(kid);
        }
        else if (currentItemType === 'survey') {
            temp_select_survey_arr.remove(kid);
            temp_select_survey_id_arr.remove(kid);
        }
        return false;
    }

    function GetLength(str) {
        var realLength = 0, len = str.length, charCode = -1;
        for (var i = 0; i < len; i++) {
            charCode = str.charCodeAt(i);
            if (charCode >= 0 && charCode <= 128) realLength += 1;
            else realLength += 2;
        }
        return realLength;
    }

    /**
     * js截取字符串，中英文都能用
     * @param str：需要截取的字符串
     * @param len: 需要截取的长度
     */
    function cutstr(str, len) {
        var str_length = 0;
        var str_len = 0;
        str_cut = new String();
        str_len = str.length;
        for (var i = 0; i < str_len; i++) {
            a = str.charAt(i);
            str_length++;
            if (escape(a).length > 4) {
                //中文字符的长度经编码之后大于4
                str_length++;
            }
            str_cut = str_cut.concat(a);
            if (str_length >= len) {
                str_cut = str_cut.concat("...");
                return str_cut;
            }
        }
        //如果给定字符串小于指定长度，则返回源字符串；
        if (str_length < len) {
            return str;
        }
    }
</script>

<?= html::jsFile('/static/frontend/js/underscore-min.js') ?>

<!--ace-template demo-->
<script id="t2" type="text/template">
    <%_.each(data.datas, function(item) {%>
    <li>
        <input name="TaskPushForm[items][item_id][]" type="hidden" class="task_id" value="<%=item.kid%>">
        <input name="TaskPushForm[items][item_type][]" type="hidden" class="task_type" value="0">
        <input name="TaskPushForm[items][item_title][]" type="hidden" class="item_title" value="<%=htmlencode(item.course_name)%>">
        <a href="javascript:void(0);" style="top: 2px !important;" class="btn btn-default btn-xs pull-right delEvent" onclick="return delTaskItem(this,'<%=item.kid%>');"><?= Yii::t('common', 'delete_button') ?></a>
        <div class="taskLine">
            <h5 title="<%=item.course_name%>">[<%=htmlencode(item.course_type)%>]<%=htmlencode(item.str_name)%></h5>
        </div>
        <input name="TaskPushForm[items][plan_complete_at][]" style="width: 160px !important; background: #fff;" class="form-control pull-right dateInput " data-type='rili' data-full="1" data-hms="20:00:00" type="text" placeholder="<?= Yii::t('common', 'end_time2') ?>" readonly>
    </li>
    <%});%>
</script>
<script id="t3" type="text/template">
    <%_.each(data.datas, function(item) {%>
    <li>
        <input name="TaskPushForm[items][item_id][]" type="hidden" class="task_id" value="<%=item.kid%>">
        <input name="TaskPushForm[items][item_type][]" type="hidden" class="task_type" value="1">
        <input name="TaskPushForm[items][item_title][]" type="hidden" class="item_title" value="<%=htmlencode(item.title)%>">
        <a href="javascript:void(0);" style="top: 2px !important;" class="btn btn-default btn-xs pull-right delEvent" onclick="return delTaskItem(this,'<%=item.kid%>');"><?= Yii::t('common', 'delete_button') ?></a>
        <div class="taskLine">
            <h5 title="<%=item.title%>">[<?= Yii::t('frontend', 'exam') ?>]<%=htmlencode(item.str_title)%></h5>
        </div>
        <input name="TaskPushForm[items][plan_complete_at][]" style="width: 160px !important; background: #fff;" class="form-control pull-right dateInput " data-type='rili' data-full="1" data-hms="20:00:00" type="text" placeholder="<?= Yii::t('common', 'end_time2') ?>" readonly>
    </li>
    <%});%>
</script>
<script id="t4" type="text/template">
    <%_.each(data.datas, function(item) {%>
    <li>
        <input name="TaskPushForm[items][item_id][]" type="hidden" class="task_id" value="<%=item.kid%>">
        <input name="TaskPushForm[items][item_type][]" type="hidden" class="task_type" value="2">
        <input name="TaskPushForm[items][item_title][]" type="hidden" class="item_title" value="<%=htmlencode(item.title)%>">
        <a href="javascript:void(0);" style="top: 2px !important;" class="btn btn-default btn-xs pull-right delEvent" onclick="return delTaskItem(this,'<%=item.kid%>');"><?= Yii::t('common', 'delete_button') ?></a>
        <div class="taskLine">
            <h5 title="<%=item.title%>">[<?= Yii::t('common', 'investigation') ?>]<%=htmlencode(item.str_title)%></h5>
        </div>
        <input name="TaskPushForm[items][plan_complete_at][]" style="width: 160px !important; background: #fff;" class="form-control pull-right dateInput " data-type='rili' data-full="1" data-hms="20:00:00" type="text" placeholder="<?= Yii::t('common', 'end_time2') ?>" readonly>
    </li>
    <%});%>
</script>
<script id="t1" type="text/template">
    <%_.each(data.datas, function(item) {%>
    <li class="lessWord" title="<%=item.name%>">
        <input name="TaskPushForm[objects][]" type="checkbox" value="<%=item.type%>,<%=item.kid%>" checked />
        <p class="name"><%=item.name%></p>
    </li>
    <%});%>
</script>
