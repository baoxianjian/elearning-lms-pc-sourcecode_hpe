<?php
/**
 * Created by PhpStorm.
 * User: LiuCheng
 * Date: 2015/6/4
 * Time: 14:52
 */
use frontend\widgets\CourseLibrary;
use frontend\widgets\QuestionArea;
use frontend\widgets\RecommendCourse;
use frontend\widgets\UserPanel;
use frontend\widgets\ContinueLearning;
use components\widgets\TBreadcrumbs;
use yii\helpers\Url;
use frontend\widgets\QuickChannel;
use yii\helpers\html;
use yii\widgets\ActiveForm;

$this->pageTitle = Yii::t('frontend', 'learning_path');// Yii::t('frontend', 'page_lesson_hot_title');
$this->params['breadcrumbs'][] = $this->pageTitle;

$current_time = time();
?>
<style>
    .tdwrapper #search_people{width:160px}
</style>
<div class="container">
    <div class="row">
        <?= TBreadcrumbs::widget([
            'tag' => 'ol',
            'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
        ]) ?>
        <div class="col-md-4 wideScreenBlock">
            <?
            $userPanel = UserPanel::widget();
            $continueLearning = ContinueLearning::widget();
            $courseLibrary = CourseLibrary::widget();
            $questionArea = QuestionArea::widget();
            $recommendCourse = RecommendCourse::widget();
            ?>
            <?
            echo $userPanel;
            ?>
            <?
            echo $continueLearning;
            ?>
            <?
            echo $recommendCourse;
            ?>
            <?
            echo $courseLibrary;
            ?>
            <?
            echo $questionArea;
            ?>
        </div>
        <div class="col-md-8">
            <div class="row">
                <div class="panel panel-default hotNews">
                    <div class="panel-heading">
                        <i class="glyphicon glyphicon-dashboard"></i> </i><?=Yii::t('frontend', 'process_tree')?>
                    </div>
                    <div class="panel-body textCenter">
                        <div class="filterBtn">
                            <a href="javascript:void(0);" id="btnCate1" class="btnFilter activeBtn"></i><?=Yii::t('common', 'course')?></a>
                            <a href="javascript:void(0);" id="btnCate2" class="btnFilter"></i><?=Yii::t('frontend', 'exam')?></a>
                            <a href="javascript:void(0);" id="btnCate3" class="btnFilter"></i><?=Yii::t('frontend', 'question_answer')?></a>
                            <a href="javascript:void(0);" id="btnCate4" class="btnFilter"></i><?=Yii::t('frontend', 'web_page')?></a>
                            <a href="javascript:void(0);" id="btnCate5" class="btnFilter"></i><?=Yii::t('frontend', 'event')?></a>
                            <a href="javascript:void(0);" id="btnCate6" class="btnFilter"></i><?=Yii::t('frontend', 'book')?></a>
                            <a href="javascript:void(0);" id="btnCate7" class="btnFilter"></i><?=Yii::t('frontend', 'experience')?></a>
                            <a href="javascript:void(0);" id="btnCate8" class="btnFilter"></i><?=Yii::t('common', 'investigation')?></a>
