<?php
/**
 * Created by PhpStorm.
 * User: LiuCheng
 * Date: 2015/6/26
 * Time: 10:08
 */
use yii\widgets\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;
use common\helpers\TStringHelper;
?>
<style type="text/css">
    .form {
        padding: 15px;
    }

    .jcorp-holder {
        position: relative;
    }

    #frm {
        margin-bottom: 0px;
    }

    #frm input {
        margin: 15px 0;
    }

    .pic-display {
        display: block;
        margin: 20px;
        width: auto;
    }

    #thum {
        width: auto;
    }

    /*#thum img{width: auto;height: auto;display: block;}*/
    #preview-pane {
        padding: 0;
        width: 150px;
        height: 150px;
        overflow: hidden;
        display: block;
        position: absolute;
        z-index: 2000;
        top: 10px;
        right: -170px;

        border: 1px rgba(0, 0, 0, .4) solid;
        background-color: white;

        -webkit-border-radius: 6px;
        -moz-border-radius: 6px;
        border-radius: 6px;

        -webkit-box-shadow: 1px 1px 5px 2px rgba(0, 0, 0, 0.2);
        -moz-box-shadow: 1px 1px 5px 2px rgba(0, 0, 0, 0.2);
        box-shadow: 1px 1px 5px 2px rgba(0, 0, 0, 0.2);
    }

    #preview-pane .preview-container {
        width: 150px;
        height: 150px;
        overflow: hidden;
        padding: 0;
        text-indent: 0;
    }

    .jcrop-preview {
        padding: 0;
        margin: 0;
        text-indent: 0;
    }

</style>
<div class=" panel-default scoreList">
    <div class="panel-body courseInfoInput">
        <?php $form = ActiveForm::begin([
            'id' => 'frm',
            'method' => 'post',
            'action' => '#'
        ]); ?>
        <div class="uploadFileTablePW row">
            <div class="col-md-12 col-sm-12">
                <div class="col-md-3 col-sm-5"><img id="img_thumb" src="<?= $model->getThumb() ?>" /></div>
                <div class="col-md-9 col-sm-7">
                    <p><?=Yii::t('common', 'headimg_url')?></p>
                    <p><?=Yii::t('frontend', 'tip_for_img_size')?></p>
                    <p><?=Yii::t('frontend', 'tip_for_img_type')?></p>
                </div>
            </div>
            <div class="col-md-12 col-sm-12">
                <input type="hidden" id="x" name="x"/>
                <input type="hidden" id="y" name="y"/>
                <input type="hidden" id="w" name="w"/>
                <input type="hidden" id="h" name="h"/>
                <input type="hidden" id="f" name="f"/>
                <?=
                Html::button(Yii::t('common', 'clear_setting'),
                    ['id' => 'clear', 'class' => 'btn btn-success pull-left'])
                ?>
                <?=
                Html::button(Yii::t('common', 'upload_thumb'),
                    ['id' => 'upload', 'class' => 'btn btn-success pull-left'])
                ?>
                <?=
                Html::button(Yii::t('common', 'confirm_crop'),
                    ['id' => 'crop', 'class' => 'btn btn-success pull-left'])
                ?>
            </div>
        </div>
        <?php ActiveForm::end(); ?>
        <div class="upload-info"></div>
        <div class="pic-display"></div>
        <div class="text-info"></div>
    </div>
