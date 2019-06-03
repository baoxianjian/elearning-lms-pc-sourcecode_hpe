<?php
/**
 * Created by PhpStorm.
 * User: adophper
 * Date: 2016/3/14
 * Time: 15:12
 */
use components\widgets\TBreadcrumbs;
use yii\helpers\Url;
use yii\helpers\Html;

$this->params['breadcrumbs'][] = ['label'=>Yii::t('common','resource_management'),'url'=>['/resource/index']];
$this->params['breadcrumbs'][] = ['label'=>Yii::t('common', 'audience').Yii::t('common','management'),'url'=>['/audience-manage/index']];
$this->params['breadcrumbs'][] = Yii::t('common', 'audience_add');
$this->params['breadcrumbs'][] = '';

?>
<div class="container">
    <div class="row">
        <?= TBreadcrumbs::widget([
            'tag' => 'ol',
            'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
        ]) ?>
        <div class="col-md-12 col-sm-12">
            <div class="panel panel-default hotNews">
                <div class="panel-heading">
                    <i class="glyphicon glyphicon-cloud-upload"></i> <?=Yii::t('common', 'add_{value}', ['value' => Yii::t('common', 'info')])?>
                </div>
                <div class="panel-body uploadCourse" style="text-align:left;">
                    <h4><?=Yii::t('common', 'tab_basic_info')?></h4>
                    <hr>
                    <form id="add-form">
                    <div class="infoBlock" style="width: 100%;">
                        <div class="row">
                            <div class="col-md-12 col-sm-12">
                                <div class="form-group form-group-sm">
                                    <label class="col-sm-2 control-label"><?=Yii::t('common', 'name')?></label>
                                    <div class="col-sm-10">
                                        <input class="form-control pull-left" type="text" id="audience_title" placeholder="<?=Yii::t('common', 'audience_title')?>" data-mode="COMMON" data-condition="required" data-alert="<?=Yii::t('frontend','{value}_not_null',['value'=>Yii::t('common', 'audience_title')])?>" value="<?=$model->audience_name?>">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12 col-sm-12">
                                <div class="form-group form-group-sm">
                                    <label class="col-sm-2 control-label"><?=Yii::t('common', 'description')?></label>
                                    <div class="col-sm-10">
                                        <textarea class="form-control pull-left" id="audience_text" data-mode="COMMON" data-condition="" data-alert="<?=Yii::t('common', 'description')?>"><?=$model->description?></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <h4><?=Yii::t('common', 'user_{value}', ['value' => Yii::t('common', 'info')])?></h4>
                    <hr>
                    <div class="actionBar">
                        <?php
                        if (!$view){
                        ?>
                        <button class="btn btn-default pull-left" id="BatchDeleteButton"><?=Yii::t('common', 'batch_delete_button')?></button>
                        <?php
                        }
                        ?>
                        <div class="form-group">
                            <?php
                            if (!$view){
                            ?>
                            <button type="button" class="btn btn-default pull-right" id="importAudience"><?=Yii::t('common', 'import')?></button>
                            <button type="button" class="btn btn-default pull-right" id="addNewMember"><?=Yii::t('frontend', 'add')?></button>
                            <?php
                            }
                            ?>
                            <a href="javascript:;" class="btn btn-default pull-right" id="resetAudience"><?=Yii::t('common', 'reset')?></a>
                            <button type="button" class="btn btn-primary pull-right" id="searchAudience" style="margin-left:10px;"><?=Yii::t('common', 'search')?></button>
                            <input type="text" class="form-control pull-right" id="temp_keyword" placeholder="<?=Yii::t('frontend', 'input_name_email')?>（<?=Yii::t('frontend', 'choose_student')?>）" style="width: 260px;">
                        </div>
                    </div>
                    <div id="audience_temp"></div>
                    <div class="centerBtnArea">
                    <?php
                    if ($view){
                    ?>
                        <a href="<?=Url::toRoute(['/audience-manage/index'])?>" class="btn btn-default btn-sm centerBtn" id="backAudience" style="width: 15%;"><?=Yii::t('common', 'back_button')?></a>
                    <?php
                    }else{
                    ?>
                        <button class="btn btn-default btn-sm centerBtn saveAudience" id="tempAudience" data-status="0" style="width: 15%;"><?=Yii::t('common', 'save_temp')?></button>
                        <button class="btn btn-default btn-sm centerBtn saveAudience" id="saveAudience" data-status="1" style="width: 15%;"><?=Yii::t('common', 'art_publish')?></button>
                    <?php
                    }
                    ?>
                    </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<input type="hidden" id="audience_batch" value="<?=$audience_batch?>" />
