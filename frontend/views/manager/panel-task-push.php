<?php
/**
 * Created by PhpStorm.
 * User: LiuCheng
 * Date: 2015/6/18
 * Time: 13:45
 */
use common\helpers\TStringHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;


?>
<?php $form = ActiveForm::begin([
    'id' => 'pushForm',
    'method' => 'post',
]); ?>
<input type="hidden" id="domain_id" name="TaskPushForm[domain]" value="" />
     <div class="col-md-8 col-sm-8" style="float: left!important;">
            <label><?=Yii::t('frontend', 'task_list')?></label>
            <a href="#" class="btn btn-xs btn-default pull-right selectBtn" id="addTask"><?=Yii::t('frontend', 'add_task')?></a>
            <div class="selectPanel selectPanel_task hide" id="selectPanel_task">
              <div class="panel panel-default">
                <div class="panel-body">

                <div class="input-group myTeamSearch">
                    <?if($domain_list!=null && count($domain_list)>1):?>
                        <div class="col-md-2">
                            <div class="btn-group">
                                <button type="button" class="btn btn-sm btn-success dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                                    <?=Yii::t('common', 'select_{value}',['value'=>Yii::t('common','domain')])?> <span class="caret stBtn"></span>
                                </button>
                                <ul class="dropdown-menu">
                                    <? foreach ($domain_list as $domain): ?>
                                        <li><a href="#" data-kid="<?= $domain->kid ?>"
                                               data-name="<?= Html::encode($domain->domain_name) ?>" onclick="selectDomain('<?= $domain->kid ?>','<?=$domain->domain_name?>')"
                                               data-toggle="modal"
                                                ><?= Html::encode($domain->domain_name) ?></a></li>
                                    <? endforeach; ?>
                                </ul>
                            </div>
                        </div>
                    <?endif;?>
                  <div class="col-md-10">
                    <input type="text" class="form-control" placeholder="<?= Yii::t('frontend', 'input_course_exm_invest_fuzzy_search') ?>" aria-describedby="basic-addon2" id="keyword">
                    <a class="btn btn-sm btn-default" id="basic-addon2"  onclick="searchCourses()"><?= Yii::t('frontend', 'top_search_text') ?></a>
                  </div>
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
                  <div class="centerBtnArea">
                    <a href="#" class="btn btn-xs centerBtn btn-success  btnComfirm showList"><?=Yii::t('frontend', 'be_sure')?></a>
                  </div>
                </div>
              </div>
            </div>
            <ul class="taskList theShownList"  id="taskList" style="margin: 15px 0;">
            </ul>
          </div>
          <div class="col-md-4 col-sm-4 myGroupList_mini" style="float: left!important;">
            <div class="input-group " style="width:100%">
              <input type="text" id="search_object_key" class="form-control search_people" style="height: 30px;" placeholder="<?=Yii::t('frontend', 'input_keyword')?>">
<!--               <span class="input-group-btn">
                  <button class="btn btn-success btn-sm" type="button">添加</button>
                </span> -->
            </div>
            <ul id="objects" class="thumbList">
            <!--
	       <? foreach($users as $u):?>
	            <li>
	                <input type="checkbox" value="<?= $u['kid'] ?>" name="TaskPushForm[objects][]"
	                       checked="checked"/>
	                <p class="name"><?=Html::encode($u['real_name'])?></p>
	                <p><?= Html::encode(TStringHelper::PositionName($u['position_name']))?></p>
	            </li>
	        <? endforeach;?>
        	-->
            </ul>
                <div class="">
        <label><input type="checkbox" checked="checked" id="checkAll" /> <?=Yii::t('common', 'check_all')?>/<?= Yii::t('frontend', 'page_info_good_cancel') ?></label>
    </div>

          </div>
          <div class="col-md-12 centerBtnArea" style="float: left!important;">
            <a href="#" id="subBtn" class="btn btn-sm btn-success centerBtn " style="margin-bottom:20px;" role="button" data-dismiss="modal" aria-label="Close" data-toggle="modal" ><?=Yii::t('frontend', 'appoint_task')?></a>
          </div>
	<div class="c"></div>
