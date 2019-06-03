<?php
use yii\helpers\Html;
use yii\helpers\Url;
use components\widgets\TBreadcrumbs;
use frontend\widgets\HotQuestion;
use frontend\widgets\HotQuestionTag;
use yii\widgets\ActiveForm;
use common\helpers\TStringHelper;
$this->pageTitle = Yii::t('frontend', 'question_answer_home');// Yii::t('frontend', 'page_lesson_hot_title');
$this->params['breadcrumbs'][] = $this->pageTitle;
?>
<style>
    .control-label {
        display: none;
    }
    .help-block {
        display: none;
    }
	h5 a{ color:#333}
	
	.setitle{
		color: #888 !important;
		margin: 0 20px 0 0 !important;
	}
	.carecol{
		color: #337ab7 !important;
	}
    #shareQuestionBtn{
        margin-top: 10px;
    }
    .centerBtnArea {
        float: left !important;
    }
</style>
<div class="container">
    <div class="row">
        <?= TBreadcrumbs::widget([
            'tag' => 'ol',
            'homeLink' => ['url' => Yii::$app->urlManager->createUrl('student/index'), 'label' => Yii::t('common','home')],
            'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
        ]) ?>
        <div class="col-md-4">
            <?= HotQuestionTag::widget();?>
            <?= HotQuestion::widget();?>
        </div>
        <div class="col-md-8">
            <ul class="nav nav-tabs hotNews" role="tablist" id="myTab">
                <li role="presentation" class="active" id="unsovedTab">
                    <a href="#home" aria-controls="home" role="tab" data-toggle="tab" id="unsolved" ><?=Yii::t('frontend', 'solve_waiting')?></a>
                </li>
                <li role="presentation" id="solvedTab">
                    <a href="#profile" aria-controls="profile" role="tab" data-toggle="tab" id="solved" ><?=Yii::t('frontend', 'solve_been')?></a>
                </li>
                <li role="presentation" id="myquestionTab">
                    <a href="#myQuestion" aria-controls="myQuestion" role="tab" data-toggle="tab" id="myquestion" ><?=Yii::t('frontend', 'my_answer')?></a>
                </li>
            </ul>
            <div class="tab-content">
                <div role="tabpanel" class="tab-pane active" id="home">
                    <div class="panel panel-default scoreList">
                        <div class="panel-body" id="homecontent">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<input id="tagNow" value="" type="hidden"/>
<input id="tagid" value="" type="hidden"/>
<!-- 收藏弹出提示 -->
<div class="ui modal" id="newCollect">
   <div class="header">
     <button type="button" class="close"  aria-label="Close">
         <span aria-hidden="true">×</span>
     </button>
     <h4 class="title" id="myModalLabelcoll"></h4>
  </div>
  <div class="content">
     <h4 style="text-align: center" id="myModalLabelcollstr"></h4>
  </div>
</div>

<!-- 分享弹出提示 -->
<div class="ui modal" id="newShare">
   <div class="header">
      <button type="button" class="close" aria-label="Close"><span aria-hidden="true">&times;</span></button>
       <h4 class="title" id="myModalLabel"><?=Yii::t('frontend', 'share')?></h4>
   </div>
    <div class="content">
       <h4><?=Yii::t('frontend', 'share_to_social_circle')?></h4>
         <?php $form = ActiveForm::begin([
           'id' => 'shareQuestionForm',
           'method' => 'post',
           'action' => Yii::$app->urlManager->createUrl('question/share'),
         ]); ?>
       <div class="form-group field-soshare-obj_id">
          <label class="control-label" for="soshare-obj_id">obj_id</label>
          <input type="hidden" id="soshare-obj_id" class="form-control" name="SoShare[obj_id]" value="">
           <div class="help-block"></div>
       </div>
       <div class="form-group field-soshare-title required">
         <label class="control-label" for="soshare-title">title</label>
         <input type="hidden" id="soshare-title" class="form-control" name="SoShare[title]" value="">
         <div class="help-block"></div>
       </div>
       <?= $form->field($shareModel, 'content')->textarea(['maxlength' => 1000,'style'=>'width:100%; height:80px;border:1px solid #eee;']) ?>
       <div id="content" style="width:100%;border:1px solid #eee; padding: 4px 8px" data-title=""></div>
       <?=
         Html::button(Yii::t('frontend', 'share'),
         ['id' => 'shareQuestionBtn', 'class' => 'btn btn-success btn-sm pull-right', 'onclick' => 'submitModalForm("","shareQuestionForm","",true,false,null,null);'])
       ?>
       <?php ActiveForm::end(); ?>
       <input type="hidden" id="sharekid" value="" />
    </div>
