<form id="bookformlist">
<div class="body">
                <div class="courseInfo">
                    <div role="tabpanel" class="tab-pane active" id="teacher_info">
                        <div class=" panel-default scoreList">
                            <div class="panel-body">
                                <div class="infoBlock">
                                    <div class="row">
                                        <div class="col-md-12 col-sm-12">
                                            <div class="form-group form-group-sm">
                                                <label class="col-sm-3 control-label"><?=Yii::t('frontend', 'book_name')?></label>
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
                                                    <input class="form-control" type="text" onkeyup="this.value=this.value.replace(/\D/g,'')"  onafterpaste="this.value=this.value.replace(/\D/g,'')"  style="width:80%; float:left;" id="bookno" value="<?=$result['isbn_no']?>">
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
                                                    <input class="form-control" type="text" style="width:80%;" id="author" value="<?=$result['author_name']?>">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12 col-sm-12">
                                            <div class="form-group form-group-sm">
                                                <label class="col-sm-3 control-label"><?= Yii::t('common', 'publisher_name') ?></label>
                                                <div class="col-sm-9">
                                                    <input class="form-control" type="text" style="width:80%;" id="publisher" value="<?=$result['publisher_name']?>">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12 col-sm-12">
                                            <div class="form-group form-group-sm">
                                                <label class="col-sm-3 control-label"><?= Yii::t('common', 'original_book_name') ?></label>
                                                <div class="col-sm-9">
                                                    <input class="form-control" type="text" style="width:80%;" id="alttitle" value="<?=$result['original_book_name']?>">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12 col-sm-12">
                                            <div class="form-group form-group-sm">
                                                <label class="col-sm-3 control-label"><?= Yii::t('common', 'translator') ?></label>
                                                <div class="col-sm-9">
                                                    <input class="form-control" type="text" id="translator" style="width:80%;" value="<?=$result['translator']?>">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12 col-sm-12">
                                            <div class="form-group form-group-sm">
                                                <label class="col-sm-3 control-label"><?= Yii::t('common', 'publisher_date') ?></label>
                                                <div class="col-sm-9">
                                                    <input class="form-control" type="text" id="pubdate" style="width:80%;" value="<?=$result['translator']?>">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12 col-sm-12">
                                            <div class="form-group form-group-sm">
                                                <label class="col-sm-3 control-label"><?= Yii::t('common', 'page_number') ?></label>
                                                <div class="col-sm-9">
                                                    <input class="form-control" type="text" id="pages" onkeyup="this.value=this.value.replace(/\D/g,'')"  onafterpaste="this.value=this.value.replace(/\D/g,'')"  style="width:80%;" value="<?=$result['page_number']?>">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12 col-sm-12">
                                            <div class="form-group form-group-sm">
                                                <label class="col-sm-3 control-label"><?= Yii::t('common', 'course_price') ?></label>
                                                <div class="col-sm-9">
                                                    <input class="form-control" type="text" id="price" style="width:80%;" value="<?=$result['price']?>">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12 col-sm-12">
                                            <div class="form-group form-group-sm">
                                                <label class="col-sm-3 control-label"><?= Yii::t('common', 'binding_layout') ?></label>
                                                <div class="col-sm-9">
                                                    <input class="form-control" type="text" id="binding" style="width:80%;" value="<?=$result['binding_layout']?>">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12 col-sm-12">
                                            <div class="form-group form-group-sm">
                                                <label class="col-sm-3 control-label"><?= Yii::t('frontend', 'brief') ?></label>
                                                <div class="col-sm-9">
                                                    <textarea id="intro" maxlength="500"><?=$result['description']?></textarea>
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
<input type="hidden" value="" id="url">
<input type="hidden" value="<?=$coursewareid?>" id="kid">
<input type="hidden" value="1" id="booktype">
</form>
<script>
    $(document).ready(function() {
        $('#compnenttitle').html('<?= Yii::t('common', 'book_componet') ?>');
        $('#saveNewBtn').attr('data-code','book');
        var validation =  app.creatFormValidation($("#bookformlist"));

    
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
                        validation.showAlert($("#htmlType1title"), "<?= Yii::t('common', 'can_not_empty_{value}',['value'=>Yii::t('common', 'investigation_title')]) ?>");
                        return false;
                    }
                    arr['content'] =$('#htmlType1content').val();
                    if(arr['content'] == ''){
                        validation.showAlert($("#htmlType1content"), "<?= Yii::t('common', 'can_not_empty_{value}',['value'=>Yii::t('frontend', 'question_content')]) ?>");
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
                            app.showMsg('<?= Yii::t('common', 'operation_success') ?>');

                        }else{
                            app.showMsg('<?= Yii::t('common', 'operation_confirm_warning_failure') ?>');
                        }
                    });
                }else if(form == 'htmlType2'){
                    var arr = new Array();
                    arr['title'] = $('#htmlType2title').val();
                    if(arr['title'] == ''){
                        validation.showAlert($("#htmlType2title"), "<?= Yii::t('common', 'can_not_empty_{value}',['value'=>Yii::t('common', 'investigation_title')]) ?>");
                        return false;
                    }
                    arr['url'] = $('#htmlType2url').val();
                    if(arr['url'] == ''){
                        validation.showAlert($("#htmlType2url"), "<?= Yii::t('common', 'can_not_empty_{value}',['value'=>Yii::t('frontend', 'link')]) ?>");
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
                            app.showMsg('<?= Yii::t('common', 'operation_success') ?>');
                        }else{
                            app.showMsg('<?= Yii::t('common', 'operation_confirm_warning_failure') ?>');
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
                    validation.showAlert($("#bookname"), "<?= Yii::t('common', 'can_not_empty_{value}',['value'=>Yii::t('frontend', 'book_name')]) ?>");
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
                        html2 +="<a class=\"glyphicon glyphicon-remove del\" title=\"<?=Yii::t('common','delete_button')?>\" href=\"javascript:;\"></a>"
                        html2 +="</div>";
                        var html = '<li id="ware" class="component componentSelected">'+html2+'<input type="hidden" class="componentid" name="component['+$("#saveBtn").attr('data-type')+'][]" value="'+$('#saveNewBtn').attr('data-key')+'"></li>';
                        $("li[data-empty='"+$("#saveNewBtn").attr('data-li')+"_empty']").parent().append(html);
                        $("li[data-empty='"+$("#saveNewBtn").attr('data-li')+"_empty']").remove();
                        $("#saveNewBtn").attr('data-id','').attr('data-li','').attr('data-type','');
                        $('#'+modalId).find(".modal-body-view").empty();
                        app.hideAlert($("#FormModal"));
                        app.showMsg('<?= Yii::t('common', 'operation_success') ?>');

                    }else{
                        app.showMsg('<?= Yii::t('common', 'operation_confirm_warning_failure') ?>');
                    }
                });
            }else{
                addcontainer = 'addModal'
            };

        });
     //opener.test();
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