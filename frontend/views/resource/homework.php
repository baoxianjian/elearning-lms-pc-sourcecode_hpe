<?php
use yii\helpers\Url;
use yii\helpers\Html;
use components\widgets\TBreadcrumbs;
use components\widgets\TUploadifive;
use yii\web\JsExpression;
use yii\widgets\ActiveForm;
?>
<div class="header">
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    <h4 class="modal-title" id="compnenttitle"><?= Yii::t('frontend', 'homework_component') ?></h4>
</div>
<div class="content">
    <form id="homeworkformlist">
    <div class="courseInfo">
            <div role="tabpanel" class="tab-pane active" id="teacher_info">
                <div class=" panel-default scoreList">
                    <div class="panel-body">
                        <div class="infoBlock">
                            <div class="row">
                                <div class="col-md-12 col-sm-12">
                                    <div class="form-group form-group-sm">
                                        <label class="col-sm-3 control-label"><?= Yii::t('frontend', 'homework_name') ?></label>
                                        <div class="col-sm-9">
                                            <input id="hwtitle" class="form-control pull-left" type="text" data-mode="COMMON" data-condition="required" data-alert="<?= Yii::t('frontend', '{value}_not_null',['value'=>Yii::t('common','name')]) ?>"  placeholder="<?= Yii::t('frontend', 'input_{value}',['value'=>Yii::t('frontend','homework_name')]) ?>" value="<?=$result['title']?>">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12 col-sm-12">
                                    <div class="form-group form-group-sm">
                                        <label class="col-sm-3 control-label"><?= Yii::t('frontend', 'homework_description') ?></label>
                                        <div class="col-sm-9">
                                            <textarea id="hwcontent" data-mode="COMMON" data-condition="required" data-alert="<?= Yii::t('frontend', '{value}_not_null',['value'=>Yii::t('frontend','requirement')]) ?>" placeholder='<?= Yii::t('frontend', 'input_{value}',['value'=>Yii::t('frontend','homework_need')]) ?>'><?=$result['requirement']?></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div id="queue" style="display: none">
                                <?php if(!empty($homeworkfile)){?>
                                    <?php foreach($homeworkfile as $k=>$v){?>
                                        <div class="uploadifive-queue-item complete" id="uploadifive-uploadScorm-file-<?=$k?>">
                                            <a href="#" class="close">X</a>
                                            <div>
                                                <span class="filename"><?=$v->file_name?></span>
                                                <span class="fileinfo"> - Completed</span>
                                            </div>
                                            <div class="progress" style="display: none;">
                                                <div class="progress-bar" style="width: 100%;"></div>
                                            </div>
                                        </div>
                                    <?php }?>
                                <?php }?>

                            </div>

                            <div id="filelist">
                                <?php if(!empty($homeworkfile)){?>
                                    <?php foreach($homeworkfile as $k=>$v){?>
                                        <div class="row">
                                            <div class="col-md-12 col-sm-12">
                                                <div class="form-group form-group-sm">
                                                    <label class="col-sm-3 control-label"><?= Yii::t('frontend', 'enclosure') ?><?=($k+1)?>:</label>
                                                    <div class="col-sm-9"><?=$v->file_name?></div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php }?>
                                <?php }?>
                            </div>
                            <div class="row">
                                <div class="col-md-12 col-sm-12">
                                    <div class=" form-group form-group-sm">
                                        <label class="col-sm-3 control-label"><?= Yii::t('frontend', 'enclosure') ?></label>
                                        <div class="col-sm-9">
                                            <?php
                                            if (!empty($tempArr)){
                                                foreach ($tempArr as $key=>$val){
                                                    echo '<input type="hidden" name="file_id[]" id="file_'.$key.'" value="'.$val['file_id'].'">';
                                                    echo '<input type="hidden" name="file_name['.$val['file_id'].']" id="file_name_'.$key.'" value="'.$val['file_name'].'">';
                                                }
                                            }
                                            ?>
                                            <div class="form-control  pull-left"id="queue_list"  style="width:75%"></div>
                                            <input type="file" id="uploadScorm"  class="btn btn-default btn-sm pull-right" style="width:20%">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <h5>要求</h5>
                            <hr>
                            <div class="infoBlock">
                                <div class="row">
                                    <div class="col-md-12 col-sm-12">
                                        <div class="form-group form-group-sm">
                                            <label class="col-sm-3 control-label"><?= Yii::t('common', 'end_time2') ?></label>
                                            <div class="col-sm-9">
                                                <input id="hwendline" class="form-control pull-left" type="text" data-mode="COMMON" data-condition="required" data-alert="<?= Yii::t('frontend', '{value}_not_null',['value'=>Yii::t('common','time')]) ?>" readonly data-type="rili"  placeholder="xxxx-xx-xx" value="<?php if(!empty($result['finish_before_at'])) echo date('Y-m-d',$result['finish_before_at'])?>">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12 col-sm-12">
                                        <div class="form-group form-group-sm">
                                            <label class="col-sm-3 control-label"><?= Yii::t('frontend', 'mode') ?></label>
                                            <div class="col-sm-9">
                                                <div class="btn-group" data-toggle="buttons">
                                                    <label style="margin-right:10px;">
                                                        <input type="radio" name="modle" <? if($result['homework_mode']==0)echo'checked = "checked" ';?> value="0"> <?= Yii::t('frontend', 'enclosure_content') ?>
                                                    </label>
                                                    <label style="margin-right:10px;">
                                                        <input type="radio" name="modle" <? if($result['homework_mode']==1)echo'checked = "checked" ';?> value="1"> <?= Yii::t('frontend', 'online_input') ?>
                                                    </label>
                                                    <label style="margin-right:10px;">
                                                        <input type="radio" name="modle" <? if($result['homework_mode']==2 || $result['homework_mode'] == '')echo'checked = "checked" ';?> value="2"> <?= Yii::t('frontend', 'optional') ?>
                                                    </label>
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
        </div>
    <input type="hidden" value="<?=$courseactivityid?>" id="kid">
    <div id="hidefileid" style="display: none">
        <?php if(!empty($homeworkfile)){?>
            <?php foreach($homeworkfile as $k=>$v){?>
                <input value="<?=$v->kid?>" name="fileids" id="<?=$v->kid?>">
            <?php }?>
        <?php }?>
    </div>
    </form>
    <div class="c"></div>