<!--                            <a href="javascript:void(0);" id="btnCate9" class="btnFilter">投票</a>-->
<!--                            <a href="javascript:void(0);" id="btnCate10" class="btnFilter">勋章</a>-->
                            <a href="javascript:void(0);" id="btnCate11" class="btnFilter"></i><?=Yii::t('common', 'serial')?></a>
                        </div>
                        <div id="timeline1" class="timeline miniLine">
                        </div>
                        <div id="timeline2" class="timeline miniLine hidden">
                        </div>
                        <div id="timeline3" class="timeline miniLine hidden">
                        </div>
                        <div id="timeline4" class="timeline miniLine hidden">
                        </div>
                        <div id="timeline5" class="timeline miniLine hidden">
                        </div>
                        <div id="timeline6" class="timeline miniLine hidden">
                        </div>
                        <div id="timeline7" class="timeline miniLine hidden">
                        </div>
                        <div id="timeline8" class="timeline miniLine hidden">
                        </div>
                        <div id="timeline9" class="timeline miniLine hidden">
                        </div>
                        <div id="timeline10" class="timeline miniLine hidden">
                        </div>
                        <div id="timeline11" class="timeline miniLine hidden">
                        </div>
                        <div class="loadingWaiting hide">
                            <span></span>
                            <span></span>
                            <span></span>
                            <span></span>
                            <span></span>
                            <p></i><?=Yii::t('frontend', 'loading')?>...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    var loading = true;

    var type = 'btnCate1';

    var course_page = 1;
    var course_url = "<?= Url::toRoute(['student/my-path-list','current_time'=> $current_time,'type'=>'课程']) ?>" + "&page=";
    var course_end = false;
    var course_time = 1;

    var exam_page = 1;
    var exam_url = "<?= Url::toRoute(['student/my-path-list','current_time'=> $current_time,'type'=>'考试']) ?>" + "&page=";
    var exam_end = false;
    var exam_time = 1;
    var exam_read = false;

    var survey_page = 1;
    var survey_url = "<?= Url::toRoute(['student/my-path-list','current_time'=> $current_time,'type'=>'调查']) ?>" + "&page=";
    var survey_end = false;
    var survey_time = 1;
    var survey_read = false;

    var qa_page = 1;
    var qa_url = "<?= Url::toRoute(['student/my-path-list','current_time'=> $current_time,'type'=>'问题']) ?>" + "&page=";
    var qa_end = false;
    var qa_time = 1;
    var qa_read = false;

    var event_page = 1;
    var event_url = "<?= Url::toRoute(['student/my-path-list','current_time'=> $current_time,'type'=>'事件']) ?>" + "&page=";
    var event_end = false;
    var event_time = 1;
    var event_read = false;

    var web_page = 1;
    var web_url = "<?= Url::toRoute(['student/my-path-list','current_time'=> $current_time,'type'=>'网页']) ?>" + "&page=";
    var web_end = false;
    var web_time = 1;
    var web_read = false;

    var book_page = 1;
    var book_url = "<?= Url::toRoute(['student/my-path-list','current_time'=> $current_time,'type'=>'书籍']) ?>" + "&page=";
    var book_end = false;
    var book_time = 1;
    var book_read = false;

    var exp_page = 1;
    var exp_url = "<?= Url::toRoute(['student/my-path-list','current_time'=> $current_time,'type'=>'书籍']) ?>" + "&page=";
    var exp_end = false;
    var exp_time = 1;
    var exp_read = false;

    var cert_page = 1;
    var cert_url = "<?= Url::toRoute(['student/my-path-list','current_time'=> $current_time,'type'=>'证书']) ?>" + "&page=";
    var cert_end = false;
    var cert_time = 1;
    var cert_read = false;

    var peopleQueryList;

    $(document).ready(
        function () {
            loadTab(course_url + course_page + '&time=' + course_time, 'timeline1');
            $(window).scroll(function () {
                var bot = 100; //bot是底部距离的高度
                if (!loading && (bot + $(window).scrollTop()) >= ($(document).height() - $(window).height())) {
                    if (type == 'btnCate1' && !course_end) {
                        loading = true;
                        course_page++;
                        loadTab(course_url + course_page + '&time=' + course_time, 'timeline1');
                    }
                    else if (type == 'btnCate2' && !exam_end) {
                        loading = true;
                        exam_page++;
                        loadTab(exam_url + exam_page + '&time=' + exam_time, 'timeline2');
                    }
                    else if (type == 'btnCate3' && !qa_end) {
                        loading = true;
                        qa_page++;
                        loadTab(qa_url + qa_page + '&time=' + qa_time, 'timeline3');
                    }
                    else if (type == 'btnCate4' && !web_end) {
                        loading = true;
                        web_page++;
                        loadTab(web_url + web_page + '&time=' + web_time, 'timeline4');
                    }
                    else if (type == 'btnCate5' && !event_end) {
                        loading = true;
                        event_page++;
                        loadTab(event_url + event_page + '&time=' + event_time, 'timeline5');
                    }
                    else if (type == 'btnCate6' && !book_end) {
                        loading = true;
                        book_page++;
                        loadTab(book_url + book_page + '&time=' + book_time, 'timeline6');
                    }
                    else if (type == 'btnCate7' && !exp_end) {
                        loading = true;
                        exp_page++;
                        loadTab(exp_url + exp_page + '&time=' + exp_time, 'timeline7');
                    }
                    else if (type == 'btnCate8' && !survey_end) {
                        loading = true;
                        survey_page++;
                        loadTab(survey_url + survey_page + '&time=' + survey_time, 'timeline8');
                    }
                    else if (type == 'btnCate11' && !cert_end) {
                        loading = true;
                        cert_page++;
                        loadTab(cert_url + cert_page + '&time=' + cert_time, 'timeline11');
                    }
                }
            });

            peopleQueryList = app.queryList("#search_people");
        });
    var btnFilter = $(".btnFilter"),
        tab1 = $("#timeline1"),
        tab2 = $("#timeline2"),
        tab3 = $("#timeline3"),
        tab4 = $("#timeline4"),
        tab5 = $("#timeline5"),
        tab6 = $("#timeline6"),
        tab7 = $("#timeline7"),
        tab8 = $("#timeline8"),
        tab9 = $("#timeline9"),
        tab10 = $("#timeline10"),
        tab11 = $("#timeline11");


    btnFilter.bind("click", function () {
        var b = $(this);
        btnFilter.removeClass("activeBtn");
        b.addClass("activeBtn");
        var btnId = $(this).attr('id');
        var tabId = btnId.replace("btnCate", "timeline");

        type = btnId;

//        var cur_time;
        if (!exam_read && type == 'btnCate2') {
            exam_read = true;
            loadTab(exam_url + exam_page + '&time=' + exam_time, 'timeline2');
        }
        else if (!qa_read && type == 'btnCate3') {
            qa_read = true;
            loadTab(qa_url + qa_page + '&time=' + qa_time, 'timeline3');
        }
        else if (!web_read && type == 'btnCate4') {
            web_read = true;
            loadTab(web_url + web_page + '&time=' + web_time, 'timeline4');
        }
        else if (!event_read && type == 'btnCate5') {
            event_read = true;
            loadTab(event_url + event_page + '&time=' + event_time, 'timeline5');
        }
        else if (!book_read && type == 'btnCate6') {
            book_read = true;
            loadTab(book_url + book_page + '&time=' + book_time, 'timeline6');
        }
        else if (!exp_read && type == 'btnCate7') {
            exp_read = true;
            loadTab(exp_url + exp_page + '&time=' + exp_time, 'timeline7');
        }
        else if (!survey_read && type == 'btnCate8') {
            survey_read = true;
            loadTab(survey_url + survey_page + '&time=' + survey_time, 'timeline8');
        }
        else if (!cert_read && type == 'btnCate11') {
            cert_read = true;
            loadTab(cert_url + cert_page + '&time=' + cert_time, 'timeline11');
        }
//        $('#li_time_' + cur_time).parent().children('li').removeClass('active');
//
//        $('#li_time_' + cur_time).addClass('active').parent().prev().html($('#a_time_' + cur_time).html() + ' &nbsp;<span class="caret"></span>');

        if ($("#" + tabId).hasClass("hidden")) {
            hiddenAll();
            $("#" + tabId).removeClass("hidden");
        }
    })

    function hiddenAll() {
        tab1.addClass("hidden");
        tab2.addClass("hidden");
        tab3.addClass("hidden");
        tab4.addClass("hidden");
        tab5.addClass("hidden");
        tab6.addClass("hidden");
        tab7.addClass("hidden");
        tab8.addClass("hidden");
        tab9.addClass("hidden");
        tab10.addClass("hidden");
        tab11.addClass("hidden");
    }

    function loadTab(ajaxUrl, container) {
        $(".loadingWaiting").removeClass('hide');
        ajaxGet(ajaxUrl, container, loadCallback);
    }

    function loadCallback(target, data) {
        $(".loadingWaiting").addClass('hide');
        $("#" + target).append(data);
        loading = false;
        var count = $(data).filter('.timeline-item').length;
        if (data == null || data == '' || count < 10) {
            if (target == 'timeline1') {
                course_end = true;
            }
            else if (target == 'timeline2') {
                exam_end = true;
            }
            else if (target == 'timeline3') {
                qa_end = true;
            }
            else if (target == 'timeline4') {
                web_end = true;
            }
            else if (target == 'timeline5') {
                event_end = true;
            }
            else if (target == 'timeline6') {
                book_end = true;
            }
            else if (target == 'timeline7') {
                exp_end = true;
            }
            else if (target == 'timeline8') {
                survey_read = true;
            }
            else if (target == 'timeline11') {
                cert_end = true;
            }
        }
    }

    function submitDownload(obj_id,obj_type)
    {
        $("#downloadform-id").val(obj_id);
        $("#downloadform-type").val(obj_type);
        $("#downloadForm").submit();
        return false;
    }

    function ShareRecord(obj,id){
        var actionUrl = '<?=Url::toRoute(['common/share-record'])?>';
        $.post(
            actionUrl,
            'rid='+id
        ).done(function(data) {
                ShareCallBack(obj,data);
            }).fail(function() {
                app.showMsg("<?=Yii::t('common', 'operation_confirm_warning_internal_error')?>");
            });
    }

    function ShareCallBack(obj,data) {
        var result = data.result;
        if (result === 'other'){
            app.showMsg(data.message);
        }else if (result === 'failure') {
            app.alert("#newFollow");
            $("#newFollow .body").html('<div style="text-align: center;padding: 20px;"><?=Yii::t('frontend', 'operation_confirm_warning_failure')?></div>');
        }else{
            app.alert("#newFollow");
            $("#newFollow .body").html('<div style="text-align: center;padding: 20px;"><?=Yii::t('frontend', 'share_sucess')?></div>');
        }
    }

    function ShowShare(id, title, type) {
        $("#soshare-content").val(null);
        $("#soshare-obj_id").val(id);
        $("#soshare-title").val(title);
        $("#soshare-type").val(type);

        if (type == 2) {
            $("#shareFormCourse #content").html('<?=Yii::t('common', 'course_name')?>: 《' + title + '》');
            app.alertSmall("#newShareCourse");
        }
        else if (type == 3) {
            $("#shareFormQuestion #content").html('<?=Yii::t('frontend', 'question')?>: ' + title);
            app.alertSmall("#newShareQuestion");
        }
    }

    function showCourseShare(id, title) {
        $("#course-share-content").val(null);
        $("#course-share-id").val(id);
        $("#course-share-title").val(title);
        $("#course-share-name").html('<?=Yii::t('common', 'course_name')?>: 《' + title + '》');

        app.alertSmall("#newShareCourse");
    }

    function ReloadPageAfterUpdate(frameId, formId, modalId, isClose)
    {
        app.alert("#newFollow");
        $("#newFollow .body").html('<div style="text-align: center;padding: 20px;"><?=Yii::t('common', 'operation_success')?></div>');
        formReset();
    }

    function formReset()
    {
        $("#soshare-content").val(null);
    }
    function  view_survey_show(uid,iid){
        var url="<?=Url::toRoute(['investigation-result/st-survey',])?>"+"?investigation_id="+iid+"&user_id="+uid;
        FmodalLoadData("view_survey_show",url);
    }
    function FmodalLoadData(target, url)
    {
        if(url){
            $('#'+target).empty();
            $('#'+target).load(url, function (){
                app.alertWide("#"+target,{
                    afterHide: function (){
                        $('#'+target).empty();
                    }
                });
            });
        }
    }

    function courseShare(){
        var actionUrl = '<?=Yii::$app->urlManager->createUrl(['common/course-share'])?>';
        var id = $("#course-share-id").val();
        if($("#course-share-content").val() == ''){
            $("#course-share-content").focus();
            return;
        }
        var title = $("#course-share-title").val();
        var content = $("#course-share-content").val();
        var users = peopleQueryList.get();

        var data = {'courseId': id, 'title': title, 'content': content, 'users': users};

        $.post(
            actionUrl,
            data
        ).done(function(data) {
            var result = data.result;
            if (result === 'other'){
                app.showMsg(data.message);
            }else if (result === 'failure') {
                app.showMsg('<?=Yii::t('common', 'operation_confirm_warning_failure')?>');
            }else{
                app.hideAlert("#newShareCourse");
                app.showMsg('<?=Yii::t('common', 'operation_success')?>');
            }
        }).fail(function() {
            app.showMsg("<?=Yii::t('common', 'operation_confirm_warning_internal_error')?>");
        });
        return true;
    }
