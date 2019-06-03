<?php
use yii\helpers\Url;
use components\widgets\TLinkPager;
use common\models\learning\LnExamination;
use common\models\learning\LnExaminationResultUser;
use common\services\framework\UserService;
use common\models\framework\FwUser;
?>
<div class="header">
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    <h4 class="modal-title" id="myModalLabel">[<?= Yii::t('frontend', 'exam') ?>]<?=$model['title']?> - <?= Yii::t('frontend', 'learn_statistics') ?></h4>
</div>
<div class="content">
    <?php
    if (empty($params['userId'])){
    ?>
    <div class="actionBar" style="margin-top: 0px;">
        <form class="form-inline" id="<?=$componentCode?>-form">
            <div class="form-group">
                <span class="pull-left" style="line-height: 40px;"><?=Yii::t('common', 'status')?>: &nbsp;&nbsp;</span>
                <select name="score_status" class="form-control">
                    <option value=""><?= Yii::t('common', 'select_{value}',['value'=>'']) ?></option>
                    <option value="0" <?=(!is_null($params['score_status']) && $params['score_status']=='0')?'selected':''?>><?=Yii::t('common', 'complete_status_0')?></option>
                    <option value="1" <?=(!is_null($params['score_status']) && $params['score_status']=='1')?'selected':''?>><?=Yii::t('common', 'complete_status_1')?></option>
                    <option value="2" <?=(!is_null($params['score_status']) && $params['score_status']=='2')?'selected':''?>><?=Yii::t('common', 'complete_status_2')?></option>
                </select>
                <?php
                if ($model->examination_mode == LnExamination::EXAMINATION_MODE_TEST) {
                ?>
                <span class="pull-left" style="line-height: 40px;"><?= Yii::t('common', 'examination_score') ?>: &nbsp;&nbsp;</span>
                <select name="score_type" class="form-control">
                    <option value=""><?=Yii::t('frontend', 'any_result')?></option>
                    <option value=">=" <?=(!empty($params['score_type']) && $params['score_type']=='>=')?'selected':''?>><?= Yii::t('common', 'examination_score') ?> >=</option>
                    <option value="<=" <?=(!empty($params['score_type']) && $params['score_type']=='<=')?'selected':''?>><?= Yii::t('common', 'examination_score') ?> <=</option>
                    <option value="=" <?=(!empty($params['score_type']) && $params['score_type']=='=')?'selected':''?>><?= Yii::t('common', 'examination_score') ?> =</option>
                </select>
                <input name="score_value" type="text" onkeyup="checkNumber(this)" onblur="checkNumber(this)" class="form-control" placeholder="<?=Yii::t('frontend', 'score')?>" style="width:60px;" value="<?=$params['score']?>">
                <?php
                }else{
                ?>
                    <select name="score_type" class="form-control" style="display: none;">
                        <option value=""><?=Yii::t('frontend', 'any_result')?></option>
                        <option value=">="><?= Yii::t('common', 'examination_score') ?> >=</option>
                        <option value="<="><?= Yii::t('common', 'examination_score') ?> <=</option>
                        <option value="="><?= Yii::t('common', 'examination_score') ?> =</option>
                    </select>
                    <input name="score_value" type="text" onkeyup="this.value=this.value.replace(/\D+/,'');" onblur="this.value=this.value.replace(/\D+/,'');" class="form-control" placeholder="<?=Yii::t('frontend', 'score')?>" style="width:60px;display: none;">
                <?php
                }
                ?>
                <input name="keyword" type="text" value="<?=$params['keyword']?>" class="form-control" placeholder="<?=Yii::t('frontend', 'input_name_email')?>">
                <button type="button" class="btn btn-primary search" style="margin-left:10px;"><?=Yii::t('common', 'search')?></button>
                <button type="reset" class="btn btn-default reset"><?=Yii::t('frontend', 'reset')?></button>
            </div>
        </form>
    </div>
    <?php
    }
    ?>
    <table class="table table-bordered table-hover table-center">
        <tbody>
        <tr>
            <td><?=Yii::t('common', 'real_name')?></td>
            <td><?=Yii::t('common', 'department')?></td>
            <td><?=Yii::t('common', 'position')?></td>
            <td><?=Yii::t('common', 'user_email')?></td>
            <td><?=Yii::t('common', 'complete_end_at')?></td>
            <?php
            if ($model->examination_mode == LnExamination::EXAMINATION_MODE_TEST) {
            ?>
            <td><?=Yii::t('common', 'complete_grade')?></td>
            <?php
            }else{
            ?>
            <td><?=Yii::t('common', 'complete_correct_rate')?></td>
            <?php
            }
            ?>
            <td><?=Yii::t('common', 'status')?></td>
            <td><?=Yii::t('common', 'action')?></td>
        </tr>
        <?
        if (!empty($data)) {
            foreach ($data as $items) {?>
                <tr>
                    <td><?=$items['real_name']?></td>
                    <td><?=$items['orgnization_name']?></td>
                    <td><?=$items['position_name']?></td>
                    <td><?=$items['email']?></td>
                    <td><?=!empty($items['end_at']) ? date('Y年m月d日 H:i', $items['end_at']) : '--'?></td>
                    <td><?=isset($items['score_before']) && !is_null($items['score_before']) && $items['score_before']!='--'?$items['score_before']:'--'?></td>
                    <td>
                        <?=Yii::t('common', 'complete_status_'.intval($items['complete_status']))?>
                    </td>
                    <td>
                        <?php
                        if (intval($items['complete_status']) > 0){
                        ?>
                        <a href="<?=Url::toRoute(['/exam-manage-main/view-log','id'=> $examination_id, 'courseId' => $courseId, 'modId' => $mod_id, 'modResId' => $modResId, 'courseactivityId' => $object_id, 'userId'=> $items['kid'], 'companyId' => $items['company_id']])?>" class="view_log"><?= Yii::t('frontend', 'view_record') ?></a>
                        <?php
                        }else{
                            echo '--';
                        }
                        ?>
                    </td>
                </tr>
            <?php
            }
        } else {
            ?>
            <tr>
                <td colspan="8"><?= Yii::t('common', 'no_data') ?></td>
            </tr>
        <?php
        }
        ?>
        </tbody>
    </table>
    <div class="col-md-12">
        <nav class="text-right" id="<?=$componentCode?>-page">
            <?php
            if (!empty($page)) {
                echo TLinkPager::widget([
                    'id' => $componentCode.'-page-'.time(),
                    'pagination' => $page,
                    'displayPageSizeSelect' => false
                ]);
            }
            ?>
        </nav>
    </div>
    <div class="c"></div>
    <script>
        $(function(){
            $(".search").on('click', function(e){
                var form = $(this).parent();
                var score_status = form.find("select[name='score_status'] option:selected").val();
                var score_type = form.find("select[name='score_type']").val();
                var score = form.find("input[name='score_value']").val();
                if (score_type!= '' && score == ""){
                    app.showMsg('<?= Yii::t('frontend', 'input_{value}',['value'=>Yii::t('frontend','result_score')]) ?>');
                    return false;
                }
                var keyword = form.find("input[name='keyword']").val().trim();
                var url = '<?=Url::toRoute(['/teacher/'.$componentCode.'-result','courseId'=>$courseId,'modResId'=>$modResId, 'itemId' => $examination_id, 'componentCode' => $componentCode])?>';
                $.get(url, {score_status: score_status, score_type: score_type, score: score, keyword: keyword}, function(r){
                    if (r){
                        $("#<?=$componentCode?>-result").html(r);
                    }else{
                        app.showMsg('<?=Yii::t('common', 'loading_fail')?>');
                        return false;
                    }
                });
            });
            $(".reset").on('click', function(){
                var form = $(this).parent();
                form.find("select[name='score_status']").find("option").attr('selected', false);
                form.find("select[name='score_status']").find("option").eq(0).attr('selected', true);
                form.find("input[name='keyword']").attr('value', '');
                form.find("select[name='score_type']").find("option").attr('selected', false);
                form.find("select[name='score_type']").find("option").eq(0).attr('selected', true);
                form.find("input[name='score_value']").attr('value', '');
            });
            $("#<?=$componentCode?>-page .pagination").on('click', 'a', function(e){
                e.preventDefault();
                $.get($(this).attr('href'), function(html){
                    if (html) {
                        $('#<?=$componentCode?>-result').html(html);
                    }
                });
            });
            $(".view_log").on('click', function(e){
                e.preventDefault();
                $.get($(this).attr('href'), function(html){
                    if (html){
                        $("#examination_log").html(html);
                        app.alertWideAgain($("#examination_log"));
                    }else{
                        return app.showMsg(app.msg.NETWORKERROR);
                    }
                });
            });
        });
    </script>

    <div class="c"></div>
</div>

<!-- 评论弹出组件 -->
<script type="text/javascript">
    function resultUserDetail(id){
        if (id == "") return ;
        $.get('<?=Url::toRoute(['/teacher/examination-result-detail'])?>', {id: id}, function(html){
            if (html){
                $("#examination").html(html);
            }else{
                return app.showMsg(app.msg.NETWORKERROR);
            }
        });
    }

    function checkNumber(obj) {
        //先把非数字的都替换掉，除了数字和.
        obj.value = obj.value.replace(/[^\d.]/g,"");
        //必须保证第一个为数字而不是.
        obj.value = obj.value.replace(/^\./g,"");
        //保证只有出现一个.而没有多个.
        obj.value = obj.value.replace(/\.{2,}/g,".");
        //保证.只出现一次，而不能出现两次以上
        obj.value = obj.value.replace(".","$#$").replace(/\./g,"").replace("$#$",".");
    }
</script>