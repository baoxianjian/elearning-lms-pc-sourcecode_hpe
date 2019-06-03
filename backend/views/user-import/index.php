<?php
/**
 * Created by PhpStorm.
 * User: Alex Liu
 * Date: 2016/6/2
 * Time: 9:11
 */
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

$ContentTanslateName = Yii::t('backend', 'user_import');

$this->params['breadcrumbs'][] = $ContentTanslateName;

?>
<?= Html::jsFile('/vendor/bower/bootstrap/dist/js/bootstrap.min.js') ?>
<?= Html::jsFile('/static/frontend/js/lang.zh-CN.js') ?>
<?= Html::jsFile('/static/frontend/js/elearning.js') ?>
<div id="content-body">
    <div class="col-sm-12">
        <div class="actionBar" style="margin-top: 10px;">
            <div class="form-inline pull-left">
                <label for=""><?=Yii::t('common', 'filename')?>: </label>
                <div class="form-group">
                    <input type="text" class="form-control" style="width: 260px" id="tmp" readonly />
                    <button id="upload" class="btn btn-primary pull-right"><?=Yii::t('common', 'upload')?></button>
                </div>
            </div>
            <a href="/upload/template/user_import.xls" class="pull-right" style=" top: 10px; position: relative; "><?=Yii::t('frontend', 'download')?><?=Yii::t('common', 'template_url')?></a>
        </div>
        <div class="previews" id="previews">
        </div>
    </div>
    <div class="c"></div>
</div>
<div id="saveDiv" class="actions centerBtnArea groupAddMember hide" style="text-align: center;">
    <a href="###" class="btn btn-success centerBtn" id="saveImportData"><?=Yii::t('common', 'import')?></a>
</div>
<a id="download_result" href="###" class="hide" style=" top: 10px;">查看结果</a>

<div class="c"></div>
<?=Html::jsFile('/static/common/js/ajaxupload.js') ?>
<script>
    var ajaxUploadUrl = "<?=Url::toRoute(['user-import/upload-file'])?>";
    new AjaxUpload("#upload", {
        action: ajaxUploadUrl,
        type: "POST",
        name: 'myfile',
        data: {'_csrf': '<?= Yii::$app->request->csrfToken ?>'},
        responseType: 'json',
        onComplete: function (file, response) {
            console.log(response);
            if (response.result == 'success'){
                $("#tmp").val(response.path).attr('data-src', response.path).attr('data-md5', response.md5);

                $.post('<?=Url::toRoute(['user-import/import-temp'])?>', {file: response.path, fileMd5:response.md5}, function(e){
                    if (e.result == 'success'){
                        getPreview(response.md5);
                    }else{
                        NotyWarning(e.errmsg, 1500);
                        return false;
                    }
                },'json');
            }else{
                $("#tmp").val('').attr('data-src','');
                NotyWarning(response.errmsg, 1500);
            }
        }
    });
    $(function(){
        $("#saveImportData").on('click', function(e){
            e.preventDefault();
            var fileMd5 = $("#tmp").attr('data-md5');
            var file = $("#tmp").attr('data-src');
            if (typeof fileMd5 == "undefined" || fileMd5 == ""){
                NotyWarning('<?=Yii::t('frontend', 'import_empty_person')?>', 1000);
                return false;
            }else{
//                app.showLoadingMsg();
                $.post('<?=Url::toRoute(['user-import/save'])?>', {file: file, fileMd5: fileMd5}, function(e){
//                    app.hideLoadingMsg();
                    if (e.result == 'success'){
                        NotyWarning('success', 1500);
                        $("#download_result").attr('href', file);
                        $("#download_result").removeClass('hide');
                    }else{
                        NotyWarning(e.errmsg, 1500);
                        return false;
                    }
                }, 'json');
            }
        });
    });

    function getPreview(fileMd5){
        $.get('<?=Url::toRoute(['user-import/preview'])?>', {fileMd5: fileMd5}, function(r){
            if (r){
                $("#previews").html(r);
                $("#saveDiv").removeClass('hide');
            }else{
                NotyWarning('<?=Yii::t('common', 'loading_fail')?>', 1500);
                return false;
            }
        });
    }
</script>