</script>
<?php
$form = ActiveForm::begin([
    'id' => 'downloadForm',
    'method' => 'post',
    'action'=>Url::toRoute(['common/download']),
]);
?>
    <input type="hidden" id="downloadform-id" class="form-control" name="DownloadForm[id]">
    <input type="hidden" id="downloadform-type" class="form-control" name="DownloadForm[type]">
<?php ActiveForm::end(); ?>

<!-- 提示的弹出窗口 -->
<div class="ui modal" id="newFollow">
    <div class="header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="myModalLabel"><?=Yii::t('frontend', 'prompt')?></h4>
    </div>
    <div class="body"></div>
</div>
<!-- 分享课程 弹出窗口 -->
<div class="ui modal" id="newShareCourse">
    <div class="header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="myModalLabel"><?=Yii::t('frontend', 'share_to_{value}',['value'=>Yii::t('frontend', 'tab_btn_social')])?></h4>
    </div>
    <div class="content" style="padding: 10px;">
        <div class="row" style="padding: 10px;">
            <input type="hidden" id="course-share-id" class="form-control" name="SoShare[obj_id]" value="">
            <input type="hidden" id="course-share-title" class="form-control" name="SoShare[title]" value="">
            <textarea id="course-share-content" class="form-control" name="SoShare[content]" style="width:100%; height: 100px; border:1px solid #eee;" placeholder="<?=Yii::t('frontend', 'say_something')?>"></textarea>
            <div id="course-share-name" style="width:100%;padding: 10px 0" data-title="">
            </div>
            <br/>
            <table class="tiwen">
                <tr>
                    <td class="mouren" style="text-align:center">@<?=Yii::t('frontend', 'some_one')?></td>
                    <td class="tdwrapper" style="width:160px">
                        <input id="search_people" style="height:30px" type="text" data-url="<?=Yii::$app->urlManager->createUrl('common/search-people')?>?format=new" data-mult="1" />
                    </td>
                    <td style="width:180px">
                        <button type="button" id="shareBtn" class="btn btn-success btn-sm pull-right" onclick="courseShare()"><?=Yii::t('frontend', 'share')?></button>
                    </td>
                </tr>
            </table>
        </div>
    </div>
    <div class="c"></div>
