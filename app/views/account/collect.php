<?php
/**
 * Created by PhpStorm.
 * User: chenli
 * Date: 7/12/15
 * Time: 3:44 AM
 */
use frontend\assets\AppAsset;
use yii\helpers\Url;
use yii\helpers\html;
use components\widgets\TBreadcrumbs;
?>

<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="row">
                <div class="tab-content panel panel-default">
                    <div role="tabpanel" class="tab-pane active  panel-body" id="question">
                        <div class="panel-body textCenter">
                            <div class="filterBtn">
                                <a href="javascript:void(0);" id="btnCate1" class="btnFilter">我的关注</a>
                                <a href="javascript:void(0);" id="btnCate2" class="btnFilter activeBtn">我的收藏</a>
                            </div>
                            <div id="tab1" class="timeline hidden">
                            </div>
                            <div id="tab2" class="timeline hidden">
                            </div>
                            <div class="timeline timeline_loading" id="time_loading" style="margin-top:0px">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?= html::jsFile('/static/common/js/common.js') ?>
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
    var attention_url = "<?=Url::toRoute(['account/get-all-attention'])?>" + "?page=";
    var attention_end = false;
    var attention_time = 1;

    var collect_page = 1;
    var collect_url = "<?=Url::toRoute(['account/get-collect'])?>" + "?page=";
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
        $("#time_loading").html('<div class="load-wrapp"><div class="load-9"><div class="spinner"><div class="bubble-1"></div><div class="bubble-2"></div></div><p>正在加载...</p></div></div>');
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
            attention_page = 1;
            attention_end = false;
            attention_time = time;
            $("#tab1").empty();
            loadTab(attention_url + attention_page + '&filter=' + filter + '&time=' + attention_time, 'tab1');
        }
        else if (type == 'collect') {
            collect_time = time;
            collect_page = 1;
            collect_end = false;
            $("#tab2").empty();
            loadTab(collect_url + collect_page + '&time=' + collect_time, 'tab2');
        }
    }
</script>
