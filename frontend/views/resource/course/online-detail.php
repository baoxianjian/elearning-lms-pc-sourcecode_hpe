<?php
/**
 * Created by PhpStorm.
 * User: Liu Cheng
 * Date: 2016/1/5
 * Time: 16:34
 */

use components\widgets\TBreadcrumbs;
use yii\helpers\Url;

$this->params['breadcrumbs'][] = ['label'=>Yii::t('common','resource_management'),'url'=>['/resource/index']];
$this->params['breadcrumbs'][] = ['label'=>Yii::t('common', 'online') . Yii::t('common','course').Yii::t('common','management'),'url'=>['/resource/course/manage']];
$this->params['breadcrumbs'][] = Yii::t('common', 'online') . Yii::t('common','course').Yii::t('common','management');
$this->params['breadcrumbs'][] = $course->course_name;

?>
<div class="container">
    <div class="row">
        <?= TBreadcrumbs::widget([
            'tag' => 'ol',
            'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
        ]) ?>
        <div class="col-md-12 col-sm-12">
            <div class="courseInfo">
                <ul class="nav nav-tabs hotNews" role="tablist" id="myTab">
                    <li role="presentation" class="active"><a href="javascript:;" class="loadStaticsTable" id="btn_tab1"><?= Yii::t('frontend', 'regist_student') ?></a></li>
<!--                    <li role="presentation"><a href="javascript:;" class="loadActiveTable" id="btn_tab2">资源完成配置</a></li>-->
                    <li role="presentation"><a id="btn_tab3" href="#tabpanel3" role="tab" data-toggle="tab" class="loadActiveTable"><?= Yii::t('frontend', 'score_record') ?></a></li>
                    <li role="presentation"><a id="btn_tab4" href="#tabpanel4" role="tab" data-toggle="tab" class="loadActiveTable"><?= Yii::t('common', 'complete_rule') ?></a></li>
                </ul>
                <div class="tab-content">
                    <div role="tabpanel" class="tab-pane active" id="courseAward4"></div>
                    <div role="tabpanel" class="tab-pane" id="resource_config"></div>
                    <div role="tabpanel" class="tab-pane" id="tabpanel3">
                        <div class=" panel-default scoreList">
                            <div class="panel-body">
                                <div class="panel-list" id="score_list" data-url="<?=Url::toRoute(['/resource/course/get-course-score','id'=>$model->kid])?>"></div>
                                <div id="list_loading" class="loadingWaiting hide" style="margin:100px auto;">
                                    <span></span>
                                    <span></span>
                                    <span></span>
                                    <span></span>
                                    <span></span>

                                    <p><?= Yii::t('frontend', 'loading') ?>...</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div role="tabpanel" class="tab-pane" id="tabpanel4">
                        <div class=" panel-default scoreList">
                            <div class="panel-body">
                                <div class="panel-list" id="config_list" data-url="<?=Url::toRoute(['/resource/course/get-course-config','id'=>$model->kid])?>"></div>
                                <div id="list_loading" class="loadingWaiting hide" style="margin:100px auto;">
                                    <span></span>
                                    <span></span>
                                    <span></span>
                                    <span></span>
                                    <span></span>

                                    <p><?= Yii::t('frontend', 'loading') ?>...</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- /container -->
<!-- 课件完成情况弹出窗口 -->
<div class="ui modal" id="scorm-result"></div>
<div class="ui modal" id="aicc-result"></div>
<div class="ui modal" id="examination-result"></div>
<div class="ui modal" id="homework-result"></div>
<div class="ui modal" id="investigation-result"></div>
<div class="ui modal" id="other-result"></div>
<!-- 学员记录 -->
<div class="ui modal" id="questionaire-result"></div>