</div>
<!-- 分享问题 弹出窗口 -->
<div class="ui modal" id="newShareQuestion">
    <div class="header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="myModalLabel"><?=Yii::t('frontend', 'share_to_{value}',['value'=>Yii::t('frontend', 'tab_btn_social')])?></h4>
    </div>
    <div class="content" style="padding: 10px;">
        <div class="row" style="padding: 10px;">
            <?php $form = ActiveForm::begin([
                'id' => 'shareFormQuestion',
                'method' => 'post',
                'action' => Yii::$app->urlManager->createUrl('common/path-share'),
            ]); ?>
            <input type="hidden" id="soshare-obj_id" class="form-control" name="SoShare[obj_id]" value="">
            <input type="hidden" id="soshare-type" class="form-control" name="SoShare[type]" value="">
            <input type="hidden" id="soshare-title" class="form-control" name="SoShare[title]" value="">
            <textarea id="soshare-content" class="form-control" name="SoShare[content]" style="width:100%; height: 100px; border:1px solid #eee;" placeholder="<?=Yii::t('frontend', 'say_something')?>"></textarea>
            <div id="content" style="width:100%;padding: 10px 0" data-title="">
            </div>
            <table class="tiwen">
                <tr>
                    <td style="width:382px">
                        <?=
                        Html::button(Yii::t('frontend', 'share'),
                            ['id' => 'shareBtn', 'class' => 'btn btn-success btn-sm pull-right', 'onclick' => 'submitModalForm("","shareFormQuestion","",true,false,null,null);'])
                        ?>
                    </td>
                </tr>
            </table>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
    <div class="c"></div>
