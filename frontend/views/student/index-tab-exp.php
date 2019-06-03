<?php
/**
 * Created by PhpStorm.
 * User: LiuCheng
 * Date: 2015/6/15
 * Time: 16:23
 */
use yii\helpers\Html;
use yii\helpers\Url;

?>
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title" id="myModalLabel"><?=Yii::t('frontend','experience')?></h4>
        </div>
        <div class="modal-body">
            <div role="tabpanel" class="tab-pane  panel-body" id="motion">
                <form id="recordExpForm" class="shareInput" action="/student/index-tab-exp.html" method="post">
                    <input type="hidden" name="_csrf" value="<?= Yii::$app->request->csrfToken ?>">
                    <h5><?=Yii::t('frontend','what_{value}_need',['value'=>Yii::t('frontend','experience')])?></h5>
                    <input type="hidden" id="is_share" name="is_share" value="0">
                    <input type="hidden" id="sorecord-duration" class="form-control" name="SoRecord[duration]">
                    <input type="hidden" id="sorecord-attach_original_filename" class="form-control" name="SoRecord[attach_original_filename]">
                    <input type="hidden" id="sorecord-attach_url" class="form-control" name="SoRecord[attach_url]">
                    <input type="hidden" id="sorecord-record_type" class="form-control" name="SoRecord[record_type]" value="3">
                    <div class="form-group field-sorecord-title required has-error">
                        <input type="text" id="sorecord-title" class="form-control" name="SoRecord[title]" maxlength="100" placeholder="<?=Yii::t('frontend','input_{value}',['value'=>Yii::t('frontend','question_title')])?>">
                    </div>
                    <div class="form-group field-sorecord-content required">
                        <textarea id="sorecord-content" class="form-control" name="SoRecord[content]" placeholder="<?=Yii::t('frontend','record_need')?>"></textarea>
                    </div>

                    <a id="exp_upload" href="javascript:void(0);" style="max-width: 40%" class="btn btn-sm btn-default lessWord"><?=Yii::t('frontend','enclosure')?></a>
                    <span class="upload-info" style="color:#008000;margin-left:5px;"></span>
                    <span>
                        <?=
                        Html::button(Yii::t('common', 'save'),
                            ['id' => 'saveBtn', 'class' => 'btn btn-success pull-right','onclick'=>'submitNoShare("recordExpForm");'])
                        ?>
                        <?=
                        Html::button(Yii::t('frontend', 'save_share'),
                            ['id' => 'saveShareBtn', 'class' => 'btn btn-default pull-right','style'=>'margin-right:15px;','onclick'=>'submitAndShare("recordExpForm");'])
                        ?>
                    </span>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
    $("#recordExpForm").on("submit", function (event) {
        event.preventDefault();

        var title = $("#recordExpForm #sorecord-title").val().trim();
        var content = $("#recordExpForm #sorecord-content").val().trim();

        if (title == '') {
            $("#recordExpForm #saveBtn").removeAttr("disabled");
            $("#recordExpForm #saveShareBtn").removeAttr("disabled");
            $("#recordExpForm #sorecord-title").focus();
            app.showMsg('<?=Yii::t('frontend','input_{value}',['value'=>Yii::t('frontend','question_title')])?>', 1500);
            return false;
        }
        if (content == '') {
            $("#recordExpForm #saveBtn").removeAttr("disabled");
            $("#recordExpForm #saveShareBtn").removeAttr("disabled");
            $("#recordExpForm #sorecord-content").focus();
            app.showMsg('<?=Yii::t('frontend','input_{value}',['value'=>Yii::t('frontend','record_content')])?>', 1500);
            return false;
        }
        submitModalForm("", "recordExpForm", "", true, false, null, null);
    });

    var ajaxUploadUrl = "<?=Url::toRoute(['student/upload'])?>";
    //异步上传文件
    new AjaxUpload("#exp_upload", {
        action: ajaxUploadUrl,
        type: "POST",
        name: 'myfile',
        data: {'_csrf': '<?= Yii::$app->request->csrfToken ?>'},
        onSubmit: function (file, ext) {
//            if ($(".text-info img").length > 0) {
//                $(".upload-info").html("<div style='color:#E3583B;margin:5px;'>" + "<?//=Yii::t('common', 'file_cropped')?>//" + "</div>");
//                return false;
//            }
            $("#recordExpForm #saveBtn").attr({"disabled":"disabled"});
            $("#recordExpForm #saveShareBtn").attr({"disabled":"disabled"});
            $("#recordExpForm .upload-info").html("<?=Yii::t('common', 'uploading')?>");
        },
        onComplete: function (file, response) {
            var result = JSON.parse(response);

            if (result.info == "<?=Yii::t('common', 'file_type_error')?>" || result.info == "<?=Yii::t('common', 'upload_error')?>") {
                $("#recordExpForm .upload-info").html(result.info);
            }
            else {
                //生成元素
                $("#exp_upload").html(result.filename);
                $('div:last').attr('title',result.filename);

                //传递参数上传
                $("#recordExpForm #sorecord-attach_original_filename").val(result.filename);
                $("#recordExpForm #sorecord-attach_url").val(result.info);
                //更新提示信息
                $("#recordExpForm .upload-info").html("<?=Yii::t('common', 'upload_completed')?>");
            }
            $("#recordExpForm #saveBtn").removeAttr("disabled");
            $("#recordExpForm #saveShareBtn").removeAttr("disabled");
        }
    });
</script>