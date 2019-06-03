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
use common\models\learning\LnCourse;
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
    #playWindow{min-height: 500px}
</style>
<div class="content" id="iframe-player" data-type="doc">
    <div class="header">
        <h4 class="modal-title" id="myModalLabel"><?=Yii::t('frontend', 'sub_homework')?><i id="endline"><?=Yii::t('common', 'end_time2')?>:<?=TTimeHelper::FormatTime($result['finish_before_at'],2)?></i></h4>
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
                                        <label class="col-sm-12 control-label"><?=Yii::t('frontend', 'homework_need')?></label>
                                        <div class="col-sm-12">
                                            <lable><?=html_entity_decode($result['requirement'])?></lable>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php
                            foreach($teacherfiles as $k=>$v){
                                ?>
                                <div class="row">
                                    <div class="col-md-12 col-sm-12">
                                        <div class="form-group form-group-sm">
                                            <label class="col-sm-3 control-label"><?=Yii::t('frontend', 'enclosure')?><?=($k+1)?>:</label>
                                            <?php $downlordurl = Url::toRoute(['/resource/homework-down','id'=>$v->kid,'file_name'=>$v->file_name])?>
                                            <a href="<?=$downlordurl?>"><div class="col-sm-9"><?=$v->file_name?></div></a>
                                        </div>
                                    </div>
                                </div>
                                <?php
                            }
                            if($result['homework_mode']==1||$result['homework_mode']==2){
                                ?>
                                <div class="row">
                                    <div class="col-md-12 col-sm-12">
                                        <div class="form-group form-group-sm">
                                            <label class="col-sm-12 control-label"><?=Yii::t('frontend', 'homework_content')?></label>
                                            <div class="col-sm-12">
                                                <textarea id="answer" <?=$disabled?"disabled":""?>  placeholder="<?=Yii::t('frontend', 'input_{value}',['value'=>Yii::t('frontend','result2')])?>"><?=$homeworkresult->homework_result?></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php
                            }
                            if($result['homework_mode']==0||$result['homework_mode']==2){
                                /*if($view == 2){*/
                                    foreach($studentfiles as $k=>$v){
                                        ?>
                                        <div class="row uploaded">
                                            <div class="col-md-12 col-sm-12">
                                                <div class="form-group form-group-sm">
                                                    <label class="col-sm-3 control-label"><?=Yii::t('frontend', 'homework')?><?=Yii::t('frontend', 'enclosure')?><span name='number'><?=$k+1?></span></label>
                                                    <?php $downlordurl = Url::toRoute(['/resource/homework-down','id'=>$v->kid,'file_name'=>$v->file_name])?>
                                                    <a href="<?=$downlordurl?>" ><div class="col-sm-9"><?=$v->file_name?></div></a>
                                                </div>
                                            </div>
                                        </div>
                                        <?php
                                    }
                               /* }*/

                                if($uploadBtn){
                                    ?>
                                    <div class="row">
                                        <div class="col-md-12 col-sm-12">
                                            <div class="form-group form-group-sm">
                                                <label class="col-sm-12 control-label"><?=Yii::t('frontend', 'upload_homework')?></label>
                                                <div class="col-sm-12">
                                                    <div id="queue"></div>
                                                    <div id="filelist"></div>
                                                    <div class=" form-control  pull-left" id="queue_list"  style="width:75%;color: #9C9C9C;"></div>
                                                    <input type="file" id="uploadScorm" class="btn btn-default btn-sm pull-right" style="width:20%">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <?php
                                }
                            }
                            if($uploadBtn){
                                ?>
                                <div class="centerBtnArea">
                                    <a href="###"  class="btn btn-success centerBtn" id="resave" style="width:20%;"><?=Yii::t('common', 'submit')?></a>
                                </div>
                                <?php
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<input type="hidden" value="<?=$homeworkresult->kid?>" id="kid">
<input type="hidden" value="<?=$result['description']?>" id="description">
<div id="hidefileid" style="display: none">
    <?php if(!empty($studentfiles)){?>
        <?php foreach($studentfiles as $k=>$v){?>
            <input value="<?=$v->kid?>" name="refileids" id="<?=$v->kid?>">
        <?php }?>
    <?php }?>
</div>
<script>
    var homeworkresult = "<?=Yii::$app->urlManager->createUrl(['resource/addhomeworkresult'])?>";
    var delurl = "<?=Yii::$app->urlManager->createUrl(['resource/delhomeworkfile'])?>";
    var no=1;
    $(document).ready(function() {
        $(".infoBlock").on('click', ".remove_file", function(){
            $('#queue_list').html('');
            var fileid = $(this).parent().parent().parent().parent().next().val();
            $.post(delurl,{fileid:fileid},function(data){});
            $(this).parent().parent().parent().parent().next().remove();
            $(this).parent().parent().parent().parent().remove();
            var num = $(this).attr('fileno');
            no = 1;
            $('#uploadifive-uploadScorm-file-'+num).remove();
            $("span[name='number']").each(
                function(){
                    $(this).empty().append(no);
                    no++;
                }
            );

        });
        LoadiFramePlayer();
    });

    function LoadiFramePlayer(){
//        resizeIframe();
        miniScreen();
        diffTemp();
    }

    $("#resave").unbind("click").click(function() {
        var obj = document.getElementsByName("refileids");
        var fileids = "";
        for (i = 0; i < obj.length; i++) {
            fileids += obj[i].id + ",";
        }
        var answer=$('#answer').val();

        if ('<?=$result['homework_mode']?>' === '0' && !fileids) {
            app.showMsg('<?=Yii::t('frontend', 'upload_homework_please')?>');
            return false;
        }
        else if ('<?=$result['homework_mode']?>' === '1' && !answer) {
            app.showMsg('<?=Yii::t('frontend', 'upload_homework_please2')?>');
            $('#answer').focus();
            return false;
        }
        else if ('<?=$result['homework_mode']?>' === '2' && !fileids && !answer) {
            app.showMsg('<?=Yii::t('frontend', 'upload_homework_please3')?>');
            $('#answer').focus();
            return false;
        }

        var kid = $('#kid').val();
        var time = 0;
        time++;

        $.post(homeworkresult, {
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
            courseAttemptNumber: '<?=$maxAttempt?>',
            fileIds:fileids
        }, function (data) {
            if (data.result =='success') {
                app.showMsg('<?=Yii::t('common', 'operation_success')?>');
                $('#answer').attr('disabled','disabled');
                $('#resave').remove();
                $("input[name='mobile']").remove();
                //$('#uploadifive-uploadScorm').empty().html('上传');
                $('#uploadifive-uploadScorm').remove();
                $('#queue_list').remove();
                $("#catalog-frame").empty();
                loadCatalog();
                $('.remove_file').remove();
                scorePointEffect(data.pointResult.show_point,data.pointResult.point_name,data.pointResult.available_point);
                location.reload();
            } else {
                app.showMsg('<?=Yii::t('common', 'operation_confirm_warning_failure')?>');
            }
         });
});
</script>
<?=TUploadifive::widget([
    'name' => 'uploadScorm',
    'scriptinit' => 'var fileQueue = [];var no2 = 0;var num = '.count($studentfiles).';',
    'core' => [
        'auto' => true,
        'fileID' => count($studentfiles),
        'buttonText'=>Yii::t('common','upload'),
        'fileSizeLimit'=>'10MB',
        'uploadScript' => Yii::$app->urlManager->createUrl(['resource/courseware/save-homework-file','uploadBatch'=>$uploadBatch,'result_id'=>$homeworkresult->kid, 'type'=>1,'id'=>$id,'','course_id'=>$courseId,'course_reg_id'=>$course_reg_id,'mod_id'=>$mod_id,'mod_res_id'=>$modResId,'courseactivity_id'=>$courseactivityId,'component_id'=>$component_id,'course_complete_id'=>$courseCompleteProcessId,'res_complete_id'=>$resCompleteId, 'course_attempt_number' => $maxAttempt]),
        'onUploadComplete' => new JsExpression(
            "function(file, data) {
                    var result = JSON.parse(data);
                    if(result.result == 'Completed'){
                        var file_id = result.file_id;
                        if (file_id == null || file_id == 'null' || file_id == ''){
                            NotyWarning(\"".Yii::t('frontend', 'upload_file_failed').".\");
                            return false;
                        }else{
                            $(\".uploaded\").remove();
                            var title = result.file_name;
                            var id = result.file_id;
                           $(\"#queue_list\").html(title);

                            var row ='<div class=\"row\">';
                            row+='<div class=\"col-md-12 col-sm-12\">';
                            row+='<div class=\"form-group form-group-sm\">';
                            row+='<label class=\"col-sm-3 control-label\">".Yii::t('frontend', 'enclosure')."<span name=\'number\'>'+parseInt(no)+'</span>:</label>';
                            row+='<div class=\"col-sm-9\"><span>'+title+'</span><a href=\"javascript:;\" class=\"remove_file\" fileno=\"'+no2+'\" style=\"margin-left: 20px;\">&times;</a></div></div></div></div>';
                            row+='<input type=\"hidden\" id=\"'+id+'\" name=\"refileids\" value=\"'+id+'\" /> ';
                            $(\"#filelist\").append(row);
                        }
                        no2++;
                        no++;
                        num++;
                    }
                }"
        )
    ]
]);?>

