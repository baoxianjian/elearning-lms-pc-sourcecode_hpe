<?php
use yii\helpers\Url;
use yii\helpers\Html;
use common\models\learning\LnCourseware;
use common\models\learning\LnCourse;
$isCourseType = $params['isCourseType'];
?>
<style>
    .adds{
        color: #ff8000 !important;
        float: left;
        padding-left: 25px;
        padding-top: 15px;
        <?php  if($result['display_position'] == '0'){?>
        display: none;
        <?php }?>
    }
</style>
<div class="header">
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    <h4 class="modal-title" id="compnenttitle"><?= Yii::t('common', '{pop}_ln_component',['pop'=>'HTML']) ?></h4>
</div>
<div class="content">
    <div class="modal-body-view" id="componentList2">
        <form id="htmlformlist" class="scoreList">
            <div role="tabpanel" class="tab-pane active panel-body">
                <div class="infoBlock">
                    <div class="row">
                        <div class="col-md-12 col-sm-12">
                            <div class="form-group form-group-sm">
                                <label class="col-sm-3 control-label"><?= Yii::t('common', '{pop}_ln_component',['pop'=>'HTML']) ?></label>
                                <div class="col-sm-9">
                                    <div class="form-group">
                                        <select <?=!empty($result['kid']) ? 'disabled ' : ''?> class="form-control htmlType">
                                            <option value="2" id="type2"><?= Yii::t('frontend', 'link_mode') ?></option>
                                            <option value="1" id="type1"><?= Yii::t('frontend', 'edit_mode') ?></option>
                                            <option value="3" id="type3"><?= Yii::t('frontend', 'file_mode') ?></option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="htmlType1 hide">
                        <div class="row">
                            <div class="col-md-12 col-sm-12">
                                <div class="form-group form-group-sm">
                                    <label class="col-sm-3 control-label"><?= Yii::t('common', 'name') ?></label>
                                    <div class="col-sm-9">
                                        <input class="form-control pull-left" maxlength="250" type="text" id="htmlType1title" value="<?=$result['courseware_name']?>" data-mode="COMMON" data-condition="required" data-alert="<?= Yii::t('common', 'field_required') ?>" placeholder="<?= Yii::t('common', 'audience_title') ?>" >
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12 col-sm-12">
                                <div class="form-group form-group-sm">
                                    <label class="col-sm-3 control-label"><?= Yii::t('frontend', 'html_content') ?></label>
                                    <div class="col-sm-9">
                                        <textarea id="htmlType1content" placeholder="<?= Yii::t('frontend', 'input_content') ?>"  data-mode="COMMON" data-condition="required" data-alert="<?= Yii::t('frontend', '{value}_not_null',['value'=>Yii::t('frontend','question_content')]) ?>" ><?=$result['embed_code']?></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12 col-sm-12">
                                <div class="form-group form-group-sm">
                                    <label class="col-sm-3 control-label"><?= Yii::t('frontend', 'window') ?></label>
                                    <div class="col-sm-9">
                                        <div class="form-group field-courseservice-course_type">
                                            <select  class="form-control" id="htmlType1display">
                                                <?php if($result['courseware_type'] == LnCourseware::COURSEWARE_TYPE_EMBED_CODE){?>
                                                <?php if($result['display_position']== 1){ ?>
                                                        <option value="0" id="type1display0"><?= Yii::t('frontend', 'same_window') ?></option>
                                                        <option value="1" id="type1display1" selected><?= Yii::t('frontend', 'new_window') ?></option>
                                                <?php }elseif($result['display_position']== 0){?>
                                                        <option value="0" id="type1display0" selected><?= Yii::t('frontend', 'same_window') ?></option>
                                                        <option value="1" id="type1display1" ><?= Yii::t('frontend', 'new_window') ?></option>
                                                <?php }else{?>
                                                        <option value="0" id="type1display0"><?= Yii::t('frontend', 'same_window') ?></option>
                                                        <option value="1" id="type1display1" selected><?= Yii::t('frontend', 'new_window') ?></option>
                                                    <?php }?>
                                                <?php }else{?>
                                                    <option value="0" id="type1display0" selected><?= Yii::t('frontend', 'same_window') ?></option>
                                                    <option value="1" id="type1display1" ><?= Yii::t('frontend', 'new_window') ?></option>
                                                <?php }?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <span class="adds"><?= Yii::t('frontend', 'tip_for_app_window') ?></span>
                            </div>

                        </div>
                    </div>
                    <div class="htmlType2">
                        <div class="row">
                            <div class="col-md-12 col-sm-12">
                                <div class="form-group form-group-sm">
                                    <label class="col-sm-3 control-label"><?= Yii::t('frontend', 'url_html') ?></label>
                                    <div class="col-sm-9">
                                        <input class="form-control" maxlength="500" type="text" style="width:80%; float:left;" id="htmlType2url"  data-mode="COMMON" data-condition="required" data-alert="<?= Yii::t('frontend', '{value}_not_null',['value'=>Yii::t('frontend','web_site')]) ?>"  value="<?=$result['embed_url']?>">
                                        <a href="###" class="btn btn-sm pull-left" onclick="gettitle()"><?= Yii::t('frontend', 'sync') ?></a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12 col-sm-12">
                                <div class="form-group form-group-sm">
                                    <label class="col-sm-3 control-label"><?= Yii::t('common', 'name') ?></label>
                                    <div class="col-sm-9">
                                        <input class="form-control pull-left" maxlength="250" type="text" data-mode="COMMON" data-condition="required" data-alert="<?= Yii::t('frontend', '{value}_not_null',['value'=>Yii::t('frontend','question_title')]) ?>"  placeholder="<?= Yii::t('common', 'audience_title') ?>" id="htmlType2title" value="<?=$result['courseware_name']?>">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12 col-sm-12">
                                <div class="form-group form-group-sm">
                                    <label class="col-sm-3 control-label"><?= Yii::t('frontend', 'window') ?></label>
                                    <div class="col-sm-9">
                                        <div class="form-group field-courseservice-course_type">
                                            <select id="htmlType2display" class="form-control" >
                                                <?php if($result['courseware_type'] == LnCourseware::COURSEWARE_TYPE_URL){?>
                                                    <?php if($result['display_position']== 1){ ?>
                                                        <option value="0" id="type2display0"><?= Yii::t('frontend', 'same_window') ?></option>
                                                        <option value="1" id="type2display1" selected><?= Yii::t('frontend', 'new_window') ?></option>
                                                    <?php }elseif($result['display_position']== 0){?>
                                                        <option value="0" id="type2display0" selected><?= Yii::t('frontend', 'same_window') ?></option>
                                                        <option value="1" id="type2display1" ><?= Yii::t('frontend', 'new_window') ?></option>
                                                    <?php }else{?>
                                                        <option value="0" id="type2display0" selected><?= Yii::t('frontend', 'same_window') ?></option>
                                                        <option value="1" id="type2display1"><?= Yii::t('frontend', 'new_window') ?></option>
                                                    <?php }?>
                                                <?php }else{?>
                                                    <option value="0" id="type2display0" selected><?= Yii::t('frontend', 'same_window') ?></option>
                                                    <option value="1" id="type2display1"><?= Yii::t('frontend', 'new_window') ?></option>
                                                <?php }?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                        </div>
                    </div>
                        <div class="row">
                            <span class="adds adds2"><?= Yii::t('frontend', 'tip_for_app_window') ?></span>
                        </div>
                    </div>
                    <div id="containerType3" class="htmlType3 hide">
                        <div class="body">
                            <div class="modal-body-view" id="componentListtype3"></div>
                        </div>
                    </div>
                </div>
            </div>
            <input type="hidden" value="<?=$result['kid']?>" id="kid">
        </form>
    </div>
    <div class="c"></div>
