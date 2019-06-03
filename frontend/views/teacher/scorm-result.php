<?php
/**
 * Created by PhpStorm.
 * User: tangming
 * Date: 9/10/2015
 * Time: 1:12 PM
 */
use common\models\framework\FwUser;
use common\services\framework\UserService;
use components\widgets\TLinkPager;
use common\services\learning\ResourceCompleteService;
use yii\helpers\Url;
?>
<div class="header">
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    <h4 class="modal-title" id="myModalLabel"><?=$itemName?>－<?= Yii::t('frontend', 'learn_statistics') ?></h4>
</div>
<div class="content">
    <div class="col-md-12 col-sm-12">
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
                        <input name="keyword" type="text" value="<?=$params['keyword']?>" class="form-control" placeholder="<?=Yii::t('frontend', 'input_name_email')?>">
                        <button type="button" class="btn btn-primary search" style="margin-left:10px;"><?=Yii::t('common', 'search')?></button>
                        <button type="reset" class="btn btn-default reset"><?=Yii::t('frontend', 'reset')?></button>
                    </div>
                </form>
            </div>
            <?php
            }
            ?>
            <div id="score_modal_list">
                <table class="table table-bordered table-hover table-striped table-center" style="margin-top:20px;">
                    <tbody>
                    <tr>
                        <td><?=Yii::t('common', 'real_name')?></td>
                        <td><?=Yii::t('common', 'department')?></td>
                        <td><?=Yii::t('common', 'position')?></td>
                        <td><?=Yii::t('common', 'user_email')?></td>
                        <td><?=Yii::t('common', 'complete_end_at')?></td>
                        <td><?=Yii::t('common', 'complete_grade')?></td>
                        <td><?=Yii::t('common', 'status')?></td>
                    </tr>
                    <?
                    if (!empty($datas)) {
                        foreach ($datas as $items) {?>
                            <tr>
                                <td><?=$items['real_name']?></td>
                                <td><?=$items['orgnization_name']?></td>
                                <td><?=$items['position_name']?></td>
                                <td><?=$items['email']?></td>
                                <td><?=!empty($items['end_at']) ? date('Y年m月d日 H:i', $items['end_at']) : '--'?></td>
                                <td><?=isset($items['complete_score']) && !is_null($items['complete_score']) && $items['complete_score']!='--'?number_format($items['complete_score'],2):'--'?></td>
                                <td>
                                    <?=Yii::t('common', 'complete_status_'.intval($items['complete_status']))?>
                                </td>
                            </tr>
                        <?php
                        }
                    } else {
                        ?>
                        <tr>
                            <td colspan="7"><?= Yii::t('common', 'no_data') ?></td>
                        </tr>
                    <?php
                    }
                    ?>
                    </tbody>
                </table>
                <nav class="text-right" id="<?=$componentCode?>-page">
                    <?php
                    if (!empty($datas)) {
                        echo TLinkPager::widget([
                            'id' => $componentCode . '-page-'.time(),
                            'displayPageSizeSelect' => false,
                            'pagination' => $pages,
                        ]);
                    }
                    ?>
                </nav>
            </div>
    </div>
</div>
<script>
    $(function(){
        $(".search").on('click', function(e){
            var form = $(this).parent();
            var score_status = form.find("select[name='score_status'] option:selected").val();
            var keyword = form.find("input[name='keyword']").val().trim();
            var url = '<?=Url::toRoute(['/teacher/'.$componentCode.'-result','courseId'=>$courseId,'modResId'=>$modResId, 'itemId' => $itemId, 'componentCode' => $componentCode])?>';
            $.get(url, {score_status: score_status, keyword: keyword}, function(r){
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
        });
        $("#<?=$componentCode?>-page .pagination").on('click', 'a', function(e){
            e.preventDefault();
            $.get($(this).attr('href'), function(r){
                if (r){
                    $("#<?=$componentCode?>-result").html(r);
                }else{
                    app.showMsg('<?=Yii::t('common', 'loading_fail')?>');
                    return false;
                }
            });
        });
    });
</script>