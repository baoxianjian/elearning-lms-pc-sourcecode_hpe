<?php
/**
 * Created by PhpStorm.
 * User: Liu Cheng, baoxianjian
 * Date: 2015/8/1, 2015/12/29
 * Time: 17:09, 16:44 
 */

use common\helpers\TStringHelper;
use common\helpers\TTimeHelper;
use components\widgets\TLinkPager;
use yii\helpers\Html;

?>

<div class="panel panel-default scoreList">
              <div class="panel-body">
                <? foreach($data as $val):?> 
                  <img  class="-thumb" src="<? if($val->theme_url):?><?=$val->theme_url?><? else:?>/static/common/images/default.jpg<? endif; ?>" alt="">
                  <div class="-course">
                  <h3><a href="<?= Yii::$app->urlManager->createUrl(['resource/course/view', 'id' => $val->kid]) ?>"><?= TStringHelper::HighlightDecode(Html::encode($val->course_name)) ?></a> <a href="javascript:void(0);" onclick="collectCourse(this,'<?=$val->kid?>')" class="follow pull-right" role="button"><?=in_array($val->kid,$collect_courses) ? Yii::t('common','audience_code').Yii::t('common','collection'):Yii::t('common','collection')?></a></h3>
                <span class="-info"><span class="-attr"><?=Yii::t('common','audience_code')?>: </span>[<?=$val->course_code?>]</span>
                <span class="-info"><span class="-attr"><?=Yii::t('frontend','date_of_issue')?>: </span><?= TTimeHelper::toDate($val->release_at)?></span>
                <span class="-info"><span class="-attr"><?=Yii::t('common','tag')?>: </span>
                <? foreach($val->tag_value as $vtag):?> 
                   <a class="-tag" href="###"><?=$vtag?></a>&nbsp;
                <? endforeach;?>
                </span>
                <p class="-desc-p">
                <? if($val->content):?>
                    <?=TStringHelper::HighlightDecode(Html::encode(trim($val->content))); ?>...
                <? else:?>
                    <?=Yii::t('frontend','temp_no_introduction_course')?>
                <? endif; ?>
                </p>
                </div>
                <div class="c"></div>
                <div class="-mods">
                    <div class="-mod">
                    
                        <? foreach($val->file_names as $key2=>$val2):?>
                        <dl class="-source">
                            <dt class="-dt">
                                <aside class="-aside"><?=$val->file_extensions[$key2]?></aside>
                                <?=$val2?><?if($val->file_contents[$key2]):?>：<?=$val->file_contents[$key2]?>
                                <?endif;?>
                            </dt>
                            
                            <dd class="-dd pull-right"><span class="-attr">【<?=Yii::t('common','supplier')?>】：</span><?=$val->file_suppliers[$key2]?></dd>
                        </dl>
                        <? endforeach;?>
                        
                    </div>
                    <div class="-expan"><i class="glyphicon glyphicon-chevron-down -expan-a"></i></div>
                </div>
                <hr>
               <? endforeach;?>  

                <div class="col-md-12">
                  <nav>
                    <?php
                        echo TLinkPager::widget([
                            'id' => 'page1',
                            'pagination' => $pages,
                        ]);
                    ?>
                  </nav>
                </div>
              </div>
            </div>
