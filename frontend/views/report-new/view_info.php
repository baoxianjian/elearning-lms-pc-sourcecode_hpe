<?php
/**
 * Created by PhpStorm.
 * User: Liu Cheng
 * Date: 2015/8/20
 * Time: 11:30
 */
use components\widgets\TLinkPager;
use common\helpers\TTimeHelper;
use common\helpers\TStringHelper;
use common\models\message\MsTask;
?>

 <style>
<!--
table#table_body td{
    overflow: hidden;
    max-width: 100px;
    text-overflow: ellipsis;
    white-space: nowrap;
}

-->
</style>
 
  <div class="header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="myModalLabel"><?=Yii::t('frontend','detail')?> </h4>
       </div>
      <div class="content">    
 <table class="table table-bordered table-hover table-center">
                    <tbody>
                      <tr>
                        <td><?= Yii::t('common', 'course_name') ?></td>
                        <td><? if('reg'==$type){?><?= Yii::t('frontend', 'signup_time') ?> <? } else { ?>  <?= Yii::t('frontend', 'exam_comp_time') ?>  <?} ?></td>
                        
                      </tr>
                      <? foreach ($data as $pinfo): ?>
                      <tr>
                        <td><?=$pinfo['course_name'] ?></td>                      
                        <td><?=$pinfo['created_at'] ?></td>
                      </tr>
                     <? endforeach; ?>
                    </tbody>
                  </table>
<nav>
    <?php
    echo TLinkPager::widget([
        'id' => 'page',
        'pagination' => $pages,
        'options'=>['class'=>'pagination pull-right']
    ]);
    ?>
</nav>
<div class="c"></div>
                     	</div>