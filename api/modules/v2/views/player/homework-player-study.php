<?php
/**
 * Created by PhpStorm.
 * User: tangming
 * Date: 7/9/2015
 * Time: 10:11 AM
 */
use yii\helpers\Url;
use yii\helpers\Html;
use components\widgets\TBreadcrumbs;
use components\widgets\TUploadifive;
use common\helpers\TTimeHelper;
use yii\web\JsExpression;
use yii\widgets\ActiveForm;
?>
<style>
    #endline{
        float: right !important;
    }
    .uploadifive-button {
        background-color: #00993a !important;
        background-image: none !important;
        border-radius: 4px !important;
        border: 1px solid transparent !important;
    }
    .uploadifive-button:hover {
        background-color: #449d44 !important;
        border-color: #398439 !important;
        background-image:none !important;
    }
    #queue a.close{display:none}

</style>
<div class="content">
    <div class="header">
        <h4 class="modal-title" id="myModalLabel">提交作业<i id="endline">截止日期:<?=TTimeHelper::FormatTime($result['finish_before_at'],2)?></i></h4>
    </div>
    <div class="modal-body">
        <div class="courseInfo">
            <div role="tabpanel" class="tab-pane active" id="teacher_info">
                <div class=" panel-default scoreList">
                    <div class="panel-body">
                        <div class="infoBlock">
                                <div class="row">
                                    <div class="col-md-12 col-sm-12">
                                        <div class="form-group form-group-sm">
                                            <label class="col-sm-12 control-label">作业要求</label>
                                            <div class="col-sm-12">
                                                <lable><?=html_entity_decode($result['requirement'])?></lable>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php foreach($teacherfiles as $k=>$v){?>
                                    <div class="row">
                                        <div class="col-md-12 col-sm-12">
                                            <div class="form-group form-group-sm">
                                                <label class="col-sm-3 control-label">附件<?=($k+1)?>:</label>
                                                <?php $downlordurl = Url::toRoute(['resource/homework-down','system_key'=>$system_key,'access_token'=>$access_token,'id'=>$v->kid,'file_name'=>$v->file_name])?>
                                                <a href="<?=$downlordurl?>"><div class="col-sm-9"><?=$v->file_name?></div></a>
                                            </div>
                                        </div>
                                    </div>
                                <?php }?>
                            <?php if($result['homework_mode']==1||$result['homework_mode']==2){?>
                                <div class="row">
                                    <div class="col-md-12 col-sm-12">
                                        <div class="form-group form-group-sm">
                                            <label class="col-sm-12 control-label">作业内容</label>
                                            <div class="col-sm-12">
                                                <textarea id="answer" <? if(!empty($homeworkresult->homework_result) || ($result['homework_mode']==2 && $studentfiles)){ echo "disabled";}?>  placeholder="填写答案"><?=$homeworkresult->homework_result?></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php }?>
                            <?php if($result['homework_mode']==0||$result['homework_mode']==2){?>
                                <?php if($view == 2){?>
                                    <?php foreach($studentfiles as $k=>$v){?>
                                        <div class="row">
                                            <div class="col-md-12 col-sm-12">
                                                <div class="form-group form-group-sm">
                                                    <label class="col-sm-3 control-label">作业附件<?=($k+1)?>:</label>
                                                    <?php $downlordurl = Url::toRoute(['resource/homework-down','system_key'=>$system_key,'access_token'=>$access_token,'id'=>$v->kid,'file_name'=>$v->file_name])?>
                                                    <a href="<?=$downlordurl?>" ><div class="col-sm-9"><?=$v->file_name?></div></a>
                                                </div>
                                            </div>
                                        </div>
                                    <?php }?>
                                <?php }?>
                            <?php if($view == 1||$view == 0){?>
                                <div class="row">
                                    <div class="col-md-12 col-sm-12">
                                        <div class="form-group form-group-sm">
                                            <label class="col-sm-12 control-label">上传作业</label>
                                            <div class="col-sm-12">
                                                <div id="queue"></div>
                                                <div id="filelist"></div>
                                                <div class=" form-control  pull-left" id="queue_list"  style="width:75%"></div>
                                                <input type="file" id="uploadScorm" class="btn btn-default btn-sm pull-right" style="width:20%">                                                </div>
                                        </div>
                                    </div>
                                </div>
                                <?php }?>
                            <?php }?>
                            <?php if($view == 1||$view == 0){?>
                                <div class="centerBtnArea">
                                    <a href="###"  class="btn btn-success centerBtn" id="resave" style="width:20%;">提交</a>
                                </div>
                            <?php }?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
</div>
<input type="hidden" value="<?=$resultid?>" id="kid">
<input type="hidden" value="<?=$result['description']?>" id="description">
<div id="hidefileid" style="display: none">
    <?php if(!empty($studentfiles)){?>
        <?php foreach($studentfiles as $k=>$v){?>
            <input value="<?=$v->kid?>" name="refileids" id="<?=$v->kid?>">
        <?php }?>
    <?php }?>
</div>
<?=html::cssFile('/static/mobile/assets/bootstrap/css/bootstrap.min.css')?>
<?= html::jsFile('/static/app/js/fastclick.js') ?>
<?= html::jsFile('/static/app/js/jquery.min.js') ?>
<?= html::jsFile('/static/app/js/main.js')?>
<?= html::jsFile('/static/common/js/common.js')?>
<link href="/static/frontend/css/elearning.1.css" rel="stylesheet">
<link href="/static/frontend/css/elearning.2.css" rel="stylesheet">
<link href="/static/frontend/css/elearning.3.css" rel="stylesheet">
<script>
    var app = {
        showMsg : function(msg) {
            alert(msg);
        }
    };
