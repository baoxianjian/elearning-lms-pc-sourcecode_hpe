<?php
/**
 * Created by PhpStorm.
 * User: adophper
 * Date: 2016/3/8
 * Time: 16:03
 */
use components\widgets\TLinkPager;
use common\models\framework\FwPointRule;
$service = new FwPointRule();
?>
<table class="table table-bordered table-hover table-striped table-center">
    <tbody>
    <tr>
        <td width="60%"><?=Yii::t('common', 'point_name')?></td>
        <td width="20%"><?=Yii::t('common', 'cycle_range')?></td>
        <td width="20%"><?=Yii::t('common', 'standard_value')?></td>
    </tr>
    <?php
    if (!empty($result['data'])) {
        foreach ($result['data'] as $item) {
            ?>
            <tr>
                <td align="left"><span class="preview" title="<?=$item['point_name']?>"><?=$item['point_name']?></span></td>
                <td><?php
                    echo $service->getCycleRanges($item['cycle_range'])
                    ?></td>
                <td><?=$item['standard_value']?></td>
            </tr>
            <?php
        }
    }else{
        ?>
        <tr><td colspan="3"><?=Yii::t('frontend', 'temp_no_data')?>!</td></tr>
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
            'id' => 'integral_point_rule_page',
            'pagination' => $result['pages'],
            'displayPageSizeSelect'=>false
        ]);
        ?>
    </nav>
    <script>
        $(function(){
            $("#integral_point_rule .pagination").on('click', 'a', function(e){
                e.preventDefault();
                $.get($(this).attr('href'), function(data){
                    if (data){
                        $("#integral_point_rule").html(data);
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
