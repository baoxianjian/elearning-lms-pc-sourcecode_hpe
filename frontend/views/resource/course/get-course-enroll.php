<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/9/9
 * Time: 16:46
 */
use components\widgets\TLinkPager;
use yii\helpers\Html;
use yii\helpers\Url;
use common\models\learning\LnCourseEnroll;
use common\services\framework\DictionaryService;
use yii\widgets\ActiveForm;

$dictionaryService = new DictionaryService();
?>
<script>
    function reloadForm() {
        var ajaxUrl = enroll_url;
        ajaxUrl = urlreplace(ajaxUrl, 'PageShowAll', $('#PageShowAll_grid').val());

        var sort = $("#enroll_success select[name='sort']").val();
        var filter = $("#enroll_success select[name='filter']").val();
        var keyword = $("#enroll_success input[name='keyword']").val().trim();

        if (sort) {
            ajaxUrl = urlreplace(ajaxUrl, 'sort', sort);
        }
        if (filter) {
            ajaxUrl = urlreplace(ajaxUrl, 'filter', filter);
        }
        if (keyword) {
            ajaxUrl = urlreplace(ajaxUrl, 'keyword', keyword);
        }

        getData(ajaxUrl, 'enroll_success');
    }
</script>
<div class="row" style="margin-top:20px;">
    <div class="col-md-12 col-sm-12">
        <h5><?=date('Y年m月d日', $model->enroll_start_time)?> ~ <?=date('m月d日', $model->enroll_end_time)?>, <?= Yii::t('frontend', 'enroll_people_number_{value}',['value'=>'<span class="reg_number">'.$enroll[4].'</span>']) ?>, <?=$model->is_allow_over? Yii::t('frontend', 'candidates_people_number_{value}',['value'=>'<span class="hx_number">'.($model->allow_over_number-$enroll[2]).'</span>,']):''?> <?= Yii::t('frontend', 'approved_people_{value}',['value'=>'<span class="allow_number">'.$enroll[1].'</span>']) ?></h5>
    </div>
    <div class="col-md-5 col-sm-5">
        <label class="pull-left" style="height: 30px; line-height: 30px;display: inline-block;vertical-align: text-bottom; "><?= Yii::t('frontend', 'add_by_manual') ?>:</label>
        <input type="text" class="form-control" placeholder="<?= Yii::t('frontend', 'input_{value}',['value'=>Yii::t('common','real_name')]) ?>" style="width:160px;" id="adduser" data-url="<?=Url::to(['/common/get-user','format'=>'new','course_id'=>"$model->kid"])?>" data-mult="1" autocomplete="on" >
        <button type="button" id="issure" class="btn btn-success btn-sm" style="vertical-align: top;"><?= Yii::t('frontend', 'be_sure') ?></button>
    </div>
    <div class="col-md-7 col-sm-7">
        <div class="form-group" style="margin-bottom:0;">
            <select class="form-control" name="filter" style="width:50%;">
                <option value="1" <?=isset($params['filter']) && $params['filter']=='1'?'selected':''?>><?= Yii::t('frontend', 'all_student') ?></option>
                <option value="2" <?=isset($params['filter']) && $params['filter']=='2'?'selected':''?>><?= Yii::t('frontend', 'not_reviewed') ?></option>
                <option value="3" <?=isset($params['filter']) && $params['filter']=='3'?'selected':''?>><?= Yii::t('frontend', 'passed') ?></option>
                <option value="4" <?=isset($params['filter']) && $params['filter']=='4'?'selected':''?>><?= Yii::t('frontend', 'manager_rejected') ?></option>
                <option value="5" <?=isset($params['filter']) && $params['filter']=='5'?'selected':''?>><?= Yii::t('frontend', 'admin_rejected') ?></option>
            </select>
        </div>
        <div class="input-group">
            <input type="text" name="keyword" class="form-control search_people" style="height: 30px;" placeholder="<?= Yii::t('common', 'real_name') ?>/<?= Yii::t('common', 'position') ?>/<?= Yii::t('common', 'department') ?>" value="<?=isset($params['keyword'])?$params['keyword']:''?>">
            <span class="input-group-btn"><button class="btn btn-success btn-sm searchBtn" type="button" ><?= Yii::t('frontend', 'top_search_text') ?></button><button id="btn_export" class="btn btn-success btn-sm " type="button" style="margin-left: 10px;    border-radius: 3px;"><?= Yii::t('frontend', 'export') ?></button></span>
        </div>
    </div>
