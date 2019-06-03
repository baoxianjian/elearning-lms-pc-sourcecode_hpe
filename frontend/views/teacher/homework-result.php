<?php
use yii\helpers\Url;
use components\widgets\TLinkPager;
use common\helpers\TTimeHelper;
use common\services\framework\DictionaryService;
use common\services\framework\UserService;
use common\models\framework\FwUser;

?>
<div class="header">
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    <h4 class="modal-title" id="myModalLabel">[<?= Yii::t('frontend', 'homework') ?>]<?=$model['title']?> - <?= Yii::t('frontend', 'learn_statistics') ?></h4>
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
                        <td>
                            <? if(!empty($items['end_at']) && ($items['end_at'] > $model->finish_before_at)){
                                echo Yii::t('common', 'complete_status_overtime');
                            }else{
                                echo Yii::t('common', 'complete_status_'.intval($items['complete_status']));
                            }?>
                        </td>
                        <td>
                            <?php
                            if (intval($items['complete_status']) > 0){
                            ?>
                            <a href="javascript:;" onclick="detailhomework('<?=$items['kid']?>','<?=$courseId?>','<?=$modResId?>','<?=$itemId?>');" class="btn-xs play-view"><?= Yii::t('common', 'view_button') ?></a>
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
                    <td colspan="7"><?= Yii::t('common', 'no_data') ?></td>
                </tr>
                <?php
            }
            ?>
            </tbody>
        </table>
        <nav class="text-right" id="<?=$componentCode?>-page">
            <?php
            if (!empty($data)) {
                echo TLinkPager::widget([
                    'id' => $componentCode . '-page-'.time(),
                    'displayPageSizeSelect' => false,
                    'pagination' => $page,
                ]);
            }
            ?>
        </nav>
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
            $(".play-view").on('click', function(e){
                e.preventDefault();
                $.get($(this).attr('href'), {dialog: true}, function(html){
                    if (html){
                        $("#<?=$componentCode?>-result").html(html);
                    }else{
                        return app.showMsg(app.msg.NETWORKERROR);
                    }
                });
            });
        });
    </script>

    <div class="c"></div>
</div>

<script type="text/javascript">
    function resultUserDetail(id){
        if (id == "") return ;
        $.get('<?=Url::toRoute(['/teacher/homework-result-detail'])?>', {id: id}, function(html){
            if (html){
                $("#<?=$componentCode?>-result").html(html);
            }else{
                return app.showMsg(app.msg.NETWORKERROR);
            }
        });
    }
</script>