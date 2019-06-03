<?php
use yii\helpers\Url;
use yii\helpers\Html;
use components\widgets\TBreadcrumbs;
use components\widgets\TUploadifive;
use yii\web\JsExpression;
use yii\widgets\ActiveForm;
use common\models\learning\LnCourse;
$isCourseType = $params['isCourseType'];
?>
<style>
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
    #queue{padding:0 25px}
    #queue a.close{display:none}

</style>
<?= html::cssFile("/components/kindeditor/themes/default/default.css")?>
<?= html::jsFile('/components/kindeditor/kindeditor.js')?>
<div class="header">
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    <h4 class="modal-title" id="compnenttitle"><?= Yii::t('common', '{pop}_ln_component',['pop'=>Yii::t('frontend','homework')]) ?></h4>
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
                                            <input id="hwtitle" class="form-control pull-left" type="text" data-mode="COMMON" data-condition="required" data-alert="<?= Yii::t('common', 'audience_title_not_empty') ?>"  placeholder="<?= Yii::t('frontend', 'input_{value}',['value'=>Yii::t('frontend','homework_name')]) ?>" value="<?=$result['title']?>">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12 col-sm-12">
                                    <div class="form-group form-group-sm">
                                        <label class="col-sm-3 control-label"><?= Yii::t('frontend', 'homework_description') ?></label>
                                        <div class="col-sm-9">
                                            <textarea id="hwcontent"  placeholder='<?= Yii::t('frontend', 'input_{value}',['value'=>Yii::t('frontend','homework_need')]) ?>'><?=$result['requirement']?></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div id="queue"></div>

                            <div id="filelist">
                                <?php if(!empty($homeworkfile)){?>
                                    <input type="hidden" value="<? echo count($homeworkfile)+1;?>" id="filenumber">

                                    <?php foreach($homeworkfile as $k=>$v){?>
                                        <div class="row">
                                            <div class="col-md-12 col-sm-12">
                                                <div class="form-group form-group-sm">
                                                    <label class="col-sm-3 control-label"><?= Yii::t('frontend', 'enclosure') ?><span name='number'><?=$k+1?></span>:</label>
                                                    <div class="col-sm-9">
                                                        <span><?=$v->file_name?></span>
                                                        <a href="javascript:;" class="remove_file" style="margin-left: 20px;">&times;</a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <input type="hidden" value="<?=$v->kid?>" name="fileids" id="<?=$v->kid?>">
                                    <?php }?>
                                <?php }else{?>
                                    <input type="hidden" value="1" id="filenumber">
                                <? }?>
                            </div>
                            <div class="row">
                                <div class="col-md-12 col-sm-12">
                                    <div class=" form-group form-group-sm">
                                        <label class="col-sm-3 control-label"><?= Yii::t('frontend', 'enclosure') ?></label>
                                        <div class="col-sm-9">
                                            <div class="form-control  pull-left" id="queue_list" style="width:75%;color: #9C9C9C;"></div>
                                            <input type="file" id="uploadScorm"  class="btn btn-default btn-sm pull-right" style="width:20%">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <h5><?= Yii::t('frontend', 'requirement') ?></h5>
                            <hr>
                            <div class="infoBlock">
                                <div class="row">
                                    <div class="col-md-12 col-sm-12">
                                        <div class="form-group form-group-sm">
                                            <label class="col-sm-3 control-label"><?= Yii::t('frontend', 'end_time') ?></label>
                                            <div class="col-sm-9">
                                                <input id="hwendline" class="form-control pull-left" data-full="1" data-hms="20:00:00" type="text" data-mode="COMMON" data-condition="required" data-alert="<?= Yii::t('frontend', '{value}_not_null',['value'=>Yii::t('common','time')]) ?>" readonly data-type="rili"  placeholder="xxxx-xx-xx" value="<?php if(!empty($result['finish_before_at'])) echo date('Y-m-d H:i:s',$result['finish_before_at'])?>">
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
        <input type="hidden" value="<?=$result['kid']?>" id="kid">
    </form>
    <div class="c"></div>