</script>
<script>
    var homeworkresult = "<?=Yii::$app->urlManager->createUrl(['../../resource/addhomeworkresult.html','company_id'=>$company_id,'userId'=>$user_id])?>";
    var delurl = "<?=Yii::$app->urlManager->createUrl(['../../resource/delhomeworkfile.html'])?>";

    $(document).ready(function() {
        $(".infoBlock").on('click', ".remove_file", function(){
            $('#queue_list').html('');
            var fileid = $(this).parent().parent().parent().parent().next().val();
            $.post(delurl,{fileid:fileid},function(data){});
            $(this).parent().parent().parent().parent().next().remove();
            $(this).parent().parent().parent().parent().remove();
        });
        LoadiFramePlayer();
    });

    function change_size(zoom)
    {
        //此方法必须存在，以便play.php调用
        var iframeWindow = $("#iframe-player");
        if (zoom) {
            //alert(zoom);
            iframeWindow.height(750);
        }
        else
        {
            if (navigator.userAgent.indexOf('MSIE') >= 0){
                //alert('你是使用IE')
            }
            else {
                iframeWindow.height(500);
            }
        }
    }

    function LoadiFramePlayer(){
//        alert(compnentCode);
        //       var playZoom = getCookie("play_zoom");
        //       if (playZoom == "0")
        //       {
        //           change_size(true);
        //      }
    }
    $("#resave").unbind("click").click(function() {
        var obj = document.getElementsByName("refileids");
        var fileids = "";
        for (i = 0; i < obj.length; i++) {
            fileids += obj[i].id + ",";
        }
        var answer=$('#answer').val();

        if ('<?=$result['homework_mode']?>' === '0' && !fileids) {
            app.showMsg('请上传作业');
            return false;
        }
        else if ('<?=$result['homework_mode']?>' === '1' && !answer) {
            app.showMsg('请填写答案');
            $('#answer').focus();
            return false;
        }
        else if ('<?=$result['homework_mode']?>' === '2' && !fileids && !answer) {
            app.showMsg('请填写答案或上传作业');
            $('#answer').focus();
            return false;
        }

        var url = null;

        url = homeworkresult;
        var kid = $('#kid').val();
        var time = 0;
        time++;

        $.post(url, {
            hwkid:"<?=$id?>",
            kid: kid,
            result: answer,
            description: $('#description').val(),
            course_id: "<?=$courseId?>",
            course_reg_id: "<?=$course_reg_id?>",
            mod_id: "<?=$mod_id?>",
            mod_res_id: "<?=$modResId?>",
            courseactivity_id: "<?=$courseactivityId?>",
            component_id: "<?=$component_id?>",
            courseCompleteFinalId:"<?=$courseCompleteFinalId?>",
            courseCompleteProcessId:"<?=$courseCompleteProcessId?>",
            resCompleteId:"<?=$resCompleteId?>",
            fileIds:fileids,
            _csrf: "<?php echo Yii::$app->request->getCsrfToken()?>"
        }, function (data) {
            if (data != false) {

                app.showMsg('<?=Yii::t('common', 'operation_success')?>');
                $('#answer').attr('disabled','disabled');
                $('#resave').remove();
                $("input[name='mobile']").remove();
                //$('#uploadifive-uploadScorm').empty().html('上传');
                $('#uploadifive-uploadScorm').remove();;
                $('#queue_list').remove();
                $("#catalog-frame").empty();
                
                loadCatalog();
                
                $('.remove_file').remove();
            } else {
                app.showMsg('<?=Yii::t('common', 'operation_confirm_warning_failure')?>');
            }
         });
});
</script>
<?=TUploadifive::widget([
    'name' => 'uploadScorm',
    'scriptinit' => 'var fileQueue = [];var num = '.count($studentfiles).';',
    'core' => [
        'auto' => true,
        'fileID' => count($studentfiles),
        'buttonText'=>'上传',
        'uploadScript' => Yii::$app->urlManager->createUrl(['../../resource/courseware/save-homework-file.html','user_id'=>$user_id,'company_id'=>$company_id,'uploadBatch'=>$uploadBatch,'type'=>1,'id'=>$id,'','course_id'=>$courseId,'course_reg_id'=>$course_reg_id,'mod_id'=>$mod_id,'mod_res_id'=>$modResId,'courseactivity_id'=>$courseactivityId,'component_id'=>$component_id,'course_complete_id'=>$courseCompleteProcessId,'res_complete_id'=>$resCompleteId]),
        'onUploadComplete' => new JsExpression(
            "function(file, data) {
                    var result = JSON.parse(data);
                    if(result.result == 'Completed'){
                        var file_id = result.file_id;
                        if (file_id == null || file_id == 'null' || file_id == ''){
                            NotyWarning(\"文件上传失败.\");
                            return false;
                        }else{
                            var title = result.file_name;
                            var id = result.file_id;
                           $(\"#queue_list\").html(title);

                            var row ='<div class=\"row\">';
                            row+='<div class=\"col-md-12 col-sm-12\">';
                            row+='<div class=\"form-group form-group-sm\">';
                            row+='<label class=\"col-sm-3 control-label\">附件:</label>';
                            row+='<div class=\"col-sm-9\"><span>'+title+'</span><a href=\"javascript:;\" class=\"remove_file\" style=\"margin-left: 20px;\">&times;</a></div></div></div></div>';
                            row+='<input type=\"hidden\" id=\"'+id+'\" name=\"refileids\" value=\"'+id+'\" /> ';
                            $(\"#filelist\").append(row);
                        }
                        num++;
                    }
                }"
        )
    ]
]);?>

