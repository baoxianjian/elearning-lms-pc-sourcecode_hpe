<?php
/**
 * User: adophper
 * Date: 2016/05/24
 * Time: 14:13
 */
use components\widgets\TLinkPager;
use common\models\learning\LnCourseComplete;
use common\models\learning\LnUserCertification;
use yii\helpers\Url;
?>
<style>
    .pagination {margin: 0!important;}
</style>
<div class=" panel-default scoreList">
    <div class="panel-body">
        <div class="row" style="margin-top:20px;">
            <div class="col-sm-4">
                <div class="form-group" style="margin-bottom:0;">
                    <select id="is_passed" class="form-control" style="width:50%;">
                        <option value="0"><?=Yii::t('frontend', 'all_pass_status')?></option>
                        <option value="1" <?=isset($params['is_passed'])&&$params['is_passed']==1? 'selected': ''?>><?=Yii::t('frontend', 'qualified')?></option>
                        <option value="2" <?=isset($params['is_passed'])&&$params['is_passed']==2? 'selected': ''?>><?=Yii::t('frontend', 'not_qualified')?></option>
                    </select>
                </div>
            </div>
            <div class="col-sm-8">
                <?php
                if ($certification){
                ?>
                <div class="form-group" style="margin-bottom:0;">
                    <select id="is_certification" class="form-control" style="width:50%;">
                        <option value="0"><?=Yii::t('frontend', 'all_certification_status')?></option>
                        <option value="1" <?=isset($params['is_certification'])&&$params['is_certification']==1? 'selected': ''?>><?=Yii::t('frontend', 'has_been')?></option>
                        <option value="2" <?=isset($params['is_certification'])&&$params['is_certification']==2? 'selected': ''?>><?=Yii::t('frontend', 'dit_not_receive')?></option>
                    </select>
                </div>
                <?php
                }
                ?>
                <div class="input-group ">
                    <input type="text" name="keyword" class="form-control search_people" style="height: 30px;" placeholder="<?= Yii::t('common', 'real_name') ?>/<?= Yii::t('frontend', 'work_number') ?>/<?= Yii::t('frontend', 'position') ?>/<?= Yii::t('frontend', 'department') ?>" value="<?=$params['keyword2']?>">
                    <span class="input-group-btn"><button class="btn btn-success btn-sm" type="button" id="searchGrade" onclick="searchReload(false);"><?=Yii::t('frontend', 'top_search_text')?></button></span>
                </div>
            </div>
        </div>
        <div class="nameList_view" style="overflow: visible;">
            <div class="nameList_table list_active">
                <table class="table table-bordered table-hover table-striped table-center" style="margin-bottom:0;">
                    <tbody>
                    <tr>
                        <td width="5%">
                            <input type="checkbox" id="check_all">
                        </td>
                        <td width="10%"><?=Yii::t('common', 'real_name')?></td>
                        <td width="10%"><?=Yii::t('frontend', 'work_number')?></td>
                        <td width="20%"><?=Yii::t('common', 'department')?></td>
                        <td width="10%"><?=Yii::t('common', 'position')?></td>
                        <td width="20%"><?=Yii::t('common', 'user_email')?></td>
                        <td width="10%"><?=Yii::t('common', 'mobile')?></td>
                        <td width="10%"><?=Yii::t('frontend', 'is_qualified')?></td>
                        <?php
                        if ($certification){
                        ?>
                        <td width="10%"><?=Yii::t('frontend', 'is_certification')?></td>
                        <?php
                        }
                        ?>
                    </tr>
                    <?php
                    if (!empty($result['data'])){
                        foreach ($result['data'] as $v){
                            ?>
                            <tr>
                                <td>
                                    <input type="checkbox" name="user_check[]" value="<?=$v['user_id']?>">
                                </td>
                                <td><?=$v['real_name']?></td>
                                <td><?=$v['user_no']?></td>
                                <td title="<?=$v['orgnization_name']?>"><?=$v['orgnization_name']?></td>
                                <td><?=$v['position_name']?></td>
                                <td><?=$v['email']?></td>
                                <td><?=$v['mobile_no']?></td>
                                <td>
                                    <div class="form-group">
                                        <select data-user="<?=$v['user_id']?>" class="check_pass form-control selectSave" style="width:100%; margin:0; height:32px!important; ">
                                            <option value="1" <?=$v['is_passed'] == LnCourseComplete::IS_PASSED_YES ? 'selected' : ''?>><?=Yii::t('common', 'yes')?></option>
                                            <option value="0" <?=$v['is_passed'] == LnCourseComplete::IS_PASSED_NO ? 'selected' : ''?>><?=Yii::t('common', 'no')?></option>
                                            <!--<option value="1" selected="selected"><?/*=Yii::t('common', 'yes')*/?></option>
                                            <option value="0"><?/*=Yii::t('common', 'no')*/?></option>-->
                                        </select>
                                    </div>
                                </td>
                                <?php
                                if ($certification){
                                ?>
                                <td>
                                    <div class="form-group">
                                        <select data-user="<?=$v['user_id']?>" class="check_certification form-control selectSave" style="width:100%; margin:0; height:32px!important;">
                                            <option value="1" <?=$v['certification_status'] == LnUserCertification::IS_ISSUE_YES ? 'selected' : ''?>><?=Yii::t('common', 'yes')?></option>
                                            <option value="0" <?=$v['certification_status'] != LnUserCertification::IS_ISSUE_YES ? 'selected' : ''?>><?=Yii::t('common', 'no')?></option>
                                        </select>
                                    </div>
                                </td>
                                <?php
                                }
                                ?>
                            </tr>
                            <?php
                        }
                    }else{
                        ?>
                        <tr>
                            <td colspan="<?=$certification?9:8?>"><?=Yii::t('common', 'no_data')?></td>
                        </tr>
                        <?php
                    }
                    ?>
                    </tbody>
                </table>
                <div class="fc-clear"></div>
                <?php
                if (!empty($result['data'])){
                ?>
                <div class="col-md-12">
                    <div class="btn-group btn-sm pull-left" style="margin: 15px 10px 0 0; padding:0;">
                        <button type="button" class="btn btn-sm btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><?=Yii::t('common', 'batch_qualified')?>
                        </button>
                        <ul class="dropdown-menu" style=" min-width: 50px; ">
                            <li><a href="###" class="leaveBtn qualified" data-value="<?=LnCourseComplete::IS_PASSED_YES?>" data-statu="leave1"><?=Yii::t('common', 'yes')?></a></li>
                            <li><a href="###" class="leaveBtn qualified" data-value="<?=LnCourseComplete::IS_PASSED_NO?>" data-statu="leave2"><?=Yii::t('common', 'no')?></a></li>
                        </ul>
                    </div>
                    <?php
                    if ($certification){
                    ?>
                    <div class="btn-group btn-sm pull-left" style="margin: 15px 10px 0 0; padding:0;">
                        <button type="button" class="btn btn-sm btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><?=Yii::t('common', 'batch_certification')?>
                        </button>
                        <ul class="dropdown-menu" style=" min-width: 50px; ">
                            <li><a href="###" class="leaveBtn certification" data-value="<?=LnUserCertification::IS_ISSUE_YES?>" data-statu="leave1"><?=Yii::t('common', 'yes')?></a></li>
                            <li><a href="###" class="leaveBtn certification" data-value="<?=LnUserCertification::IS_ISSUE_NO?>" data-statu="leave2"><?=Yii::t('common', 'no')?></a></li>
                        </ul>
                    </div>
                    <?php
                    }
                    ?>
                    <nav style="margin-top: 15px; text-align: right;">
                        <?php
                        if (!empty($result['page'])) {
                            echo TLinkPager::widget([
                                'id' => 'page',
                                'pagination' => $result['page'],
                                'displayPageSizeSelect' => false
                            ]);
                        }
                        if ($params['full'] == 'False'){
                        ?>
                        <button type="button" class="btn btn-default resizeBtn" title="<?= Yii::t('frontend', 'resize_full_button') ?>" onclick="GradeResizeFullButton();"><?= Yii::t('frontend', 'resize_full_button') ?></button>
                        <?php
                        }else{
                        ?>
                        <button type="button" class="btn btn-default resizeBtn" title="<?= Yii::t('frontend', 'resize_current_button') ?>" onclick="GradeResizeCurrentButton();"><?= Yii::t('frontend', 'resize_current_button') ?></button>
                        <?php
                        }
                        ?>
                    </nav>
                </div>
                <?php
                }
                ?>
            </div>
        </div>
    </div>
