<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/4/6
 * Time: 15:15
 */

?>

<table class="table table-bordered table-hover table-striped table-center addPageList">
    <tbody>
    <tr>
        <td width="15%">
            <?php
            if (!$view){
            ?>
            <input type="checkbox" class="checkbox" id="checkAll">
            <?php
            }
            ?>
            <?=Yii::t('common', 'real_name')?>
        </td>
        <td width="20%"><?=Yii::t('common', 'department')?></td>
        <td width="20%"><?=Yii::t('common', 'position')?></td>
        <td width="20%"><?=Yii::t('common', 'user_email')?></td>
        <td width="20%"><?=Yii::t('common', 'mobile_no')?></td>
        <?php
        if (!$view){
        ?>
        <td width="5%"><?=Yii::t('common', 'action')?></td>
        <?php
        }
        ?>
    </tr>
    <?php
    if (!empty($data['data'])) {
        foreach ($data['data'] as $item){
    ?>
    <tr class="fwUser">
        <td title="<?=$item->real_name?>">
            <span class="preview">
            <?php
            if (!$view){
            ?>
                <input type="checkbox" class="checkbox" value="<?=$item->kid?>" data-user="<?=$item['user_id']?>">
            <?php
            }
            ?>
                <?=$item->real_name?>
            </span>
        </td>
        <td title="<?=$item->orgnization?>"><span class="preview"><?=$item->orgnization?></span></td>
        <td title="<?=$item->position?>"><span class="preview"><?=$item->position?></span></td>
        <td title="<?=$item->email?>"><span class="preview"><?=$item->email?></span></td>
        <td title="<?=$item->mobile_no?>"><span class="preview"><?=$item->mobile_no?></span></td>
        <?php
        if (!$view){
        ?>
        <td>
            <a href="###" class="btn-xs icon iconfont removeUser" id="remove_<?=$item['kid']?>" data-user="<?=$item['user_id']?>" title="<?=Yii::t('common', 'delete')?>">&#x1006;</a>
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
        <td colspan="6"><?=Yii::t('common', 'no_data')?></td>
    </tr>
    <?php
    }
    ?>
    </tbody>
</table>
<nav class="c text-right">
    <?php
    if (!empty($data['data'])) {
        echo \components\widgets\TLinkPager::widget([
            'id' => 'audience-page',
            'displayPageSizeSelect' => false,
            'pagination' => $data['page'],
        ]);
    }
    ?>
</nav>
<script>
    $(function(){
        $("#audience_temp .pagination").on('click', 'a', function(e){
            e.preventDefault();
            $.get($(this).attr('href'), function(r){
                if (r){
                    $("#audience_temp").html(r);
                } else {
                    app.showMsg('<?=Yii::t('common', 'loading_fail')?>');
                }
            });
        });
    });
</script>
