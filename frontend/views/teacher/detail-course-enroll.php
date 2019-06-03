<?php
/**
 * User: zhanglei
 * Date: 2015/8/12
 * Time: 13:02
 */
use common\services\framework\DictionaryService;
use components\widgets\TLinkPager;
use frontend\viewmodels\message\SendMailForm;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

$dictionaryService = new DictionaryService();
?>
<script>
    function reloadForm()
    {
        //            alert("reloadForm");
        //        $.pjax.reload({container:"#grid"});
        //            $.pjax.reload({container:"#gridframe"});
        var ajaxUrl = courseAwardUrl;
        //        alert('ajaxUrl:'+ajaxUrl);
        ajaxUrl = urlreplace(ajaxUrl,'PageShowAll',$('#PageShowAll_grid').val());

        FmodalLoad('courseAward', ajaxUrl);
    }
</script>
<div class=" panel-default scoreList">
    <div class="panel-body">
        <div class="row" style="margin-top:20px;">
            <div class="col-md-7 col-sm-7">
            </div>
            <div class="col-md-5 col-sm-5">
                <div class="form-group" style="margin-bottom:0;">
                    <select class="form-control" id='sort' style="width:30%;">
                        <option value="1"><?= Yii::t('frontend', 'rank_by_position') ?></option>
                        <option value="2" <?if($param['sort']==2):?> selected <?endif;?>><?= Yii::t('frontend', 'rank_by_organization') ?></option>
                    </select>
                </div>
                <div class="input-group ">
                    <input type="text" id="keyword" class="form-control search_people" style="height: 30px;" placeholder="<?= Yii::t('common', 'real_name') ?>/<?= Yii::t('frontend', 'position') ?>/<?= Yii::t('frontend', 'department') ?>" <?if($param['keyword']):?> value="<?=$param['keyword']?>"<?endif;?>>
                    <span class="input-group-btn"><button class="btn btn-success btn-sm" id="student_search_btn" type="button"><?= Yii::t('frontend', 'top_search_text') ?></button><button id="btn_export" class="btn btn-success btn-sm " type="button" style="margin-left: 10px;    border-radius: 3px;"><?= Yii::t('frontend', 'export') ?></button></span>
                </div>
            </div>
        </div>
        <div class="nameList_table list_active">
            <table class="table table-bordered table-hover table-striped table-center">
                <tbody>
                <tr>
                    <td width="5%"><input id="check_all" type="checkbox" /></td>
                    <td width="10%"><?= Yii::t('common', 'real_name') ?></td>
                    <td width="28%"><?= Yii::t('common', 'department') ?></td>
                    <td width="20%"><?= Yii::t('common', 'position') ?></td>
                    <td width="16%"><?= Yii::t('common', 'user_email') ?></td>
                    <td width="12%"><?= Yii::t('common', 'mobile') ?></td>
                    <td width="9%"><?= Yii::t('common', 'action') ?></td>
                </tr>
                <? if (($num = count($students)) > 0): ?>
                    <? foreach ($students as $stu): ?>
                        <tr>
                            <td><input name="user_check" type="checkbox" data-kid="<?=$stu['user_id']?>" data-name="<?=$stu['real_name']?>" data-mail="<?=$stu['email']?>"/></td>
                            <td><?=$stu['real_name'] ?></td>
                           <!-- <td><?
                                if (isset($stu['location'])) {
                                    echo $dictionaryService->getDictionaryNameByCode("location", $stu['location']), '/';
                                }
                                echo $stu['orgnization_name'];
                                ?></td>
                                -->
                            <?
                            $orgName = '';
                            $orgFullName = '';
                            if ($stu['orgnization_name_path'] && strpos($stu['orgnization_name_path'], '/') !== false) {
                                $orgName = substr(strrchr($stu['orgnization_name_path'], '/'), 1) . '/' . $stu['orgnization_name'];
                                $orgFullName = $stu['orgnization_name_path'] . '/' . $stu['orgnization_name'];
                            } elseif ($stu['orgnization_name_path'] && strpos($stu['orgnization_name_path'], '/') === false) {
                                $orgFullName = $orgName = $stu['orgnization_name_path'] . '/' . $stu['orgnization_name'];
                            } else {
                                $orgFullName = $orgName = $stu['orgnization_name'];
                            }
                            ?>
                            <td><label title="<?= $orgFullName ?>"><?= $orgName ?></label></td>
                            <td><?=$stu['position_name']?></td>
                            <td><?=$stu['email']?></td>
                            <td><?=$stu['mobile_no']?></td>
                            <td><a href="javascript:void(0);" onclick="singleSendMail('<?=$stu['user_id']?>','<?=$stu['real_name']?>','<?=$stu['email']?>')"><?= Yii::t('frontend', 'send_mail') ?></a></td>
                        </tr>
                    <? endforeach;?>
                <? else: ?>
                    <tr>
                        <td colspan="7"><?= Yii::t('frontend', 'temp_no_data') ?></td>
                    </tr>
                <? endif; ?>
                </tbody>
            </table>
            <? if ($pages->totalCount > 0): ?>
                <div class="col-md-12">
                    <a id="btn_SendMail" href="javascript:void(0);" class="btn btn-default btn-sm pull-left" style="margin: 20px 10px 0 0;"><?= Yii::t('frontend', 'send_mail') ?></a>
                    <a id="btn_AllSendMail" href="javascript:void(0);" class="btn btn-default btn-sm pull-left" style="margin: 20px 10px 0 0;"" title="<?= Yii::t('frontend', '{value}_save_course_regular_student_to_audience', ['value' => $pages->totalCount]) ?>"><?= Yii::t('frontend', 'batch_send_mail') ?></a>
                    <nav style="text-align: right;">
                        <?php
                        echo TLinkPager::widget([
                            'id' => 'page',
                            'pagination' => $pages,
                            'displayPageSizeSelect' => false,
                        ]);
                        ?>
                        <?
                        if ($forceShowAll == 'True') {
                            $pageButton = Html::button(Yii::t('common', 'resize_current_button'), [
                                'title' => Yii::t('common', 'resize_current_button'), 'class' => 'btn btn-default resizeBtn',
                                'onclick' => 'ResizeCurrentButton();'
                            ]);
                        } else {
                            $pageButton = Html::button(Yii::t('common', 'resize_full_button'), [
                                'title' => Yii::t('common', 'resize_full_button'), 'class' => 'btn btn-default resizeBtn',
                                'onclick' => 'ResizeFullButton();'
                            ]);
                        }
                        ?>
                        <?echo $pageButton; ?>
                    </nav>
                    <input type="hidden" id="PageShowAll_grid" value="False"/>
                </div>
            <? endif; ?>
        </div>
    </div>
