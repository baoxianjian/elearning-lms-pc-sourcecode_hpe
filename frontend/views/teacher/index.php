<?php
/**
 * User: zhanglei
 * Date: 2015/8/12
 * Time: 13:02
 */
use components\widgets\TBreadcrumbs;
use yii\helpers\Url;
use common\helpers\TTimeHelper;
use common\helpers\TStringHelper;
use yii\helpers\Html;
use components\widgets\TLinkPager;

$this->pageTitle = Yii::t('frontend','teacher_home');// Yii::t('frontend', 'page_lesson_hot_title');
$this->params['breadcrumbs'][] = $this->pageTitle;
?>
<style type="text/css">
    .hideCalendar{display:none;}
    #headingOne nav{text-align: right;}
    thead tr{background: none !important;}
</style>
<div class="container">
    <div class="row">
        <?= TBreadcrumbs::widget([
            'tag' => 'ol',
            'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
        ]) ?>
        <div class="col-md-4 leftContent">
            <div class="panel panel-default examState">
                <div class="panel-body">
                    <div class="nameCard">
                        <div class="boxHead">
                            <div class="col-xs-12">
                                <a href="javascript:void(0)" class="userIcon">
                                    <img src="<?=TStringHelper::Thumb(Yii::$app->user->identity->thumb,Yii::$app->user->identity->gender)?>" alt="<?= Yii::$app->user->identity->real_name ?>">
                                </a>
                            </div>
                        </div>
                        <h4 style="text-align: center; margin-top: 30px;"><?= Yii::$app->user->identity->real_name ?></h4>
                        <div class="boxBody">
                            <div class="info">
                                <span><strong><?= $statCourse['startnum'] ?></strong><?= Yii::t('frontend', 'class_is_doing') ?></span>
                                <span><strong><?= $statCourse['beforenum'] ?></strong><?= Yii::t('frontend', 'waiting_for_class') ?></span>
                                <span><strong><?= $statCourse['endnum'] ?></strong><?=Yii::t('frontend', 'complete_status_done')?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="panel panel-default finishLearn">
                <div class="panel-heading">
                    <i class="glyphicon glyphicon-flag"></i><?= Yii::t('frontend', 'calendar_table') ?>
                </div>
                <div class="panel-body minicalendar">
                    <div id="datetimepicker"></div>
                    <div class="centerBtnArea">
                        <a href="javascript:void(0)" id="showFullCalendar" class="btn btn-default btn-sm centerBtn additionBtn"><?= Yii::t('frontend', 'view_detail') ?></a>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-8 rightContent" style="margin-bottom:60px;">
            <div class="courseInfo">
                <ul class="nav nav-tabs hotNews" role="tablist" id="myTab">
                    <li role="presentation" class="active"><a href="#courseIntro" onclick="reloadcourse('courseIntro')" aria-controls="courseIntro" role="tab" data-toggle="tab"><?=Yii::t('frontend', 'class_is_doing')?></a></li>
                    <li role="presentation"><a href="#courseAward" onclick="reloadcourse('courseAward')" aria-controls="courseAward" role="tab" data-toggle="tab"><?= Yii::t('frontend', 'waiting_for_class') ?></a></li>
                    <li role="presentation"><a href="#courseTeacher" onclick="reloadcourse('courseTeacher')" aria-controls="courseTeacher" role="tab" data-toggle="tab"><?=Yii::t('frontend', 'complete_status_done')?></a></li>
                </ul>
                <div class="tab-content">
                    <div role="tabpanel" class="tab-pane active" id="courseIntro">
                        <!-- 未开始课程 -->
                    </div>

                    <div role="tabpanel" class="tab-pane" id="courseAward">
                        <!-- <?=Yii::t('frontend', 'complete_status_doing')?>的课程 -->
                    </div>

                    <div role="tabpanel" class="tab-pane" id="courseTeacher">
                        <!-- 已完成课程 -->
                    </div>

                </div>
            </div>
        </div>
        <div class="col-md-12 calendarContainer">
            <a href="#" class="btn btn-default btn-sm additionBackBtn"><?= Yii::t('common', 'back_button') ?></a>
            <hr/>
            <div id='calendar'></div>
        </div>
    </div>
</div>
<!-- /container -->
<?= html::jsFile('/static/frontend/js/moment.js') ?>
<?= html::jsFile('/static/frontend/js/fullcalendar.js') ?>
<?  $nowtime= time(); ?>
<script>
    $("document").ready(function() {
        $("#showFullCalendar").one("click", app.genFullRichCalendar);
        app.genFullRichCalendar("#calendar");
        $('.calendarContainer').addClass('hideCalendar');
    });

    var thisYear = <?=date("Y",$nowtime)?>;
    var thisMonth = <?=date("m",$nowtime)?>;
    var thisRichCalendar = {
        header: {
            left: 'prev,next',
            center: 'title',
            right: 'month'
        },
        lang: 'zh-cn',
        defaultDate: '<?=date("Y-m-d",$nowtime)?>',
        editable: false,
        selectable: false,
        eventLimit: true, // allow "more" link when too many events 灰色-已过期, 橙色-正在进行, 蓝色-还未开始
        events: [
            <?if(count($timeCourse)>0): ?>
            <?foreach ($timeCourse as $c): ?>
            {

                title: '<?=$c->course_name ?>  <?=$teacherList[$c->kid]?>',
                start: '<?if(isset($c->open_start_time)): echo date('Y-m-d',$c->open_start_time); endif;?>',
                end: '<?if(isset($c->open_end_time)): echo date('Y-m-d',($c->open_end_time+86400)); endif;?>',
                url:'<?= Yii::$app->urlManager->createUrl(['teacher/detail','id'=>$c->kid]) ?>',
                <?if($c->open_status==1):?>
                color: '#f56a40'
                <?elseif ($c->open_status ==2 ):?>
                color: '#ccc'
                <?endif;?>
            },
            <?endforeach;?>
            <?endif;?>
        ]
    };
</script>
<script type="text/javascript">
    $('.additionBtn').bind('click', function() {
        $('.leftContent').addClass('hide');
        $('.rightContent').addClass('hide');
        $('.calendarContainer').addClass('showCal').removeClass('hideCalendar');//.css("display","block");
        app.bindFullCalendar();
    });
    $('.additionBackBtn').bind('click', function() {
        $('.leftContent').removeClass('hide');
        $('.rightContent').removeClass('hide');
        $('.calendarContainer').removeClass('showCal').addClass('hideCalendar');//.css("display","none");
    });

    // 时间插件
    $('#datetimepicker').datetimepicker();

    var beforeId='courseAward' ,startId="courseIntro",endId="courseTeacher";

    var courseBefore="<?=Yii::$app->urlManager->createUrl(['teacher/course-before'])?>";
    var courseStart="<?=Yii::$app->urlManager->createUrl(['teacher/course-start'])?>";
    var courseEnd="<?=Yii::$app->urlManager->createUrl(['teacher/course-end'])?>";

    $(document).ready(function () {
        FmodalLoad(startId,courseStart);
        //FmodalLoad(startId, courseStart);
        //FmodalLoad(endId, courseEnd);
    });

    function FmodalLoad(target, url)
    {
        if(url){
            $('#'+target).empty();
            $('#'+target).load(url);
        }
    }

    function reloadcourse(Id) {

        if (Id == 'courseIntro') {
            FmodalLoad(startId, courseStart);
        }
        else if (Id == 'courseAward') {
            FmodalLoad(beforeId,courseBefore);
        }
        else if (Id == 'courseTeacher') {
            FmodalLoad(endId, courseEnd);
        }

    }

</script>