<div class="ui modal" id="addPerson"></div>
<div class="ui modal" id="addImport"></div>
<?= Html::jsFile('/static/frontend/js/xss.js')?>
<script>
    var validation = app.creatFormValidation($("#add-form"));
    var user_list = <?=json_encode($userList)?>;
    $(function(){
       $("#addNewMember").on('click', function(){
           var url = '<?=Url::toRoute(['/audience-manage/add-person', 'audience_batch' => $audience_batch])?>';
           $.get(url, function(r){
               if (r){
                   $("#addPerson").html(r);
                   app.alertWide($("#addPerson"));
               }else{
                   app.showMsg('<?=Yii::t('common', 'loading_fail')?>');
               }
           });
       });
        $("#importAudience").on('click', function(){
            var url = '<?=Url::toRoute(['/audience-manage/add-import', 'audience_batch' => $audience_batch])?>';
            $.get(url, function(r){
                if (r){
                    $("#addImport").html(r);
                    app.alertWide($("#addImport"));
                }else{
                    app.showMsg('<?=Yii::t('common', 'loading_fail')?>');
                }
            });
        });
        $("#audience_temp").on('click', '#checkAll', function(e){
            if ($(this).is(":checked") == true) {
                $(".fwUser").find("input[class='checkbox']").each(function() {
                    if ($(this).hasClass('hide')){
                        return false;
                    } else {
                        this.checked = true;
                    }
                });
            } else {
                $(".fwUser").find("input[class='checkbox']").each(function() {
                    if ($(this).hasClass('hide')){
                        return false;
                    } else {
                        this.checked = false;
                    }
                });
            }
        });
        $("#BatchDeleteButton").on('click', function(e){
            e.preventDefault();
            var checkedNumber = $(".fwUser").find("input[class='checkbox']").is(":checked").length;
            if (checkedNumber < 1){
                app.showMsg('<?=Yii::t('frontend', 'select_data_delete')?>');
                return false;
            }
            var key = $(".fwUser").find("input[class='checkbox']:checked").map(function() {
                return $(this).val();
            }).get().join();
            /*reload*/
            removeAudienceTemp(key);
            /*清除user_list*/
            $(".fwUser").find("input[class='checkbox']:checked").each(function(){
                actionCheckUserList('min', $(this).attr('data-user'));
            });
        });
        $("#audience_temp").on('click', '.removeUser', function(e){
            e.preventDefault();
            var removeKid = $(this).parents(".fwUser").find("input[class='checkbox']").val();
            removeAudienceTemp(removeKid);
        });
        $("#resetAudience").on('click', function(e){
            e.preventDefault();
           $("#temp_keyword").val("");
            reloadTemp("");
        });
        $("#temp_keyword").keypress(function(e){
            var keyCode = e.keyCode ? e.keyCode : e.which ? e.which : e.charCode;
            if (keyCode == 13) {
                e.preventDefault();
                var keyword = $("#temp_keyword").val().replace(/(^\s*)|(\s*$)/g,'');
                $("#temp_keyword").val(keyword);
                reloadTemp(keyword);
                return false;
            }
        });
        $("#searchAudience").on('click', function(){
            var error = 0;
           var keyword = $("#temp_keyword").val().replace(/(^\s*)|(\s*$)/g,'');
            $("#temp_keyword").val(keyword);
            /*if (keyword == ""){
                app.showMsg("<?=Yii::t('frontend', 'input_name_email')?>");
                return false;
            }else{*/
                var xss_keyword = filterXSS(keyword);
                if (keyword != xss_keyword){
                    error ++;
                    $("#temp_keyword").focus();
                    app.showMsg('<?=Yii::t('common', 'input_xss_error')?>');
                    return false;
                }
            /*}*/
            if (error > 0) return false;
            reloadTemp(keyword);
        });
        var submiting = false;
        $(".saveAudience").on('click', function(){
            if (submiting) {
                app.showMsg('<?=Yii::t('common', 'submiting')?>');
                return false;
            }
            if (user_list.length > 1000){
                app.showMsg('<?=Yii::t('frontend', 'audience_best_count')?>');
                return false;
            }
            var audience_title = $("#audience_title").val().replace(/(^\s*)|(\s*$)/g,'');
            $("#audience_title").val(audience_title);
            if (audience_title == ""){
                $("#audience_title").focus();
                validation.showAlert($("#audience_title"));
                return false;
            }
            var xss_audience_title = filterXSS(audience_title);
            if (audience_title != xss_audience_title){
                $("#audience_title").focus();
                validation.showAlert($("#audience_title"), "<?=Yii::t('frontend', '{value}_lillegal_char',['value'=>Yii::t('common','name')])?>");
                return false;
            }
            if (app.stringLength(audience_title) > 150){
                $("#audience_title").focus();
                validation.showAlert($("#audience_title"), "<?=Yii::t('frontend', 'user_name_length_more_{value}', ['value' => 50])?>");
                return false;
            }
            var audience_description = $("#audience_text").val();
            var xss_audience_description = filterXSS(audience_description);
            if (audience_description != "" && audience_description != xss_audience_description){
                $("#audience_text").focus();
                validation.showAlert($("#audience_text"), "<?=Yii::t('frontend', '{value}_lillegal_char',['value'=>Yii::t('common', 'description')])?>");
                return false;
            }
            var status = $(this).attr('data-status');
            app.hideLoadingMsg();
            submiting = true;
            $(this).siblings(".saveAudience").attr('disabled', 'disabled');
            $.post('<?=Url::toRoute(['/audience-manage/save', 'audience_batch' => $audience_batch, 'audienceId' => $model->kid])?>', {TreeNodeId: '<?=$TreeNodeId?>',audience_title: audience_title, description: audience_description, status: status}, function(r){
                app.hideLoadingMsg();
                if (r.result == 'success'){
                    app.showMsg('<?=Yii::t('frontend', 'save_sucess')?>');
                    setTimeout(function(){
                        location.href = '<?=Url::toRoute(['/audience-manage/index'])?>';
                    }, 1500);
                }else{
                    app.showMsg(r.errmsg);
                    submiting = false;
                    $(this).siblings(".saveAudience").removeAttr('disabled');
                    return false;
                }
            }, 'json');
            return false;
        });
        reloadTemp("");
    });

    /**
     * 操作临时user_list
     **/
    function actionCheckUserList(action, userId){
        var index = user_list.indexOf(userId);
        if (action == 'add'){
            if (index > -1){
                return ;
            }else{
                if (user_list.length > 1000){
                    app.showMsg('<?=Yii::t('frontend', 'audience_best_count')?>');
                    return false;
                }
                user_list.push(userId);
            }
            user_list = unique(user_list);
        }else{
            if (index > -1) {
                user_list.splice(index, 1);
            }
        }
    }

    function unique(arr){
        var tmp = [];
        for(var i in arr){
            if(tmp.indexOf(arr[i])==-1){
                tmp.push(arr[i]);
            }
        }
        return tmp;
    }

    function saveAddPerson(){
        //if (user_list.length > 0) {
        app.showLoadingMsg();
            $.post('<?=Url::toRoute(['/audience-manage/set-temp'])?>', {
                format: 'json',
                user: user_list,
                audience_batch: '<?=$audience_batch?>'
            }, function (r) {
                app.hideLoadingMsg();
                if (r.result == 'success') {
                    /*reload*/
                    reloadTemp("");
                } else {
                    app.showMsg('<?=Yii::t('common', 'loading_fail')?>');
                }
            }, 'json');
        /*}else{
            /!**!/
        }*/
        app.hideAlert($("#addPerson"));
    }

    /**
     * 删除
     * @param key
     */
    function removeAudienceTemp(key){
        $.post('<?=Url::toRoute(['/audience-manage/remove-audience-temp', 'audience_batch' => $audience_batch])?>', {kid: key}, function(r){
            if (r.result == 'success'){
                reloadTemp("");
                var user_id = $("#remove_"+key).attr('data-user');
                actionCheckUserList('min', user_id);
            }else{
                app.showMsg('<?=Yii::t('common', 'loading_fail')?>');
            }
        }, 'json');
    }

    /**
     * 更新列表
     */
    function reloadTemp(keyword){
        $.get("<?=Url::toRoute(['/audience-manage/audience-temp', 'audience_batch' => $audience_batch, 'view' => $view])?>", {keyword: keyword}, function(r){
            if (r){
                $("#audience_temp").html(r);
            }else{
                app.showMsg('<?=Yii::t('common', 'loading_fail')?>');
            }
        });
    }
</script>
