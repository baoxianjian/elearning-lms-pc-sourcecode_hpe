<?php
use yii\helpers\Html;

?><form id="htmlformlist">
            <div class="body">
                <div class="courseInfo">
                    <div role="tabpanel" class="tab-pane active" id="teacher_info">
                        <div class=" panel-default scoreList">
                            <div class="panel-body">
                                <div class="infoBlock">
                                    <div class="row">
                                        <div class="col-md-12 col-sm-12">
                                            <div class="form-group form-group-sm">
                                                <label class="col-sm-3 control-label">HTML模式</label>
                                                <div class="col-sm-9">
                                                    <div class="form-group">
                                                        <select <?php if(!empty($result->kid))echo 'disabled '?> class="form-control htmlType">
                                                            <option value="1" id="type1" onclick="changeform(1);">编辑模式</option>
                                                            <option value="2" id="type2" onclick="changeform(2);">链接模式</option>
                                                            <option value="3" id="type3" onclick="changeform(3);">文件模式</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="htmlType1">
                                        <div class="row">
                                            <div class="col-md-12 col-sm-12">
                                                <div class="form-group form-group-sm">
                                                    <label class="col-sm-3 control-label">名称</label>
                                                    <div class="col-sm-9">
                                                        <input class="form-control pull-left" type="text" id="htmlType1title" value="<?php if($result['courseware_type'] ==2)echo $result['courseware_name']?>" data-mode="COMMON" data-condition="required" data-alert="<?= Yii::t('common', 'field_required') ?>" placeholder="<?=Yii::t('frontend', 'input_{value}',['value'=>Yii::t('frontend', 'question_title')])?>" >
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-12 col-sm-12">
                                                <div class="form-group form-group-sm">
                                                    <label class="col-sm-3 control-label">HTML全文</label>
                                                    <div class="col-sm-9">
                                                        <textarea id="htmlType1content" placeholder="<?=Yii::t('frontend', 'input_{value}',['value'=>Yii::t('frontend', 'question_content')])?>"  data-mode="COMMON" data-condition="required" data-alert="<?=Yii::t('frontend', '{value}_not_null',['value'=>Yii::t('frontend', 'question_content')])?>" ><?php if($result['courseware_type'] ==2)echo $result['embed_code']?></textarea>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-12 col-sm-12">
                                                <div class="form-group form-group-sm">
                                                    <label class="col-sm-3 control-label">窗口</label>
                                                    <div class="col-sm-9">
                                                        <div class="form-group field-courseservice-course_type">
                                                            <select  class="form-control" id="htmlType1display">
                                                                <?php if($result['courseware_type'] ==2){?>
                                                                <?php if($result['display_position']== 1){ ?>
                                                                        <option value="1" id="type1display1" selected>新窗口</option>
                                                                        <option value="0" id="type1display0">同一窗口</option>
                                                                <?php }elseif($result['display_position']== 0){?>
                                                                        <option value="1" id="type1display1" >新窗口</option>
                                                                        <option value="0" id="type1display0" selected>同一窗口</option>
                                                                <?php }else{?>
                                                                    <option value="1" id="type1display1" selected>新窗口</option>
                                                                    <option value="0" id="type1display0">同一窗口</option>
                                                                    <?php }?>
                                                                <?php }else{?>
                                                                    <option value="1" id="type1display1" selected>新窗口</option>
                                                                    <option value="0" id="type1display0">同一窗口</option>
                                                                <?php }?>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="htmlType2 hide">
                                        <div class="row">
                                            <div class="col-md-12 col-sm-12">
                                                <div class="form-group form-group-sm">
                                                    <label class="col-sm-3 control-label">HTML地址</label>
                                                    <div class="col-sm-9">
                                                        <input class="form-control" type="text" style="width:80%; float:left;" id="htmlType2url"  data-mode="COMMON" data-condition="required" data-alert="<?=Yii::t('frontend', '{value}_not_null',['value'=>Yii::t('frontend', 'web_site')])?>"  value="<?php if($result['courseware_type'] ==1)echo $result['embed_url']?>">
                                                        <a href="###" class="btn btn-sm pull-left" onclick="gettitle()"><?= Yii::t('frontend', 'sync') ?></a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-12 col-sm-12">
                                                <div class="form-group form-group-sm">
                                                    <label class="col-sm-3 control-label">名称</label>
                                                    <div class="col-sm-9">
                                                        <input class="form-control pull-left" type="text" data-mode="COMMON" data-condition="required" data-alert="<?=Yii::t('frontend', '{value}_not_null',['value'=>Yii::t('frontend', 'question_title')])?>"  placeholder="<?=Yii::t('frontend', 'input_{value}',['value'=>Yii::t('frontend', 'question_title')])?>" id="htmlType2title" value="<?php if($result['courseware_type'] ==1)echo $result['courseware_name']?>">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-12 col-sm-12">
                                                <div class="form-group form-group-sm">
                                                    <label class="col-sm-3 control-label">窗口</label>
                                                    <div class="col-sm-9">
                                                        <div class="form-group field-courseservice-course_type">
                                                            <select id="htmlType2display" class="form-control" >
                                                                <?php if($result['courseware_type'] ==1){?>
                                                                    <?php if($result['display_position']== 1){ ?>
                                                                        <option value="1" id="type1display1" selected>新窗口</option>
                                                                        <option value="0" id="type1display0">同一窗口</option>
                                                                    <?php }elseif($result['display_position']== 0){?>
                                                                        <option value="1" id="type1display1" >新窗口</option>
                                                                        <option value="0" id="type1display0" selected>同一窗口</option>
                                                                    <?php }else{?>
                                                                        <option value="1" id="type1display1" selected>新窗口</option>
                                                                        <option value="0" id="type1display0">同一窗口</option>
                                                                        <?php }?>
                                                                <?php }else{?>
                                                                    <option value="1" id="type1display1" selected>新窗口</option>
                                                                    <option value="0" id="type1display0">同一窗口</option>
                                                                <?php }?>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div id="containerType3" class="htmlType3 hide">
                                        <div class="body">
                                            <div class="modal-body-view" id="componentListtype3"></div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12 col-sm-12 centerBtnArea">
                                            <input id="formnow" type="hidden" value="htmlType1">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
