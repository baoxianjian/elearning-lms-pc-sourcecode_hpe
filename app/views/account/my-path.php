<?php
/**
 * Created by PhpStorm.
 * User: LiuCheng
 * Date: 2015/6/4
 * Time: 14:52
 */
use frontend\widgets\UserPanel;
use frontend\widgets\ContinueLearning;
use components\widgets\TBreadcrumbs;
use yii\helpers\Url;
use frontend\widgets\QuickChannel;
use yii\helpers\html;
?>
<div class="container">
    <div class="row">
        <div class="col-md-8">
            <div class="row">
                <div class="panel panel-default hotNews">
                    <div class="panel-body textCenter">
                        <div class="filterBtn">
                            <a href="javascript:void(0);" id="btnCate1" class="btnFilter activeBtn">课程</a>
<!--                            <a href="javascript:void(0);" id="btnCate2" class="btnFilter">测验</a>-->
                            <a href="javascript:void(0);" id="btnCate3" class="btnFilter">问答</a>
<!--                            <a href="javascript:void(0);" id="btnCate4" class="btnFilter">勋章</a>-->
<!--                            <a href="javascript:void(0);" id="btnCate5" class="btnFilter">投票</a>-->
<!--                            <a href="javascript:void(0);" id="btnCate6" class="btnFilter">调查</a>-->
                            <a href="javascript:void(0);" id="btnCate7" class="btnFilter">记录</a>
                            <a href="javascript:void(0);" id="btnCate8" class="btnFilter">分享</a>
                        </div>
<!--                        <p class="filterInfo">请及时按要求完成任务</p>-->
                        <div id="tab1" class="timeline miniLine">
                        </div>
<!--                        <div id="tab2" class="timeline hidden">-->
<!--                        </div>-->
                        <div id="tab3" class="timeline hidden">
                        </div>
<!--                        <div id="tab4" class="timeline hidden">勋章-->
<!--                        </div>-->
<!--                        <div id="tab5" class="timeline hidden">投票-->
<!--                        </div>-->
<!--                        <div id="tab6" class="timeline hidden">调查-->
<!--                        </div>-->
                        <div id="tab7" class="timeline hidden">
                        </div>
                        <div id="tab8" class="timeline hidden">
                        </div>
<!--                        <a href="#" class="btn btn-success btn-md timeLineLoadMore">自动加载更多</a>-->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?= html::jsFile('/static/common/js/common.js') ?>
<script>
    var loading = true;

    var type = 'btnCate1';

    var course_page = 1;
    var course_url = "<?=Url::toRoute(['account/my-course-path'])?>" + "?page=";
    var course_end = false;
    var course_time = 1;

    var qa_page = 1;
    var qa_url = "<?=Url::toRoute(['account/get-question'])?>" + "?page=";
    var qa_end = false;
    var qa_time = 1;

    var share_page = 1;
    var share_url= "<?=Url::toRoute(['account/get-share'])?>" + "?page=";
    var share_end=false;

    var record_page = 1;
    var record_url= "<?=Url::toRoute(['account/get-record'])?>" + "?page=";
    var record_end=false;

    $(document).ready(
        function () {
            loadTab(course_url + course_page + '&time=' + course_time, 'tab1');
            loadTab(qa_url + qa_page + '&time=' + qa_time, 'tab3');
            loadTab(record_url + record_page,'tab7');
            loadTab(share_url + share_page,'tab8');
            $(window).scroll(function () {
                var bot = 100; //bot是底部距离的高度
                if (!loading && (bot + $(window).scrollTop()) >= ($(document).height() - $(window).height())) {
                    if (type == 'btnCate1' && !course_end) {
                        loading = true;
                        course_page++;
                        loadTab(course_url + course_page + '&time=' + course_time, 'tab1');
                    }
                    else if (type == 'btnCate3' && !qa_end) {
                        loading = true;
                        qa_page++;
                        loadTab(qa_url + qa_page + '&time=' + qa_time, 'tab3');
                    }
                    else if (type == 'btnCate7' && !record_end) {
                        loading = true;
                        record_page++;
                        loadTab(record_url + record_page + '&time=' + qa_time, 'tab7');
                    }
                    else if (type == 'btnCate8' && !share_end) {
                        loading = true;
                        share_page++;
                        loadTab(share_url + share_page + '&time=' + qa_time, 'tab8');
                    }
                }
            });
        });
    var btnFilter = $(".btnFilter"),
        tab1 = $("#tab1"),
        tab2 = $("#tab2"),
        tab3 = $("#tab3"),
//        tab4 = $("#tab4"),
        tab5 = $("#tab5"),
//        tab6 = $("#tab6"),
        tab7 = $("#tab7"),
        tab8 = $("#tab8");


    btnFilter.bind("click", function () {
        var b = $(this);
        btnFilter.removeClass("activeBtn");
        b.addClass("activeBtn");
        var btnId = $(this).attr('id');
        var tabId = btnId.replace("btnCate", "tab");

        type = btnId;

        var cur_time;
        if (type == 'btnCate1') {
            cur_time = course_time;
        }
        else if (type == 'btnCate3') {
            cur_time = qa_time;
        }

        $('#li_time_' + cur_time).parent().children('li').removeClass('active');

        $('#li_time_' + cur_time).addClass('active').parent().prev().html($('#a_time_' + cur_time).html() + ' &nbsp;<span class="caret"></span>');

        if ($("#" + tabId).hasClass("hidden")) {
            hiddenAll();
            $("#" + tabId).removeClass("hidden");
        }
    })

    function hiddenAll() {
        tab1.addClass("hidden");
        tab2.addClass("hidden");
        tab3.addClass("hidden");
//        tab4.addClass("hidden");
        tab5.addClass("hidden");
//        tab6.addClass("hidden");
        tab7.addClass("hidden");
        tab8.addClass("hidden");
    }

    function loadTab(ajaxUrl, container) {
        $("#" + container).append('<div class="load-wrapp"><div class="load-9"><div class="spinner"><div class="bubble-1"></div><div class="bubble-2"></div></div><p>正在加载</p></div></div>');
        ajaxGet(ajaxUrl, container, loadCallback);
    }

    function loadCallback(target, data) {
        $("#" + target).append(data);
        $("#" + target + ' .load-wrapp').remove();
        loading = false;
        var count = $(data).filter('.timeline-item').length;
        if (data == null || data == '' || count < 10) {
            if (target == 'tab1') {
                course_end = true;
            }
            else if (target == 'tab3') {
                qa_end = true;
            }
        }
    }

    function changeTime(obj, time) {
        $(obj).parent().parent().children('li').removeClass('active');

        $(obj).parent().addClass('active').parent().prev().html($(obj).html() + ' &nbsp;<span class="caret"></span>');

        if (type == 'btnCate1') {
            course_time = time;
            course_page = 1;
            course_end = false;
            $("#tab1").empty();
            loadTab(course_url + course_page + '&time=' + course_time, 'tab1');
        }
        else if (type == 'btnCate3') {
            qa_time = time;
            qa_page = 1;
            qa_end = false;
            $("#tab3").empty();
            loadTab(qa_url + qa_page + '&time=' + qa_time, 'tab3');
        }
    }
</script>