</div>
<div class="actions">
    <?= Html::button(Yii::t('common', 'save'), ['id'=>'saveBtn1','class'=>'btn btn-primary'])?>
</div>

<script type="text/javascript">
    var addhtml = "<?=Yii::$app->urlManager->createUrl(['resource/addhtml'])?>";
    var geturl = "<?=Yii::$app->urlManager->createUrl(['resource/gettitle'])?>";
    var icon = '<?=$component->icon?>';

    $(document).ready(function() {
        $('#type1display1').unbind('click').bind('click',function(){
            $('.adds').show();
        });
        $('#type1display0').unbind('click').bind('click',function(){
            $('.adds').hide();
        });
        $('#type2display1').unbind('click').bind('click',function(){
            $('.adds2').show();
        });
        $('#type2display0').unbind('click').bind('click',function(){
            $('.adds2').hide();
        });
        update();
        $('.htmlType').change(function() {
            var typeValue = $(this).val();
            if (typeValue == 1) {
                $('.htmlType1').removeClass('hide');
                $('.htmlType2').addClass('hide');
                $('.htmlType3').addClass('hide');
                $('#containerType3').html('');
            } else if (typeValue == 2) {
                $('.htmlType1').addClass('hide');
                $('.htmlType2').removeClass('hide');
                $('.htmlType3').addClass('hide');
                $('#containerType3').html('');
            }else if(typeValue == 3){
                $('.htmlType1').addClass('hide');
                $('.htmlType2').addClass('hide');
                $.get('<?=Yii::$app->urlManager->createUrl(['resource/component/courseware'])?>?component_id=<?=$params['component_id']?>&sequence_number=<?=$params['sequence_number']?>&domain_id=<?=$data['domain_id']?>&component_code=<?=$params['component_code']?>&from=<?=!empty($params['from'])?$params['from']:''?>&isCourseType=<?=$isCourseType?>&is_copy=<?=$params['is_copy']?>&companyId=<?=$params['companyId']?>', function(html){
                    if (html){
                        $('#containerType3').html(html);
                    }else{
                        $('#containerType3').html('<p><?= Yii::t('common', 'no_data') ?>！</p>');
                    }
                });
                $('.htmlType3').removeClass('hide');
            }
        });
        var validationHTML =  app.creatFormValidation($("#htmlformlist"));
        $("#saveBtn1").on('click',function(){
            var code = $("#addModal").attr('data-code');
            var type = $("#addModal").attr('data-type');
            var kid =$('#kid').val();
            if (code == 'html'){
                if($(".htmlType").val() == 3){
                    $("#saveBtn").click();
                    return false;
                }else{
                    if($(".htmlType").val() == 1){
                        var title = $('#htmlType1title').val();
                        if($.trim(title) == ''){
                            validationHTML.showAlert($("#htmlType1title"), "<?= Yii::t('frontend', '{value}_not_null',['value'=>Yii::t('frontend','question_title')]) ?>");
                            return false;
                        }
                        var content =$('#htmlType1content').val();
                        if($.trim(content) == ''){
                            validationHTML.showAlert($("#htmlType1content"), "<?= Yii::t('frontend', '{value}_not_null',['value'=>Yii::t('frontend','question_content')]) ?>");
                            return false;
                        }
                        var display = $("#htmlType1display").find("option:selected").val();
                        var courseware_type = '<?=LnCourseware::COURSEWARE_TYPE_EMBED_CODE?>';
                        var url = '';
                    }else if($(".htmlType").val() == 2) {
                        var title = $('#htmlType2title').val();
                        if ($.trim(title) == '') {
                            validationHTML.showAlert($("#htmlType2title"), "<?= Yii::t('frontend', '{value}_not_null',['value'=>Yii::t('frontend','question_title')]) ?>");
                            return false;
                        }
                        var url = $('#htmlType2url').val();
                        if ($.trim(url) == '') {
                            validationHTML.showAlert($("#htmlType2url"), "<?= Yii::t('frontend', '{value}_not_null',['value'=>Yii::t('frontend','question_content')]) ?>");
                            return false;
                        }
                        var display = $("#htmlType2display").find("option:selected").val();
                        var courseware_type = '<?=LnCourseware::COURSEWARE_TYPE_URL?>';
                        var content = '';
                    }

                    $.post(addhtml, {
                        title: title,
                        content: content,
                        display: display,
                        url: url,
                        component_id: '<?=$params['component_id']?>',
                        sequence_number: '<?=$params['sequence_number']?>',
                        domain_id: '<?=$params['domain_id']?>',
                        component_code: '<?=$params['component_code']?>',
                        courseware_type: courseware_type,
                        kid:kid
                    },function(data){
                        var result = data.result;
                        if (result === 'success') {
                            var t_kid = data.kid;
                            var parentMod = $("li[data-id='" + $("#addModal").attr('data-li') + "']").parent();
                            var li = parentMod.find(".componentSelected").length;
                            if (parentMod.find("#ware_" + t_kid).length > 0) {
                                parentMod.find("#ware_" + t_kid).find(".component-tbody").html(icon + '&nbsp;' + title);
                            } else {
                                var html = '<li id="ware_' + t_kid + '" class="component componentSelected clearfix"  data-component="<?=$params['component_code']?>">';
                                html += '<a href="javascript:;" class="pull-left component-tbody" onclick="loadModalFormData(\'addModal\',\'<?=Url::toRoute([$component->action_url])?>?component_id=<?=$params['component_id']?>&sequence_number=<?=$params['sequence_number']?>&domain_id=<?=$params['domain_id']?>&typeno=&component_code=<?=$params['component_code']?>&id=' + t_kid + '\',this,\'' + type + '\',\'html\',\'1\');">';
                                html += icon + '&nbsp;' + title;
                                html += '</a>';
                                html += '<div class="addAction pull-right">';
                                html += '<a class="glyphicon glyphicon-remove del" title="<?= Yii::t('common', 'delete_button') ?>" href="javascript:;"></a>';
                                <?php 
                                if ($isCourseType == LnCourse::COURSE_TYPE_ONLINE){
                                ?>
                                html += '<a href="###" class="del_config" onclick="loadModalFormData(\'addModal\',\'/resource/component/config.html?component_id=<?=$params['component_id']?>&sequence_number=<?=$params['sequence_number']?>&domain_id=<?=$params['domain_id']?>&component_code=<?=$params['component_code']?>&id=' + t_kid + '&title=' + encodeURIComponent(app.clean(title)) + '\',this,\'' + type + '\',\'html\',\'1\');"><?= Yii::t('frontend', 'configuration') ?></a>';
                                <?php
                                //}else if ($isCourseType == LnCourse::COURSE_TYPE_FACETOFACE && in_array($params['component_code'], $is_setting_component)){
                                }else if ($isCourseType == LnCourse::COURSE_TYPE_FACETOFACE){
                                ?>
                                html += '<a href="###" class="del_config" onclick="loadModalFormData(\'addModal\',\'/resource/component/config.html?component_id=<?=$params['component_id']?>&sequence_number=<?=$params['sequence_number']?>&domain_id=<?=$params['domain_id']?>&component_code=<?=$params['component_code']?>&id=' + t_kid + '&title=' + encodeURIComponent(app.clean(title)) + '\',this,\'' + type + '\',\'html\',\'1\');"><?= Yii::t('frontend', 'configuration') ?></a>';
                                <?php                                	
                                }
                                ?>
                                
                                html += '</div>';
                                html += '<input type="hidden" class="componentid" data-restitle="' + title + '" data-compnenttitle="<?=$component->title?>" data-completerule="<?=$component->complete_rule?>" data-isscore="<?=$component->is_record_score?>"  name="resource[<?=$params['sequence_number']?>][coursewares][<?=$params['component_code']?>][' + (li + 1) + ']" value="' + t_kid + '">';
                                html += '</li>';
                                parentMod.append(html);
                            }
                            $("li[data-empty='" + $("#addModal").attr('data-li') + "_empty']").remove();
                            $("#addModal").attr('data-id', '').attr('data-li', '').attr('data-type', '').attr('data-code', '');
                            $('#addModal').empty();
                            app.hideAlert($("#addModal"));
                            app.showMsg('<?= Yii::t('common', 'operation_success') ?>');
                        }
                        else if (result === 'other') {
                            app.showMsg(data.message);
                        }
                        else if (result === 'failure') {
                            app.showMsg('<?= Yii::t('common', 'operation_confirm_warning_failure') ?>');
                        }
                    });
                }
            }else{

            }

        });
    });

    function gettitle(){
        var url = $('#htmlType2url').val();
        $.post(geturl,{url:url},function(data){
            $("#htmlType2title").val(data);
        });
    }
    function update(){
        <?php
        if(!empty($result['kid']) && $params['typeno'] != 3){
            if($result['courseware_type'] == LnCourseware::COURSEWARE_TYPE_EMBED_CODE){
        ?>
            $('.htmlType1').removeClass('hide');
            $('.htmlType2').addClass('hide');
            $('.htmlType3').addClass('hide');
            $('#type1').attr('selected','selected');
            $('#htmlType1display<?=$result['is_display_pc']?>').attr('selected','selected');
        <?php
            }else if($result['courseware_type'] == LnCourseware::COURSEWARE_TYPE_URL){
        ?>
            $('.htmlType1').addClass('hide');
            $('.htmlType2').removeClass('hide');
            $('.htmlType3').addClass('hide');
            $('#type2').attr('selected','selected');
            $('#htmlType2display<?=$result['is_display_pc']?>').attr('selected','selected');
        <?php
             }
         }else if(isset($params['typeno']) && $params['typeno'] == 3){
        ?>
        $('.htmlType1').addClass('hide');
        $('.htmlType2').addClass('hide');
        $('.htmlType3').removeClass('hide');
        $('#type3').attr('selected','selected');
        $('#type3').parent().attr('disabled','disabled');
        $.get('<?=Yii::$app->urlManager->createUrl(['resource/component/courseware'])?>?component_id=<?=$params['component_id']?>&sequence_number=<?=$params['sequence_number']?>&domain_id=<?=$data['domain_id']?>&component_code=<?=$params['component_code']?>&isCourseType=<?=$isCourseType?>', function(html){
            if (html){
                $('#containerType3').html(html);
            }else{
                $('#containerType3').html('<p><?=Yii::t('common','no_data')?>！</p>');
            }
        });
        <?php
         }
        ?>
    }
</script>