<?php ActiveForm::end(); ?>
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
    }

    var currentItemType = 'course';

    var temp_select_course_arr = new Array();
    var temp_select_course_id_arr = new Array();

    var temp_select_exam_arr = new Array();
    var temp_select_exam_id_arr = new Array();

    var temp_select_survey_arr = new Array();
    var temp_select_survey_id_arr = new Array();

    //任务添加 begin
    var addTask = $("#addTask"),
        taskPanel = $(".selectPanel_task");

    addTask.bind("click", function () {
        if (taskPanel.hasClass("hide")) {
            taskPanel.removeClass("hide")
        } else {
            taskPanel.addClass("hide")
        }
    });
    //任务添加 end
    $(function () {
        //$("#courseTaskButton").show();
        $("#checkAll").click(function (e) {
            // e.stopPropagation();
            if (!$(this).is(":checked")) {
                $(".thumbList input[type='checkbox']").prop('checked', false);
            } else {
                $(".thumbList input[type='checkbox']").prop('checked', true);
            }
        });
    });

    var selectPeopleBtn = $(".selectBtn"),
        selectPeoplePanel = $(".selectPanel_people");

    selectPeopleBtn.bind("click", function () {
        if (selectPeoplePanel.hasClass("hide")) {
            selectPeoplePanel.removeClass("hide")
        } else {
            selectPeoplePanel.addClass("hide")
        }
    });

    var btnComfirm = $(".btnComfirm");
    btnComfirm.bind("click", function () {
        if (taskPanel.hasClass("hide")) {
            taskPanel.removeClass("hide")
        } else {
            taskPanel.addClass("hide")
        }
    });

    var subbtnClickTime = 0;
    $("#subBtn").bind("click", function () {
        if (subbtnClickTime > 0) {
            return false;
        }
        subbtnClickTime = 1;

        var domain = $("#pushForm #domain_id").val();
        var items = $("#pushForm .task_type").length;
        var objects = $("#pushForm input:checkbox[name='TaskPushForm[objects][]']:checked").length;

        if (domain == '') {
            subbtnClickTime = 0
            app.showMsg("<?=Yii::t('common', 'select_{value}',['value'=>Yii::t('common','domain')])?>", 1000);
            return false;
        }
        if (items == 0) {
            subbtnClickTime = 0
            app.showMsg("<?=Yii::t('common', 'select_{value}',['value'=>Yii::t('frontend','task')])?>", 1000);
            return false;
        }
        if (objects == 0) {
            subbtnClickTime = 0
            app.showMsg("<?=Yii::t('common', 'select_{value}',['value'=>Yii::t('frontend','student')])?>", 1000);
            return false;
        }

        //var d2 = new Date(endDate.replace(/\-/g, "\/"));
        nowdate = new Date();
        dateok = true;
        dateselcet = true;
        $('input[data-type=rili]').each(function () {
            sdate = $(this).val();
            if (sdate) {
                sdate = new Date(sdate.replace(/\-/g, "\/"));
                if (sdate < nowdate) {
                    dateok = false;
                }
            } else {
                dateselcet = false;
            }
        })

        if (!dateselcet) {
            subbtnClickTime = 0
            app.showMsg("<?=Yii::t('common', 'select_{value}',['value'=>Yii::t('frontend','end_time')])?>", 1000);
            return false;
        }
        if (!dateok) {
            subbtnClickTime = 0
            app.showMsg("<?= Yii::t('frontend', 'alert_warning_time5') ?>", 1000);
            return false;
        }

        submitModalForm('none', 'pushForm', 'none', true, false, null, null);
    });

    function changeType(type) {
        currentItemType = type;
    }

    function ReloadPageAfterUpdate(frameId, formId, modalId, isClose) {
        app.showMsg("<?=Yii::t('common', 'operation_success')?>", 1000);
        setTimeout('window.location.reload()', 1500);
    }

    function searchCourses() {
        var domain = $("#domain_id").val();
        var keyword = $("#keyword").val().trim();
        if (!domain) {
            app.showMsg("<?=Yii::t('common', 'select_{value}',['value'=>Yii::t('common','domain')])?>", 1000);
            return false;
        }

        if (currentItemType === 'course') {
            var inputdata = {selected: temp_select_course_arr, keyword: keyword, domain: domain};
            loadData("<?=Url::toRoute(['common/search-course'])?>", "courseList", inputdata);
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

    function bindData(target, data) {
        $(".tab-content > .loadingWaiting").addClass('hide');
        $("#" + target).html(data);
        $("#" + target + ' .pagination a').bind('click', function () {
            var url = $(this).attr('href');
            loadData(url, target, null);
            return false;
        });
    }

    function loadData(ajaxUrl, container, inputdata) {
        $("#" + container).empty();
        ajaxGet(ajaxUrl, container, bindData, inputdata);
        $(".tab-content > .loadingWaiting").removeClass('hide');
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

    function delTaskItem(obj, kid) {
        $("#" + kid).removeClass('btn-success').html('<?=Yii::t('frontend', 'select')?>').removeAttr('disabled');
        $(obj).parent().remove();

        if (currentItemType === 'course') {
            temp_select_course_arr.remove(kid);
            temp_select_course_id_arr.remove(kid);
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

    var objects = $("#objects");

    $(function () {

        var defaultDomainId = "<?=$defaultDomainId?>";
        var defaultDomainName = "<?=$defaultDomainName?>";
        if (defaultDomainId != "") {
            selectDomain(defaultDomainId, defaultDomainName);
        }
        $('.search_people').keyup(
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

                    }
                }
            });
    });

    function searchObject() {
        //获得文本框内容
        var word = $("#search_object_key").val().trim();
        var domain = $("#domain_id").val();
        if (!domain) {
            app.showMsg("<?=Yii::t('common', 'select_{value}',['value'=>Yii::t('common','domain')])?>", 1000);
            return false;
        }

        var url = "<?=Yii::$app->urlManager->createUrl('manager/search-object')?>";
        $.post(url, {'key': word, 'domain': domain},
            function (data) {
                var data = eval('(' + data + ')');
                var lists = [];
                $.each(data, function (i, n) {
                    lists.push({kid: n.kid, position_name: n.position_name, real_name: htmlencode(n.real_name)});
                });
                //alert(lists);return ;
                objects.empty();
                addObjectArrays(lists);
            }, "json");
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

    function addObjectArrays(lists) {
        var temp = _.template($("#t1").html(), {variable: 'data'})({datas: lists});
        $("#objects").append(temp);
    }

    function selectDomain(kid, domain_name) {
        $("#domain_id").val(kid);
        $("#myModalLabelTask").html('<?=Yii::t('frontend', 'new_task')?>(' + domain_name + ')');

        searchObject();
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

<!--ace-template demo-->
<script id="t2" type="text/template">
    <%_.each(data.datas, function(item) {%>
    <li>
        <input name="TaskPushForm[items][item_id][]" type="hidden" class="task_id" value="<%=item.kid%>">
        <input name="TaskPushForm[items][item_type][]" type="hidden" class="task_type" value="0">
        <input name="TaskPushForm[items][item_title][]" type="hidden" class="item_title" value="<%=htmlencode(item.course_name)%>">
        <a href="javascript:void(0);" style="top: 2px !important;" class="btn btn-default btn-xs pull-right delEvent" onclick="return delTaskItem(this,'<%=item.kid%>');"><?= Yii::t('common', 'delete_button') ?></a>
        <div class="taskLine">
            <h5 title="<%=item.course_name%>">[<?= Yii::t('common', 'course') ?>]<%=htmlencode(item.str_name)%></h5>
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
    <li>
        <input name="TaskPushForm[objects][]" type="checkbox" value="<%=item.kid%>"   checked="checked" />
        <p class="name"><%=item.real_name%></p>
		<p><%=item.position_name%></p>
    </li>
    <%});%>
</script>