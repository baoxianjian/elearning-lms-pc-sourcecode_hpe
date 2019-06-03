<?php
use yii\helpers\Url;
use yii\helpers\Html;
use common\models\learning\LnCourse;
$isCourseType = $params['isCourseType'];
?>
<div class="header">
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    <h4 class="modal-title" id="compnenttitle"><?= Yii::t('common', '{pop}_ln_component',['pop'=>Yii::t('frontend','book_2')]) ?></h4>
</div>
<div class="content">
    <form id="bookformlist" name="bookformlist">
        <div class="courseInfo">
        <div class=" panel-default scoreList">
            <div class="panel-body">
                <div class="infoBlock">
                    <div class="row">
                        <div class="col-md-12 col-sm-12">
                            <div class="form-group form-group-sm">
                                <label class="col-sm-3 control-label"><?= Yii::t('frontend', 'book_name') ?></label>
                                <div id="book-query-list" class="col-sm-9">
                                    <!--<input name="title" maxlength="125" class="form-control" type="text" style="width:80%; float:left;"  data-mode="COMMON" data-condition="required" data-alert="书名不能为空"  id="bookname" value="<?=$result['book_name']?>">-->
                                    <input name="title" type="text" class="form-control" id="bookname" value="<?=$result['book_name']?>" style="display:inline-block;width:100%;" data-condition="required" data-alert="<?= Yii::t('common', 'can_not_empty_{value}',['value'=>Yii::t('frontend', 'book_name')]) ?>"  id="bookname" data-url="<?=Url::to(['/resource/get-book','cate_code'=>'course','format'=>'new'])?>" autocomplete="off" />
                                    <div id="booksDiv"></div>
                                    <a href="###" class="btn btn-sm pull-left"  ONCLICK="getbook('id','bookname')"><?= Yii::t('frontend', 'sync') ?></a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 col-sm-12">
                            <div class="form-group form-group-sm">
                                <label class="col-sm-3 control-label"><?= Yii::t('common', 'isbn_no') ?></label>
                                <div class="col-sm-9">
                                    <input class="form-control" maxlength="50" type="text" onkeyup="this.value=this.value.replace(/\D/g,'')"  onafterpaste="this.value=this.value.replace(/\D/g,'')"  style="width:80%; float:left;" id="bookno" value="<?=$result['isbn_no']?>" name="bookno">
                                    <a href="###" class="btn btn-sm pull-left"  ONCLICK="getbook('isbn','bookno')"><?= Yii::t('frontend', 'sync') ?></a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 col-sm-12">
                            <div class="form-group form-group-sm">
                                <label class="col-sm-3 control-label"><?= Yii::t('common', 'art_author') ?></label>
                                <div class="col-sm-9">
                                    <input class="form-control" maxlength="50" type="text" style="width:80%;" id="author" value="<?=$result['author_name']?>" name="author">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 col-sm-12">
                            <div class="form-group form-group-sm">
                                <label class="col-sm-3 control-label"><?= Yii::t('common', 'publisher_name') ?></label>
                                <div class="col-sm-9">
                                    <input class="form-control" maxlength="50" type="text" style="width:80%;" id="publisher" value="<?=$result['publisher_name']?>" name="publisher">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 col-sm-12">
                            <div class="form-group form-group-sm">
                                <label class="col-sm-3 control-label"><?= Yii::t('common', 'original_book_name') ?></label>
                                <div class="col-sm-9">
                                    <input class="form-control" maxlength="125" type="text" style="width:80%;" id="alttitle" value="<?=$result['original_book_name']?>" name="alttitle">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 col-sm-12">
                            <div class="form-group form-group-sm">
                                <label class="col-sm-3 control-label"><?= Yii::t('common', 'translator') ?></label>
                                <div class="col-sm-9">
                                    <input class="form-control" maxlength="50" type="text" id="translator" style="width:80%;" value="<?=$result['translator']?>" name="translator">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 col-sm-12">
                            <div class="form-group form-group-sm">
                                <label class="col-sm-3 control-label"><?= Yii::t('common', 'publisher_date') ?></label>
                                <div class="col-sm-9">
                                    <input class="form-control" maxlength="50" type="text" id="pubdate" style="width:80%;" value="<?=$result['publisher_date']?>" name="pubdate">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 col-sm-12">
                            <div class="form-group form-group-sm">
                                <label class="col-sm-3 control-label"><?= Yii::t('common', 'page_number') ?></label>
                                <div class="col-sm-9">
                                    <input class="form-control" maxlength="9" type="text" id="pages" onkeyup="this.value=this.value.replace(/\D/g,'')"  onafterpaste="this.value=this.value.replace(/\D/g,'')"  style="width:80%;" value="<?=$result['page_number']?>" name="pages">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 col-sm-12">
                            <div class="form-group form-group-sm">
                                <label class="col-sm-3 control-label"><?= Yii::t('common', 'course_price') ?></label>
                                <div class="col-sm-9">
                                    <input class="form-control" maxlength="50" type="text" id="price" style="width:80%;ime-mode: disabled;" oninput="checkprice(this.value)" value="<?=$result['price']?>" name="price">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 col-sm-12">
                            <div class="form-group form-group-sm">
                                <label class="col-sm-3 control-label"><?= Yii::t('common', 'binding_layout') ?></label>
                                <div class="col-sm-9">
                                    <input class="form-control" maxlength="50" type="text" id="binding" style="width:80%;" value="<?=$result['binding_layout']?>" name="binding">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 col-sm-12">
                            <div class="form-group form-group-sm">
                                <label class="col-sm-3 control-label"><?= Yii::t('frontend', 'brief') ?></label>
                                <div class="col-sm-9">
                                    <textarea id="intro" name="intro" maxlength="500"><?=$result['description']?></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
        <input type="hidden" name="url" value="" id="url">
        <input type="hidden" name="kid" value="<?=$result['courware_id']?>" id="kid">
        <input type="hidden" name="booktype" value="0" id="booktype">
        <input type="hidden" name="component_id" value="<?=$params['component_id']?>">
        <input type="hidden" name="domain_id" value="<?=$params['domain_id']?>">
        <input type="hidden" name="component_code" value="<?=$params['component_code']?>">
        <input type="hidden" name="courseware_type" value="<?=\common\models\learning\LnCourseware::COURSEWARE_TYPEE_OTHER?>">
    </form>