</div>
<div class="actions">
    <?= Html::button(Yii::t('common', 'save'), ['id'=>'saveBtn','class'=>'btn btn-primary'])?>
</div>
<script>
    var component = <?=urldecode(json_encode($lncomponent_title))?>;
    var updatehomework = "<?=Yii::$app->urlManager->createUrl(['resource/updatehomework'])?>";
    var addhw = "<?=Yii::$app->urlManager->createUrl(['resource/addhomework'])?>";
    $(document).ready(function() {
        var validation =  app.creatFormValidation($("#homeworkformlist"));
    });
    app.genCalendar();
    /*移出一行数据*/
    function removeRow(id, num){
        NotyConfirm('<?= Yii::t('common', 'operation_confirm') ?>',  function(data){
            var file_name = $("#row_"+id+'_'+num).find('td').eq(0).text();
            $(".uploadifive-queue-item").each(function(){
                if ($(this).find('.filename').html() == file_name) {
                    $(this).remove();
                }
            });
            $("#row_"+id+'_'+num).remove();
            $("#file_"+num).remove();
            $("#file_name_"+num).remove();
            if ($("#file_list > tr").length == 1){
                $("#file_list > tr").eq(0).show();
                $("#nextButton").attr("disabled",true);
            }
        });
    };
    $("#saveBtn").unbind("click").click(function() {
        var obj = document.getElementsByName("fileids");
        var fileids = "";
        for (i = 0; i < obj.length; i++) {
            fileids += obj[i].id + ",";
        }
        var url = null;
        var type = $("#addModal").attr('data-type');
        var code = $("#addModal").attr('data-code');
        var kid = $('#kid').val();
        var componentId = $("#addModal").attr('data-componentid');
        var item = $("#addModal").attr('data-id');
        if (code == 'homework') {
            url = updatehomework;
            var form = $('#formnow').val();
            var formtitle = $('#hwtitle').val();

            var arr = new Array();
            arr['title'] = $('#hwtitle').val();
            if (arr['title'] == '') {
                validation.showAlert($("#title"), "<?= Yii::t('frontend', '{value}_not_null',['value'=>Yii::t('common','investigation_title')])?>");
                return false;
            }
            arr['content'] = $('#hwcontent').val();
            if (arr['content'] == '') {
                validation.showAlert($("#hwcontent"), "<?= Yii::t('frontend', '{value}_not_null',['value'=>Yii::t('common','question_content')])?>");
                return false;
            }
            arr['endline'] = $('#hwendline').val();
            if (arr['endline'] == '') {
                validation.showAlert($("#hwendline"), "<?= Yii::t('frontend', '{value}_not_null',['value'=>Yii::t('common','end_time2')])?>");
                return false;
            }
            arr['modle'] = $("input[name=modle]:checked").val();
            var time = 0;
            time++;
            $.post(addhw, {
                title: arr['title'],
                content: arr['content'],
                endline: arr['endline'],
                hwmode: arr['modle'],
                component_id: componentId,
                sequence_number: item,
                domain_id: domain_id,
                component_code: type,
                kid: kid,
                fileids: fileids
            }, function (data) {
                if (data != false) {
                    var parentMod = $("li[data-id='" + $("#addModal").attr('data-li') + "']").parent();
                    var li = parentMod.find(".componentSelected").length;
                    if (parentMod.find("#ware_" + data).length > 0) {
                        parentMod.find("#ware_" + data).find(".component-tbody").html(icon + '&nbsp;' + title);
                    } else {
                        var html2 = "<a onclick=\"loadModalFormData('addModal','" + url + "?component_id=" + componentId + "&sequence_number=" + item + "&domain_id=" + domain_id + "&component_code=" + type + "&id=" + data + "',this,'" + type + "','" + code + "');\" href=\"javascript:;\">";
                        html2 += "<i style='color:#008333' class='icon iconfont'>ဘ</i>";
                        html2 += formtitle;
                        html2 += "</a>";
                        html2 += "<div class=\"addAction pull-right\">";
                        html2 += "<a class=\"glyphicon glyphicon-remove del\" title=\"<?= Yii::t('frontend', 'brief') ?><?= Yii::t('common', 'delete_button') ?>\" href=\"javascript:;\"></a>"
                        html2 += "</div>";
                        var html = '<li id="ware" class="component componentSelected">' + html2 + '<input type="hidden" class="componentid" name="resource[' + type + '][' + code + '][' + (li + 1) + ']" value="' + data + '"></li>';
                        $("li[data-empty='" + $("#addModal").attr('data-li') + "_empty']").parent().append(html);
                        $("li[data-empty='" + $("#addModal").attr('data-li') + "_empty']").remove();
                        $("#addModal").attr('data-id', '').attr('data-li', '').attr('data-type', '').attr('data-code', '');
                        $('#addModal').empty();
                        app.hideAlert($("#addModal"));
                        app.showMsg('<?= Yii::t('common', 'operation_success') ?>');
                    }
                } else {
                    app.showMsg('<?= Yii::t('frontend', 'operation_confirm_warning_failure') ?>');
                }
            });
        }
    });
