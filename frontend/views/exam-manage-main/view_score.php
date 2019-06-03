<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/12/1
 * Time: 14:20
 */
use yii\helpers\Html;
use yii\helpers\Url;
use components\widgets\TGridView;
use common\models\learning\LnExaminationResultUser;
use common\models\learning\LnExamination;
?>
<div class="header">
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    <h4 class="modal-title" id="myModalLabel"><?=$model->title?> <?=Yii::t('frontend', 'exam_chengjibiao')?></h4>
</div>
<div class="content">
    <div class="panel-body">
        <p class="text-right">*<?=Yii::t('frontend', 'exam_yituisong_special_start_2')?> <?=$relatedCount?><?=Yii::t('frontend', 'exam_mingxueyuan_special_end_2')?> <?=Yii::t('common', 'complete_status_2')?> <span style="color: #008000;"><?=$completeCount?></span>人 <?=Yii::t('common', 'complete_status_1')?> <span style="color: #0000FF;"><?=$processCount?></span>人 <?=Yii::t('common', 'complete_status_0')?> <span style="color: #FF0000;"><?=$notCount?></span>人</p>
        <div class="actionBar" style="margin-top: 0;">
            <a class="btn btn-success pull-left" href="javascript:;" id="export"><?=Yii::t('frontend', 'exam_quanbudaochu')?></a>
            <div class="form-inline pull-right">
                <div class="form-group">
                    <div class="form-group pull-left">
                        <select id="examination_status" class="form-control">
                            <option value=""><?=Yii::t('frontend', 'exam_quanbuxueyuan')?></option>
                            <option value="0" <?=(isset($params['examination_status'])&&$params['examination_status']==LnExaminationResultUser::EXAMINATION_STATUS_NOT)?'selected':''?>><?=Yii::t('common', 'complete_status_0')?>(<?=$notCount?>)</option>
                            <option value="1" <?=(isset($params['examination_status'])&&$params['examination_status']==LnExaminationResultUser::EXAMINATION_STATUS_START)?'selected':''?>><?=Yii::t('common', 'complete_status_1')?>(<?=$processCount?>)</option>
                            <option value="2" <?=(isset($params['examination_status'])&&$params['examination_status']==LnExaminationResultUser::EXAMINATION_STATUS_END)?'selected':''?>><?=Yii::t('common', 'complete_status_2')?>(<?=$completeCount?>)</option>
                        </select>
                    </div>
                    <div class="form-group pull-left">
                        <select id="searchExpression" class="form-control">
                            <option value=""><?=Yii::t('frontend', 'exam_renyichengji')?></option>
                            <option value="0" <?=(isset($params['expression'])&&$params['expression']=='0')?'selected':''?>><?=Yii::t('frontend', 'exam_score')?>&gt;=</option>
                            <option value="1" <?=(isset($params['expression'])&&$params['expression']=='1')?'selected':''?>><?=Yii::t('frontend', 'exam_score')?>&lt;=</option>
                            <option value="2" <?=(isset($params['expression'])&&$params['expression']=='2')?'selected':''?>><?=Yii::t('frontend', 'exam_score')?>=</option>
                        </select>
                    </div>
                    <?php
                    if ($model->examination_mode == LnExamination::EXAMINATION_MODE_TEST){
                    ?>
                    <input type="text" id="searchScore" class="form-control" placeholder="<?=Yii::t('frontend', 'exam_fenshu')?>" style="width:60px;" value="<?=isset($params['examination_score']) ? $params['examination_score'] : ''?>">
                    <?php
                    }else{
                    ?>
                    <input type="text" id="searchScore" class="form-control" placeholder="<?=Yii::t('frontend', 'exam_zhengquelv')?>" style="width:60px;" value="<?=isset($params['examination_score']) ? $params['examination_score'] : ''?>">
                    <?php
                    }
                    ?>
                    <input type="text" class="form-control" id="searchUser" style="width:140px" value="<?=isset($params['user_keyword']) ? $params['user_keyword']:''?>" placeholder="<?=Yii::t('frontend', 'exam_type_name_email')?>">

                    <button type="button" class="btn btn-default pull-right" id="resetForm"><?=Yii::t('common', 'reset')?></button>
                    <button type="button" class="btn btn-primary pull-right" id="searchForm" style="margin-left:10px;" onclick="searchScoreForm('');"><?=Yii::t('common', 'search')?></button>
                </div>
            </div>
            <div class="c"></div>
        </div>
        <div class="c"></div>
        <table class="table table-bordered table-hover table-striped table-center" style="margin-top:20px;">
            <tbody>
            <tr>
                <td><?=Yii::t('common', 'real_name')?></td>
                <td><?=Yii::t('common', 'user_email')?></td>
                <td><?=Yii::t('common', 'mobile_no')?></td>
                <td><?=Yii::t('common', 'status')?></td>
                <?php
                if ($model->examination_mode == LnExamination::EXAMINATION_MODE_TEST) {
                ?>
                <td><?=Yii::t('common', 'examination_end_at')?></td>
                <td><?= Yii::t('common', 'examination_score') ?></td>
                <?php
                }else{
                ?>
                <td><?=Yii::t('common', 'examination_submit_at')?></td>
                <td><?=Yii::t('frontend', 'exam_zhengquelv')?></td>
                <?php
                }
                ?>
                <td><?=Yii::t('frontend', 'exam_choose_caozuo')?></td>
            </tr>
            <?php
            if ($page->totalCount > 0){
                foreach ($data as $item) {
            ?>
            <tr data-key="<?=$item['result_id']?>">
                <td><?= $item['real_name'] ?></td>
                <td><?= $item['email'] ?></td>
                <td><?= $item['mobile_no'] ?></td>
                <td>
                    <?php
                    if ($item['examination_status'] == LnExaminationResultUser::EXAMINATION_STATUS_NOT || is_null($item['examination_status'])){
                        echo '<strong>'.Yii::t('common', 'complete_status_0').'</strong>';
                    }else if ($item['examination_status'] == LnExaminationResultUser::EXAMINATION_STATUS_START){
                        echo Yii::t('common', 'complete_status_1');
                    }else if ($item['examination_status'] == LnExaminationResultUser::EXAMINATION_STATUS_END){
                        echo Yii::t('common', 'complete_status_2');
                    }
                    ?>
                </td>
                <td>
                    <?php
                    if ($item['examination_status'] == LnExaminationResultUser::EXAMINATION_STATUS_NOT || is_null($item['examination_status'])){
                        echo '--';
                    }else if ($item['examination_status'] == LnExaminationResultUser::EXAMINATION_STATUS_START){
                        echo '--';
                    }else if ($item['examination_status'] == LnExaminationResultUser::EXAMINATION_STATUS_END){
                        echo date('Y-m-d H:i', $item['end_at']);
                    }
                    ?>
                </td>
                <td>
                <?php
                    if ($model->examination_mode == LnExamination::EXAMINATION_MODE_TEST){
                        if ($item['examination_status'] == LnExaminationResultUser::EXAMINATION_STATUS_END ){
                            echo $item['examination_score'];
                        }else{
                            echo '--';
                        }
                    }else{
                        if ($item['examination_status'] == LnExaminationResultUser::EXAMINATION_STATUS_END ){
                            echo $item['correct_rate'] .'%';
                        }else{
                            echo '--';
                        }
                    }
                ?>
                </td>
                <td>
                    <?php
                    if ($item['examination_status'] == LnExaminationResultUser::EXAMINATION_STATUS_END){
                    ?>
                    <a href="<?=Url::toRoute(['/exam-manage-main/view-log','id'=>$item['examination_id'],'userId'=> ($item['user_id'] ? $item['user_id'] : $item['uid']), 'companyId' => $item['company_id']])?>" class="view_log"><?=Yii::t('frontend', 'exam_chakanjilu')?></a>
                    <?php
                    }else{
                        echo '--';
                    }
                    ?>
                </td>
            </tr>
            <?php
                }
            }else{
            ?>
            <tr>
                <td colspan="7"><?=Yii::t('common', 'no_data')?></td>
            </tr>
            <?php
            }
            ?>
            </tbody>
        </table>
        <div class="col-md-12">
            <nav class="text-right" id="grid_score">
            <?php
                echo \components\widgets\TLinkPager::widget([
                    'id' => 'page',
                    'pagination' => $page,
                    'displayPageSizeSelect'=>false
                ]);
            ?>
            </nav>
        </div>
        <div class="c"></div>
    </div>