</div>

<!-- 关注弹出提示 -->
<div class="ui modal" id="newFollow">
  <div class="header">
    <button type="button" class="close" aria-label="Close">
        <span aria-hidden="true">×</span>
    </button>
      <h4 class="" id="myModalLabelcare"></h4>
  </div>
    <div class="content">
       <h4 style="text-align: center" id="myModalLabelcarestr"></h4>
    </div>
</div>
<!-- /container -->
<!--[if lt IE 9]><script type="text/javascript" src="/static/frontend/js/excanvas.js"></script><![endif]-->
<script src="/static/frontend/js/tagcanvas.js" type="text/javascript"></script>
<script type="text/javascript">
    app.extend("alert");
    var options ={
		dragControl : true
	}
    window.onload = function() {
        try {
            TagCanvas.Start('myCanvas','',options);
        } catch (e) {
            // something went wrong, hide the canvas container
            document.getElementById('myCanvasContainer').style.display = 'none';
        }
    };
</script>
<script>
    var status = 0;
    var statusnow;
    var value=<?php $finvalue = $value ?$value :'';echo '\''.$finvalue.'\''?>;
    var kid=<?php $finkid = $kid ?$kid :'';echo '\''.$finkid.'\''?>;
    var activeBtn = 'questionmine';
	var carenow = 1;
	var colnow = 1;
	var care = '';
	var col = '';
	var carecount=0;
	var colcount=0;
    var sharenow = '';
    var page = 1;
    var key = null;
    var end = false;
    var is_clear = 1;
    var small = false;
    var timenow = <?=$timenow?>;
    var tagid = $('#tag').val();
    var container = 'homecontent';//questionismine
    var question_type='unsolved';
    var url = "<?=Url::toRoute(['question/solved'])?>";
    (function(){
        $(document).ready(function() {
            if(kid != '' && value != ''){
                tagpage(kid,value);
            }
            __QT.Question.pageFilte();//我的问答页面切换
            __QT.Question.autoLoading();//自动加载页面方法
            __QT.Question.firstPage();
            __QT.Question.pageTab();//标签切换页脚本
            $("#unsolved").click(function(){
                small = false;
                container = 'homecontent';
                question_type='unsolved';
                __QT.Question.firstPage();
            });
            $("#solved").click(function(){
                small = false;
                container = 'homecontent';
                question_type='solved';
                __QT.Question.firstPage();
            });
            $("#myquestion").click(function(){
                small = true;
                container = 'homecontent';
                question_type='mine';
                __QT.Question.firstPage();
            });
            $("#homecontent").on('click', '#questionmine', function(){
                small = true;
                question_type='mine';
                __QT.Question.firstPage();
                activeBtn = 'questionmine'
            });
            $("#homecontent").on('click', '#icare', function(){
                small = true;
                question_type='care';
                __QT.Question.firstPage();
                activeBtn = 'icare';
            });
            $("#homecontent").on('click', '#atme', function(){
                small = true;
                question_type='atme';
                __QT.Question.firstPage();
                activeBtn = 'atme';
            });
            $("#homecontent").on('click', '#ianswered', function(){
                small = true;
                question_type='answer';
                __QT.Question.firstPage();
                activeBtn = 'ianswered';
            });
            $("#homecontent").on('click', '#searchbutton', function(){
                small = true;
                key = $('#searchcontent').val();
                key = encodeURI(key);
                if(question_type!='search'){
                    statusnow = question_type;
                }
                question_type='search';
                __QT.Question.firstPage();
            });


        });
        var __QT = {};
        __QT.Question ={

            firstPage:function(){
                if (is_clear) {
                    $("#" + container).empty();
                    page = 1;
                    end = false;
                }
                tagid = $('#tagid').val();
                ajaxGet(url+'?page='+page+'&type='+question_type+'&keyword='+key+'&tag='+tagid+'&time='+timenow+'&status='+statusnow,container );
            },

            <!--我的问答页面切换-->
            pageFilte:function(){
                var btnNum = $('.btnFilter').length;
                for (i = 0; i < btnNum; i++) {
                    $($('.btnFilter')[i]).attr("data-num", i)
                };
                //为每个按钮添加事件
                $('.btnFilter').bind('click', function() {
                    var actBtn = $(this).attr("data-num");
                    // 给选中的按钮加上 activeBtn 样式
                    $('.btnFilter').removeClass('activeBtn');
                    $(this).addClass('activeBtn');
                    $('.questionLine').addClass('hidden');
                    $($('.questionLine')[actBtn]).removeClass('hidden');
                });
            },
            <!-- 标签切换页脚本 -->
            pageTab:function(){
                $('#myTab a:first').tab('show');
            },
            <!--自动加载页面方法-->
            autoLoading:function(){

                $(window).scroll(function () {
                    if ($(window).scrollTop() == $(document).height() - $(window).height()) {
                        tagid = $('#tagid').val();
                        page++;
                        ajaxGet(url+'?page='+page+'&type='+question_type+'&keyword='+key+'&small='+small+'&tag='+tagid+'&time='+timenow+'&status='+statusnow,container,infoBind);
                    }
                });
            },
          };
    })();
    function infoBind(target, data) {
        if (data != null && data != '') {
            $("#" + target).append(data);
        }

        loading = false;
       if (data == null || data == '' ) {
            end = true;
        }
    }
   function subCare(id) {
     //  app.alert('#newFollow');

       care = $('#carestr'+id).attr('now');

	   if(care == '')care=0;
	    care ++;
        var url = "<?=Url::toRoute(['question/care'])?>";
        $.post(url, {"qid": id},
            function (data) {
                //score-Effect(data.point);
                var result = data.result;
                if (result === 'other') {
                    $('#myModalLabelcarestr').html(data.message);
                }
                else if (result === 'failure') {
                    $('#myModalLabelcare').html('<?=Yii::t('common', 'operation_confirm_warning_failure')?>');
                    $('#myModalLabelcarestr').html('<?=Yii::t('common', 'operation_confirm_warning_failure')?>');
                }
                else if (result === 'success') {
					if(care%2 != 0){
						$('#myModalLabelcare').html('<?= Yii::t('frontend', 'attention_sucess') ?>');
						$('#myModalLabelcarestr').html('<?= Yii::t('frontend', 'attention_sucess') ?>');
						mycarenow = $('#carestr'+id).attr('now');
						carecount = $('#carestr'+id).attr('count');
						carecount ++;
						$('#carestr'+id).empty().html('<?=Yii::t('common', 'cancel_attention')?>('+carecount+')');
						mycarenow = $('#carestr'+id).attr('now',1);
						carecount = $('#carestr'+id).attr('count',carecount);
                        
                        if (checkPointResult(data.pointResult))
                        {
                            scorePointEffect(data.pointResult.show_point,data.pointResult.point_name,data.pointResult.available_point);
                        }
                        else
                        {
                            app.showMsg("<?= Yii::t('frontend', 'attention_sucess') ?>", 1000);
                        }

					}else{
                        app.showMsg("<?=Yii::t('frontend', 'cancel_sucess')?>", 1000);

                        mycarenow = $('#carestr'+id).attr('now');
						carecount = $('#carestr'+id).attr('count');
						carecount --;
						$('#myModalLabelcare').html('<?=Yii::t('frontend', 'cancel_sucess')?>');
						$('#myModalLabelcarestr').html('<?=Yii::t('frontend', 'cancel_sucess')?>!');
						$('#carestr'+id).empty().html('<?=Yii::t('common', 'attention')?>('+carecount+')');
						mycarenow = $('#carestr'+id).attr('now','');
						carecount = $('#carestr'+id).attr('count',carecount);
                        var type = '';
                        type = $('#'+id).attr('type');
                        if(type==1){
                            $('#'+id).remove();
                        }
					}
                }
            }, "json");
        return false;
    }
    <!--收藏功能-->
    function subCollect(id) {
      //  app.alert('#newCollect');

        col = $('#colstr'+id).attr('now');

        if(col == '')col=0;
        col ++;

        var url = "<?=Url::toRoute(['question/collect'])?>";
        $.post(url, {"qid": id},
            function (data) {
                //score-Effect(data.point); 
                var result = data.result;
                if (result === 'other') {
                    $('#myModalLabelcollstr').text(data.message);
                }
                else if (result === 'failure') {
                    $('#myModalLabelcoll').text('<?=Yii::t('common', 'operation_confirm_warning_failure')?>');
                    $('#myModalLabelcollstr').html('<?=Yii::t('common', 'operation_confirm_warning_failure')?>');
                }
                else if (result === 'success') {
					if(col%2 != 0){
						$('#myModalLabelcoll').empty().text('<?=Yii::t('frontend', 'collection_sucess')?>');
						$('#myModalLabelcollstr').empty().html('<?=Yii::t('frontend', 'collection_sucess')?>');
                        mycarenow = $('#colstr'+id).attr('now');
                        carecount = $('#colstr'+id).attr('count');
                        carecount ++;
                        $('#colstr'+id).empty().html('<?=Yii::t('common', 'canel_collection')?>('+carecount+')');
                        mycarenow = $('#colstr'+id).attr('now',1);
                        carecount = $('#colstr'+id).attr('count',carecount);
                        if (checkPointResult(data.pointResult))
                        {
                            scorePointEffect(data.pointResult.show_point,data.pointResult.point_name,data.pointResult.available_point);
                        }
                        else
                        {
                            app.showMsg("<?=Yii::t('frontend', 'collection_sucess')?>", 1000);
                        }

                    }else{
						$('#myModalLabelcoll').empty().text('<?=Yii::t('frontend', 'cancel_sucess')?>');
						$('#myModalLabelcollstr').empty().html('<?=Yii::t('frontend', 'cancel_sucess')?>!');
                        mycarenow = $('#colstr'+id).attr('now');
                        carecount = $('#colstr'+id).attr('count');
                        carecount --;
                        $('#colstr'+id).empty().html('<?= Yii::t('common', 'collection')?>('+carecount+')');
                        mycarenow = $('#colstr'+id).attr('now','');
                        carecount = $('#colstr'+id).attr('count',carecount);
                        app.showMsg("<?=Yii::t('frontend', 'cancel_sucess')?>", 1000);

					}
                }
            }, "json");
        return false;
    }
    function questionid(kid,title,sharenum) {
        $('#soshare-obj_id').val(kid)
        $('#soshare-title').val(title);
        $('#sharekid').val(kid);
        $('#content').attr('data-title',title);
        $('#content').empty().append('<?=Yii::t('frontend', 'question')?>：'+title);
        var tempshare = $('#sharenum'+kid).val();
        if(tempshare==''){
            $('#sharenum'+kid).val(sharenum);
        }else{
            sharenum =  sharenow;
            $('#sharenum'+kid).val(sharenum);
        }
        app.alert('#newShare');
    }
    function tagpage(id,value) {
        $('#tagid').val(id);
        var nowpageId = 'homecontent';
        $('#' + nowpageId).empty();
        $('#myquestionTab').remove();
        $("#unsovedTab").addClass('active');
        $("#solvedTab").removeClass('active');
        page = 1;
        ajaxGet(url + '?page=' + page + '&type=unsolved' + '&small=' + small + '&tag=' + id + '&time=' + timenow+'&status='+statusnow, container);
        $("#tagvalue").remove();
        $("#tagstr").remove();
        if(status == 0){
            html5 = '<li></li>';
            $('.breadcrumb').append(html5);
            status = 1;
        }
        var counturl = "<?=Url::toRoute(['question/solved-count'])?>";
        $.post(counturl, {"tag": id,"type": 1},function(data){
            $("#solved").empty().append('<?=Yii::t('frontend', 'solve_been')?>（'+data+'）');
        },'text');
        countunsolved =  $.post(counturl, {"tag": id,"type": 0},function(data){
            $("#unsolved").empty().append('<?=Yii::t('frontend', 'solve_waiting')?>（'+data+'）');
        },'text');
        html3 =  '<li id="tagvalue" class="active">' + value +'</li>' +
            '<p id="tagstr" class="pull-right" style="margin-top:34px;">“' + value + '”<?=Yii::t('frontend', 'hot_tag_result')?></p>'
        $('.breadcrumb').append(html3);
        $("#tagvalue").prev().empty();
        html4 = '<a href="<?= Yii::$app->urlManager->createUrl('question/index')?>"><?=$this->pageTitle?></a>';
        $("#tagvalue").prev().append(html4);
        $("#tagvalue").prev().prev().empty();
        html2 = '<a id="headhref" href="<?= Yii::$app->urlManager->createUrl('student/index')?>"><?=Yii::t('common', 'home')?></a>';
        $("#tagvalue").prev().prev().append(html2);
        $("#tagvalue").prev().prev().removeAttr("class");
        $("#tagvalue").prev().prev().prev().empty();
        $("#tagvalue").prev().prev().prev().append("<h2 id='head'><?=Yii::t('frontend', 'hot_tag')?></h2>");

    }
    function ReloadPageAfterUpdate(frameId, formId, modalId, isClose)
    {
        var kid = $('#sharekid').val();
        var sharenum =  $('#sharenum'+kid).val();
        sharenum++;
        $('#sharenum'+kid).val(sharenum);

        $('#sharestr'+kid).empty().html('<?=Yii::t('frontend', 'share')?>('+sharenum+')');
        app.hideAlert("#newShare");
        app.showMsg('<?=Yii::t('common', 'operation_success')?>');
        formReset();
        sharenow = sharenum;
    }

    function formReset()
    {
        $("#soshare-content").val(null);
    }
</script>