</div>
<div class="actions">
    <?= Html::button(Yii::t('common', 'save'), ['id'=>'saveBtn','class'=>'btn btn-primary'])?>
</div>
<script>

    var addhw = "<?=Yii::$app->urlManager->createUrl(['resource/addhomework'])?>";
    var icon = '<?=$component->icon?>';
    var validationFHW =  app.creatFormValidation($("#homeworkformlist"));
    var no = $('#filenumber').val();

    app.genCalendar();
    /*移出一行数据*/
    $("#addModal").on('click', ".remove_file", function(){
        $('#queue_list').html('');
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
    $("#saveBtn").unbind("click").click(function() {
        var obj = document.getElementsByName("fileids");
        var fileids = "";
        for (i = 0; i < obj.length; i++) {
            fileids += obj[i].id + ",";
        }
        var type = $("#addModal").attr('data-type');
        var code = $("#addModal").attr('data-code');
        var kid = $('#kid').val();
        var componentId = $("#addModal").attr('data-componentid');
        var item = $("#addModal").attr('data-id');
        if (code == 'homework') {
            var formtitle = $('#hwtitle').val();
            var arr = [];
            arr['title'] = formtitle;
            if ($.trim(arr['title']) == '') {
                validationFHW.showAlert($("#hwtitle"), "<?= Yii::t('frontend', '{value}_not_null',['value'=>Yii::t('frontend','homework_name')]) ?>");
                return false;
            }
            arr['content'] = editor.text();
            if ($.trim(arr['content']) == '') {
                app.showMsg('<?= Yii::t('frontend', '{value}_not_null',['value'=>Yii::t('frontend','question_content')]) ?>');
                return false;

                //    validationFHW.showAlert($("#hwcontent"), "内容不能为空");
           //     return false;
            }
            arr['endline'] = $('#hwendline').val();
            if ($.trim(arr['endline']) == '') {
                validationFHW.showAlert($("#hwendline"), "<?= Yii::t('frontend', '{value}_not_null',['value'=>Yii::t('frontend','end_time')]) ?>");
                return false;
            }
            
            var endDate=new Date(arr['endline']);
            var nowDate=new Date();
            if(nowDate>endDate)
            {
                validationFHW.showAlert($("#hwendline"), "<?= Yii::t('frontend', 'start_time_beyond_end_time') ?>");
                return false;
            }
            
            arr['modle'] = $("input[name=modle]:checked").val();
            var time = 0;
            time++;
            $.post(addhw, {
                title: app.clean(arr['title']),
                content: app.clean(editor.html()),
                endline: arr['endline'],
                hwmode: arr['modle'],
                component_id: componentId,
                sequence_number: item,
                domain_id: '<?=$params['domain_id']?>',
                component_code: type,
                kid: kid,
                fileids: fileids
            }, function (data) {
                if (data != false) {
                    var parentMod = $("li[data-id='" + $("#addModal").attr('data-li') + "']").parent();
                    var li = parentMod.find(".componentSelected").length;
                    if (parentMod.find("#ware_" + data).length > 0) {
                        parentMod.find("#ware_" + data).find(".component-tbody").html(icon + '&nbsp;' + formtitle);
                    } else {
                        var html2 = "<a href=\"javascript:;\" class=\"pull-left component-tbody\" onclick=\"loadModalFormData('addModal',\'<?=Url::toRoute([$component->action_url])?>?component_id=<?=$params['component_id']?>&sequence_number=<?=$params['sequence_number']?>&domain_id=<?=$params['domain_id']?>&component_code=<?=$params['component_code']?>&id=" + data + "',this,'" + type + "','" + code + "','0');\">";
                        html2 += icon + '&nbsp;' + formtitle;
                        html2 += "</a>";
                        html2 += "<div class=\"addAction pull-right\">";
                        html2 += "<a class=\"glyphicon glyphicon-remove del\" title=\"<?= Yii::t('frontend', 'delete_button') ?>\" href=\"javascript:;\"></a>"
                        <?php 
                        if ($isCourseType == LnCourse::COURSE_TYPE_ONLINE){
                        ?>
                        html2 += '<a href="###" class="del_config" onclick="loadModalFormData(\'addModal\',\'/resource/component/config.html?component_id=<?=$params['component_id']?>&sequence_number=<?=$params['sequence_number']?>&domain_id=<?=$params['domain_id']?>&component_code=<?=$params['component_code']?>&id='+data+'&title='+encodeURIComponent(app.clean(formtitle))+'\',this,\''+type+'\',\''+code+'\',\'0\');"><?= Yii::t('frontend', 'configuration') ?></a>';
                        <?php
                        //}else if ($isCourseType == LnCourse::COURSE_TYPE_FACETOFACE && in_array($params['component_code'], $is_setting_component)){
                        }else if ($isCourseType == LnCourse::COURSE_TYPE_FACETOFACE){
                        ?>
                        html2 += '<a href="###" class="del_config" onclick="loadModalFormData(\'addModal\',\'/resource/component/config.html?component_id=<?=$params['component_id']?>&sequence_number=<?=$params['sequence_number']?>&domain_id=<?=$params['domain_id']?>&component_code=<?=$params['component_code']?>&id='+data+'&title='+encodeURIComponent(app.clean(formtitle))+'\',this,\''+type+'\',\''+code+'\',\'0\');"><?= Yii::t('frontend', 'configuration') ?></a>';
                        <?php                                	
                            }
                        ?>
                        html2 += "</div>";
                        var html = '<li id="ware_'+data+'" class="component componentSelected clearfix"  data-component="<?=$params['component_code']?>">' + html2 + '<input type="hidden" class="componentid" data-modnum="<?=$params['mod_num']?>" data-restitle="'+formtitle+'" data-compnenttitle="<?=$component->title?>" data-completerule="<?=$component->complete_rule?>" data-isscore="<?=$component->is_record_score?>" name="resource[<?=$params['sequence_number']?>][' + type + '][' + code + '][' + (li + 1) + ']" value="' + data + '"></li>';
                        parentMod.append(html);
                    }
                    $("li[data-empty='" + $("#addModal").attr('data-li') + "_empty']").remove();
                    $("#addModal").attr('data-id', '').attr('data-li', '').attr('data-type', '').attr('data-code', '');
                    $('#addModal').empty();
                    app.hideAlert($("#addModal"));
                    app.showMsg('<?= Yii::t('common', 'operation_success') ?>');
                } else {
                    app.showMsg('<?= Yii::t('common', 'operation_confirm_warning_failure') ?>');
                }
            });
        }
    });
    var editor;

    KindEditor.ready(function (K) {
        editor = K.create('#hwcontent', {
            filterMode: true,
            allowFileManager: false,
            allowImageRemote : false,
            autoHeightMode : false,
            width:'100%',
            height:'180px',
            items: [
                'justifyleft', 'justifycenter', 'justifyright',
                'formatblock', 'fontsize', 'forecolor', 'hilitecolor', 'bold',
                'italic', 'underline', 'image', 'link', 'unlink'
            ],
            afterCreate: function () {
                this.sync();
                this.loadPlugin('autoheight');
            },
            afterBlur: function () {
                this.sync();
            }
        });
    });
</script>
<?=TUploadifive::widget([
    'name' => 'uploadScorm',
    'scriptinit' => 'var fileQueue = [];var no2 = 0; var num = '.count($homeworkfile).';',
    'core' => [
        'auto' => true,
        'fileID' => count($homeworkfile),
        'buttonText'=>Yii::t('common', 'upload') ,
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
                            row+='<label class=\"col-sm-3 control-label\">".Yii::t('frontend', 'enclosure')."<span name=\'number\'>'+parseInt(no)+'</span>:</label>';
                            row+='<div class=\"col-sm-9\"><span>'+title+'</span><a href=\"javascript:;\" class=\"remove_file\" fileno = \"'+no2+'\" style=\"margin-left: 20px;\">&times;</a></div></div></div></div>';
                            row+='<input type=\"hidden\" id=\"'+id+'\" name=\"fileids\" value=\"'+id+'\" /> ';
                            $(\"#filelist\").append(row);
                        }
                        no2++
                        no++;
                        num++;
                    }
                }"
        )
    ]
]);?>