</div>
<!-- 发邮件的弹出窗口 -->
<div class="modal ui" id="sendMail">
    <div class="header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
        <h4 class="modal-title"><?= Yii::t('frontend', 'top_mail_text') ?></h4>
    </div>
    <div class="content">
        <?php
        $form = ActiveForm::begin([
            'id' => 'SendMailForm',
            'method' => 'post',
            'action' => Url::to(['common/send-mail']),
        ]);
        ?>
        <input id="sendUsers" name="SendMailForm[sendUsers]" type="hidden">
        <input name="SendMailForm[objectId]" type="hidden" value="<?= $id ?>">
        <input name="SendMailForm[scenes]" type="hidden" value="enroll">
        <div class="infoBlock">
            <div class="row">
                <div class="col-md-12 col-sm-12">
                    <div class="form-group form-group-sm">
                        <label class="col-sm-2 control-label"><?= Yii::t('frontend', 'recipient') ?></label>
                        <div class="col-sm-10">
                            <textarea id="user_list" style="min-height: auto !important;resize: none !important;" rows="1" readonly ></textarea>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12 col-sm-12">
                    <div class="form-group form-group-sm">
                        <label class="col-sm-2 control-label"><?= Yii::t('common', 'investigation_title') ?></label>
                        <div class="col-sm-10">
                            <input name="SendMailForm[title]" class="form-control" type="text" data-mode="COMMON" data-condition="required" data-alert="<?= Yii::t('common', 'field_required') ?>">
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12 col-sm-12">
                    <div class="form-group form-group-sm">
                        <label class="col-sm-2 control-label"><?= Yii::t('frontend', 'question_content') ?></label>
                        <div class="col-sm-10">
                            <textarea name="SendMailForm[content]" data-mode="COMMON" data-condition="required" data-alert="<?= Yii::t('common', 'field_required') ?>" style="resize: none !important;"></textarea>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12 col-sm-12">
                    <div class="form-group form-group-sm">
                        <label class="col-sm-2 control-label"><?= Yii::t('frontend', 'cc') ?></label>
                        <div class="col-sm-10">
                            <input id="ccEmail" name="SendMailForm[ccEmail]" class="form-control" type="text" style="width:45%;" placeholder="<?= Yii::t('frontend', 'rule_for_mail_address') ?>" data-mode="COMMON" data-alert="<?= Yii::t('frontend', 'wrong_format') ?>" data-delay="1">
                            <label style="margin-right:20px;">
                                <input type="checkbox" name="SendMailForm[ccSelf]" value="1"> <?= Yii::t('frontend', 'cc_self') ?>
                            </label>
                            <label>
                                <input type="checkbox" name="SendMailForm[ccManager]" value="1"> <?= Yii::t('frontend', 'cc_leader') ?>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12 col-sm-12">
                    <div class="form-group form-group-sm">
                        <label class="col-sm-2 control-label"><?= Yii::t('frontend', 'rank_by_organization') ?></label>
                        <div class="col-sm-10">
                            <label style="margin-right:20px;">
                                <input type="radio" name="SendMailForm[sendMethod]" value="0" checked> <?= Yii::t('frontend', 'send_more') ?>
                            </label>
                            <label style="margin-right:20px;">
                                <input type="radio" name="SendMailForm[sendMethod]" value="1"><?= Yii::t('frontend', 'send_independent') ?>
                            </label>
                            <label>
                                <input type="checkbox" name="SendMailForm[sendSMS]" value="1"> <?= Yii::t('frontend', 'send_and_msg') ?>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-12 col-sm-12 centerBtnArea">
                <a href="javascript:void(0);" class="btn btn-success btn-sm centerBtn" style="width:20%;" onclick="submitSendMail()"><?= Yii::t('frontend', 'send') ?></a>
            </div>
        </div>
        <?php ActiveForm::end(); ?>
        <div class="c"></div>
    </div>
    <div class="c"></div>
