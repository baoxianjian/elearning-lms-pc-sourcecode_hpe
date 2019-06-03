<?php
use yii\helpers\Html;
use yii\helpers\Url;
use components\widgets\TBreadcrumbs;
use yii\widgets\ActiveForm;
use common\helpers\TStringHelper;

$this->pageTitle = Yii::t('frontend', 'tag_tag_manage');// Yii::t('frontend', 'page_lesson_hot_title');
$this->params['breadcrumbs'][] = ['label'=>Yii::t('frontend', 'tag_res_manage'),'url'=>['resource/index']];
$this->params['breadcrumbs'][] = $this->pageTitle;
?>
<style>
  .form-control{
    width: 100%;
  }
  .pagination{
    float:right;
  }
  .form-group input{
    text-align: left !important;
  }
  .centerBtnArea {
    float: left !important;
  }
</style>
  <div class="container">
    <div class="row">
      <?= TBreadcrumbs::widget([
          'tag' => 'ol',
          'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
      ]) ?>
      <div class="col-md-12 col-sm-12">
        <div class="panel panel-default hotNews">
          <div class="panel-heading">
            <i class="glyphicon glyphicon-cloud-upload"></i> <?=Yii::t('frontend', 'tag_tag_hub')?>
          </div>
          <div class="panel-body">
            <div class="col-md-12">
              <ul class="nav nav-tabs hotNews" role="tablist" id="myTab">
                <li role="presentation" class="active"><a href="#" id="course" aria-controls="home" role="tab" data-toggle="tab" aria-expanded="true"><?=Yii::t('frontend', 'tag_course_tag')?></a></li>
                <li role="presentation" class=""><a href="#" id="question" aria-controls="profile" role="tab" data-toggle="tab" aria-expanded="false"><?=Yii::t('frontend', 'tag_qa_tag')?></a></li>
                <li role="presentation" class=""><a href="#" id="knowledge" aria-controls="knowledge" role="tab" data-toggle="tab" aria-expanded="false"><?=Yii::t('frontend', 'tag_point_tag')?></a></li>
              </ul>
              <div class="tab-content" style="display: block;float: left;width: 100%;border: 1px solid #eee;">
                <div role="tabpanel" class="tab-pane active" id="home">
                  <div class="col-md-12 col-sm-12">
                    <div class="actionBar">
                      <a class="btn btn-success  pull-left" href="#" id="addBtn"><?=Yii::t('frontend', 'tag_add_course_tag')?></a>
                      <form class="form-inline pull-right">
                        <div class="form-group">
                          <input type="text" class="form-control" placeholder="<?=Yii::t('common', 'audience_name')?>" id="searchcontent" value="">
                          <button type="button" class="btn btn-default pull-right" id="reset"><?=Yii::t('frontend', 'reset')?></button>
                          <button type="button" class="btn btn-primary pull-right" style="margin-left:10px;" id="search"><?=Yii::t('frontend', 'tag_query')?></button>
                        </div>
                      </form>
                    </div>
                    <div id="content">
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
<input id="PageSize_grid" type="hidden" value="" />
  <!-- 增加新的标签 -->
<div id="newLabel" class="ui modal">
  <div class="header"><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button><h4><?=Yii::t('frontend', 'tag_add_tag')?></h4></div>
  <div class="content">
    <div class="row">
      <div class="col-md-12 col-sm-12">
        <div class="form-group form-group-sm">
          <div class="col-sm-8" style="padding: 30px 0 0 0;margin:0 auto;float:none;width:90%">
            <input class="form-control" placeholder="<?=Yii::t('frontend', 'tag_pls_type_tag')?>" type="text" id="tagname" maxlength="20">
            <input type="hidden" id="tagkid" value="" />
            <input type="hidden" id="tagcategory" value="course" />
            <input type="hidden" id="tagtype" value = "">
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="actions" style="text-align:center;border:0">

    <div class="btn btn-success centerBtn ok"><?=Yii::t('common', 'save')?></div>
  </div>
</div>
 <!--确认标签-->
