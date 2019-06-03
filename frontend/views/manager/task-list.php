<?php
/**
 * Created by PhpStorm.
 * User: Liu Cheng
 * Date: 2015/12/29
 * Time: 11:30
 */
use common\models\message\MsTaskItem;
use common\helpers\TStringHelper;
use components\widgets\TLinkPager;
use yii\helpers\Html;

?>
<style>
    .p_a{
        color: #337ab7 !important;
    }
    .p_a:hover{
        color: #23527c !important;
        cursor: pointer;
    }
</style>
<div class="panel-group" id="accordion" role="tablist" aria-multiselectable="true" style="float: left;width: 100%">
    <? $i=1;?>
    <? if (count($task) > 0) : foreach($task as $t): ?>
        <div class="panel panel-default scoreList pathBlock myGroup">
            <div role="tab" id="heading<?=$i?>">
                <table>
                    <tr>
                        <td class="colOne">
                            <? if ($t['item_type']===MsTaskItem::ITEM_TYPE_COURSE):?>
                            <img src="<?=$t['theme'] ? $t['theme']:'/static/frontend/images/course_theme_big.png' ?>" />
                            <? elseif ($t['item_type']===MsTaskItem::ITEM_TYPE_EXAM):?>
                                <img src="<?=$t['theme'] ? $t['theme']:'/static/frontend/images/exam_theme.jpg' ?>" />
                            <? elseif ($t['item_type']===MsTaskItem::ITEM_TYPE_SURVEY):?>
                                <img src="<?=$t['theme'] ? $t['theme']:'/static/frontend/images/survey_theme.jpg' ?>" />
                            <? endif; ?>
                        </td>
                        <td class="colTow">
                            <h3><a data-toggle="collapse" data-parent="#accordion" href="#collapse<?=$i?>" aria-expanded="true" aria-controls="collapse<?=$i?>"><?=TStringHelper::GetTaskItemTypeText($t['item_type']).Html::encode($t['course_name'])?></a></h3>
                            <span><?=Yii::t('frontend', 'to_the_end_time')?> <strong><?php echo $t['plan_complete_str'];?></strong></span></td>
                        <td class="colThree">
                            <div id="canvas-holder">
                                <canvas id="chart-area-<?=$i?>" width="100" height="100" />
                            </div>
                            <i><?=Yii::t('frontend', 'completion_degree')?>:<?= round(($t['count'] - $t['un_count']) / $t['count'] * 100, 2) ?>%</i>
                        </td>
                    </tr>
                </table>
                <hr />
            </div>
            <? if(intval($t['un_count']) > 0):?>
                <div id="collapse<?=$i?>" class="panel-collapse collapse <?=$i==1?'in':''?> myGroupList" role="tabpanel" aria-labelledby="heading<?=$i?>">
                    <div class="panel-body">
                        <p><?=Yii::t('frontend', 'unfinish_student')?>(<?=$t['un_count']?>)&nbsp;&nbsp;<?if($t['un_count']>8): ?><a href="javascript:void(0);" class="showAll" onclick="showAll(this);"><?=Yii::t('frontend', 'all')?></a> <? endif; ?> <?if($t['plan_complete_str']!==Yii::t('common', 'status_2') && $t['un_count']>0): ?><a class="beginBtn pull-right" href="javascript:void(0);" onclick="sendRemind('collapse<?=$i?>','<?=$t['item_id']?>','<?=$t['item_type']?>','<?=$t['plan_complete_at']?>')"><?=Yii::t('frontend', 'remind_all')?></a><? endif; ?></p>
                        <ul class="thumbList">
                            <? foreach($t["un_users"] as $u): ?>


                                <li class="popContainer">
                                    <a href="javascript:void(0)">
                                        <img src="<?= TStringHelper::Thumb($u['thumb'],$u['gender']) ?>" alt="<?= Yii::t('common', 'headimg_url') ?>" width="99" height="99" />
                                        <p class="name" title="<?=$u['email']?>"><?=Html::encode($u['real_name'])?></p>
                                    </a>
                                    <? if($t['item_type'] === MsTaskItem::ITEM_TYPE_COURSE): ?>
                                        <p class="scores p_a" onclick="showCourseDetail('<?=$u['kid']?>','<?=$t['item_id']?>')"><?=Yii::t('frontend', 'completion_degree')?>:<?= round(($u['resCompleteNum'] / $t['courseResNum']) * 100, 2) ?>%</p>
                                    <? else: ?>
                                        <p class="scores"><?=Yii::t('frontend', 'completion_degree')?>:<?= round(($u['resCompleteNum'] / $t['courseResNum']) * 100, 2) ?>%</p>
                                    <? endif; ?>
                                    <input type="hidden" name="un_user[]" value="<?=$u['kid']?>" />

                                    <ul class="popPanel">
                                      <!--  <li><a href="javascript:void(0);" onclick="attentionUser(this,'<?=$u['kid']?>')" class="btn btn-xs"><?=in_array($u['kid'],$attention_users) ? Yii::t('frontend','page_info_good_cancel').Yii::t('common','attention'):Yii::t('common','attention')?></a></li>-->
                                        <?if($uid != $u['kid']):?>
                                        <li><a href="javascript:void(0);" class="btn btn-xs" onclick="showPointTransBox('<?=$u['kid']?>')"><?=Yii::t('frontend', 'point_gratuity')?></a></li>
                                        <?endif; ?>
                                        <?if($t['plan_complete_str']!==Yii::t('common', 'status_2')): ?>
                                            <li><a href="javascript:void(0);" class="btn btn-xs" onclick="sendRemindP('<?=$u['kid']?>','<?=$t['item_id']?>','<?=$t['item_type']?>','<?=$t['plan_complete_at']?>')"><?=Yii::t('frontend', 'remind_this')?></a></li>
                                        <? endif; ?>
                                    </ul>
                                </li>
















                            <? endforeach;?>
                        </ul>
                    </div>
                </div>
            <? endif;?>
        </div>
        <? $i++;?>
    <? endforeach; else:?>
        <div class="centerBtnArea noData">
            <i class="glyphicon glyphicon-calendar"></i>
            <p><?=Yii::t('common', 'no_data')?></p>
        </div>
    <?endif; ?>
</div>
<div id="manager_task_page" style="text-align:center">
    <?php
    echo TLinkPager::widget([
        'id' => 'my_team_page',
        'pagination' => $page,
        'displayPageSizeSelect'=>false
    ]);
    ?>
</div>
<script>
    <? $i = 1;?>
    <? if (count($task) > 0) : foreach($task as $t): ?>
    var pieData<?=$i?> = [{
        value: <?=$t['count'] - $t['un_count']?>,
        color: "#f56a40",
        highlight: "#f89378",
        label: "<?=Yii::t('frontend', 'finish')?>"
    }, {
        value: <?=$t['un_count']?>,
        color: "#eaeded",
        highlight: "#D8D8D8",
        label: "<?=Yii::t('frontend', 'page_lesson_hot_tab_2')?>"
    }];
    <? $i++;?>
    <? endforeach; endif; ?>

    $(function () {
        $("#manager_task_page .pagination").on('click', 'a', function(e){
            e.preventDefault();
            ajaxGet($(this).attr('href'), "task_list_panel");
        });

        <? $i = 1;?>
        <? if (count($task) > 0) : foreach($task as $t): ?>
        var ctx = document.getElementById("chart-area-<?=$i?>").getContext("2d");
        window.myPie = new Chart(ctx).Pie(pieData<?=$i?>);
        <? $i++;?>
        <? endforeach; endif;?>
    });
</script>