</div>
<!--
<div class="modal ui" id="newShare">
    <div class="header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="myModalLabel">分享到社交圈</h4>
    </div>
    <div class="content" style="padding: 10px;">
        <?php $form = ActiveForm::begin([
            'id' => 'shareForm',
            'method' => 'post',
            'action' => Yii::$app->urlManager->createUrl('common/path-share'),
        ]); ?>
        <input type="hidden" id="soshare-obj_id" class="form-control" name="SoShare[obj_id]" value="">
        <input type="hidden" id="soshare-type" class="form-control" name="SoShare[type]" value="">
        <input type="hidden" id="soshare-title" class="form-control" name="SoShare[title]" value="">
        <textarea id="soshare-content" class="form-control" name="SoShare[content]" maxlength="1000" style="width:100%; height:80px;border:1px solid #eee;"></textarea>
        <div id="content" style="width:100%;border:1px solid #eee; padding: 4px 8px" data-title="">
        </div>
        <?=
        Html::button(Yii::t('frontend', 'share'),
            ['id' => 'shareBtn', 'class' => 'btn btn-success btn-sm pull-right', 'onclick' => 'submitModalForm("","shareForm","",true,false,null,null);'])
        ?>
        <?php ActiveForm::end(); ?>
        <div class="c"></div>
    </div>
</div>
-->
<div class="ui modal" id="view_survey_show" >
</div>