</div>
<script>
    var searchUrl = '<?=Yii::$app->urlManager->createUrl(['teacher/detail-course-enroll', 'id' => $id])?>';
    var sendUserArray;

    var validation = app.creatFormValidation($("#SendMailForm"));

    $("document").ready(function () {
        $('#student_search_btn').bind('click', function () {
            var sort = $('#sort').val();
            var keyword = $('#keyword').val();
            var inputdata = {sort: sort, keyword: keyword};
            ajaxGet(searchUrl, "courseAward", null, inputdata);
        });

        $("#check_all").click(function () {
            var status = $(this).is(':checked');
            $("input[name='user_check'][type='checkbox']").prop("checked", status);
        });

        $("#btn_SendMail").click(function () {
            sendUserArray = new Array();
            resetForm();

            var sendUserStr = "";
            var count = 0;
            $("input[name='user_check'][type='checkbox']:checked").each(function () {
                var chk = $(this);
                sendUserArray.push(chk.attr('data-kid'));
                sendUserStr += chk.attr('data-name') + "<" + chk.attr('data-mail') + ">" + ";";
                count++;
            });
            if (count === 0) {
                app.showMsg('<?= Yii::t('frontend', 'choose_send') ?>');
                return false;
            }

            $("#user_list").val(sendUserStr);
            if (2 < count && count < 4) {
                $("#user_list").attr('rows', 2);
            }
            else if (4 < count && count < 6) {
                $("#user_list").attr('rows', 3);
            }
            else if (6 < count) {
                $("#user_list").attr('rows', 4);
            }
            app.alert("#sendMail");
        });

        $("#btn_AllSendMail").click(function () {
            sendUserArray = "all";
            resetForm();

            var sendUserStr = "<?= Yii::t('frontend', 'all_student') ?>;";

            $("#user_list").val(sendUserStr);
            $("#user_list").attr('rows', 1);

            app.alert("#sendMail");
        });
    });

    var export_url = "<?=Url::toRoute(['/resource/course/export-course-enroll-user', 'id' => $id, 'type' => 'teacher'])?>";

    $("#btn_export").click(function () {
        var temp_url = export_url;
        var sort = $("#enroll_success select[name='sort']").val();
        var key = $("#enroll_success select[name='keyword']").val();
        if (sort) {
            temp_url = urlreplace(temp_url, 'sort', sort);
        }
        if (key) {
            temp_url = urlreplace(temp_url, 'keyword', key);
        }

        window.location.target = "_blank";
        window.location.href = temp_url + "&ran=" + Math.random();
    });

    $(function () {
        $(".pagination").on('click', 'a', function (e) {
            e.preventDefault();
            ajaxGet($(this).attr('href'), "courseAward");
        });
    });

    function singleSendMail(uid, name, email) {
        sendUserArray = new Array(uid);
        resetForm();
        $("#user_list").val(name + "<" + email + ">");
        $("#user_list").attr('rows', 1);
        app.alert("#sendMail");
    }

    function submitSendMail() {
        if (!validation.validate()) {
            return false;
        }

        var cc = $("#ccEmail").val();
        if (cc && !checkEmails(cc)) {
            validation.showAlert("#ccEmail", "<?= Yii::t('frontend', 'wrong_format') ?>");
            return false;
        }

        $("#sendUsers").val(sendUserArray);
        submitForm("SendMailForm");
    }

    function ReloadPageAfterUpdate() {
        app.hideAlert("#sendMail");
        app.showMsg('<?=Yii::t('common', 'operation_success')?>');
    }

    function checkEmails(emails) {
        var _isEmailRegs = [/^\S+@(\S+)$/, /^(?:[A-Za-z0-9-]+\.)+[A-Za-z]{2,6}$/], checked = true;
        emails.split(';').forEach(function (email) {
            var _email = String(email).trim(), r = _isEmailRegs;
            checked = checked && email === _email && email.length < 255 && r[0].test(email) && r[1].test(RegExp['$1']);
        });
        return checked
    }

    function resetForm() {
        $("#SendMailForm")[0].reset();
    }
</script>