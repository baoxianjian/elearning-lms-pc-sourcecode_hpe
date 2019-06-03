<?php
/**
 * Created by PhpStorm.
 * User: LiuCheng
 * Date: 2015/6/15
 * Time: 16:23
 */
use yii\helpers\Url;
use yii\helpers\Html;
?>
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title" id="myModalLabel"><?=Yii::t('frontend','book')?></h4>
        </div>
        <div class="modal-body">
            <div role="tabpanel" class="tab-pane  panel-body" id="motion">
                <form id="recordBookForm" class="shareInput" action="/student/index-tab-book.html" method="post">
                    <input type="hidden" name="_csrf" value="<?= Yii::$app->request->csrfToken ?>">
                    <h5><?=Yii::t('frontend','what_{value}_need',['value'=>Yii::t('frontend','book')])?></h5>
                    <input type="hidden" id="is_share" name="is_share" value="0">
                    <input type="hidden" id="sorecord-duration" class="form-control" name="SoRecord[duration]" value="">
                    <input type="hidden" id="sorecord-attach_original_filename" class="form-control" name="SoRecord[attach_original_filename]" value="">
                    <input type="hidden" id="sorecord-attach_url" class="form-control" name="SoRecord[attach_url]" value="">
                    <input type="hidden" id="sorecord-record_type" class="form-control" name="SoRecord[record_type]" value="2">
                    <div class="form-group field-sorecord-title required has-error">
                        <input type="text" id="sorecord-title" class="form-control" name="SoRecord[title]" maxlength="100" placeholder="<?=Yii::t('frontend','input_{value}',['value'=>Yii::t('frontend','question_title')])?>">
                    </div>
                    <div class="form-group field-sorecord-content required">
                        <textarea id="sorecord-content" class="form-control" name="SoRecord[content]" placeholder="<?=Yii::t('frontend','record_need')?>"></textarea>
                    </div>
                    <div class="form-group field-sorecord-url">
                        <input type="text" onchange="getUrlTitle(this,'recordBookForm')" id="sorecord-url" class="form-control" name="SoRecord[url]" maxlength="225" placeholder="<?=Yii::t('frontend','related_link')?> eg:http://...">
                    </div>
                    <div class="btn-group timeScope pull-left">
                        <button id="btn_dropdown" class="btn btn-default btn-sm dropdown-toggle" type="button" data-toggle="dropdown" aria-expanded="false"><?=Yii::t('frontend','duration_time')?> &nbsp;<span class="caret"></span>
                        </button>
                        <ul class="dropdown-menu">
                            <li><a href="javascript:void(0)" onclick="selectDuration('recordBookForm',this,5)">5<?=Yii::t('common','time_minute')?></a></li>
                            <li><a href="javascript:void(0)" onclick="selectDuration('recordBookForm',this,10)">10<?=Yii::t('common','time_minute')?></a></li>
                            <li><a href="javascript:void(0)" onclick="selectDuration('recordBookForm',this,30)">30<?=Yii::t('common','time_minute')?></a></li>
                            <li><a href="javascript:void(0)" onclick="selectDuration('recordBookForm',this,60)">1<?=Yii::t('common','time_hour')?></a></li>
                            <li><a href="javascript:void(0)" onclick="selectDuration('recordBookForm',this,120)">2<?=Yii::t('common','time_hour')?></a></li>
                            <li><a href="javascript:void(0)" onclick="selectDuration('recordBookForm',this,180)">3<?=Yii::t('common','time_hour')?></a></li>
                            <li><a href="javascript:void(0)" onclick="selectDuration('recordBookForm',this,240)">4<?=Yii::t('common','time_hour')?></a></li>
                            <li><a href="javascript:void(0)" onclick="selectDuration('recordBookForm',this,300)">5<?=Yii::t('common','time_hour')?></a></li>
                            <li><a href="javascript:void(0)" onclick="selectDuration('recordBookForm',this,360)">6<?=Yii::t('common','time_hour')?></a></li>
                            <li><a href="javascript:void(0)" onclick="selectDuration('recordBookForm',this,420)">7<?=Yii::t('common','time_hour')?></a></li>
                            <li><a href="javascript:void(0)" onclick="selectDuration('recordBookForm',this,480)">8<?=Yii::t('common','time_hour')?></a></li>
                            <li><a href="javascript:void(0)" onclick="selectDuration('recordBookForm',this,540)">9<?=Yii::t('common','time_hour')?></a></li>
                            <li><a href="javascript:void(0)" onclick="selectDuration('recordBookForm',this,600)">10<?=Yii::t('common','time_hour')?></a></li>
                            <li><a href="javascript:void(0)" onclick="selectDuration('recordBookForm',this,720)">0.5<?=Yii::t('common','time_day')?></a></li>
                            <li><a href="javascript:void(0)" onclick="selectDuration('recordBookForm',this,1440)">1<?=Yii::t('common','time_day')?></a></li>
                            <li><a href="javascript:void(0)" onclick="selectDuration('recordBookForm',this,2880)">2<?=Yii::t('common','time_day')?></a></li>
                        </ul>
                    </div>
                    <a id="book_upload" href="javascript:void(0);" style="max-width: 25%" class="btn btn-sm btn-default lessWord"><?=Yii::t('frontend','enclosure')?></a>
                    <span class="upload-info" style="color:#008000;margin-left:5px;"></span>
                    <span>
                        <?=
                        Html::button(Yii::t('common', 'save'),
                            ['id' => 'saveBtn', 'class' => 'btn btn-success pull-right','onclick'=>'submitNoShare("recordBookForm");'])
                        ?>
                        <?=
                        Html::button(Yii::t('frontend', 'save_share'),
                            ['id' => 'saveShareBtn', 'class' => 'btn btn-default pull-right','style'=>'margin-right:15px;','onclick'=>'submitAndShare("recordBookForm");'])
                        ?>
                    </span>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    $("#recordBookForm").on("submit", function (event) {
        event.preventDefault();
        var url = $("#recordBookForm #sorecord-url").val().trim();
        var title = $("#recordBookForm #sorecord-title").val().trim();
        var content = $("#recordBookForm #sorecord-content").val().trim();

        if (title == '') {
            $("#recordBookForm #saveBtn").removeAttr("disabled");
            $("#recordBookForm #saveShareBtn").removeAttr("disabled");
            $("#recordBookForm #sorecord-title").focus();
            app.showMsg('<?=Yii::t('frontend','input_{value}',['value'=>Yii::t('frontend','question_title')])?>', 1500);
            return false;
        }
        if (content == '') {
            $("#recordBookForm #saveBtn").removeAttr("disabled");
            $("#recordBookForm #saveShareBtn").removeAttr("disabled");
            $("#recordBookForm #sorecord-content").focus();
            app.showMsg('<?=Yii::t('frontend','input_{value}',['value'=>Yii::t('frontend','record_content')])?>', 1500);
            return false;
        }
        if (url != '') {
            var strRegex = '^((https|http|ftp|rtsp|mms)?://)'
                + '?(([0-9a-z_!~*\'().&=+$%-]+: )?[0-9a-z_!~*\'().&=+$%-]+@)?' //ftp的user@
                + '(([0-9]{1,3}.){3}[0-9]{1,3}' // IP形式的URL- 199.194.52.184
                + '|' // 允许IP和DOMAIN（域名）
                + '([0-9a-z_!~*\'()-]+.)*' // 域名- www.
                + '([0-9a-z][0-9a-z-]{0,61})?[0-9a-z].' // 二级域名
                + '[a-z]{2,6})' // first level domain- .com or .museum
                + '(:[0-9]{1,4})?' // 端口- :80
                + '((/?)|' // a slash isn't required if there is no file name
                + '(/[0-9a-z_!~*\'().;?:@&=+$,%#-]+)+/?)$';
            var pattern = new RegExp(strRegex);
            if (!pattern.test(url.toLowerCase())) {
                $("#recordBookForm #saveBtn").removeAttr("disabled");
                $("#recordBookForm #saveShareBtn").removeAttr("disabled");
                $("#recordBookForm #sorecord-url").focus();
                app.showMsg('<?=Yii::t('frontend','invalid_url')?>', 1500);
                return false;
            }
        }
        submitModalForm("", "recordBookForm", "", true, false, null, null);
    });

    var ajaxUploadUrl = "<?=Url::toRoute(['student/upload'])?>";
    //异步上传文件
    new AjaxUpload("#book_upload", {
        action: ajaxUploadUrl,
        type: "POST",
        name: 'myfile',
        data: {'_csrf': '<?= Yii::$app->request->csrfToken ?>'},
        onSubmit: function (file, ext) {
            $("#recordBookForm #saveBtn").attr({"disabled":"disabled"});
            $("#recordBookForm #saveShareBtn").attr({"disabled":"disabled"});
            $("#recordBookForm .upload-info").html("<?=Yii::t('common', 'uploading')?>");
        },
        onComplete: function (file, response) {
            var result = JSON.parse(response);

            if (result.info == "<?=Yii::t('common', 'file_type_error')?>" || result.info == "<?=Yii::t('common', 'upload_error')?>") {
                $("#recordBookForm .upload-info").html(result.info);
            }
            else {
                //生成元素
                $("#book_upload").html(result.filename);
                $('div:last').attr('title',result.filename);
                //传递参数上传
                $("#recordBookForm #sorecord-attach_original_filename").val(result.filename);
                $("#recordBookForm #sorecord-attach_url").val(result.info);
                //更新提示信息
                $("#recordBookForm .upload-info").html("<?=Yii::t('common', 'upload_completed')?>");
            }
            $("#recordBookForm #saveBtn").removeAttr("disabled");
            $("#recordBookForm #saveShareBtn").removeAttr("disabled");
        }
    });
</script>