<div class="ui modal" id="courseware"></div>
<div class="ui modal" id="questionairedetail"></div>
<div class="ui modal" id="questionairedetailone"></div>
<div class="ui modal" id="checksurvay"></div>
<div class="ui modal" id="examination"></div>
<div class="ui modal" id="examination_log"></div>
<div class="ui modal" id="examination_log-result"></div>
<div class="ui modal" id="homework"></div>
<div class="ui modal" id="scoreDetails"></div>
<div class="ui modal" id="scoreDetailsPerson"></div>
<script>
    var score_url = "<?=Url::toRoute(['/resource/course/get-course-score', 'id' => $course->kid, 'pageSize' => 10])?>";
    var config_url = "<?=Url::toRoute(['/resource/course/get-course-config', 'id' => $course->kid, 'pageSize' => 10])?>";

    $("#btn_tab3").click(function(){
        if ($("#score_list").html() == "") {
            loadPage(score_url, 'score_list', true);
        }
    });
    $("#btn_tab4").click(function(){
        if ($("#config_list").html() == "") {
            loadPage(config_url, 'config_list', true);
        }
    });

    function loadPage(ajaxUrl, container, is_clear) {
        if(is_clear){
            $("#" + container).empty();
            $("#list_loading").removeClass("hide");
        }
        app.get(ajaxUrl,function(data){
            if(is_clear) {
                $("#list_loading").addClass('hide');
            }
            $("#"+container).html(data);
            $("#" + container + ' .pagination a').bind('click', function () {
                var url = $(this).attr('href');
                loadPage(url, container, is_clear);
                return false;
            });
        });
    }
    var btn_tab1_url = "<?=Url::toRoute(['/teacher/detail-score', 'id' => $course->kid, 'iframe' => 'course', 'showHomework' => false])?>";
    var btn_tab2_url = "<?=Url::toRoute(['/resource/course/resource-config', 'id' => $course->kid])?>";
    var scoreDetailUrl = "<?=Url::toRoute(['/resource/course/get-score-detail'])?>";
    var exportScoreDetailUrl = "<?=Url::toRoute(['/resource/course/export-score-detail'])?>";
    var viewModId=null;

    $("#btn_tab2").click(function(e){
        e.preventDefault();
        $("#myTab").find('li').removeClass('active');
        $(this).parent().addClass('active');
        $(".tab-content").find("div[role='tabpanel']").removeClass('active');
        $("#resource_config").addClass('active');
        getData('resource_config', btn_tab2_url);
    });

    function getData(div, url){
        $.get(url, function(data){
            if (data){
                $("#"+div).html(data);
            }
        });
    }

    $("#btn_tab1").click(function(e){
        e.preventDefault();
        $("#myTab").find('li').removeClass('active');
        $(this).parent().addClass('active');
        $(".tab-content").find("div[role='tabpanel']").removeClass('active');
        $("#courseAward4").addClass('active');
        getData('courseAward4', btn_tab1_url);
    });
    getData('courseAward4', btn_tab1_url);

    function showScoreDetail(modId, title, component_code) {
    
        if(component_code=="investigation"){

        	 $.get("<?=Yii::$app->urlManager->createUrl(['investigation-result/online-course-investigation-detail'])?>"+"?modId="+modId,function(data){
             	
             	   if(data.result.type=="0"){
                 	   //alert("问卷");
             		   window.location = "<?=Yii::$app->urlManager->createUrl(['investigation-result/course-survey-manage-result-survey'])?>"+"?id="+data.result.id+"&&course_id="+data.result.course_id+"&&course_type=0";
                		}else{
                		 window.location = "<?=Yii::$app->urlManager->createUrl(['investigation-result/course-survey-manage-result-vote'])?>"+"?id="+data.result.id+"&&course_id="+data.result.course_id+"&&course_type=0";
                    }

                 });
        	
        }else{
        	  viewModId=modId;
              var url = scoreDetailUrl + '?id=' + modId;

              $("#score_modal_title").text(title + " <?= Yii::t('frontend', 'transcript') ?>");
              loadPage(url, 'score_modal_list', false);
              app.alertWide('#printScore');

        }
      
    }

    function exportScoreDetail()
    {
        if(viewModId)
        {
            window.location.href=exportScoreDetailUrl+"?id="+viewModId;
        }
    }

    /*加载成绩查看列表*/
    function LoadCompleteInfo(obj, courseId, modResId, itemId, itemName, componentCode, userId)
    {
        var url = '<?=Url::toRoute(['/teacher/common-result'])?>';
        if (componentCode != 'scorm' && componentCode != 'aicc' && componentCode != 'examination' && componentCode != 'investigation' && componentCode != 'homework'){
            componentCode = 'other';
        }
        if (typeof userId != 'undefined'){
            url = urlreplace(url, 'userId', userId);
            if (componentCode == 'investigation') {
                componentCode = 'questionaire';
            }
        }
        url = url.replace('common', componentCode);
        url = urlreplace(url, 'courseId', courseId);
        url = urlreplace(url, 'modResId', modResId);
        url = urlreplace(url, 'itemId', itemId);
        url = urlreplace(url, 'componentCode', componentCode);
        if (componentCode == 'examination' && typeof $(obj).attr('data-log') != 'undefined'){
            var activityId = $(obj).attr('data-activity-id');
            var modId = $(obj).attr('data-mod-id');
            url = '<?=Url::toRoute(['/exam-manage-main/view-log'])?>?id='+itemId+'&courseId='+courseId+'&modId='+modId+'&modResId='+modResId+'&courseactivityId='+activityId+'&userId='+userId+'&companyId=';
            componentCode += '_log';
        }
        $.get(url, function (r){
            if (r){
                $("#"+componentCode+"-result").html(r);
                app.alertWideAgain($("#"+componentCode+"-result"));
            }else{
                app.showMsg(app.msg.NETWORKERROR);
            }
        });
    }
    //查看某个人的调查投票结果
    var detail_free = true;
    function detail(userId, courseId, modResId, itemId){
        var modalId = "questionairedetail";
        var ajaxUrl = "<?= Yii::$app->urlManager->createUrl('teacher/questionaire-result')?>";
        ajaxUrl = urlreplace(ajaxUrl, 'courseId', courseId);
        ajaxUrl = urlreplace(ajaxUrl, 'modResId', modResId);
        ajaxUrl = urlreplace(ajaxUrl, 'itemId', itemId);
        ajaxUrl = urlreplace(ajaxUrl, 'userId', userId);
        if(detail_free)
        {
            detail_free = false;
            app.get(ajaxUrl, function (r)
            {
                if(r)
                {
                    app.alertWideAgain($("#" + modalId).html(r));
                }
                detail_free = true;
            });
        }
    }
    function detailhomework(userId,courseId,modResId,itemId){
        var modalId = "courseware";
        var ajaxUrl = "<?= Yii::$app->urlManager->createUrl('teacher/homework-player')?>";
        ajaxUrl = urlreplace(ajaxUrl, 'courseId', courseId);
        ajaxUrl = urlreplace(ajaxUrl, 'modResId', modResId);
        ajaxUrl = urlreplace(ajaxUrl, 'itemId', itemId);
        ajaxUrl = urlreplace(ajaxUrl, 'userId',userId);
        if(detail_free)
        {
            detail_free = false;
            app.get(ajaxUrl, function (r)
            {
                if(r)
                {
                    app.alertWideAgain($("#" + modalId).html(r));
                }
                detail_free = true;
            });
        }
    }

</script>