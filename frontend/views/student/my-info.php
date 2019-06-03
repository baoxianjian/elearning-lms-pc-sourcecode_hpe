<?php
/**
 * Created by PhpStorm.
 * User: LiuCheng
 * Date: 2015/5/25
 * Time: 10:18
 */
use frontend\assets\AppAsset;
use yii\helpers\Url;
use components\widgets\TBreadcrumbs;

/* @var $this yii\web\View */
/* @var $content string */

AppAsset::register($this);
//$this->title = ($this->pageTitle ? $this->pageTitle . '-' : '') . '惠普在线学习平台';
$this->pageTitle = Yii::t('frontend', 'my_tools');// Yii::t('frontend', 'page_lesson_hot_title');
$this->params['breadcrumbs'][] = $this->pageTitle;
?>
<div class="container">
    <div class="row">
        <?= TBreadcrumbs::widget([
            'tag' => 'ol',
            'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
        ]) ?>
        <div class="col-md-12">
            <div class="row">
                <!--                <ul class="nav nav-tabs hotNews" role="tablist" id="myTab">-->
                <!--                    <li role="presentation" class="active"><a href="#question" aria-controls="question" role="tab"-->
                <!--                                                              data-toggle="tab">学习经历</a></li>-->
                <!--                </ul>-->
                <div class="tab-content panel panel-default">
                    <div role="tabpanel" class="tab-pane active  panel-body" id="question">
                        <div class="panel-body textCenter">
                            <div class="filterBtn">
                                <div class="btn-group timeScope pull-left">
                                    <button class="btn btn-default btn-xs dropdown-toggle" type="button"
                                            data-toggle="dropdown" aria-expanded="false"><?=Yii::t('frontend', 'one_week')?> &nbsp;<span
                                            class="caret"></span>
                                    </button>
                                    <ul class="dropdown-menu" role="menu">
                                        <li id="li_time_1" class="active"><a id="a_time_1" href="javascript:void(0);"
                                                                             onclick="changeTime(this,1)"><?=Yii::t('frontend', 'one_week')?></a>
                                        </li>
                                        <li id="li_time_2"><a id="a_time_2" href="javascript:void(0);"
                                                              onclick="changeTime(this,2)"><?=Yii::t('frontend', 'one_month')?></a></li>
                                        <li id="li_time_3"><a id="a_time_3" href="javascript:void(0);"
                                                              onclick="changeTime(this,3)"><?=Yii::t('frontend', 'three_month')?></a></li>
                                        <li class="divider"></li>
                                        <li id="li_time_null"><a id="a_time_null" href="javascript:void(0);"
                                                                 onclick="changeTime(this,null)"><?=Yii::t('frontend', 'three_month')?></a></li>
                                    </ul>
                                    <span id="care_filter" style="display: none;">
                                    <input class="care" type="checkbox" name="careFilter" id="cb_care" value="p"
                                           checked/><label for="cb_care"><?=Yii::t('frontend', 'care_man')?></label>
                                    <input class="care" type="checkbox" name="careFilter" id="cb_question" value="q"
                                           checked/><label for="cb_question"><?=Yii::t('frontend', 'care_question')?></label>
                                    </span>
                                </div>

                                <a href="javascript:void(0);" id="btnCate1" class="btnFilter"><?=Yii::t('frontend', 'my_care')?></a>
                                <a href="javascript:void(0);" id="btnCate2" class="btnFilter"><?=Yii::t('frontend', 'my_favorite')?>my_favorite</a>
                            </div>
                            <div id="tab1" class="timeline hidden">
                            </div>
                            <div id="tab2" class="timeline hidden">
                            </div>
                            <div class="timeline timeline_loading" id="time_loading" style="margin-top: -50px">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    $.extend({
        getUrlVars: function () {
            var vars = [], hash, url = window.location.href;
            var index = url.indexOf('?');
            if (index > 0) {
                var hashes = url.slice(index + 1).split('&');
                for (var i = 0; i < hashes.length; i++) {
                    hash = hashes[i].split('=');
                    vars.push(hash[0]);
                    vars[hash[0]] = hash[1];
                }
            }
            return vars;
        },
        getUrlVar: function (name) {
            return $.getUrlVars()[name];
        }
    });

    var loading = true;

    var type = 'care';

    var attention_page = 1;
    var attention_url = "<?=Url::toRoute(['common/get-all-attention'])?>" + "?page=";
    var attention_end = false;
    var attention_time = 1;

    var collect_page = 1;
    var collect_url = "<?=Url::toRoute(['common/get-collect'])?>" + "?page=";
    var collect_end = false;
    var collect_time = 1;

    var filter = 3;

    $(document).ready(
        function () {
            var tab = $.getUrlVar('init-tab');
            switch (tab) {
                case 'care':
                    btnCate1.addClass("activeBtn")
                    tab1.removeClass("hidden")
                    type = 'care'
                    $("#care_filter").show();
                    break;
                case 'collect':
                    btnCate2.addClass("activeBtn")
                    tab2.removeClass("hidden")
                    type = 'collect'
                    $("#care_filter").hide();
                    break;
                default:
                    btnCate1.addClass("activeBtn")
                    tab1.removeClass("hidden")
                    $("#care_filter").show();
            }

            loadTab(attention_url + attention_page + '&filter=' + filter + '&time=' + attention_time, 'tab1');
            loadTab(collect_url + collect_page + '&time=' + collect_time, 'tab2');

            $(".care").click(function () {
                var p_checked = document.getElementById('cb_care').checked;
                var q_checked = document.getElementById('cb_question').checked;

                if (p_checked && q_checked) {
                    filter = 3;//全选
                }
                else if (!p_checked && q_checked) {
                    filter = 2;//关注的问题
                }
                else if (p_checked && !q_checked) {
                    filter = 1;//关注的人
                }
                else {
                    document.getElementById('cb_care').checked = true;
                    document.getElementById('cb_question').checked = true;
                    filter = 3;//全选
                }

                $("#tab1").empty();
                attention_page = 1;
                attention_end = false;
                loadTab(attention_url + attention_page + '&filter=' + filter + '&time=' + attention_time, 'tab1');
            });

            $(window).scroll(function () {
                var bot = 100; //bot是底部距离的高度
                if (!loading && (bot + $(window).scrollTop()) >= ($(document).height() - $(window).height())) {
                    //当底部基本距离+滚动的高度〉=文档的高度-窗体的高度时；
                    //我们需要去异步加载数据了
                    if (type == 'care' && !attention_end) {
                        loading = true;
                        attention_page++;
                        loadTab(attention_url + attention_page + '&filter=' + filter + '&time=' + attention_time, 'tab1');
                    }
                    else if (type == 'collect' && !collect_end) {
                        loading = true;
                        collect_page++;
                        loadTab(collect_url + collect_page + '&time=' + collect_time, 'tab2');
                    }
                }
            });
        });
    var
        btnFilter = $(".btnFilter"),
        btnCate1 = $("#btnCate1"),
        tab1 = $("#tab1"),
        btnCate2 = $("#btnCate2"),
        tab2 = $("#tab2")

    btnFilter.bind("click", function () {
        var b = $(this)
        btnFilter.removeClass("activeBtn")
        b.addClass("activeBtn")
    })

    btnCate1.bind("click", function () {
        if (tab1.hasClass("hidden")) {
            hiddenAll();
            tab1.removeClass("hidden")
            type = 'care'

            $('#li_time_' + attention_time).parent().children('li').removeClass('active');
            $('#li_time_' + attention_time).addClass('active').parent().prev().html($('#a_time_' + attention_time).html() + ' &nbsp;<span class="caret"></span>');

            $("#care_filter").show();

        }
    })
    btnCate2.bind("click", function () {
        if (tab2.hasClass("hidden")) {
            hiddenAll();
            tab2.removeClass("hidden")
            type = 'collect'

            $('#li_time_' + collect_time).parent().children('li').removeClass('active');
            $('#li_time_' + collect_time).addClass('active').parent().prev().html($('#a_time_' + collect_time).html() + ' &nbsp;<span class="caret"></span>');

            $("#care_filter").hide();
        }
    })

    function hiddenAll() {
        tab1.addClass("hidden")
        tab2.addClass("hidden")
    }

    function loadTab(ajaxUrl, container) {
        $("#time_loading").html('<div class="load-wrapp"><div class="load-9"><div class="spinner"><div class="bubble-1"></div><div class="bubble-2"></div></div><p><?=Yii::t('frontend', 'loading')?>...</p></div></div>');
        ajaxGet(ajaxUrl, container, loadCallback);
    }

    function loadCallback(target, data) {
        $("#time_loading").html('');
        $("#" + target).append(data);
        loading = false;
        var count = $(data).filter('.timeline-item').length;
        if (data == null || data == '' || count < 10) {
            if (target == 'tab1') {
                attention_end = true;
            }
            else if (target == 'tab2') {
                collect_end = true;
            }
        }
    }
    function changeTime(obj, time) {
        $(obj).parent().parent().children('li').removeClass('active');

        $(obj).parent().addClass('active').parent().prev().html($(obj).html() + ' &nbsp;<span class="caret"></span>');

        if (type == 'care') {
            loading = true;
            attention_page = 1;
            attention_end = false;
            attention_time = time;
            $("#tab1").empty();
            loadTab(attention_url + attention_page + '&filter=' + filter + '&time=' + attention_time, 'tab1');
        }
        else if (type == 'collect') {
            loading = true;
            collect_time = time;
            collect_page = 1;
            collect_end = false;
            $("#tab2").empty();
            loadTab(collect_url + collect_page + '&time=' + collect_time, 'tab2');
        }
    }

    function cancelCare(objId, type) {
        if (type == 'q') {
            var url = "<?=Url::toRoute(['question/care'])?>";
            $.post(url, {"qid": objId},
                function (data) {
                    var result = data.result;
                    if (result === 'other') {
                        NotyWarning(data.message, 'center', 1500);
                    }
                    else if (result === 'failure') {
                        NotyWarning('<?=Yii::t('common', 'operation_confirm_warning_failure')?>', 'center', 1500);
                    }
                    else if (result === 'success') {
                        NotyWarning('<?=Yii::t('common', 'operation_success')?>', 'center', 1500);
                        setTimeout('window.location.reload()', 1500);
                    }
                }, "json");
        }
        else if (type == 'u') {
            var url = "<?=Url::toRoute(['common/attention-user'])?>";
            $.post(url, {"uid": objId},
                function (data) {
                    var result = data.result;
                    if (result === 'other') {
                        NotyWarning(data.message, 'center', 1500);
                    }
                    else if (result === 'success') {
                        NotyWarning('<?=Yii::t('common', 'operation_success')?>', 'center', 1500);
                        setTimeout('window.location.reload()', 1500);
                    }
                }, "json");
        }
        return false;
    }

    function cancelCollect(objId, type){
        if (type == 'c') {
            var url = "<?=Url::toRoute(['resource/course/collection'])?>";
            $.post(url, {"obj_id": objId},
                function (data) {
                    var result = data.result;
                    if (result === 'other') {
                        NotyWarning(data.message, 'center', 1500);
                    }
                    else if (result === 'success') {
                        NotyWarning('<?=Yii::t('common', 'operation_success')?>', 'center', 1500);
                        setTimeout('window.location.reload()', 1500);
                    }
                }, "json");

            return false;
        }
        else if (type == 'q') {
            var url = "<?=Url::toRoute(['question/collect'])?>";
            $.post(url, {"qid": objId},
                function (data) {
                    var result = data.result;
                    if (result === 'other') {
                        NotyWarning(data.message, 'center', 1500);
                    }
                    else if (result === 'success') {
                        NotyWarning('<?=Yii::t('common', 'operation_success')?>', 'center', 1500);
                        setTimeout('window.location.reload()', 1500);
                    }
                }, "json");

            return false;
        }
    }
</script>