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
$dictionaryService = new DictionaryService();
?>
<?php if($params['enroll_type']==LnCourseEnroll::ENROLL_TYPE_REG){?>
    <div class="col-md-12 col-sm-12" style="margin-top:20px;">
        <div class="col-md-12 col-sm-12">
            <h5><?=date('Y年m月d日', $model->enroll_start_time)?> ~ <?=date('m月d日', $model->enroll_end_time)?>, <?= Yii::t('frontend', 'enroll_people_number_{value}',['value'=>'<span class="reg_number">'.$enroll[4].'</span>']) ?>, <?=$model->is_allow_over? Yii::t('frontend', 'candidates_people_number_{value}',['value'=>'<span class="hx_number">'.($model->allow_over_number-$enroll[2]).'</span>,']):''?> <?= Yii::t('frontend', 'approved_people_{value}',['value'=>'<span class="allow_number">'.$enroll[1].'</span>']) ?></h5>
        </div>
        <div class="col-md-5 col-sm-5">
            <label class="pull-left" style="height: 30px; line-height: 30px;display: inline-block;vertical-align: text-bottom; "><?= Yii::t('frontend', 'add_by_manual') ?>:</label>
            <input type="text" class="form-control" placeholder="<?= Yii::t('frontend', 'input_{value',['value'=>Yii::t('common','real_name')]) ?>" style="width:160px;" id="adduser" data-url="<?=Url::to(['/common/get-user','format'=>'new','course_id'=>"$model->kid"])?>" data-mult="1" autocomplete="on" >
            <button type="button" id="issure" class="btn btn-success btn-sm" style="vertical-align: top;"><?= Yii::t('frontend', 'be_sure') ?></button>
        </div>
        <div class="col-md-7 col-sm-7">
            <div class="form-group" style="margin-bottom:0;">
                <select class="form-control" name="sort" style="width:50%;">
                    <option value="1" <?=isset($params['sort']) && $params['sort']=='1'?'selected':''?>><?= Yii::t('frontend', 'rank_by_position') ?></option>
                    <option value="2" <?=isset($params['sort']) && $params['sort']=='2'?'selected':''?>><?= Yii::t('frontend', 'rank_by_organization') ?></option>
                </select>
            </div>
            <div class="input-group ">
                <input type="text" name="keyword" class="form-control search_people" style="height: 30px;" placeholder="<?= Yii::t('common', 'real_name') ?>/<?= Yii::t('common', 'position') ?>/<?= Yii::t('common', 'department') ?>" value="<?=isset($params['keyword'])?$params['keyword']:''?>" onkeyup="this.value=this.value.replace(/\s+/,'');">
                <span class="input-group-btn"><button class="btn btn-success btn-sm searchBtn" type="button" ><?= Yii::t('frontend', 'top_search_text') ?></button></span>
            </div>
        </div>
    </div>
<?}else{?>
    <div class="col-md-12 col-sm-12" style="margin-top:20px;">
        <div class="col-md-5 col-sm-5" style="display: none">
            <label class="pull-left" style="height: 30px; line-height: 30px;display: inline-block;vertical-align: text-bottom; "><?= Yii::t('frontend', 'add_by_manual') ?>:</label>
            <input type="text" class="form-control" placeholder="<?= Yii::t('frontend', 'input_{value}',['value'=>Yii::t('common','real_name')]) ?>" style="width:160px;" id="adduser" data-url="<?=Url::to(['/common/get-user','format'=>'new','course_id'=>"$model->kid"])?>" data-mult="1" autocomplete="on" >
            <button type="button" id="issure" class="btn btn-success btn-sm" style="vertical-align: top;"><?= Yii::t('frontend', 'be_sure') ?></button>
        </div>
        <div class="col-md-7 col-sm-7">
            <h5><?=date('Y年m月d日', $model->enroll_start_time)?> ~ <?=date('m月d日', $model->enroll_end_time)?>, <?= Yii::t('frontend', 'enroll_people_number_{value}',['value'=>'<span class="reg_number">'.$enroll[4].'</span>']) ?>, <?=$model->is_allow_over? Yii::t('frontend', 'candidates_people_number_{value}',['value'=>'<span class="hx_number">'.($model->allow_over_number-$enroll[2]).'</span>,']):''?><?= Yii::t('frontend', 'approved_people_{value}',['value'=>'<span class="allow_number">'.$enroll[1].'</span>'])?>
        </div>
        <div class="col-md-5 col-sm-5">
            <div class="form-group" style="margin-bottom:0;">
                <select class="form-control" style="width:50%;">
                    <option value="1" <?=isset($params['sort']) && $params['sort']=='1'?'selected':''?>><?= Yii::t('frontend', 'rank_by_position') ?></option>
                    <option value="2" <?=isset($params['sort']) && $params['sort']=='2'?'selected':''?>><?= Yii::t('frontend', 'rank_by_organization') ?></option>
                </select>
            </div>
            <div class="input-group ">
                <input type="text" name="keyword" class="form-control search_people" style="height: 30px;" placeholder="<?= Yii::t('common', 'real_name') ?>/<?= Yii::t('common', 'position') ?>/<?= Yii::t('common', 'department') ?>" value="<?=isset($params['keyword'])?$params['keyword']:''?>" onkeyup="this.value=this.value.replace(/\s+/,'');">
                <span class="input-group-btn"><button class="btn btn-success btn-sm searchBtn" type="button" ><?= Yii::t('frontend', 'top_search_text') ?></button></span>
            </div>
        </div>
    </div>
<?}?>
<?php
if (!empty($result['data'])) {
    ?>
    <div class="nameList_view">
        <!--div class="col-md-12">
        <label>显示模式: </label>
            <a href="###" class="btn btn-md shiftList glyphicon <? //if($type == '0'){?>glyphicon-th-list<? //}else{?>glyphicon-th<?//}?>" <? //if($type == '0'){?>title="列表模式"<?//}else{?>title="卡片模式"<?//}?> style=" position: relative; top: -2px; "></a>
    </div-->
        <div class="nameList_table <? if($type == '0'){ echo 'list_active';}?>">
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
                <?php
                foreach ($result['data'] as $key => $val) {
                    ?>
                    <tr>
                        <td><?= $val['real_name'] ?></td>
                        <td><?= $val['user_no'] ?></td>
                        <td><?=$dictionaryService->getDictionaryNameByCode("location",$val['location'])?> <?= $val['orgnization_name'] ?></td>
                        <td><?= $val['position_name'] ?></td>
                        <td><?= date('Y年m月d日', $val['enroll_time']) ?></td>
                        <td>
                            <div class="controlBtns">
                                <?php

                                    if ($val['enroll_type'] == LnCourseEnroll::ENROLL_TYPE_REG) {
                                        ?>
                                        <a href="###" class="btn btn-success btn-sm btn_allow" data-type="<?= LnCourseEnroll::ENROLL_TYPE_ALLOW ?>" data-id="<?= $val['kid'] ?>" id="allow_<?= $val['kid'] ?>" data-click="true"><?= Yii::t('frontend', 'adopt') ?></a>
                                        <a href="###" class="btn btn-default btn-sm btn_disallow" data-type="<?= LnCourseEnroll::ENROLL_TYPE_DISALLOW ?>" data-id="<?= $val['kid'] ?>" id="disallow_<?= $val['kid'] ?>" data-click="true"><?= Yii::t('frontend', 'refuse') ?></a>
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
                            ?>
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
        <div class="nameList_card <? if($type == '1'){ echo 'list_active';}?>">
            <div class="col-md-12 col-sm-12 nameList">
                <ul>
                    <?php
                    foreach ($result['data'] as $key => $val) {
                        ?>
                        <li class="col-md-3 col-sm-4 col-xs-12">
                            <div class="controlBtns">
                                <?php
                                if ($val['approved_state'] == LnCourseEnroll::APPROVED_STATE_APPLING){
                                    ?>
                                    <a href="###" class="btn btn-success btn-sm pull-right approval" data-courseId="<?=$model->kid?>" data-uid="<?= $val['user_id'] ?>"><?= Yii::t('common', 'approval') ?></a>
                                    <?php
                                }else if ($val['approved_state'] == LnCourseEnroll::APPROVED_STATE_APPROVED) {
                                    if ($val['enroll_type'] == LnCourseEnroll::ENROLL_TYPE_REG) {
                                        ?>
                                        <a href="###" class="btn btn-success btn-sm pull-right btn_allow" data-type="<?= LnCourseEnroll::ENROLL_TYPE_ALLOW ?>" data-id="<?= $val['kid'] ?>" id="allow_<?= $val['kid'] ?>" data-click="true"><?= Yii::t('frontend', 'adopt') ?></a>
                                        <a href="###" class="btn btn-default btn-sm pull-right btn_disallow" data-type="<?= LnCourseEnroll::ENROLL_TYPE_DISALLOW ?>" data-id="<?= $val['kid'] ?>" id="disallow_<?= $val['kid'] ?>" data-click="true"><?= Yii::t('frontend', 'refuse') ?></a>
                                        <?php
                                    } elseif ($val['enroll_type'] == LnCourseEnroll::ENROLL_TYPE_ALLOW) {
                                        ?>
                                        <a href="###" class="btn-sm pull-right"><?= Yii::t('frontend', 'passed') ?></a>
                                        <?
                                    } elseif ($val['enroll_type'] == LnCourseEnroll::ENROLL_TYPE_ALTERNATE) {
                                        ?>
                                        <a href="###" class="btn btn-success btn-sm pull-right btn_in" data-type="<?= LnCourseEnroll::ENROLL_TYPE_REG ?>" data-id="<?= $val['kid'] ?>" id="in_<?= $val['kid'] ?>" data-click="true"><?= Yii::t('frontend', 'add_entroll') ?></a>
                                        <?php
                                    } elseif ($val['enroll_type'] == LnCourseEnroll::ENROLL_TYPE_DISALLOW) {
                                        ?>
                                        <a href="###" class="btn-sm pull-right"><?= Yii::t('frontend', 'refused') ?></a>
                                        <?php
                                    }
                                }else if ($val['approved_state'] == LnCourseEnroll::APPROVED_STATE_REJECTED){
                                    ?>
                                    <a href="###" class="btn-sm pull-right"><?= Yii::t('frontend', 'approval_no_pass') ?></a>
                                    <?php
                                }else if ($val['approved_state'] == LnCourseEnroll::APPROVED_STATE_CANCELED){
                                    ?>
                                    <a href="###" class="btn-sm pull-right"><?= Yii::t('frontend', 'invalid') ?></a>
                                    <?php
                                }
                                ?>
                            </div>
                            <h5><?= $val['real_name'] ?></h5>
                            <p><?= Yii::t('frontend', 'work_number') ?>：<?= $val['user_no'] ?></p>
                            <p><?= Yii::t('common', 'position') ?>: <?= $val['position_name'] ?></p>
                            <div class="hide">
                                <p><?= Yii::t('frontend', 'from_text') ?>: <?=$dictionaryService->getDictionaryNameByCode("location",$val['location'])?> <?= $val['orgnization_name'] ?></p>
                                <p><?= Yii::t('frontend', 'enroll_time') ?>: <?= date('Y年m月d日', $val['enroll_time']) ?></p>
                            </div>
                            <?php if ($val['enroll_type'] == LnCourseEnroll::ENROLL_TYPE_REG && $val['approved_state'] == LnCourseEnroll::APPROVED_STATE_APPROVED) {?>
                                <a href="###" class="btn btn-sm delBtn glyphicon glyphicon-remove" data-id="<?=$val['kid']?>"  id="del_<?= $val['kid'] ?>" title="<?= Yii::t('frontend', 'tag_less_5') ?><?= Yii::t('common', 'delete_button') ?>"></a>
                            <?php }?>
                            <input type="hidden" name="userid" value="<?= $val['user_id'] ?>">
                        </li>
                        <?php
                    }
                    ?>
                </ul>
            </div>
        </div>
        <div class="fc-clear"></div>
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
        </nav>
    </div>
    <input type="hidden" value="list" id="tabletype" />
    <script>
        var enroll_url = "<?=Url::toRoute(['/resource/course/get-course-enroll', 'id'=>$model->kid,'enroll_type'=>LnCourseEnroll::ENROLL_TYPE_REG])?>";
        var enroll_url2 = "<?=Url::toRoute(['/resource/course/get-course-enroll', 'id'=>$model->kid,'enroll_type'=>LnCourseEnroll::ENROLL_TYPE_ALTERNATE])?>";
        var set_url = "<?=Url::toRoute(['/resource/course/set-course-enroll-status'])?>";
        var move_url = "<?=Url::toRoute(['/resource/course/move-course-enroll'])?>";
        var enroll_url3 = "<?=Url::toRoute(['/resource/course/get-enroll-count-other'])?>";
        var enroll_id = "<?=Url::toRoute(['/resource/course/get-course-reg-id'])?>";
        var url;
        var shiftList_statu = <?=$type?>;

        $(function(){
            <?php
            if ($params['enroll_type'] == LnCourseEnroll::ENROLL_TYPE_REG){
            ?>
            $('.btn_allow').click(function (e) {
                if ($(this).attr('data-click') == 'false') return false;
                $(this).attr({"disabled":"disabled"});
                var id = $(this).attr('id');
                var dataId=$(this).attr('data-id');
                $.post(set_url, {id: dataId, type: $(this).attr('data-type')}, function (response) {
                    if (response.result == 'success') {
                        app.showMsg('<?= Yii::t('frontend', 'passed') ?>');
                        //    loadPage(enroll_url, 'enroll_success', true);

                        $("#del_"+dataId).remove();
                        $("#" + id).removeClass('btn btn-success').removeClass('btn_allow').text('<?= Yii::t('frontend', 'passed') ?>');
                        $("#" + id).next('.btn-default').remove();
                        $(".allow_number").html(parseInt($(".allow_number").eq(0).html())+1);
                        $("#"+id).attr('class', 'btn btn-sm btn-default').attr('data-click', 'false');
                        $.get('<?=Url::toRoute(['/resource/course/enroll-send-email'])?>',{courseId: response.courseId, userId: response.userId, status: response.status},function(html){

                        });
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
                            //  loadPage(enroll_url, 'enroll_success', true);

                            $("#del_" + dataId).remove();
                            $("#" + id).removeClass('btn btn-default').removeClass('btn_disallow').text('<?= Yii::t('frontend', 'refused') ?>');
                            $("#" + id).prev('.btn-success').remove();
                            $(".reg_number").html(parseInt($(".reg_number").eq(0).html()) + 1);
                            $("#" + id).attr('class', 'btn btn-sm btn-default').attr('data-click', 'false');
                            $.get('<?=Url::toRoute(['/resource/course/enroll-send-email'])?>', {
                                courseId: response.courseId,
                                userId: response.userId,
                                status: response.status
                            }, function (html) {

                            });
                        } else {
                            app.showMsg(response.errmsg);
                        }
                    }, 'json');
                });
                $("#" + id).removeAttr("disabled");
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
            <?php
            }else{
            ?>
            $('.btn_in').click(function (e) {
                if ($(this).attr('data-click') == 'false') return false;
                $(this).attr({"disabled":"disabled"});
                var id = $(this).attr('id');
                $.post(move_url, {id: $(this).attr('data-id'), type: $(this).attr('data-type')}, function (response) {
                    if (response.result == 'success') {
                        app.showMsg('<?= Yii::t('frontend', 'added') ?>');
                        //  loadPage(enroll_url, 'enroll_hb', true);

                        $("#" + id).removeClass('btn btn-success').text('<?= Yii::t('frontend', 'added') ?>');
                        $(".reg_number").html(parseInt($(".reg_number").eq(0).html())-1);
                        $(".hx_number").html(parseInt($(".hx_number").eq(0).html())+1);
                        $("#"+id).attr('class', 'btn btn-sm btn-default').attr('data-click', 'false');
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
            <?php
            }
            ?>
        });
    </script>
    <?php
}else{
    ?>
    <p><?=Yii::t('frontend','temp_no_data_{value}',['value'=>($params['enroll_type']==LnCourseEnroll::ENROLL_TYPE_REG?Yii::t('frontend', 'enroll'):Yii::t('frontend', 'candidates'))])?></p>
    <script>
        var enroll_url = "<?=Url::toRoute(['/resource/course/get-course-enroll', 'id'=>$model->kid,'enroll_type'=>LnCourseEnroll::ENROLL_TYPE_REG])?>";
        var enroll_url2 = "<?=Url::toRoute(['/resource/course/get-course-enroll', 'id'=>$model->kid,'enroll_type'=>LnCourseEnroll::ENROLL_TYPE_ALTERNATE])?>";
        var set_url = "<?=Url::toRoute(['/resource/course/set-course-enroll-status'])?>";
        var move_url = "<?=Url::toRoute(['/resource/course/move-course-enroll'])?>";
        var enroll_url3 = "<?=Url::toRoute(['/resource/course/get-enroll-count-other'])?>";
        var enroll_id = "<?=Url::toRoute(['/resource/course/get-course-reg-id'])?>";
        var url;
        var shiftList_statu = <?=$type?>;

        $(function() {
            <?php
            if ($params['enroll_type'] == LnCourseEnroll::ENROLL_TYPE_REG){
            ?>
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
                        loadPage(enroll_url, 'enroll_success', true);

                        $.get('<?=Url::toRoute(['/resource/course/enroll-send-email'])?>',{courseId: response.courseId, userId: response.userId, status: response.status},function(html){
                            //
                        });
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

                            $.get('<?=Url::toRoute(['/resource/course/enroll-send-email'])?>', {
                                courseId: response.courseId,
                                userId: response.userId,
                                status: response.status
                            }, function (html) {
                                //
                            });
                        } else {
                            app.showMsg(response.errmsg);
                        }
                    }, 'json');
                });
                $("#" + id).removeAttr("disabled");
            });
            <?php
            }else{
            ?>
            $('.btn_in').click(function (e) {
                if ($(this).attr('data-click') == 'false') return false;
                $(this).attr({"disabled":"disabled"});
                var id = $(this).attr('id');
                $.post(move_url, {id: $(this).attr('data-id'), type: $(this).attr('data-type')}, function (response) {
                    if (response.result == 'success') {
                        $("#" + id).removeClass('btn btn-success').text('<?= Yii::t('frontend', 'added') ?>');
                        $(".reg_number").html(parseInt($(".reg_number").eq(0).html())-1);
                        $(".hx_number").html(parseInt($(".hx_number").eq(0).html())+1);
                        $("#"+id).attr('class', 'btn btn-sm btn-default').attr('data-click', 'false');
                        //  loadPage(enroll_url, 'enroll_success', true);

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
            <?php
            }
            ?>
        });
    </script>
    <?php
}
?>
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

<div class="fc-clear"></div>