</div>
<script>
    var detailGradeUrl = '<?=Url::toRoute(['/teacher/detail-grade', 'id' => $id, 'iframe' => $iframe])?>';
    $(function(){
        $("#check_all").click(function () {
            var status = $(this).is(':checked');
            $("input[name^='user_check'][type='checkbox']").prop("checked", status);
        });
        $(".pagination").on('click', 'a', function (e) {
            e.preventDefault();
            var parent = $(this).parents('.tab-pane').attr('id');
            $.get($(this).attr('href'),function(r){
                $("#"+parent).html(r);
            });
        });
        $(".qualified").click(function(){
            var parent = $(this).parent();
            var pass = $(this).attr('data-value');
            parent.attr('disabled', 'disabled');
            var count = $("input[name^='user_check'][type='checkbox']:checked").length;
            if (count == 0) {
                app.showMsg('<?= Yii::t('frontend', 'please_select_handle_object') ?>');
                parent.removeAttr("disabled");
                return false;
            }
            var selectUser = $("input[name^='user_check'][type='checkbox']:checked").map(function(){
                return $(this).val();
            }).get();
            app.showLoadingMsg('<?=Yii::t('frontend', 'data_get_ready')?>');

            $.post('<?=Url::toRoute(['/teacher/set-course-pass', 'id' => $id])?>', {pass: pass, user: selectUser}, function(data){
                app.hideLoadingMsg();
                parent.removeAttr("disabled");
                if (data.result == 'success'){
                    app.showMsg('<?=Yii::t('common', 'operation_success')?>');
                    searchReload('<?=$page?>');
                }else{
                    app.showMsg(data.errmsg);
                }
                return false;
            },'json');
        });
        $(".check_pass").bind('change', function(){
            var is_passed = $(this).val();
            var user = $(this).attr('data-user');
            app.showLoadingMsg('<?=Yii::t('frontend', 'data_get_ready')?>');
            $.post('<?=Url::toRoute(['/teacher/set-course-pass', 'id' => $id])?>', {pass: is_passed, user: user}, function(data){
                app.hideLoadingMsg();
                if (data.result == 'success'){
                    app.showMsg('<?=Yii::t('common', 'operation_success')?>');
                }else{
                    app.showMsg(data.errmsg);
                }
                return false;
            },'json');
        });
        $(".certification").click(function(){
            var parent = $(this).parent();
            var certification = $(this).attr('data-value');
            parent.attr('disabled', 'disabled');
            var count = $("input[name^='user_check'][type='checkbox']:checked").length;
            if (count == 0) {
                app.showMsg('<?= Yii::t('frontend', 'please_select_handle_object') ?>');
                parent.removeAttr("disabled");
                return false;
            }
            var selectUser = $("input[name^='user_check'][type='checkbox']:checked").map(function(){
                return $(this).val();
            }).get();
            app.showLoadingMsg('<?=Yii::t('frontend', 'data_get_ready')?>');

            $.post('<?=Url::toRoute(['/teacher/set-course-certification', 'id' => $id])?>', {certification: certification, user: selectUser}, function(data){
                app.hideLoadingMsg();
                parent.removeAttr("disabled");
                if (data.result == 'success'){
                    app.showMsg('<?=Yii::t('common', 'operation_success')?>');
                    searchReload('<?=$page?>');
                }else{
                    app.showMsg(data.errmsg);
                }
                return false;
            },'json');
        });
        $(".check_certification").bind('change', function(){
            var certification = $(this).val();
            var user = $(this).attr('data-user');
            app.showLoadingMsg('<?=Yii::t('frontend', 'data_get_ready')?>');
            $.post('<?=Url::toRoute(['/teacher/set-course-certification', 'id' => $id])?>', {certification: certification, user: user}, function(data){
                app.hideLoadingMsg();
                if (data.result == 'success'){
                    app.showMsg('<?=Yii::t('common', 'operation_success')?>');
                }else{
                    app.showMsg(data.errmsg);
                }
                return false;
            },'json');
        });
    });
    var full = 'False';
    function searchReload(page){
        var keyword = $(".search_people").val().replace(/(^\s*)|(\s*$)/g, '');
        $(".search_people").val(keyword);
        var is_passed = $("#is_passed").val();
        if (typeof $("#is_certification").val() == 'undefined'){
            var is_certification = 0;
        }else {
            var is_certification = $("#is_certification").val();
        }
        var data = {
            keyword2: keyword,
            is_passed: is_passed,
            is_certification: is_certification,
            full: full
        };
        if (page){
            data['page'] = page;
        }
        $.get(detailGradeUrl, data, function(r){
            $("#courseAward5").html(r);
        })
    }
    function GradeResizeFullButton(){
        full = 'True';
        searchReload(false);
    }
    function GradeResizeCurrentButton(){
        full = 'False';
        searchReload(false);
    }
</script>