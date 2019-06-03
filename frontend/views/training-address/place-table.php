<?php
use yii\helpers\Html;
use yii\helpers\Url;
use components\widgets\TLinkPager;
use common\helpers\TTimeHelper;
?>
<table class="table table-bordered table-hover table-striped table-center">
  <tbody>
      <tr>
        <td width="10%"><?=Yii::t('common', 'serial_number')?></td>
        <td width="20%"><?=Yii::t('common', 'name')?></td>
        <td width="25%"><?= Yii::t('common', 'description') ?></td>
        <td width="15%"><?=Yii::t('common', 'address_code')?></td>
        <td width="10%"><?=Yii::t('common', 'status')?></td>
        <td width="20%"><?= Yii::t('common', 'action') ?></td>
      </tr>
      <?
      if(!empty($data)){

      foreach ($data as $k => $v) {
      ?>
      <tr>
        <td><span id="number_<?=$v->kid?>" data="<?=$k+1 ?>"><?=$k+1 ?></span></td>
        <td align="left">
            <a href="###" class="preview" onclick='view("<?=$v->kid?>")' id="address_name_<?=$v->kid?>" title="<?=Html::encode($v->address_name)?>"><?=Html::encode($v->address_name)?></a>
        </td>
          <td align="left">
              <span class="preview" title="<?=Html::encode($v->description)?>" id="description_<?=$v->kid?>"><?=Html::encode($v->description)?></span>
          </td>
          <td align="left">
              <span class="preview" title="<?=Html::encode($v->address_code)?>" id="code_<?=$v->kid?>"><?=Html::encode($v->address_code)?></span>
          </td>
        <? $array = array('0' => Yii::t('common', 'status_temp'),'1' => Yii::t('common', 'status_normal') , '2' => Yii::t('common', 'status_stop') );?>
        <td>
            <span id="status_<?=$v->kid?>" <? if($array["$v->status"] == Yii::t('common', 'status_stop')){ echo 'style="color:red"';}?>><?=$array["$v->status"]?></span>
        </td>
        <td>
          <a href="###" class="btn-xs icon iconfont"  onclick='view("<?=$v->kid?>")' title="<?= Yii::t('common', 'view_button') ?>">&#x1007;</a>
          <a href="###" class="btn-xs icon iconfont"  onclick='updateview("<?=$v->kid?>")' title="<?=Yii::t('frontend', 'editor_text')?>">&#x1001;</a>
          <a href="###" id="stop_<?=$v->kid?>" class="btn-xs glyphicon <? if($v->status == 2){ echo 'glyphicon-ok-circle';}else{echo 'glyphicon-remove-circle';}?>" onclick='stop("<?=$v->kid?>","<? if($v->status == 2){ echo 'start';}else{echo 'stop';}?>")' title="<? if($v->status == 2){ echo Yii::t('common', 'change_status_start');}else{echo Yii::t('common', 'change_status_stop');}?>"></a>
          <a href="###" class="btn-xs icon iconfont" onclick='delcfm("<?=$v->kid?>")' title="<?= Yii::t('common', 'delete_button') ?>">&#x1006;</a>
        </td>
      </tr>
      <? } ?>
      <?}else{?>
          <tr>
              <td style="width: 100%;text-align:left !important" colspan="6"><?=Yii::t('yii', 'No results found.')?></td>
          </tr>
      <?}?>
  </tbody>
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