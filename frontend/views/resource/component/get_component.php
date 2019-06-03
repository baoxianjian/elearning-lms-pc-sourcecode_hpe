<?php
/**
 * Created by PhpStorm.
 * User: adophper
 * Date: 2015/12/8
 * Time: 16:22
 */
use yii\helpers\Html;
use yii\helpers\Url;
?>
<div class="componentPart">
    <?php
    if(!empty($resources)){
    ?>
    <div class="panel panel-default hotNews">
        <div class="panel-heading">
            <i class="glyphicon glyphicon-dashboard"></i> <?=Yii::t('common','resource')?>
            <a href="javascript:void(0)" class="pull-right miniBtn"><?= Yii::t('frontend', 'toggle_tab') ?></a>
        </div>
        <div class="panel-body resourceStatu resourcePart">
            <ul>
                <?php  foreach($resources as $component){?>
                    <li class="ui-draggable ui-draggable-handle" title="<?=$component['title']?>" kid="<?=$component['kid']?>" data-type="coursewares" data-code="<?=$component['component_code']?>" data-model="<?=$component['component_code']?>" data-window="<?=$component['window_mode']?>" data-uri="<?=$component['action_url'] ? Url::toRoute([$component['action_url']]) : ''?>">
                        <?=$component['icon']?>
                        <a>
                            <p class="statuNum scrom"><?=$component['title']?></p>
                            <p><?=$component['description']?></p>
                        </a>
                    </li>
                <? }?>
            </ul>
        </div>
    </div>
    <?php
    }
    if(!empty($activity)){
    ?>
    <div class="panel panel-default hotNews">
        <div class="panel-heading">
            <i class="glyphicon glyphicon-dashboard"></i> <?=Yii::t('common','active')?>
            <a class="pull-right miniBtn"><?= Yii::t('frontend', 'toggle_tab') ?></a>
        </div>
        <div class="panel-body resourceStatu resourcePart">
            <ul>
                <?php  foreach($activity as $component){?>
                    <li class="ui-draggable ui-draggable-handle" title="<?=$component['title']?>" kid="<?=$component['kid']?>" data-type="activity" data-code="<?=$component['component_code']?>"  data-model="<?=$component['component_code']?>" data-window="<?=$component['window_mode']?>" data-uri="<?=$component['action_url'] ? Url::toRoute([$component['action_url']]) : ''?>">
                        <?=$component['icon']?>
                        <a>
                            <p class="statuNum scrom"><?=$component['title']?></p>
                            <p><?=$component['description']?></p>
                        </a>
                    </li>
                <?php } ?>
            </ul>
        </div>
    </div>
    <?php
    }
    ?>
</div>
<script>
    $(function(){
        $('.miniBtn').bind('click', function() {
            if ($('#componentPanel').hasClass('miniestTools')) {
                $('#componentPanel').removeClass('miniestTools').css('top','0')
            } else {
                $('#componentPanel').addClass('miniestTools').css('top','290px')
            }
        });
    });
</script>

<!--  滚动时,调整资源面板的位置 -->
 <script type="text/javascript">
  $(document).ready(function() {
    function resetPanel() {
      var activeLine = $(document).scrollTop();
      (activeLine >= 90) ? $('.miniestTools').css('top', '120px'): $('.miniestTools').css('top', '290px');
    }
    resetPanel();
    $(document).bind('scroll', resetPanel);
  })
  </script>
