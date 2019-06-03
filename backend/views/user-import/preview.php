<?php
/**
 * Created by PhpStorm.
 * User: Alex Liu
 * Date: 2016/7/20
 * Time: 11:10
 */
use common\models\framework\FwUser;
use components\widgets\TLinkPager;

?>
<table class="table table-bordered table-hover table-striped table-center">
    <tbody>
    <tr>
        <td width="7%"><?=Yii::t('common', 'xls_row')?></td>
        <td width="10%"><?=Yii::t('common', '操作')?></td>
        <td width="15%"><?=Yii::t('common', 'user_name')?></td>
        <td width="10%"><?=Yii::t('common', 'real_name')?></td>
        <td width="14%"><?=Yii::t('common', 'email')?></td>
        <td width="10%"><?=Yii::t('common', '部门')?></td>
        <td width="10%"><?=Yii::t('common', '岗位')?></td>
        <td width="10%"><?=Yii::t('common', '是否经理')?></td>
        <td width="14%"><?=Yii::t('common', '上级经理账户名')?></td>
    </tr>
    <?php
    if (!empty($data)) {
        foreach ($data as $items) {
    ?>
    <tr>
        <td><?=$items['row']?></td>
        <td><?= $items['op'] === 'A' ? '新增' : '删除' ?></td>
        <td><?=$items['user_name']?></td>
        <td><?=$items['real_name']?></td>
        <td><?=$items['email']?></td>
        <td><?=$items['orgnization_name']?></td>
        <td><?=$items['position_name']?></td>
        <td><?= $items['is_manager'] === FwUser::MANAGER_FLAG_YES ? '是' : '否' ?></td>
        <td><?=$items['manager_account']?></td>
    </tr>
    <?php
        }
    }else{
    ?>
    <tr>
        <td colspan="9"><?=Yii::t('common', 'no_data')?></td>
    </tr>
    <?php
    }
    ?>
    </tbody>
</table>
<nav class="c text-right">
    <?php
    if (!empty($page)) {
        echo TLinkPager::widget([
            'id' => 'audience-page',
            'displayPageSizeSelect' => false,
            'pagination' => $page,
        ]);
    }
    ?>
</nav>
<script>
$(function(){
    $("#previews .pagination").on('click', 'a', function(e){
        e.preventDefault();
        $.get($(this).attr('href'), function(r){
            if (r){
                $("#previews").html(r);
            } else {
                app.showMsg('<?=Yii::t('common', 'loading_fail')?>');
            }
        });
    });
});
</script>