</div>
<?php
if (!empty($result['data'])) {
    ?>
    <div class="nameList_view">
        <div class="nameList_table list_active">
            <table class="table table-bordered table-hover table-striped table-center">
                <tbody>
                <tr>
                    <td width="5%"><input id="check_all" type="checkbox" /></td>
                    <td width="10%"><?= Yii::t('common', 'real_name') ?></td>
                    <td width="10%"><?= Yii::t('frontend', 'work_number') ?></td>
                    <td width="15%"><?= Yii::t('frontend', 'email') ?></td>
                    <td width="20%"><?= Yii::t('common', 'department') ?></td>
                    <td width="10%"><?= Yii::t('common', 'position') ?></td>
                    <td width="15%"><?= Yii::t('frontend', 'enroll_time') ?></td>
                    <td width="15%"><?= Yii::t('common', 'action') ?></td>
                </tr>
                <?php
                foreach ($result['data'] as $key => $val) {
                    ?>
                    <tr>
                        <td>
                            <? if ($val['approved_state'] === LnCourseEnroll::APPROVED_STATE_APPLING): ?>
                                <input name="user_check" type="checkbox" data-kid="<?=$val['kid']?>" data-uid="<?=$val['user_id']?>" data-name="<?=$val['real_name']?>" data-mail="<?=$val['email']?>"/>
                            <? elseif ($val['enroll_type'] === LnCourseEnroll::ENROLL_TYPE_REG): ?>
                                <input name="user_check_reg" type="checkbox" data-kid="<?=$val['kid']?>" data-uid="<?=$val['user_id']?>" data-name="<?=$val['real_name']?>" data-mail="<?=$val['email']?>"/>
                            <? else: ?>
                                <input name="user_check" type="checkbox" data-kid="<?=$val['kid']?>" data-uid="<?=$val['user_id']?>" data-name="<?=$val['real_name']?>" data-mail="<?=$val['email']?>"/>
                            <? endif; ?>
                        </td>
                        <td><?= $val['real_name'] ?></td>
                        <td><?= $val['user_no'] ?></td>
                        <td><?= $val['email'] ?></td>
                        <?
                        $orgName = '';
                        $orgFullName = '';
                        if ($val['orgnization_name_path'] && strpos($val['orgnization_name_path'], '/') !== false) {
                            $orgName = substr(strrchr($val['orgnization_name_path'], '/'), 1) . '/' . $val['orgnization_name'];
                            $orgFullName = $val['orgnization_name_path'] . '/' . $val['orgnization_name'];
                        } elseif ($val['orgnization_name_path'] && strpos($val['orgnization_name_path'], '/') === false) {
                            $orgFullName = $orgName = $val['orgnization_name_path'] . '/' . $val['orgnization_name'];
                        } else {
                            $orgFullName = $orgName = $val['orgnization_name'];
                        }
                        ?>
                        <td><label title="<?= $orgFullName ?>"><?= $orgName ?></label></td>
                        <td><?= $val['position_name'] ?></td>
                        <td><?= date('Y年m月d日', $val['enroll_time']) ?></td>
                        <td align="left">
                            <div class="controlBtns">
                                <?php
                                if ($val['approved_state'] == LnCourseEnroll::APPROVED_STATE_APPLING){
                                    ?>
                                    <a href="###" class="btn btn-default btn-sm approval" data-courseId="<?=$model->kid?>" data-uid="<?= $val['user_id'] ?>"><?= Yii::t('common', 'approval') ?></a>
                                    <?php
                                }else if ($val['approved_state'] == LnCourseEnroll::APPROVED_STATE_APPROVED) {
                                    if ($val['enroll_type'] == LnCourseEnroll::ENROLL_TYPE_REG) {
                                        ?>
                                        <a href="###" class="btn btn-success btn-sm btn_allow" data-type="<?= LnCourseEnroll::ENROLL_TYPE_ALLOW ?>" data-id="<?= $val['kid'] ?>" id="allow_<?= $val['kid'] ?>" data-click="true"><?= Yii::t('frontend', 'adopt') ?></a>
                                        <a href="###" class="btn btn-default btn-sm btn_disallow" data-type="<?= LnCourseEnroll::ENROLL_TYPE_DISALLOW ?>" data-id="<?= $val['kid'] ?>" id="disallow_<?= $val['kid'] ?>" data-click="true"><?= Yii::t('frontend', 'refuse') ?></a>
                                        <? if($val['enroll_method'] == LnCourseEnroll::ENROLL_METHOD_MANUAL):?>
                                            <a href="###" class="btn btn-danger btn-sm delBtn" data-id="<?=$val['kid']?>"  id="del_<?= $val['kid'] ?>" data-click="true"><?= Yii::t('common', 'delete_button') ?></a>
                                        <? endif;?>
                                        <?php
                                    } elseif ($val['enroll_type'] == LnCourseEnroll::ENROLL_TYPE_ALLOW) {
                                        ?>
                                        <a href="###" class="btn btn-default btn-sm"><?= Yii::t('frontend', 'passed') ?></a>
                                        <?
                                    } elseif ($val['enroll_type'] == LnCourseEnroll::ENROLL_TYPE_ALTERNATE) {
                                        ?>
                                        <a href="###" class="btn btn-success btn-sm btn_in" data-type="<?= LnCourseEnroll::ENROLL_TYPE_REG ?>" data-id="<?= $val['kid'] ?>" id="in_<?= $val['kid'] ?>" data-click="true"><?= Yii::t('frontend', 'add_entroll') ?></a>
                                        <?php
                                    } elseif ($val['enroll_type'] == LnCourseEnroll::ENROLL_TYPE_DISALLOW) {
                                        ?>
                                        <a href="###" class="btn btn-default btn-sm"><?= Yii::t('frontend', 'refused') ?></a>
                                        <?php
                                    }
                                }else if ($val['approved_state'] == LnCourseEnroll::APPROVED_STATE_REJECTED){
                                    ?>
                                    <a href="###" class="btn btn-default btn-sm"><?= Yii::t('frontend', 'approval_no_pass') ?></a>
                                    <?php
                                }else if ($val['approved_state'] == LnCourseEnroll::APPROVED_STATE_CANCELED){
                                    ?>
                                    <a href="###" class="btn btn-default btn-sm"><?= Yii::t('frontend', 'invalid') ?></a>
                                    <?php
                                }
                                ?>
                                <!--
                                <a href="javascript:void(0);" class="btn btn-success btn-sm" onclick="singleSendMail('<?=$val['user_id']?>','<?=$val['real_name']?>','<?=$val['email']?>')"><?= Yii::t('frontend', 'send_mail') ?></a>
                            -->
                            </div>
                        </td>
                        <input type="hidden" name="userid" value="<?= $val['user_id'] ?>">
                    </tr>
                    <?php
                }
                ?>
                </tbody>
            </table>

        </div>
        <div class="fc-clear"></div>
        <div class="col-md-12">
            <a id="btn_Allow" href="javascript:void(0);" class="btn btn-success btn-sm pull-left" style="margin: 20px 10px 0 0;"><?= Yii::t('frontend', 'adopt') ?></a>
            <a id="btn_Reject" href="javascript:void(0);" class="btn btn-default btn-sm pull-left" style="margin: 20px 10px 0 0;"><?= Yii::t('frontend', 'refuse') ?></a>
            <a id="btn_SendMail" href="javascript:void(0);" class="btn btn-default btn-sm pull-left" style="margin: 20px 10px 0 0;"><?= Yii::t('frontend', 'send_mail') ?></a>
            <button id="btn_AllSendMail" class="btn btn-default btn-sm pull-left" style="margin: 20px 10px 0 0;" title="<?= Yii::t('frontend', '{value}_send_mail_to_regular_student', ['value' => $regularCount]) ?>"><?= Yii::t('frontend', 'batch_send_mail') ?></button>
            <button id="btn_SaveToAudience" class="btn btn-default btn-sm pull-left" style="margin: 20px 10px 0 0;" title="<?= Yii::t('frontend', '{value}_save_course_regular_student_to_audience', ['value' => $regularCount]) ?>"><?= Yii::t('frontend', 'save_to_audience') ?></button>
            <nav style="background-color: transparent!important;text-align: right;">
                <?php
                if (!empty($result['pages'])) {
                    echo TLinkPager::widget([
                        'id' => 'page',
                        'pagination' => $result['pages'],
                        'displayPageSizeSelect' => false
                    ]);
                }
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
    </div>
    <script>
        var enroll_url = "<?=Url::toRoute(['/resource/course/get-course-enroll', 'id' => $model->kid, 'enroll_type' => LnCourseEnroll::ENROLL_TYPE_REG])?>";
        var enroll_url2 = "<?=Url::toRoute(['/resource/course/get-course-enroll', 'id' => $model->kid, 'enroll_type' => LnCourseEnroll::ENROLL_TYPE_ALTERNATE])?>";
        var set_url = "<?=Url::toRoute(['/resource/course/set-course-enroll-status'])?>";
        var move_url = "<?=Url::toRoute(['/resource/course/move-course-enroll'])?>";
        var manual_enroll_url = "<?=Url::toRoute(['/resource/course/manual-enroll', 'id' => $model->kid])?>";
        var enroll_id = "<?=Url::toRoute(['/resource/course/get-course-reg-id'])?>";
        var batch_set_url = "<?=Url::toRoute(['/resource/course/batch-set-course-enroll-status'])?>";

        var allSendMailTitle="<?=Yii::t('frontend', '{value}_send_mail_to_regular_student')?>";
        var saveToAudienceTitle="<?=Yii::t('frontend', '{value}_save_course_regular_student_to_audience')?>";

        var url;
        var shiftList_statu = <?=$type?>;
        var regularCount = "<?= $regularCount ?>";
        var  common_adduser = app.queryList("#adduser");
        $('#issure').on('click',function(){
            var userid = document.getElementsByName("userid");
            var struserids = [];
            for (i = 0; i < userid.length; i++) {
                struserids[i] = userid[i].value ;
            }

            var user = [];
            var html;
            var user_json = common_adduser.get();
            if (typeof user_json != 'undefined') {
                var user_length = user_json.length;
                if (user_length > 0) {
                    var userIds = new Array();

                    for (var i = 0; i < user_length; i++) {
                        if ($.inArray(user_json[i]['kid'], struserids) == -1) {
                            userIds.push(user_json[i]['kid']);
                        }
                    }

                    $.post(manual_enroll_url, {userIds: userIds}, function (response) {
                        if (response.result == 'success') {
                            app.showMsg('<?= Yii::t('common', 'operation_success') ?>');
                            loadPage(enroll_url, 'enroll_success', false);
                        } else {
                            app.showMsg(response.errmsg);
                        }
                    }, 'json');
                }
            }
        });
        var export_url = "<?=Url::toRoute(['/resource/course/export-course-enroll-user', 'id' => $model->kid])?>";

        $("#btn_export").click(function () {
            var temp_url = export_url;
            var sort = $("#enroll_success select[name='sort']").val();
            var filter = $("#enroll_success select[name='filter']").val();
            var keyword = $("#enroll_success input[name='keyword']").val().trim();

            if (sort) {
                temp_url = urlreplace(temp_url, 'sort', sort);
            }
            if (filter) {
                temp_url = urlreplace(temp_url, 'filter', filter);
            }
            if (keyword) {
                temp_url = urlreplace(temp_url, 'keyword', keyword);
            }

            window.location.target = "_blank";
            window.location.href = temp_url + "&ran=" + Math.random();
        });

        var sendUserArray,enrollIdArray;
        var validation = app.creatFormValidation($("#SendMailForm"));

        $(function(){
            if (regularCount === "0") {
                $("#btn_AllSendMail").attr({"disabled":"disabled"});
                $("#btn_SaveToAudience").attr({"disabled":"disabled"});
            }

            $("#check_all").click(function () {
                var status = $(this).is(':checked');
                $("input[name*='user_check'][type='checkbox']").prop("checked", status);
            });

            $("#btn_Allow").click(function () {
                $("#btn_Allow").attr({"disabled":"disabled"});
                enrollIdArray = new Array();
//                resetForm();

                var count = 0;
                $("input[name='user_check_reg'][type='checkbox']:checked").each(function () {
                    var chk = $(this);
                    enrollIdArray.push(chk.attr('data-kid'));
                    count++;
                });
                if (count === 0) {
                    app.showMsg('<?= Yii::t('frontend', 'please_select_handle_object') ?>');
                    $("#btn_Allow").removeAttr("disabled");
                    return false;
                }
                app.showLoadingMsg('<?=Yii::t('frontend', 'data_get_ready')?>');
                $.post(batch_set_url, {cid:"<?=$model->kid?>", ids: enrollIdArray, type: "<?=LnCourseEnroll::ENROLL_TYPE_ALLOW?>"}, function (response) {
                    if (response.result == 'success') {
                        app.showMsg('<?= Yii::t('frontend', 'passed') ?>');
                            loadPage(enroll_url, 'enroll_success', false);
                    } else {
                        app.showMsg(response.msg);
                    }
                    app.hideLoadingMsg();
                    $("#btn_Allow").removeAttr("disabled");
                }, 'json');
            });

            $("#btn_Reject").click(function () {
                $("#btn_Reject").attr({"disabled":"disabled"});
                enrollIdArray = new Array();

                var count = 0;
                $("input[name='user_check_reg'][type='checkbox']:checked").each(function () {
                    var chk = $(this);
                    enrollIdArray.push(chk.attr('data-kid'));
                    count++;
                });
                if (count === 0) {
                    app.showMsg('<?= Yii::t('frontend', 'please_select_handle_object') ?>');
                    $("#btn_Reject").removeAttr("disabled");
                    return false;
                }
                app.showLoadingMsg('<?= Yii::t('frontend', 'data_get_ready') ?>...');
                $.post(batch_set_url, {cid:"<?=$model->kid?>", ids: enrollIdArray, type: "<?=LnCourseEnroll::ENROLL_TYPE_DISALLOW?>"}, function (response) {
                    if (response.result == 'success') {
                        app.showMsg('<?= Yii::t('frontend', 'refused') ?>');
                        loadPage(enroll_url, 'enroll_success', false);
                    } else {
                        app.showMsg(response.msg);
                    }
                    app.hideLoadingMsg();
                    $("#btn_Reject").removeAttr("disabled");
                }, 'json');
            });

            $('.btn_allow').click(function (e) {
                if ($(this).attr('data-click') == 'false') return false;
                $(this).attr('data-click','false');
                $(this).attr({"disabled":"disabled"});
                var id = $(this).attr('id');
                var dataId=$(this).attr('data-id');
                $.post(set_url, {id: dataId, type: $(this).attr('data-type')}, function (response) {
                    if (response.result == 'success') {
                        app.showMsg('<?= Yii::t('frontend', 'passed') ?>');
                        //    loadPage(enroll_url, 'enroll_success', true);

                        $("#del_" + dataId).remove();
                        $("#" + id).removeClass('btn btn-success').removeClass('btn_allow').text('<?= Yii::t('frontend', 'passed') ?>');
                        $("#" + id).next('.btn-default').remove();

                        var allow_number = parseInt($(".allow_number").eq(0).html()) + 1;
                        $(".allow_number").html(parseInt($(".allow_number").eq(0).html()) + 1);

                        $("#btn_AllSendMail").attr('title', allSendMailTitle.replace("{value}", allow_number));
                        $("#btn_SaveToAudience").attr('title', saveToAudienceTitle.replace("{value}", allow_number));

                        $("#" + id).parent().parent().parent().find("[name='user_check_reg']").attr("name", 'user_check');

                        $("#" + id).attr('class', 'btn btn-sm btn-default').attr('data-click', 'false');

                        if (regularCount === "0") {
                            $("#btn_AllSendMail").removeAttr("disabled");
                            $("#btn_SaveToAudience").removeAttr("disabled");
                            regularCount = "1";
                        }
//                        $.get('<?//=Url::toRoute(['/resource/course/enroll-send-email'])?>//',{courseId: response.courseId, userId: response.userId, status: response.status},function(html){
//                        });
                    } else {
                        app.showMsg(response.errmsg);
                    }
                    $("#" + id).removeAttr("disabled");
                }, 'json');
            });
            $('.btn_disallow').click(function (e) {
                if ($(this).attr('data-click') == 'false') return false;
                $(this).attr('data-click','false');
                $(this).attr({"disabled":"disabled"});
                var id = $(this).attr('id');
                var dataId = $(this).attr('data-id');
                var type = $(this).attr('data-type');
                NotyConfirm('<?= Yii::t('common', 'operation_confirm') ?>',  function() {
                    $.post(set_url, {id: dataId, type: type}, function (response) {
                        if (response.result == 'success') {
                            app.showMsg('<?= Yii::t('frontend', 'refused') ?>');
                            //  loadPage(enroll_url, 'enroll_success', true);

                            $("#del_" + dataId).remove();
                            $("#" + id).removeClass('btn btn-default').removeClass('btn_disallow').text('<?= Yii::t('frontend', 'refused') ?>');
                            $("#" + id).prev('.btn-success').remove();
                            $(".reg_number").html(parseInt($(".reg_number").eq(0).html()) + 1);

                            $("#" + id).parent().parent().parent().find("[name='user_check_reg']").attr("name",'user_check');

                            $("#" + id).attr('class', 'btn btn-sm btn-default').attr('data-click', 'false');
//                            $.get('<?//=Url::toRoute(['/resource/course/enroll-send-email'])?>//', {
//                                courseId: response.courseId,
//                                userId: response.userId,
//                                status: response.status
//                            }, function (html) {
//                            });
                        } else {
                            app.showMsg(response.errmsg);
                        }
                    }, 'json');;
                });
                $(this).attr('data-click','true');
                $("#" + id).removeAttr("disabled")
            });
            /*审批*/
            $(".approval").bind('click', function(){
                var courseId = $(this).attr('data-courseId');
                var userId = $(this).attr('data-uid');
                $.get('<?=Url::toRoute('/resource/course/course-approval')?>', {courseId: courseId, userId: userId}, function(data){
                    if (data.result == 'success'){
                        // loadPage(enroll_url, 'enroll_success', true);

                        $.get(enroll_url, function(html){
                            if (html) {
                                $('#enroll_success').html(html)
                            }
                        });
                    }else{
                        app.showMsg(data.errmsg);
                        return false;
                    }
                }, 'json');
            });

            $("#btn_SendMail").click(function () {
                sendUserArray = new Array();
                resetForm();

                var sendUserStr = "";
                var count = 0;
                $("input[name*='user_check'][type='checkbox']:checked").each(function () {
                    var chk = $(this);
                    sendUserArray.push(chk.attr('data-uid'));
                    sendUserStr += chk.attr('data-name') + "<" + chk.attr('data-mail') + ">" + ";";
                    count++;
                });
                if (count === 0) {
                    app.showMsg('<?= Yii::t('frontend', 'please_select_send_object') ?>');
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

            $("#btn_SaveToAudience").click(function () {
                var name = "<?= Html::encode($model->course_name) ?>";
                app.get("<?=Url::to(['common/save-to-audience'])?>?courseName=" + encodeURIComponent(name) + "&courseId=<?=$model->kid?>", function (d) {
                    $("#saveGroup").html(d);
                    app.alert("#saveGroup");
                });
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
                validation.showAlert("#ccEmail", "<?= Yii::t('frontend', 'format_incorrect') ?>");
                return false;
            }

            $("#sendUsers").val(sendUserArray);
            submitForm("SendMailForm");
        }

        function ReloadPageAfterUpdate() {
            app.hideAlert("#sendMail");
            app.hideAlert("#saveGroup");
            app.showMsg('<?= Yii::t('common', 'operation_success') ?>');
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
    <?php
}else{
    ?>
    <div class="nameList_view">
        <div class="nameList_table list_active">
            <table class="table table-bordered table-hover table-striped table-center">
                <tbody>
                <tr>
                    <td width="15%"><?= Yii::t('common', 'real_name') ?></td>
                    <td width="15%"><?= Yii::t('frontend', 'work_number') ?></td>
                    <td width="20%"><?= Yii::t('common', 'department') ?></td>
                    <td width="10%"><?= Yii::t('common', 'position') ?></td>
                    <td width="20%"><?= Yii::t('frontend', 'enroll_time') ?></td>
                    <td width="20%"><?= Yii::t('common', 'action') ?></td>
                </tr>
                <tr>
                    <td colspan="6">
                        <?=Yii::t('frontend','temp_no_data')?>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
    <script>
        var enroll_url = "<?=Url::toRoute(['/resource/course/get-course-enroll', 'id'=>$model->kid,'enroll_type'=>LnCourseEnroll::ENROLL_TYPE_REG])?>";
        var enroll_url2 = "<?=Url::toRoute(['/resource/course/get-course-enroll', 'id'=>$model->kid,'enroll_type'=>LnCourseEnroll::ENROLL_TYPE_ALTERNATE])?>";
        var set_url = "<?=Url::toRoute(['/resource/course/set-course-enroll-status'])?>";
        var move_url = "<?=Url::toRoute(['/resource/course/move-course-enroll'])?>";
        var manual_enroll_url = "<?=Url::toRoute(['/resource/course/manual-enroll', 'id' => $model->kid])?>";
        var enroll_id = "<?=Url::toRoute(['/resource/course/get-course-reg-id'])?>";
        var url;
        var shiftList_statu = <?=$type?>;
        var  common_adduser = app.queryList("#adduser");
        $('#issure').on('click',function(){
            var userid = document.getElementsByName("userid");
            var struserids = [];
            for (i = 0; i < userid.length; i++) {
                struserids[i] = userid[i].value ;
            }

            var user = [];
            var html;
            var user_json = common_adduser.get();
            if (typeof user_json != 'undefined') {
                var user_length = user_json.length;
                if (user_length > 0) {
                    var userIds = new Array();

                    for (var i = 0; i < user_length; i++) {
                        if ($.inArray(user_json[i]['kid'], struserids) == -1) {
                            userIds.push(user_json[i]['kid']);
                        }
                    }

                    $.post(manual_enroll_url, {userIds: userIds}, function (response) {
                        if (response.result == 'success') {
                            app.showMsg('<?= Yii::t('common', 'operation_success') ?>');
                            loadPage(enroll_url, 'enroll_success', false);
                        } else {
                            app.showMsg(response.errmsg);
                        }
                    }, 'json');
                }
            }
        });

        $(function() {
            if (regularCount === "0") {
                $("#btn_AllSendMail").attr({"disabled":"disabled"});
                $("#btn_SaveToAudience").attr({"disabled":"disabled"});
            }

            $('.btn_allow').click(function (e) {
                if ($(this).attr('data-click') == 'false') return false;
                $(this).attr({"disabled":"disabled"});
                var id = $(this).attr('id');
                var dataId=$(this).attr('data-id');
                $.post(set_url, {id: dataId, type: $(this).attr('data-type')}, function (response) {
                    if (response.result == 'success') {
                        app.showMsg('<?= Yii::t('frontend', 'passed') ?>');
                        $("#del_"+dataId).remove();
                        $("#" + id).removeClass('btn btn-success').removeClass('btn_allow').text('<?= Yii::t('frontend', 'passed') ?>');
                        $("#" + id).next('.btn-default').remove();
                        $(".allow_number").html(parseInt($(".allow_number").eq(0).html())+1);
                        $("#"+id).attr('class', 'btn btn-sm btn-default').attr('data-click', 'false');
//                        loadPage(enroll_url, 'enroll_success', true);

//                        $.get('<?//=Url::toRoute(['/resource/course/enroll-send-email'])?>//',{courseId: response.courseId, userId: response.userId, status: response.status},function(html){
//                        });
                    } else {
                        app.showMsg(response.errmsg);
                    }
                    $("#" + id).removeAttr("disabled");
                }, 'json');
            });
            $('.btn_disallow').click(function (e) {
                if ($(this).attr('data-click') == 'false') return false;
                $(this).attr({"disabled":"disabled"});
                var id = $(this).attr('id');
                var dataId = $(this).attr('data-id');
                var type = $(this).attr('data-type');
                NotyConfirm('<?= Yii::t('common', 'operation_confirm') ?>',  function() {
                    $.post(set_url, {id: dataId, type: type}, function (response) {
                        if (response.result == 'success') {
                            app.showMsg('<?= Yii::t('frontend', 'refused') ?>');
                            $("#del_" + dataId).remove();
                            $("#" + id).removeClass('btn btn-default').removeClass('btn_disallow').text('<?= Yii::t('frontend', 'refused') ?>');
                            $("#" + id).prev('.btn-success').remove();
                            $(".reg_number").html(parseInt($(".reg_number").eq(0).html()) + 1);
                            $("#" + id).attr('class', 'btn btn-sm btn-default').attr('data-click', 'false');
                            //    loadPage(enroll_url, 'enroll_success', true);

//                            $.get('<?//=Url::toRoute(['/resource/course/enroll-send-email'])?>//', {
//                                courseId: response.courseId,
//                                userId: response.userId,
//                                status: response.status
//                            }, function (html) {
//                                //
//                            });
                        } else {
                            app.showMsg(response.errmsg);
                        }
                    }, 'json');
                });
                $("#" + id).removeAttr("disabled");
            });
        });
    </script>
<? } ?>
<script type="text/javascript">
    var del_url = "<?=Url::toRoute(['/resource/course/del-enroll'])?>";
    $(function(){
        $('.delBtn').click(function (e) {
            var id = $(this).attr('data-id');
            NotyConfirm('<?= Yii::t('common', 'operation_confirm') ?>',  function(){
                $.post(del_url, {id: id}, function (response) {
                    if (response.result == 'success') {
                        $.get(enroll_url, function(html){
                            if (html) {
                                $('#enroll_success').html(html)
                            }
                        });
                    } else {
                        app.showMsg(response.errmsg);
                    }
                    $("#" + id).removeAttr("disabled");
                }, 'json');
            });
        });
        $(".pagination").on('click', 'a', function (e) {
            e.preventDefault();
            var parent = $(this).parents('.panel-list').attr('id');
            if (parent == 'undefined'){
                parent = $(this).parents('.tab-pane').attr('id');
            }
            $.get($(this).attr('href')+'&type='+shiftList_statu,function(r){
                $("#"+parent).html(r);
            });
        });
    });
    function loadPage(ajaxUrl, container, is_clear) {
        if (is_clear) {
            $("#" + container).empty();
            $("#list_loading").removeClass("hide");
        }
        app.get(ajaxUrl, function (data) {
            if (is_clear) {
                $("#list_loading").addClass('hide');
            }
            $("#" + container).html(data);
            $("#" + container + ' .pagination a').bind('click', function () {
                var url = $(this).attr('href');
                loadPage(url+'^&type='+shiftList_statu, container, is_clear);
                return false;
            });
        });
    }
</script>
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
        <input name="SendMailForm[objectId]" type="hidden" value="<?= $model->kid ?>">
        <input name="SendMailForm[scenes]" type="hidden" value="enroll">
        <div class="infoBlock">
            <div class="row">
                <div class="col-md-12 col-sm-12">
                    <div class="form-group form-group-sm">
                        <label class="col-sm-2 control-label"><?= Yii::t('frontend', 'recipient') ?></label>
                        <div class="col-sm-10">
                            <textarea id="user_list" style="min-height: auto !important;max-height: 135px;resize: none !important;" rows="1" readonly disabled></textarea>
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
                                <input type="radio" name="SendMailForm[sendMethod]" value="1"> <?= Yii::t('frontend', 'send_independent') ?>
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
<!-- 另存为受众的弹出窗口 -->
<div class="modal ui" id="saveGroup">
</div>

<div class="fc-clear"></div>