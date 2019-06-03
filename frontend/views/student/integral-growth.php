<?php
/**
 * Created by PhpStorm.
 * User: adophper
 * Date: 2016/3/8
 * Time: 16:17
 */
use components\widgets\TLinkPager;
?>

<table class="table table-bordered table-hover table-striped table-center">
    <tbody>
    <tr>
        <td width="15%"><?=Yii::t('common', 'stage_name')?></td>
        <td width="20%"><?=Yii::t('common', 'level_name')?></td>
        <td width="40%"><?=Yii::t('common', 'description')?></td>
        <td width="10%"><?=Yii::t('common', 'sequence_number')?></td>
        <td width="15%"><?=Yii::t('common', 'integral')?></td>
    </tr>
    <?php
    if (!empty($result['data'])) {
    foreach ($result['data'] as $item) {
    ?>
    <tr>
        <td><?=$item['stage_name']?></td>
        <td><?=$item['level_name']?></td>
        <td align="left"><span class="preview" title="<?=$item['description']?>"><?=$item['description']?></span></td>
        <td><?=$item['sequence_number']?></td>
        <td><?=$item['require_point']?></td>
    </tr>
        <?php
    }
    }else{
        ?>
        <tr><td colspan="5"><?=Yii::t('frontend', 'temp_no_data')?>!</td></tr>
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
            'id' => 'integral_growth_page',
            'pagination' => $result['pages'],
            'displayPageSizeSelect'=>false
        ]);
        ?>
    </nav>
    <script>
        $(function(){
            $("#integral_growth .pagination").on('click', 'a', function(e){
                e.preventDefault();
                $.get($(this).attr('href'), function(data){
                    if (data){
                        $("#integral_growth").html(data);
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
