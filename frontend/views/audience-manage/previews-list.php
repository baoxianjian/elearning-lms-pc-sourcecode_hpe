<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/4/20
 * Time: 11:10
 */
?>
<table class="table table-bordered table-hover table-striped table-center">
    <tbody>
    <tr>
        <td width="10%"><?=Yii::t('common', 'xls_row')?></td>
        <td width="25%"><?=Yii::t('common', 'user_name')?></td>
        <td width="15%"><?=Yii::t('common', 'real_name')?></td>
        <td width="25%"><?=Yii::t('common', 'email')?></td>
        <td width="25%"><?=Yii::t('common', 'status')?></td>
    </tr>
    <?php
    if (!empty($data['data'])) {
        foreach ($data['data'] as $items) {
    ?>
    <tr>
        <td><?=$items['row']?></td>
        <td><?=$items['user_name']?></td>
        <td><?=urldecode($items['real_name'])?></td>
        <td><?=$items['email']?></td>
        <td style="color: #ff0000;"><?=$items['res']?></td>
    </tr>
    <?php
        }
    }else{
    ?>
    <tr>
        <td colspan="5"><?=Yii::t('common', 'no_data')?></td>
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
<span id="importSuccessNumbers" style="display: none;"><?=$data['successNumbers']?></span>
<span id="importFailNumbers" style="display: none;"><?=$data['failNumbers']?></span>
<script>
$(function(){
    $("#successNumbers").html('<?=$data['successNumbers']?>');
    $("#failNumbers").html('<?=$data['failNumbers']?>');
    $(".tab-content .pagination").on('click', 'a', function(e){
        e.preventDefault();
        var id = $(this).parents('.tab-pane').attr('id');
        $.get($(this).attr('href'), function(r){
            if (r){
                $("#"+id).html(r);
                app.refreshAlert($('#addImport'));
            } else {
                app.showMsg('<?=Yii::t('common', 'loading_fail')?>');
            }
        });
    });
});
</script>