<input type="hidden" value="<?=$coursewareid?>" id="kid">
    </form>

            <script type="text/javascript">
                var addhtml = "<?=Yii::$app->urlManager->createUrl(['resource/addhtml'])?>";
                var geturl = "<?=Yii::$app->urlManager->createUrl(['resource/gettitle'])?>";
                $('.htmlType').change(function() {
                    var typeValue = $(this).val()
                    if (typeValue == 1) {
                        $('#formnow').val('htmlType'+typeValue);

                        $('.htmlType1').removeClass('hide')
                        $('.htmlType2').addClass('hide')
                        $('.htmlType3').addClass('hide')
                    } else if (typeValue == 2) {
                        $('#formnow').val('htmlType'+typeValue);

                        $('.htmlType1').addClass('hide')
                        $('.htmlType2').removeClass('hide')
                        $('.htmlType3').addClass('hide')
                    }else if(typeValue == 3){
                        $('#formnow').val('htmlType'+typeValue);

                        $('.htmlType1').addClass('hide')
                        $('.htmlType2').addClass('hide')
                        var typeValue = $(this).val();
                        ajaxGet('<?=Yii::$app->urlManager->createUrl(['resource/file-html'])?>?component_id=<?=$data['component_id']?>&sequence_number=<?=$data['sequence_number']?>&domain_id=<?=$data['domain_id']?>&component_code=<?=$data['component_code']?>&courseware_type=0&entry_mode=0', 'containerType3');
                        $('.htmlType3').removeClass('hide')
                    }
                });
                function changeform(formtypeno){
                    $('#formnow').val('htmlType'+formtypeno);
                };
                function gettitle(){
                    var url = $('#htmlType2url').val();
                    $.post(geturl,{url:url},function(data){
                        $("#htmlType2title").val(data);
                    });

                };
                $("#saveNewBtn").unbind("click").click(function(){

                    var modalId = 'FormModal';
                    var url = null;
                    var type = $(this).attr('data-type');
                    var code = $(this).attr('data-code');
                    var kid =$('#kid').val();
                    if ($(this).attr('data-type') == 'coursewares'){
                        url = coursewares;
                    }else{
                        url = activity;
                    }
                    var componentId = $(this).attr('data-componentid');
                    var item = $("#saveNewBtn").attr('data-id');

                    if (code == 'html'){
                        url = updatenewhtml;
                        addcontainer = 'FormModal';
                        var form = $('#formnow').val();
                        var formtitle = $('#'+form+'title').val();

                        if(form == 'htmlType1'){
                            var arr = new Array();
                            arr['title'] = $('#htmlType1title').val();
                            if(arr['title'] == ''){
                                validation.showAlert($("#htmlType1title"), "<?=Yii::t('frontend', '{value}_not_null',['value'=>Yii::t('frontend', 'question_title')])?>");
                                return false;
                            }
                            arr['content'] =$('#htmlType1content').val();
                            if(arr['content'] == ''){
                                validation.showAlert($("#htmlType1content"), "<?=Yii::t('frontend', '{value}_not_null',['value'=>Yii::t('common', 'question_content')])?>");
                                return false;
                            }
                            arr['display'] = $("#htmlType1display").find("option:selected").val();
                            $.post(addhtml,{title:arr['title'],content:arr['content'],display:arr['display'],component_id:componentId,sequence_number:item,domain_id:domain_id,component_code:type,courseware_type:2,kid:kid},function(data){
                                if(data != false){
                                    $("#saveNewBtn").attr('data-key',data);
                                    var html2 = "<a onclick=\"loadModalFormData('"+addcontainer+"','"+url+"?component_id="+componentId+"&sequence_number="+item+"&domain_id="+domain_id+"&component_code="+type+"&coursewareid="+data+"',this,'"+type+"');\" href=\"javascript:;\">";
                                    html2 += "<i class=\"icon iconfont\" style=\"'color:#rgb(75, 122, 222);'\">မ</i>";
                                    html2 += formtitle;
                                    html2 +="</a>";
                                    html2 +="<div class=\"addAction pull-right\">";
                                    html2 +="<a class=\"glyphicon glyphicon-remove del\" title=\"<?= Yii::t('common', 'delete_button') ?>\" href=\"javascript:;\"></a>"
                                    html2 +="</div>";
                                    var html = '<li id="ware" class="component componentSelected">'+html2+'<input type="hidden" class="componentid" name="component['+$("#saveBtn").attr('data-type')+'][]" value="'+$('#saveNewBtn').attr('data-key')+'"></li>';
                                    $("li[data-empty='"+$("#saveNewBtn").attr('data-li')+"_empty']").parent().append(html);
                                    $("li[data-empty='"+$("#saveNewBtn").attr('data-li')+"_empty']").remove();
                                    $("#saveNewBtn").attr('data-id','').attr('data-li','').attr('data-type','');
                                    $('#'+modalId).find(".modal-body-view").empty();
                                    app.hideAlert($("#FormModal"));
                                    app.showMsg('<?=Yii::t('common', 'operation_success')?>');

                                }else{
                                    app.showMsg('<?=Yii::t('common', 'operation_confirm_warning_failure')?>');
                                }
                            });
                        }else if(form == 'htmlType2'){
                            var arr = new Array();
                            arr['title'] = $('#htmlType2title').val();
                            if(arr['title'] == ''){
                                validation.showAlert($("#htmlType2title"), "<?=Yii::t('frontend', '{value}_not_null',['value'=>Yii::t('frontend', 'question_title')])?>");
                                return false;
                            }
                            arr['url'] = $('#htmlType2url').val();
                            if(arr['url'] == ''){
                                validation.showAlert($("#htmlType2url"), "<?=Yii::t('frontend', '{value}_not_null',['value'=>Yii::t('frontend', 'link')])?>");
                                return false;
                            }
                            arr['display'] = $("#htmlType2display").find("option:selected").val();

                            $.post(addhtml,{title:arr['title'],url:arr['url'],display:arr['display'],component_id:componentId,sequence_number:item,domain_id:domain_id,component_code:type,courseware_type:1,kid:kid},function(data){
                                if(data != false){
                                    $("#saveNewBtn").attr('data-key',data);
                                    var html2 = "<a onclick=\"loadModalFormData('"+addcontainer+"','"+url+"?component_id="+componentId+"&sequence_number="+item+"&domain_id="+domain_id+"&component_code="+type+"&coursewareid="+data+"',this,'"+type+"');\" href=\"javascript:;\">";
                                    html2 += "<i class=\"icon iconfont\" style=\"'color:#rgb(75, 122, 222);'\">မ</i>";
                                    html2 += formtitle;
                                    html2 +="</a>";
                                    html2 +="<div class=\"addAction pull-right\">";
                                    html2 +="<a class=\"glyphicon glyphicon-remove del\" title=\"<?= Yii::t('common', 'delete_button') ?>\" href=\"javascript:;\"></a>"
                                    html2 +="</div>";
                                    var html = '<li id="ware" class="component componentSelected">'+html2+'<input type="hidden" class="componentid" name="component['+$("#saveBtn").attr('data-type')+'][]" value="'+$('#saveNewBtn').attr('data-key')+'"></li>';
                                    $("li[data-empty='"+$("#saveNewBtn").attr('data-li')+"_empty']").parent().append(html);
                                    $("li[data-empty='"+$("#saveNewBtn").attr('data-li')+"_empty']").remove();
                                    $("#saveNewBtn").attr('data-id','').attr('data-li','').attr('data-type','');
                                    $('#'+modalId).find(".modal-body-view").empty();
                                    app.hideAlert($("#FormModal"));
                                    app.showMsg('<?=Yii::t('common', 'operation_success')?>');
                                }else{
                                    app.showMsg('<?=Yii::t('common', 'operation_confirm_warning_failure')?>');
                                }
                            });
                        }else if(form == 'htmlType3'){
                            $('#'+modalId).find('.component').each(function(){
                                var item = $("#saveNewBtn").attr('data-id');
                                if ($(this).hasClass('componentSelected')){
                                    if ($('.ulEditContent').eq(item-1).find("#"+$(this).attr('id')).length > 0){
                                        /*列表已经存在*/
                                    }else {
                                        $(this).find("a").attr('onclick', 'loadModalFormData(\''+addcontainer+'\',\''+url+'?component_id='+componentId+'&sequence_number='+item+'&domain_id='+domain_id+'&typeno=3'+'&component_code='+type+'\',this,\''+type+'\');');
                                        $(this).unbind('click').find('.addAction').html('<a href="javascript:;" class="glyphicon glyphicon-remove del" title="<?= Yii::t('common', 'delete_button') ?>"></a>');
                                        var html = '<li id="'+$(this).attr('id')+'" class="component componentSelected">'+$(this).html()+'<input type="hidden" class="componentid" name="component['+$("#saveNewBtn").attr('data-type')+'][]" value="'+$(this).parent().attr('data-key')+'"></li>';
                                        $('.ulEditContent').eq(item-1).append(html);
                                        $(".del").bind("click", function () {
                                            $(this).parent().parent().remove()
                                        });
                                        html = '';
                                    }
                                }else{
                                    $('.ulEditContent').eq(item-1).find("#"+$(this).attr("id")).remove();
                                }
                            });
                            //            $("li[data-id='"+$("#saveBtn").attr('data-li')+"']").remove();
                            $("li[data-empty='"+$("#saveNewBtn").attr('data-li')+"_empty']").remove();
                            $("#saveNewBtn").attr('data-id','').attr('data-li','').attr('data-type','');
                            app.hideAlert($("#"+modalId));
                            $('#'+modalId).find(".modal-body-view").empty();
                        }
                    }else if(code == 'book'){
                        url = updatebook;
                        addcontainer = 'FormModal';
                        var formtitle = $('#bookname').val();

                        var arr = new Array();
                        arr['bookname'] = encodeURI($('#bookname').val());
                        if(arr['bookname'] == ''){
                            validation.showAlert($("#bookname"), "<?=Yii::t('frontend', '{value}_not_null',['value'=>Yii::t('frontend', 'book_name')])?>");
                            return false;
                        }
                        arr['bookno'] = encodeURI($('#bookno').val());
                        arr['author'] = encodeURI($('#author').val());
                        arr['publisher'] = encodeURI($('#publisher').val());
                        arr['alttitle'] = encodeURI($('#alttitle').val());
                        arr['translator'] = encodeURI($('#translator').val());
                        arr['pubdate'] = encodeURI($('#pubdate').val());
                        arr['pages'] = encodeURI($('#pages').val());
                        arr['price'] = encodeURI($('#price').val());
                        arr['binding'] = encodeURI($('#binding').val());
                        arr['intro'] = encodeURI($('#intro').val());
                        arr['url'] = encodeURI($('#url').val());
                        arr['booktype'] = encodeURI($('#booktype').val());

                        $.get(addbook+'?title='+arr['bookname']+ "&bookno="+arr['bookno']+"&author="+arr['author']+"&publisher="+arr['publisher']+"&alttitle="+arr['alttitle']+"&translator="+arr['translator']+"&pubdate="+arr['pubdate']+"&pages="+arr['pages']+"&price="+arr['price']+"&binding="+arr['binding']+"&intro="+arr['intro']+"&component_id="+componentId+"&sequence_number="+item+"&domain_id="+domain_id+"&component_code="+type+"&courseware_type=3&kid="+kid,'',function(data){
                            //               $.post(addbook,{titile:arr['bookname'],bookno:arr['bookno'],author:arr['author'],publisher:arr['publisher'],alttitle:arr['alttile'],translator:arr['translator'],pubdate:arr['pubdate'],pages:arr['pages'],price:arr['price'],binding:arr['binding'],intro:arr['intro'],component_id:componentId,sequence_number:item,domain_id:domain_id,component_code:type,courseware_type:3,kid:kid},function(data){
                            if(data != false){
                                $("#saveNewBtn").attr('data-key',data);
                                var html2 = "<a onclick=\"loadModalFormData('"+addcontainer+"','"+url+"?component_id="+componentId+"&sequence_number="+item+"&domain_id="+domain_id+"&component_code="+type+"&coursewareid="+data+"',this,'"+type+"');\" href=\"javascript:;\">";
                                html2 += "<i style=\"color:#f48325\" class=\"icon iconfont\">ဗ</i>";
                                html2 += formtitle;
                                html2 +="</a>";
                                html2 +="<div class=\"addAction pull-right\">";
                                html2 +="<a class=\"glyphicon glyphicon-remove del\" title=\"<?= Yii::t('common', 'delete_button') ?>\" href=\"javascript:;\"></a>"
                                html2 +="</div>";
                                var html = '<li id="ware" class="component componentSelected">'+html2+'<input type="hidden" class="componentid" name="component['+$("#saveBtn").attr('data-type')+'][]" value="'+$('#saveNewBtn').attr('data-key')+'"></li>';
                                $("li[data-empty='"+$("#saveNewBtn").attr('data-li')+"_empty']").parent().append(html);
                                $("li[data-empty='"+$("#saveNewBtn").attr('data-li')+"_empty']").remove();
                                $("#saveNewBtn").attr('data-id','').attr('data-li','').attr('data-type','');
                                $('#'+modalId).find(".modal-body-view").empty();
                                app.hideAlert($("#FormModal"));
                                app.showMsg('<?=Yii::t('common', 'operation_success')?>');

                            }else{
                                app.showMsg('<?=Yii::t('common', 'operation_confirm_warning_failure')?>');
                            }
                        });
                    }else{
                        addcontainer = 'addModal'
                    };

                });
                $(document).ready(function() {
                    update();
                    var validation =  app.creatFormValidation($("#htmlformlist"));
                });
                function update(){
                    $('#saveNewBtn').attr('data-code','html');
                    $('#compnenttitle').html('HTML组件');

                    <?php  if(isset($result)&&($typeno != 3)){?>
                        <?php if($result['courseware_type'] ==2){?>
                    var form = $('#formnow').val('htmlType1');

                            $('.htmlType1').removeClass('hide');
                            $('.htmlType2').addClass('hide');
                            $('.htmlType3').addClass('hide');
                            $('#type1').attr('selected','selected');
                            $('#htmlType1display<?=$result['is_display_pc']?>').attr('selected','selected');
                        <?php }elseif($result['courseware_type'] ==1){ ?>
                    var form = $('#formnow').val('htmlType2');

                    $('.htmlType1').addClass('hide');
                            $('.htmlType2').removeClass('hide');
                            $('.htmlType3').addClass('hide');
                            $('#type2').attr('selected','selected');
                            $('#htmlType2display<?=$result['is_display_pc']?>').attr('selected','selected');
                        <?php }?>
                    <?php }elseif($typeno == 3){?>
                    var form = $('#formnow').val('htmlType3');

                    $('.htmlType1').addClass('hide');
                    $('.htmlType2').addClass('hide');
                    $('.htmlType3').removeClass('hide');
                    $('#type3').attr('selected','selected');
                    $('#type3').parent().attr('disabled','disabled');
                    ajaxGet('<?=Yii::$app->urlManager->createUrl(['resource/file-html'])?>?component_id=<?=$data['component_id']?>&sequence_number=<?=$data['sequence_number']?>&domain_id=<?=$data['domain_id']?>&component_code=<?=$data['component_code']?>&courseware_type=0&entry_mode=0', 'containerType3');

                    <?php }?>
                };

            </script>