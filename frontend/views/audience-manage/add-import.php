<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/4/19
 * Time: 16:08
 */
use yii\helpers\Html;
use yii\helpers\Url;

?>
<div class="header">
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    <h4 class="modal-title" id="myModalLabel"><?=Yii::t('common', 'import_button')?></h4>
</div>
<div class="content">
    <div class="col-sm-12">
        <p style="color: #ff0000;"><?=Yii::t('frontend', 'note')?>: <?=Yii::t('frontend', 'audience_best_count')?></p>
        <div class="actionBar" style="margin-top: 10px;">
            <div class="form-inline pull-left">
                <label for=""><?=Yii::t('common', 'filename')?>: </label>
                <div class="form-group">
                    <input type="text" class="form-control" id="tmp" readonly />
                    <button id="importButton" class="btn btn-primary pull-right" style="margin-left:10px;"><?=Yii::t('common', 'upload')?></button>
                    <button id="upload" class="btn btn-primary pull-right"><?=Yii::t('frontend', 'select')?></button>
                </div>
            </div>
            <a href="/upload/audience-template/personal_import.xls" class="pull-right" style=" top: 10px; position: relative; "><?=Yii::t('frontend', 'download')?><?=Yii::t('common', 'template_url')?></a>
        </div>
        <div class="c"></div>
        <div class="upload-content" style="margin-top: 30px">
            <ul class="nav nav-tabs" role="tablist" id="myTab" style="background-color: transparent!important;">
                <li role="presentation" class="active" data-status="success">
                    <a href="#importsuccess" aria-controls="importsuccess" role="tab" data-toggle="tab" aria-expanded="true" data-loading="false"><?=Yii::t('common', 'action_status_normal')?>(<em id="successNumbers">0</em>)</a>
                </li>
                <li role="presentation" class="" data-status="fail">
                    <a href="#importfail" aria-controls="importfail" role="tab" data-toggle="tab" aria-expanded="false" style="color: #ff0000;" data-loading="false"><?=Yii::t('common', 'action_status_error')?>(<em id="failNumbers">0</em>)</a>
                </li>
            </ul>
            <div class="tab-content">
                <div role="tabpanel" class="tab-pane active previews" id="importsuccess">
                    <table class="table table-bordered table-hover table-striped table-center">
                        <tbody>
                        <tr>
                            <td width="10%"><?=Yii::t('common', 'xls_row')?></td>
                            <td width="25%"><?=Yii::t('common', 'user_name')?></td>
                            <td width="15%"><?=Yii::t('common', 'real_name')?></td>
                            <td width="25%"><?=Yii::t('common', 'email')?></td>
                            <td width="25%"><?=Yii::t('common', 'status')?></td>
                        </tr>
                        <tr>
                            <td colspan="5"><?=Yii::t('common', 'no_data')?></td>
                        </tr>
                        </tbody>
                    </table>
                </div>
                <div role="tabpanel" class="tab-pane importError" id="importfail">
                    <table class="table table-bordered table-hover table-striped table-center">
                        <tbody>
                        <tr>
                            <td width="10%"><?=Yii::t('common', 'xls_row')?></td>
                            <td width="25%"><?=Yii::t('common', 'user_name')?></td>
                            <td width="15%"><?=Yii::t('common', 'real_name')?></td>
                            <td width="25%"><?=Yii::t('common', 'email')?></td>
                            <td width="25%"><?=Yii::t('common', 'status')?></td>
                        </tr>
                        <tr>
                            <td colspan="5"><?=Yii::t('common', 'no_data')?></td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="c"></div>
</div>
<div class="actions centerBtnArea groupAddMember" style="text-align: center;">
    <!--<a href="###" class="btn centerBtn" id="closeImportAlert"><?/*=Yii::t('common', 'close')*/?></a>-->
    <a href="###" class="btn centerBtn" id="saveImportData"><?=Yii::t('common', 'save')?></a>
