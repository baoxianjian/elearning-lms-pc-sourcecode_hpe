<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/3/29
 * Time: 11:23
 */
use common\models\social\SoAudience;
use yii\helpers\Url;
?>
<table class="table table-bordered table-hover table-striped table-center">
    <tbody>
    <tr>
        <td width="15%"><?=Yii::t('common', 'audience_code')?></td>
        <td width="15%"><?=Yii::t('common', 'audience_name')?></td>
        <td width="15%"><?=Yii::t('common', 'audience_created_at')?></td>
        <!--<td width="15%"><?/*=Yii::t('common', 'audience_end_at')*/?></td>-->
        <td width="5%"><?=Yii::t('common', 'audience_member')?></td>
        <td width="15%"><?=Yii::t('common', 'status')?></td>
        <td width="25%"><?=Yii::t('common', 'action')?></td>
    </tr>
    <?php
    if (!empty($result['data'])) {
        foreach ($result['data'] as $item){
    ?>
    <tr>
        <td><?=$item['audience_code']?></td>
        <td><span class="preview" title="<?=$item['audience_name']?>"><?=$item['audience_name']?></span></td>
        <td><?=date('Y-m-d', $item['created_at'])?></td>
        <!--<td><?/*=!empty($item['end_at']) ? date('Y-m-d', $item['end_at']) : ""*/?></td>-->
        <td><?=$item['memberCount']?></td>
        <td>
            <?php
            if ($item['status'] == SoAudience::STATUS_FLAG_TEMP) {
                echo Yii::t('common', 'status_0');
            }elseif ($item['status'] == SoAudience::STATUS_FLAG_NORMAL){
                echo Yii::t('common', 'status_1');
            }else{
                echo '<font color="#ff0000">'.Yii::t('common', 'status_2').'</font>';
            }
            ?>
        </td>
        <td>
            <?php
            if ($item['status'] == SoAudience::STATUS_FLAG_NORMAL){
            ?>
                <a href="<?=Url::toRoute(['/audience-manage/add', 'kid' => $item['kid'], 'view' => true])?>" class="btn-xs icon iconfont" title="<?=Yii::t('common', 'view_button')?>">&#x1007;</a>
                <a href="javascript:;" data-key="<?=$item['kid']?>" class="btn-xs icon iconfont copy" title="<?=Yii::t('common', 'copy_button')?>">&#x1005;</a>
                <a href="javascript:;" data-key="<?=$item['kid']?>" class="btn-xs glyphicon glyphicon-remove-circle stop" title="<?=Yii::t('common', 'change_status_stop')?>"></a>
            <?php
            }elseif ($item['status'] == SoAudience::STATUS_FLAG_TEMP){
            ?>
                <a href="javascript:;" data-key="<?=$item['kid']?>" class="btn-xs icon iconfont publish" title="<?=Yii::t('common', 'art_publish')?>">&#x1004;</a>
                <a href="javascript:;" data-key="<?=$item['kid']?>" class="btn-xs icon iconfont copy" title="<?=Yii::t('common', 'copy_button')?>">&#x1005;</a>
                <a href="<?=Url::toRoute(['/audience-manage/add', 'kid' => $item['kid']])?>" class="btn-xs icon iconfont" title="<?=Yii::t('common', 'edit_button')?>">&#x1001;</a>
                <a href="javascript:;" data-key="<?=$item['kid']?>" class="btn-xs icon iconfont deleted" title="<?=Yii::t('common', 'delete_button')?>">&#x1006;</a>
            <?php
            }else{
            ?>
                <a href="<?=Url::toRoute(['/audience-manage/add', 'kid' => $item['kid'], 'view' => true])?>" class="btn-xs icon iconfont" title="<?=Yii::t('common', 'view_button')?>">&#x1007;</a>
                <a href="javascript:;" data-key="<?=$item['kid']?>" class="btn-xs icon iconfont copy" title="<?=Yii::t('common', 'copy_button')?>">&#x1005;</a>
                <a href="javascript:;" data-key="<?=$item['kid']?>" class="btn-xs glyphicon glyphicon-ok-circle start" title="<?=Yii::t('common', 'change_status_start')?>"></a>
            <?php
            }
            ?>
        </td>
    </tr>
    <?php
        }
    }else{
    ?>
    <tr>
        <td colspan="6"><?=Yii::t('common', 'no_data')?></td>
    </tr>
    <?php
    }
    ?>
    </tbody>
</table>
<nav class="text-right" id="sodience-page">
    <?php
    if (!empty($result['data'])) {
        echo \components\widgets\TLinkPager::widget([
            'displayPageSizeSelect' => false,
            'pagination' => $result['page'],
        ]);
    }
    ?>
</nav>
<script>
    $(function(){
        $("#rightList .pagination").on('click', 'a', function(e){
            e.preventDefault();
            $.get($(this).attr('href'), function(r){
                if (r){
                    $("#rightList").html(r);
                }else{
                    app.showMsg('<?=Yii::t('common', 'loading_fail')?>');
                }
            });
        });
    });
</script>