<div id="delete" class="ui modal">
  <div class="header"><?=Yii::t('frontend', 'tag_del')?><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></div>
  <div class="content">
    <p><?=Yii::t('frontend', 'tag_sure_to_del')?></p>
  </div>
  <div class="actions">
    <div class="btn btn-default ok"><?=Yii::t('frontend', 'be_sure')?></div>
    <div class="btn btn-default cancel"><?= Yii::t('frontend', 'page_info_good_cancel') ?></div>
  </div>
</div>
  <!-- /container -->
  <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
  <script src="dist/js/jquery.min.js"></script>
  <!-- Include all compiled plugins (below), or include individual files as needed -->
  <script src="dist/js/bootstrap.min.js"></script>
  <script src="dist/js/jquery-ui.min.js"></script>
  <script src="dist/js/jstree.min.js"></script>
<script>
 // $('#jsTree').jstree();
</script>
  <!-- 临时用, 资源管理页面直接进入新增页面. -->
  <script>
    var url = "<?=Url::toRoute(['tag/content'])?>";
    var is_clear = 1;
    var container = 'content';
    var category = 'course';
    var page = 1;
    var key = '';
    var refresh ='';
    var keyrecordnow = '';

  (function(){
      $(document).ready(function() {
        app.extend("alert");
        __TG.Tag.tableList();
        __TG.Tag.draggable();
   //     __TG.Tag.tree();
        $("#addBtn").click(function(){
          $('#tagname').val('');
          $('#tagtype').val('add');
          __TG.Tag.tagsave();
        });
        $("#course").click(function(){
          category = 'course';
          $('#searchcontent').val('');
          __TG.Tag.tableList();
          __TG.Tag.changerBtnhtml();
          $('#tagcategory').val(category);
        });
        $("#knowledge").click(function(){
          category = 'knowledge';
          $('#searchcontent').val('');
          __TG.Tag.tableList();
          __TG.Tag.changerBtnhtml();
          $('#tagcategory').val(category);
        });
        $("#question").click(function(){
          category = 'conversation';
          $('#searchcontent').val('');
          __TG.Tag.tableList();
          __TG.Tag.changerBtnhtml();
          $('#tagcategory').val(category);
        });
        $("#reset").click(function(){
          $('#searchcontent').val('');
        });
        $("#search").click(function(){
          is_clear = 1;
          var search = $('#searchcontent').val();
          key = search;
          keyrecordnow = search;
          __TG.Tag.tableList();
          key = '';
        });
      });
      var __TG = {};
      __TG.Tag ={

          tagsave:function(){
            app.alertSmall('#newLabel',{
              ok: function () {
                var type = $('#tagtype').val();
                if(type=='add'){
                  var tableurl = "<?=Url::toRoute(['tag/tagadd'])?>";
                }else{
                  var kid = $('#tagkid').val();
                  var tableurl = "<?=Url::toRoute(['tag/tagupdate'])?>";
                }
                var type = $('#tagtype').val();
                var namestr = $('#tagname').val();
                var name  = namestr.trim();
                var kid = $('#tagkid').val();
                var category = $('#tagcategory').val();
                var checkurl = "<?=Url::toRoute(['tag/tagisset'])?>";
/*              app.get(url,function(r){
                  if(r){}
                });
                */
                $.get(checkurl,{"name":name,"category": category},function (data) {
                  if(data == 'null'){
                    app.showMsg('<?=Yii::t('frontend', '{value}_not_null',['value'=>Yii::t('common', 'tag')])?>');
                  }else if(data == 'isset'){
                    app.showMsg('<?=Yii::t('frontend', 'tag_tag_exist')?>');
                  }else if(data == 'long'){
                    app.showMsg('<?=Yii::t('frontend', 'tag_tag_too_more')?>');
                  }else if(data == 'success'){
                    json = {"type": type, "name": name, "kid": kid, "category": category};
                    $.get(tableurl, json, function (data) {
                      if (is_clear) {
                        $("#" + container).empty();
                        page = 1;
                        end = false;
                      }
                      var keyurl = encodeURI(key);
                      ajaxGet(url+'?keyword='+keyurl+'&page='+page+'&category='+category,container );
                    });
                    app.hideAlert("#newLabel");
                  }
                });
                return false;
              },
              cancel: function ()
              {
                return true;
              }
            });
          },
         changerBtnhtml:function(){
            if(category == 'course'){
              $('#addBtn').empty().html('<?=Yii::t('frontend', 'tag_add_course_tag')?>');
            }else if(category == 'conversation'){
              $('#addBtn').empty().html('<?=Yii::t('frontend', 'tag_add_ht_tag')?>');
            }else{
              $('#addBtn').empty().html('<?=Yii::t('frontend', 'tag_add_pt_tag')?>');
            }
        },
        tree:function(){
          $('#jsTree').jstree();
        },
        draggable:function(){
          $("#newLabel").draggable({
            handle: ".modal-header"
          });
        },
        tableList:function(){
          if (is_clear) {
            $("#" + container).empty();
            page = 1;
            end = false;
          }
          var keyurl = encodeURI(key);
          ajaxGet(url+'?keyword='+keyurl+'&page='+page+'&category='+category,container );
          $('#question').attr('data-toggle','tab');
        },
      };
    })();
    ! function($) {
      var hash = location.hash && location.hash.substr(1).split("@"),
          $_element, $_evt;
      if (hash && 2 === hash.length) {
        $_element = hash[0];
        $_evt = hash[1];
        try {
          $($_element)[$_evt]();
        } catch (e) {
          console.log(e.stack || e);
        }
      }
    }(jQuery);
    function delcfm(id) {
      app.alertSmall('#delete',{
        ok: function ()
        {
          $('#tagtype').val('del');
          $('#tagkid').val(id);
          var tableurl = "<?=Url::toRoute(['tag/tagupdate'])?>";
          var type = $('#tagtype').val();

          json={"type":type,"kid":id,};

          $.get(tableurl,json,function (data) {
              var keyurl = encodeURI(key);
              ajaxGet(url+'?keyword='+keyurl+'&page='+page+'&category='+category,container );
          });
          is_clear = 1;
          if (is_clear) {
            $("#" + container).empty();
            page = 1;
            end = false;
          }

          return true;

        },
        cancel: function ()
        {
          return true;
        }
      });

    };
    function changelabel(id) {
      $('#tagkid').val(id);
      $('#tagtype').val('update');
      var tampname = $('#value'+id).attr('data-name');
      $('#tagname').val(tampname);
      app.alertSmall('#newLabel',{
        ok: function () {
          var type = $('#tagtype').val();
          if(type=='add'){
            var tableurl = "<?=Url::toRoute(['tag/tagadd'])?>";
          }else{
            var kid = $('#tagkid').val();
            var tableurl = "<?=Url::toRoute(['tag/tagupdate'])?>";
          }
          var type = $('#tagtype').val();
          var namestr = $('#tagname').val();
          var name  = namestr.trim();
          var kid = $('#tagkid').val();
          var category = $('#tagcategory').val();
          var checkurl = "<?=Url::toRoute(['tag/tagisset'])?>";
          /*              app.get(url,function(r){
           if(r){}
           });
           */
          $.get(checkurl,{"name":name,"category": category},function (data) {
            if(data == 'null'){

              app.showMsg('<?=Yii::t('frontend', '{value}_not_null',['value'=>Yii::t('common', 'tag')])?>');
            }else if(data == 'isset'){
              app.showMsg('<?=Yii::t('frontend', 'tag_tag_exist')?>');
            }else if(data == 'long'){
              app.showMsg('<?=Yii::t('frontend', 'tag_tag_too_more')?>');
            }else if(data == 'success'){
              json = {"type": type, "name": name, "kid": kid, "category": category};
              $.get(tableurl, json, function (data) {
                if (is_clear) {
                  $("#" + container).empty();
                  page = 1;
                  end = false;
                }
                var keyurl = encodeURI(key);
                ajaxGet(url+'?keyword='+keyurl+'&page='+page+'&category='+category,container );
              });
              app.hideAlert("#newLabel");
            }
          });
          return false;
        },
        cancel: function ()
        {
          return true;
        }
      });
    };
    $(function(){
      $("#courseIntro .pagination").on('click', 'a', function(e){
        e.preventDefault();
        ajaxGet($(this).attr('href'), 'content');
      });
    });
  </script>
