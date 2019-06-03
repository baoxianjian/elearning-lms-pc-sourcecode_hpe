<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/3/9
 * Time: 10:13
 */
use components\widgets\TLinkPager;
?>
<table class="table table-bordered table-hover table-striped table-center">
    <tbody>
    <tr>
        <td width="50%"><?=Yii::t('common', 'art_from')?></td>
        <td width="15%"><?=Yii::t('common', 'point_type')?></td>
        <td width="10%"><?=Yii::t('common', 'score_detail')?></td>
        <td width="25%"><?=Yii::t('common', 'gen_at')?></td>
    </tr>
    <?php
    if (!empty($result['data'])) {
        foreach ($result['data'] as $item) {
    ?>
    <tr data-key="<?=$item['kid']?>">
        <td align="left"><span class="preview" title="<?=$item['reason']?>"><?=$item['reason']?></span></td>
        <td><?=Yii::t('common', 'point_type_'.$item['point_type'])?></td>
        <td><?=doubleval($item['point'])?></td>
        <td><?=date('Y-m-d H:i:s', $item['get_at'])?></td>
    </tr>
    <?php
        }
    }else{
    ?>
    <tr><td colspan="4"><?=Yii::t('frontend', 'temp_no_data')?>!</td></tr>
    <?php
    }
    ?>
    </tbody>
</table>
<?php
if (!empty($result['pages'])){
    ?>
    <nav style="text-align: right;">
        <?php
        echo TLinkPager::widget([
            'id' => 'integral_list_page',
            'pagination' => $result['pages'],
            'displayPageSizeSelect'=>false
        ]);
        ?>
    </nav>
    <script>
        $(function(){
            $("#integral_list .pagination").on('click', 'a', function(e){
                e.preventDefault();
                $.get($(this).attr('href'), function(data){
                    if (data){
                        $("#integral_list").html(data);
                    }else{
                        app.showMsg('<?=Yii::t('frontend', 'data_fail')?>');
                        return false;
                    }
                });
            });
        });
    </script>
    <?php
}
?>