</div>
<div class="c"></div>
<?=Html::jsFile('/static/common/js/ajaxupload.js') ?>
<script>
var ajaxUploadUrl = "<?=Url::toRoute(['/audience-manage/import-file'])?>";
new AjaxUpload("#upload", {
    action: ajaxUploadUrl,
    type: "POST",
    name: 'myfile',
    data: {'_csrf': '<?= Yii::$app->request->csrfToken ?>'},
    onComplete: function (file, response) {
        console.log(response);
        var jsonData = eval("("+response+")");
        if (jsonData.result == 'success'){
            $("#tmp").val(jsonData.basename).attr('data-src', jsonData.errmsg).attr('data-md5', jsonData.md5);
        }else{
            $("#tmp").val('').attr('data-src','');
            app.showMsg(jsonData.errmsg);
        }
    }
});
    $(function(){
        $("#closeImportAlert").on('click', function(e){
            app.hideAlert($("#addImport"));
        });
        $("#importButton").on('click', function(e){
            if ($(this).attr('data-disabled') == 'disabled') return false;
            if ($("#tmp").val() == ""){
               app.showMsg('<?=Yii::t('common', 'please_select_import_file')?>');
               return false;
            }
            $("#successNumbers").html(0);
            $("#failNumbers").html(0);
            var fileName = $("#tmp").val();
            var file = $("#tmp").attr('data-src');
            $(this).attr('data-disabled', 'disabled');
            var fileMd5 = $("#tmp").attr('data-md5');
            app.showLoadingMsg();
            $.post('<?=Url::toRoute(['/audience-manage/import-submit'])?>', {audience_batch: '<?=$audience_batch?>',file: file, fileName: fileName, fileMd5: fileMd5}, function(e){
                $("#importButton").attr('data-disabled', 'false');
                app.hideLoadingMsg();
                if (e.result == 'success'){
                    $("#tmp").val('');
                    var status = $("#myTab li.active").attr('data-status');
                    getImportData(fileMd5, status);
                }else{
                    app.showMsg(e.errmsg);
                    return false;
                }
            },'json');
        });

        $("#saveImportData").on('click', function(e){
            e.preventDefault();
            var fileMd5 = $("#tmp").attr('data-md5');
            if (typeof fileMd5 == "undefined" || fileMd5 == ""){
                app.showMsg('<?=Yii::t('frontend', 'import_empty_person')?>');
                return false;
                //app.hideAlert($("#addImport"));
            }else{
                if (user_list.length > 1000){
                    app.showMsg('<?=Yii::t('frontend', 'audience_best_count')?>');
                    return false;
                }
                app.showLoadingMsg();
                $.get('<?=Url::toRoute(['/audience-manage/insert-import-data'])?>', {audience_batch: '<?=$audience_batch?>', fileMd5: fileMd5}, function(e){
                    app.hideLoadingMsg();
                    if (e.result == 'success'){
                        app.hideAlert($("#addImport"));
                        if (e.right > 0) {
                            app.showMsg('<?=Yii::t('common', 'import_right_number')?>' + e.right);
                            var temp_user = e.user;
                            if (user_list.length > 0){
                                user_list = user_list.concat(temp_user);
                            }else{
                                user_list = temp_user;
                            }
                            user_list = unique(user_list);
                        }else{
                            app.showMsg('<?=Yii::t('frontend', 'import_empty_person')?>');
                        }
                        reloadTemp("");
                    }else{
                        app.showMsg(e.errmsg);
                        return false;
                    }
                }, 'json');
            }
        });

        $("#myTab a").on('click', function (e) {
            e.preventDefault();
            if ($(this).attr('data-loading') == 'true'){
                setTimeout(function (){app.refreshAlert($('#addImport'))}, 0);
                //return false;
            }else {
                var fileMd5 = $("#tmp").attr('data-md5');
                var status = $(this).attr('aria-controls').replace(/import/g, '');
                getImportData(fileMd5, status);
            }
        });
    });

    function getImportData(fileMd5, status){
        $.get('<?=Url::toRoute(['audience-manage/get-import-data'])?>', {audience_batch: '<?=$audience_batch?>', fileMd5: fileMd5, status: status}, function(r){
            if (r){
                $("a[aria-controls='import"+status+"']").attr('data-loading', 'true');
                $("#import"+status).html(r);
                /*$("#successNumbers").html($("#importSuccessNumbers").html());
                $("#failNumbers").html($("#importFailNumbers").html());*/
            }else{
                spp.showMsg('<?=Yii::t('common', 'loading_fail')?>');
                return false;
            }
        });
    }
</script>
