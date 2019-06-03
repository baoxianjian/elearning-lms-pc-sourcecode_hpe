<?php
use yii\helpers\Url;
use components\widgets\TBreadcrumbs;
use common\models\learning\LnCourse;
$this->pageTitle = Yii::t('frontend', 'page_learn_path_tab_1');// Yii::t('frontend', 'page_lesson_hot_title');
$this->params['breadcrumbs'][] = $this->pageTitle;

$current_time = time();
?>
<style type="text/css">
  .scoreList span{margin:0 20px 0 0 !important;}

   #list_panel .p{
       text-align: left;
   }
   .loadingWaiting span {
    display: inline-block;
    width: 8px;
    height: 100%;
    border-radius: 4px;
    background: #5484a3;
    animation: load 1s ease infinite;
    -webkit-animation: load 1s ease infinite;
    margin: 0 !important;
  }
  #list_panel h5{
    text-align: left;
  }
</style>
<div class="container">
  <div class="row">
    <?= TBreadcrumbs::widget([
        'tag' => 'ol',
        'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
    ]) ?>
    <div class="col-md-8">
      <ul class="nav nav-tabs hotNews" role="tablist" id="myTab">
        <li role="presentation" class="active"><a href="#allCourse" aria-controls="allCourse" role="tab" data-toggle="tab"><?= Yii::t('frontend', 'page_lesson_hot_tab_1') ?></a></li>
      </ul>
      <div class="tab-content">
        <div role="tabpanel" class="tab-pane active" id="allCourse">
          <div class="panel panel-default scoreList">
              <div style="margin: 0 20px -10px 20px;">
                  <div class="row" style="padding-top:10px;margin-bottom:0px;">
                      <div class="btn-group timeScope pull-left" style="margin-top:10px;">
                          <button style="padding:1px 5px" id="select_course_type" class="btn btn-default btn-xs dropdown-toggle" type="button" data-toggle="dropdown" aria-expanded="false"><?= Yii::t('frontend', 'all_type_course') ?> &nbsp;<span class="caret" style="margin:0;"></span>
                          </button>
                          <ul class="dropdown-menu" role="menu">
                              <li><a href="javascript:void(0)" onclick="changeType('all',this)"><?= Yii::t('frontend', 'all_type_course') ?></a></li>
                              <li><a href="javascript:void(0)" onclick="changeType(<?= LnCourse::COURSE_TYPE_ONLINE ?>,this)"><?= Yii::t('frontend', 'course_online') ?></a></li>
                              <li><a href="javascript:void(0)" onclick="changeType(<?= LnCourse::COURSE_TYPE_FACETOFACE ?>,this)"><?= Yii::t('frontend', 'course_face') ?></a></li>
                          </ul>
                      </div>
                      <a href="javascript:void(0)" onclick="setOrder('hot')" class="pull-right btn"><?= Yii::t('frontend', 'hotest') ?></a>
                      <a href="javascript:void(0)" onclick="setOrder('new')" class="pull-right btn"><?= Yii::t('frontend', 'newst') ?></a>
                  </div>
                  <hr>
              </div>
              <div id="list_panel" class="panel-body textCenter">
              </div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="panel panel-default examState">
        <div class="panel-body" id="category_panel"></div>
      </div>
    </div>
  </div>
</div>
<script type="text/javascript">
    var loading = true;
    var page = 1;
    var end = false;
    var order = 'new';
    var course_type='all';
    var checkIds = '0';
    var url = "<?=Url::toRoute(['resource/course/list-course','current_time'=>$current_time])?>";
    $(document).ready(function () {
        var category_url = "<?=Url::toRoute(['resource/course/get-category'])?>";
        app.get(category_url, function(r){
            if (r){
                $('#category_panel').html(r);
            }
        });
//        $("#class_0").attr('checked', 'true');
        loadInfo(url + "&page=" + page + "&order=" + order, 'list_panel', true);

        $(window).scroll(function () {
            var bot = 100; //bot是底部距离的高度
            if (!loading && (bot + $(window).scrollTop()) >= ($(document).height() - $(window).height())) {
                //当底部基本距离+滚动的高度〉=文档的高度-窗体的高度时；
                //我们需要去异步加载数据了
                if (!end) {
                    loading = true;
                    page++;
                    if (checkIds != '0') {
                        loadInfo(url + "&ids=" + checkIds + '&type='+course_type + '&page=' + page + "&order=" + order, 'list_panel', false);
                    }
                    else {
                        loadInfo(url +  '&type='+course_type + '&page=' + page + "&order=" + order, 'list_panel', false);
                    }
                }
            }
        });
    });

    function loadInfo(ajaxUrl, container, is_clear) {
        if (is_clear) {
            $("#" + container).empty();
            page = 1;
            end = false;
        }
        $("#" + container).append('<div class="loadingWaiting"><span></span><span></span><span></span><span></span><span></span><p><?= Yii::t('frontend', 'loading') ?>...</p></div>');
        app.get(ajaxUrl, function(r){
            infoBind(container, r);
        });
    }
    function infoBind(target, data) {
        $(".loadingWaiting").remove();
        if (data != null && data != '') {
            $("#" + target).append("<div class='row'>" + data + "</div>");
        }
        loading = false;
        var count=$(data).find('.cover').length;
        if (data == null || data == '' || count < 9) {
            end = true;
        }
    }

    function setOrder(o) {
        order = o;
        page = 1;
        end = false;
        if (checkIds != '0') {
            loadInfo(url + "&ids=" + checkIds + '&type='+course_type + '&page=' + page + "&order=" + order, 'list_panel', true);
        }
        else {
            loadInfo(url +  '&type='+course_type + '&page=' + page + "&order=" + order, 'list_panel', true);
        }
        return false;
    }

    function changeType(type,obj)
    {
        course_type=type;
        $("#select_course_type").html($(obj).html()+' &nbsp;<span class="caret" style="margin:0;"></span>');
        page = 1;
        end = false;
        if (checkIds != '0') {
            loadInfo(url + '&ids=' + checkIds + '&type='+course_type + '&page=' + page + "&order=" + order, 'list_panel', true);
        }
        else {
            loadInfo(url + '&type='+course_type + '&page=' + page + "&order=" + order, 'list_panel', true);
        }
    }
</script>