</div>
<?= Html::cssFile('/static/common/css/jquery.Jcrop.css') ?>
<?= Html::jsFile('/static/common/js/ajaxupload.js') ?>
<?= Html::jsFile('/static/common/js/jquery.Jcrop.min.js') ?>
<script>
    $(document).ready(function () {
        $("#crop").hide();
    });
    var ajaxUploadUrl = "<?=Url::toRoute(['common/upload'])?>";
    var g_oJCrop = null;
    //alert(ajaxUploadUrl);
    //异步上传文件
    new AjaxUpload("#upload", {
        action: ajaxUploadUrl,
        type: "POST",
        name: 'myfile',
        data: {'_csrf': '<?= Yii::$app->request->csrfToken ?>'},
        onSubmit: function (file, ext) {
            if ($(".text-info img").length > 0) {
                $(".upload-info").html("<div style='color:#E3583B;margin:5px;'>" + "<?=Yii::t('common', 'file_cropped')?>" + "</div>");
                return false;
            }
            $(".upload-info").html("<div style='color:#008000;margin:5px;'>" + "<?=Yii::t('common', 'uploading')?>" + "</div>");
        },
        onComplete: function (file, response) {
            if (g_oJCrop != null) {
                g_oJCrop.destroy();
            }

            if (response == "<?=Yii::t('common', 'file_type_error')?>" || response == "<?=Yii::t('common', 'upload_error')?>") {
                $(".upload-info").html("<div style='color:#008000;margin:5px;'>" + response + "</div>");
                $("#crop").hide();
            }
            else {
                //生成元素
                $(".pic-display").html("<div class='thum'><img id='target' src='" + response + "'/></div>");

                //初始化裁剪区
                $('#target').Jcrop({
                    onChange: updatePreview,
                    onSelect: updatePreview,
                    aspectRatio: 1
                }, function () {
                    g_oJCrop = this;

                    //插入略缩图
                    $(".jcrop-holder").append("<div id='preview-pane'><div class='preview-container'><img  class='jcrop-preview' src='" + response + "' /></div></div>");

                    var bounds = g_oJCrop.getBounds();
                    var x1, y1, x2, y2;
                    if (bounds[0] / bounds[1] > 150 / 150) {
                        y1 = 0;
                        y2 = bounds[1];

                        x1 = (bounds[0] - 150 * bounds[1] / 150) / 2;
                        x2 = bounds[0] - x1;
                    }
                    else {
                        x1 = 0;
                        x2 = bounds[0];

                        y1 = (bounds[1] - 150 * bounds[0] / 150) / 2;
                        y2 = bounds[1] - y1;
                    }


                    g_oJCrop.setSelect([x1, y1, x2, y2]);

                });
                //传递参数上传
                $("#f").val(response);

                //更新提示信息
                $(".upload-info").html("<div style='color:#008000;margin:5px;'>" + "<?=Yii::t('common', 'prepare_crop')?>" + "</div>");

                $("#crop").show();
            }
        }
    });

    //更新裁剪图片信息
    function updatePreview(c) {

        if (parseInt(c.w) > 0) {
            $('#x').val(c.x);
            $('#y').val(c.y);
            $('#w').val(c.w);
            $('#h').val(c.h);
            var bounds = g_oJCrop.getBounds();

            var rx = 150 / c.w;
            var ry = 150 / c.h;

            $('.preview-container img').css({
                width: Math.round(rx * bounds[0]) + 'px',
                height: Math.round(ry * bounds[1]) + 'px',
                marginLeft: '-' + Math.round(rx * c.x) + 'px',
                marginTop: '-' + Math.round(ry * c.y) + 'px'
            });
        }
    }


    var ajaxCutPicUrl = "<?=Url::toRoute(['common/cut-pic'])?>";

    //表单异步提交后台裁剪
    $("#crop").click(function () {
        var w = parseInt($("#w").val());
        if (!w) {
            w = 0;
        }
        if (w > 0) {
            $.post(
                ajaxCutPicUrl,
                {
                    'x': $("input[name=x]").val(),
                    'y': $("input[name=y]").val(),
                    'w': $("input[name=w]").val(),
                    'h': $("input[name=h]").val(),
                    'f': $("input[name=f]").val(),
                    '_csrf': '<?= Yii::$app->request->csrfToken ?>'
                },
                function (data) {
                    //  alert(data.status);
                    if (data.status == 1) {
                        $(".pic-display").html("");
                        $(".upload-info").html("<div style='color:#008000;margin:5px;'>" + "<?=Yii::t('common', 'thumb_upload_ok')?>" + "</div>")
                        //                    $(".text-info").html("<img src='"+data.data+"'>");
                        $(".text-info").html("");
                        $("#img_thumb").attr('src', data.data);
                        $("#crop").hide();
                        //$("#upload").hide();
                    }

                }, 'json');
        } else {
            $(".upload-info").html("<div style='color:#E3583B;margin:5px;'>" + "<?=Yii::t('common', 'crop_area_select')?>" + "</div>");
        }
    });


    var ajaxClearPicUrl = "<?=Url::toRoute(['common/clear-pic'])?>";

    //表单异步提交后台裁剪
    $("#clear").click(function () {
        var msg = "<?=Yii::t('common','operation_confirm')?>";
        var gender = "<?=$model->gender ?>";
        var thumbUrl = '/static/common/images/man.jpeg';
        if (gender ==<?=Yii::t('common','gender_female')?> ) {
            thumbUrl = '/static/common/images/woman.jpeg';
        }
        NotyConfirm(msg, function (data) {
            $.post(
                ajaxClearPicUrl,
                {
                    '_csrf': '<?= Yii::$app->request->csrfToken ?>'
                },
                function (data) {
                    //  alert(data.status);
                    if (data.result == 'success') {
                        var msg = "<?=Yii::t('common','operation_success')?>";
                        app.showMsg(msg,1500);
                        $("#img_thumb").attr('src', thumbUrl);
                        $(".info").html("");
                        $(".pic-display").html("");
                        $(".text-info").html("");
                    }

                }, 'json');
        });

    });

</script>