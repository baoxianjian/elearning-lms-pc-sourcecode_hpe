<?php
/**
 * Created by PhpStorm.
 * User: chenli
 * Date: 7/1/15
 * Time: 4:49 PM
 */
use yii\helpers\Html;
use yii\helpers\Url;
use common\models\message\MsMessage;

?>

<div class="container">
    <div class="row">
    <div class="panel panel-default hotNews">
    <div class="panel-body textCenter">
    <div class="filterBtn">
        <a href="javascript:void(0);" id="btnCate0" class="btnFilter activeBtn">课 程</a>
        <a href="javascript:void(0);" id="btnCate1" class="btnFilter">问 答</a>
        <a href="javascript:void(0);" id="btnCate2" class="btnFilter">新鲜事</a>
    </div>
        <p class="filterInfo">请及时按要求完成任务</p>
        <div class="timeline" id="timeline1">
        </div>
        <div class="timeline hidden" id="timeline2">
        </div>
        <div class="timeline hidden" id="timeline3">
        </div>
        <div class="timeline timeline_loading" id="time_loading" style="margin-top: -10px">
    </div>
    </div>
    </div>
    </div>
</div>

<?= html::jsFile('/static/common/js/common.js') ?>
<script>

    var loading = true;

    var type = '<?=MsMessage::TYPE_COURSE?>';

    var course_page = 1;
    var course_url = "<?=Url::toRoute(['search/get-my-course'])?>";
    var course_end = false;
    var course_time = 1;

    var qa_page = 1;
    var qa_url = "<?=Url::toRoute(['search/get-dynamic-message','type'=> MsMessage::TYPE_QA])?>" + "&page=";
    var qa_end = false;
    var qa_time = 2;

    var news_page = 1;
    var news_url = "<?=Url::toRoute(['search/get-dynamic-message','type'=>MsMessage::TYPE_SOCIAL])?>" + "&page=";
    var news_end = false;
    var news_time = 2;


    $(document).ready(function () {
        loadTab(course_url);

        loadTab(qa_url + qa_page + '&time=' + qa_time, 'timeline2');

        loadTab(news_url + news_page + '&time=' + news_time, 'timeline3');


        $(window).scroll(function () {
            var bot = 100; //bot是底部距离的高度
            if (!loading && (bot + $(window).scrollTop()) >= ($(document).height() - $(window).height())) {
                //当底部基本距离+滚动的高度〉=文档的高度-窗体的高度时；
                //我们需要去异步加载数据了
                if (type == '<?=MsMessage::TYPE_COURSE?>' && !course_end) {
                    loading = true;
                    course_page++;
                    loadTab(course_url);
                }
                else if (type == '<?=MsMessage::TYPE_QA?>' && !qa_end) {
                    loading = true;
                    qa_page++;
                    loadTab(qa_url + qa_page + '&time=' + qa_time, 'timeline2');
                }
                else if (type == '<?=MsMessage::TYPE_SOCIAL?>' && !news_end) {
                    loading = true;
                    news_page++;
                    loadTab(news_url + news_page + '&time=' + news_time, 'timeline3');
                }

            }
        });
    });
    function loadTab(ajaxUrl, container) {
        $("#time_loading").html('<div class="load-wrapp"><div class="load-9"><div class="spinner"><div class="bubble-1"></div><div class="bubble-2"></div></div><p>正在加载...</p></div></div>');
        ajaxGet(ajaxUrl, container, loadCallback);
    }

    function loadCallback(target, data) {
        $("#time_loading").html('');
        $("#" + target).append(data);
        loading = false;
        var count=$(data).filter('.timeline-item').length;
        if (data == null || data == '' || count < 10) {
            if (target == 'timeline1') {
                course_end = true;
            }
            else if (target == 'timeline2') {
                qa_end = true;
            }
            else if (target == 'timeline3') {
                news_end = true;
            }

        }
    }

    var
        btnFilter = $(".btnFilter"),

        btnCate0 = $("#btnCate0"),
        btnCate1 = $("#btnCate1"),
        btnCate2 = $("#btnCate2")

        timeline1 = $("#timeline1"),
        timeline2 = $("#timeline2"),
        timeline3 = $("#timeline3")


    btnFilter.bind("click", function() {
        var b = $(this)
        btnFilter.removeClass("activeBtn")
        b.addClass("activeBtn")
    })

    btnCate0.bind("click", function() {
        if (timeline1.hasClass("hidden")) {
            timeline1.removeClass("hidden")
            timeline2.addClass("hidden")
            timeline3.addClass("hidden")
            type = '<?=MsMessage::TYPE_COURSE?>'
            $(".filterInfo").show();

        }
    })

    btnCate1.bind("click", function() {
        if (timeline2.hasClass("hidden")) {
            timeline2.removeClass("hidden")
            timeline1.addClass("hidden")
            timeline3.addClass("hidden")
            type = '<?=MsMessage::TYPE_QA?>'
            $(".filterInfo").hide();

        }
    })

    btnCate2.bind("click", function() {
        if (timeline3.hasClass("hidden")) {
            timeline3.removeClass("hidden")
            timeline1.addClass("hidden")
            timeline2.addClass("hidden")
            type = '<?=MsMessage::TYPE_SOCIAL?>'
            $(".filterInfo").hide();

        }
    })

</script>
