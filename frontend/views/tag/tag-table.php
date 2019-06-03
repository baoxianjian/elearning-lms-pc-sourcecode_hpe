<?php
use yii\helpers\Html;
use yii\helpers\Url;
use components\widgets\TLinkPager;
use common\helpers\TTimeHelper;
?>
    <table style="table-layout: fixed" class="table table-bordered table-hover table_teacher">
    <?php if(!empty($data)){?>
    <tr>
        <td style="width: 16%;"><?=Yii::t('frontend', 'tag_mingcheng')?></td>
        <td><?=Yii::t('frontend', 'tag_creat_date')?></td>
        <td><?=Yii::t('frontend', 'tag_rl')?></td>
        <td><?=Yii::t('frontend', 'tag_opt')?></td>
    </tr>
    <?php foreach($data as $v):?>
        <tr id="tag<?=$v->kid?>">
            <td id="value<?=$v->kid?>" data-name='<?=Html::encode($v->tag_value)?>'><?=Html::encode($v->tag_value)?></td>
            <td><?=TTimeHelper::FormatTime($v->created_at,2)?></td>
            <td><?=$v->reference_count?></td>
            <td>
                 <?php if($v->reference_count == 0) {
                     echo '<a href="#" class="btn-xs icon iconfont"  onclick="changelabel(\'' . $v->kid . '\')">ခ</a>
                        <a href="#" class="btn-xs icon iconfont" onclick="delcfm(\'' . $v->kid . '\')">ဆ</a>';
                     };
                 ?>
            </td>
        </tr>
    <?php endforeach;?>
</table>
<nav>
    <?php
    echo TLinkPager::widget([
        'id' => 'page',
        'pagination' => $pages,
        'displayPageSizeSelect'=>true
    ]);
    ?>
</nav>
<script>
    $(function(){
        $("#content .pagination").on('click', 'a', function(e){
            e.preventDefault();
            ajaxGet($(this).attr('href'), 'content');
        });
    });
</script>
<?php
}else{
    echo '
    <tr>
        <td style="width: 16%;">'.Yii::t('frontend', 'tag_mingcheng').'</td>
        <td>'.Yii::t('frontend', 'tag_creat_date').'</td>
        <td>'.Yii::t('frontend', 'tag_rl').'</td>
        <td>'.Yii::t('frontend', 'tag_opt').'</td>
    </tr>
    <tr>
        <td style="width: 100%;text-align:left !important" colspan="4"> '.Yii::t('yii', 'No results found.').'</td>
    </tr>
';
}
?>