</script>
<?=TUploadifive::widget([
    'name' => 'uploadScorm',
    'scriptinit' => 'var fileQueue = [];var num = '.count($tempArr).';',
    'core' => [
        'auto' => true,
        'fileID' => count($tempArr),
        'buttonText'=>Yii::t('frontend', 'upload'),
        'uploadScript' => Yii::$app->urlManager->createUrl(['resource/courseware/save-homework-file','uploadBatch'=>$uploadBatch]),
        'onUploadComplete' => new JsExpression(
            "function(file, data) {
                    var result = JSON.parse(data);
                    if(result.result == 'Completed'){
                        var file_id = result.file_id;
                        if (file_id == null || file_id == 'null' || file_id == ''){
                            NotyWarning(\"".Yii::t('frontend', 'upload_file_failed').".\");
                            return false;
                        }else{
                            var title = result.file_name;
                            var id = result.file_id;
                           $(\"#queue_list\").html(title);

                            var row ='<div class=\"row\">';
                                row+='<div class=\"col-md-12 col-sm-12\">';
                                row+='<div class=\"form-group form-group-sm\">';
                                row+='<label class=\"col-sm-3 control-label\">".Yii::t('frontend', 'enclosure')."'+(num+1)+':</label>';
                                row+='<div  class=\"col-sm-9\">'+title+'</div></div></div></div>';
                            $(\"#filelist\").append(row);
                            var idrow ='<input id=\"'+id+'\" name=\"fileids\" value=\"'+id+'\" /> ';
                            $(\"#hidefileid\").append(idrow);
                        }
                        num++;
                    }
                }"
        )
    ]
]);?>

