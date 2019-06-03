<?php
/**
 * Created by PhpStorm.
 * User: LiuCheng
 * Date: 2015/6/12
 * Time: 11:16
 */
use yii\helpers\Url;
use common\helpers\TTimeHelper;

?>
<? foreach ($data as $v): ?>
    <? if ($v['grade']): ?>
        <div class="timeline-item eventCate2">
            <div class="timeline-icon">
                <i class="glyphicon glyphicon-book" title="课程"></i>
            </div>
            <div class="timeline-content">
                <h2><?=$v['course_name']?><strong class="noticeOver">已完成</strong></h2>
                <table class="timeLine_pathBlock">
                    <tr>
                        <td>历时:45分钟</td>
                        <td>成绩:<?=$v['grade']?>分</td>
                    </tr>
                    <tr>
                        <td>报名数:<?=$v['reg_count']?>人次</td>
                        <td>关注人数:0人</td>
                    </tr>
                </table>
                <hr/>
                <span><i class="glyphicon glyphicon-time"></i>注册时间: <?=TTimeHelper::toDate($v['reg_time'])?></span>
                <a href="<?= Yii::$app->urlManager->createUrl(['resource/course/view', 'id' => $v['course_id']]) ?>" class="pull-right">回顾课程</a>
            </div>
        </div>
    <? else: ?>
        <div class="timeline-item eventCate3">
            <div class="timeline-icon">
                <i class="glyphicon glyphicon-book"></i>
            </div>
            <div class="timeline-content">
                <h2><?=$v['course_name']?></h2>

                <p>
                    <?=$v['course_desc']?>
                </p>
                <hr/>
                <span><i class="glyphicon glyphicon-time"></i>注册时间: <?=TTimeHelper::toDate($v['reg_time'])?></span>
                <span><i class="glyphicon glyphicon-time"></i>结束时间: <?=TTimeHelper::toDate($v['end_time'])?></span>
                <a href="<?= Yii::$app->urlManager->createUrl(['resource/course/view', 'id' => $v['course_id']]) ?>" class="pull-right">去完成</a>
            </div>
        </div>
    <? endif; ?>
<? endforeach; ?>