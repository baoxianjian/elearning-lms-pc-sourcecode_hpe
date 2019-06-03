<?php
/**
 * Created by PhpStorm.
 * User: Liu Cheng
 * Date: 2015/8/1
 * Time: 17:09
 */

use components\widgets\TLinkPager;
?>
<?=$this->render('/common/point-trans')?>
<div class="panel panel-default scoreList">
    <div class="panel-body">
        <div class="col-md-12 col-sm-12 nameList">
            <ul->
                <? foreach($data as $val):?>
                    <li class="col-md-4 col-sm-4 col-xs-12 popContainer">

                        <? if(Yii::$app->user->getId()!==$val->kid): ?>
                            <ul class="popPanel">
                                <li><a href="javascript:void(0);" onclick="attentionUser(this,'<?=$val->kid?>')" class="btn btn-xs"><?=in_array($val->kid,$attention_users) ? Yii::t('frontend','page_info_good_cancel').Yii::t('common','attention'):Yii::t('common','attention')?></a></li>
                                <li><a href="javascript:void(0);" class="btn btn-xs" onclick="showPointTransBox('<?=$val->kid?>')"><?=Yii::t('frontend', 'point_gratuity')?></a></li>
                            </ul>
                        <? endif;?>

                        <div class="-pp">
                            <!--
                            <? if(Yii::$app->user->getId()!==$val->kid): ?>
                            <div class="controlBtns">
                                <a href="javascript:void(0);" onclick="attentionUser(this,'<?=$val->kid?>')" class="btn btn-success btn-sm pull-right"><?=in_array($val->kid,$attention_users) ? Yii::t('frontend','page_info_good_cancel').Yii::t('common','attention'):Yii::t('common','attention')?></a>
                            </div>
                            <? endif;?>
                            -->
                            <div class="-avatar">
                            <?php 
                                if(!$val->thumb)
                                {
                                    if($val->gender==Yii::t('common','gender_male'))
                                    {
                                       $val->thumb='/static/common/images/man.jpeg'; 
                                    }
                                    else
                                    {
                                        $val->thumb='/static/common/images/woman.jpeg'; 
                                    }
                                }
                            ?>
                                <img class="-img" src="<?=$val->thumb?>">
                                <h5 class="-h5"><?=$val->real_name?></h5>
                            </div>
                            <p class="-p"><?=Yii::t('frontend','department')?>: <?=$val->orgnization_name?></p>
                            <p class="-p"><?=Yii::t('frontend','position')?>: <?=$val->position_name?></p>
                            <p class="-p"><?=Yii::t('common','user_email')?>: <?=$val->email?></p>
                            <a href="javascript:void(0);" onclick="showHistory('<?=$val->kid?>','<?=$val->old_real_name?>')" class="btn pull-right" data-toggle="modal" data-target="#sharedHistory"><?=Yii::t('frontend','share_record')?></a>
                            
                            <div class="c"></div>
                        </div>
                    </li>
                <? endforeach;?>
            </ul->
        </div>
        



              

        
        <div class="col-md-12">
            <nav class="paginationWrapper">
                <?php
                echo TLinkPager::widget([
                    'id' => 'page3',
                    'pagination' => $pages,
                ]);
                ?>
            </nav>
        </div>
    </div>
</div>