</div>
<div class="actions">
    <?= Html::button(Yii::t('common', 'save'), ['id'=>'saveBtn','class'=>'btn btn-primary'])?>
</div>
<script>
    var book_url = '<?=Url::toRoute(['/resource/addbook'])?>';
    var icon = '<?=$component->icon?>';
    var tempvalue;
    function checkprice(it){
        var reg = /^\d+\.?\d{0,2}$/;
        if(reg.test(it)){
            tempvalue = it;
            $('#price').val(it);
        }else{
            $('#price').val(tempvalue);
        }
    }
    $(document).ready(function() {
        var validation =  app.creatFormValidation($("#bookformlist"));
        $("#saveBtn").on('click', function(){
            var bookname = $('#bookname').val();
            if($.trim(bookname) == ''){
                validation.showAlert($("#bookname"), "<?= Yii::t('common', 'can_not_empty_{value}',['value'=>Yii::t('frontend', 'book_name')]) ?>");
                return false;
            }
            var type = $("#addModal").attr('data-type');
            $.post(book_url, $("#bookformlist").serialize(), function(data){
                if(data != false){
                    var parentMod = $("li[data-id='"+$("#addModal").attr('data-li')+"']").parent();
                    if (parentMod.find("#ware_"+data).length > 0){
                        parentMod.find("#ware_"+data).find(".component-tbody").html(icon + '&nbsp;' + bookname);
                    }else{
                        var li = parentMod.find(".componentSelected").length;
                        var html = '<li id="ware_'+data+'" class="component componentSelected clearfix" data-component="<?=$params['component_code']?>">';
                        html += '<a href="javascript:;" class="pull-left component-tbody" onclick="loadModalFormData(\'addModal\',\'<?=Url::toRoute([$component->action_url])?>?component_id=<?=$params['component_id']?>&sequence_number=<?=$params['sequence_number']?>&domain_id=<?=$params['domain_id']?>&component_code=<?=$params['component_code']?>&id='+data+'\',this,\''+type+'\',\'book\',\'0\');">';
                        html += icon  + '&nbsp;' + bookname;
                        html += '</a>';
                        html += '<div class="addAction pull-right">';
                        html += '<a class="glyphicon glyphicon-remove del" title="<?= Yii::t('common', 'delete_button') ?>" href="javascript:;"></a>';
                        <?php 
                        if ($isCourseType == LnCourse::COURSE_TYPE_ONLINE){
                        ?>
                        html += '<a href="###" class="del_config" onclick="loadModalFormData(\'addModal\',\'<?=Url::toRoute(['/resource/component/config'])?>?component_id=<?=$params['component_id']?>&sequence_number=<?=$params['sequence_number']?>&domain_id=<?=$params['domain_id']?>&component_code=<?=$params['component_code']?>&id='+data+'&title='+bookname+'\',this,\''+type+'\',\'book\',\'0\');"><?= Yii::t('frontend', 'configuration') ?></a>';
                        <?php
                        //}else if ($isCourseType == LnCourse::COURSE_TYPE_FACETOFACE && in_array($params['component_code'], $is_setting_component)){
                        }else if ($isCourseType == LnCourse::COURSE_TYPE_FACETOFACE){
                    	?>
                    	html += '<a href="###" class="del_config" onclick="loadModalFormData(\'addModal\',\'<?=Url::toRoute(['/resource/component/config'])?>?component_id=<?=$params['component_id']?>&sequence_number=<?=$params['sequence_number']?>&domain_id=<?=$params['domain_id']?>&component_code=<?=$params['component_code']?>&id='+data+'&title='+bookname+'\',this,\''+type+'\',\'book\',\'0\');"><?= Yii::t('frontend', 'configuration') ?></a>';
                    	<?php                                	
                        }
                    	?>
                        html += '</div>';
                        html += '<input type="hidden" class="componentid" data-modnum="<?=$params['mod_num']?>"  data-restitle="'+bookname+'" data-compnenttitle="<?=$component->title?>"  data-completerule="<?=$component->complete_rule?>" data-isscore="<?=$component->is_record_score?>" name="resource[<?=$params['sequence_number']?>]['+$("#addModal").attr('data-type')+'][<?=$params['component_code']?>]['+(li+1)+']" value="'+data+'">';
                        html += '</li>';
                        parentMod.append(html);
                    }
                    $("li[data-empty='"+$("#addModal").attr('data-li')+"_empty']").remove();
                    $("#addModal").attr('data-id','').attr('data-li','').attr('data-code','').attr('data-type','');
                    $('#addModal').empty();
                    app.hideAlert($("#addModal"));
                    app.showMsg('<?= Yii::t('common', 'operation_success') ?>');
                }else{
                    app.showMsg('<?= Yii::t('common', 'operation_confirm_warning_failure') ?>');
                }
            });
        });
    });
    var geturl = "<?=Yii::$app->urlManager->createUrl(['resource/get-book'])?>";
    function getbook(type,inputid) {
        var rquestUrl=null;   
        var b = bookQuerylist.get();
        var inputVal = $('#'+inputid).val(); 
        if(type=='id')
        {  
            if(typeof b.kid == 'undefined')
            {
                 rquestUrl=geturl + '?name=' + inputVal;
            }
            else
            {
                rquestUrl=geturl + '?id=' + b.kid;
            }
        }
        else
        {
            rquestUrl=geturl+'?isbn='+inputVal;
        }
         
        $.getJSON(rquestUrl, '', function (data) {
            $('#bookname').val(data.title);
            $('#bookno').val(data.isbn13);
            $('#author').val(data.author);
            $('#publisher').val(data.publisher);
            $('#alttitle').val(data.alt_title);
            $('#translator').val(data.translator);
            $('#pubdate').val(data.pubdate);
            $('#pages').val(data.pages);
            $('#price').val(data.price);
            $('#binding').val(data.binding);
            var str = data.summary;
            var newstr = str.substring(0, 490);
            $('#intro').val(newstr);
            $('#url').val(data.alt);
            $('#booktype').val('1');
        });

      
    }
</script>
<script type="text/javascript">
setTimeout(function (){      
window.bookQuerylist = app.queryList("#bookname",'<?=$result['book_name']?>');
}, 0);   
</script>