</div>
<?= Html::jsFile('/static/frontend/js/xss.js')?>
<script>
    $(function(){
        $("#grid_score .pagination").on('click', 'a', function(e){
            e.preventDefault();
            app.get($(this).attr('href'), function(data){
                if (data){
                    $("#view_score").html(data);
                }
            });
        });
       $("#resetForm").on('click', function(){
           $("#searchUser").val('');
           $("#searchExpression").find("option").eq(0).attr('selected', true);
           $("#examination_status option").eq(0).attr('selected', true);
           $("#searchScore").val('');
       }) ;

        $(".view_log").on('click', function(e){
            e.preventDefault();
            $.get($(this).attr('href'), function(data) {
                if (data) {
                    $("#examination_log").html(data);
                    app.alertWideAgain($("#examination_log"));
                }
            });
        });

        $("#export").on('click', function(e){
            e.preventDefault();
            var user_keyword = $("#searchUser").val().replace(/(^\s*)|(\s*$)/g,'');
            $("#searchUser").val(user_keyword);
            var xss_user_keyword = filterXSS(user_keyword);
            if (user_keyword != xss_user_keyword){
                $("#searchUser").focus();
                validation.showAlert($("#searchUser"), "<?=Yii::t('frontend', 'exam_kw_ill_err')?>");
                return false;
            }
            var expression = $("#searchExpression").val();
            var examination_score = $("#searchScore").val().replace(/(^\s*)|(\s*$)/g,'');
            $("#searchScore").val(examination_score);
            var examination_status = $("#examination_status").val();
            var url = '<?=Url::toRoute(['/exam-manage-main/export-exam-user','id'=>$model->kid])?>&export=True& examination_status='+examination_status+'&user_keyword='+user_keyword+'&expression='+expression+'&examination_score='+examination_score+'&<?=$page->pageParam?>=<?=$nowPage?>&_' +Math.random();
            window.open(url);
        });
    });
function searchScoreForm(is_export){
    var examination_status = $("#examination_status").val();
    var user_keyword = $("#searchUser").val().replace(/(^\s*)|(\s*$)/g,'');
    $("#searchUser").val(user_keyword);
    var xss_user_keyword = filterXSS(user_keyword);
    if (user_keyword != xss_user_keyword){
        $("#searchUser").focus();
        validation.showAlert($("#searchUser"), "<?=Yii::t('frontend', 'exam_kw_ill_err')?>");
        return false;
    }
    var expression = $("#searchExpression").val();
    var examination_score = $("#searchScore").val().replace(/(^\s*)|(\s*$)/g,'');
    $("#searchScore").val(examination_score);
    var url = '<?=Url::toRoute(['/exam-manage-main/view-score','id'=>$model->kid])?>';
    $.get(url, {export: is_export, examination_status: examination_status, user_keyword: user_keyword, expression: expression, examination_score: examination_score}, function(html){
        if (html){
            $("#view_score").html(html);
        }
    });
}
</script>
