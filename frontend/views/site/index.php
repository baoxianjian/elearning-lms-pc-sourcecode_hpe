<?php

use common\models\framework\FwCompanyMenu;
use yii\helpers\Html;
use common\helpers\TTimeHelper;
use common\helpers\TStringHelper;
use yii\helpers\Url;

/* @var $this yii\web\View */
?>

<?=$portalMenu?>
<div class="container">
    <div class="row">
        <div id="mainMenu" class="col-md-12">
            <a> <span><?=Yii::t('frontend','welcome_{value}',['value'=>''])?></span><img src="/static/frontend/images/0.jpeg" width="490" height="350" alt="" title="Image Menu 2"/>
                <div class="welcome activeSlide">
                    <h1><?=Yii::t('frontend','welcome_{value}',['value'=>Yii::t('system','frontend_name')])?></h1>
                    <p><?=Yii::t('frontend','introduction')?></p>
                </div>
            </a>
            <a> <span><?=Yii::t('frontend','update_statistics')?></span> <img src="/static/frontend/images/1.jpeg" width="490" height="350" alt="" title="Image Menu 2"/>
                <div class="myLearning activeSlide">
                    <h1><?=Yii::t('frontend','update_course_proportion')?></h1>

                    <div id="canvas-holder">
                        <canvas id="chart-area" style="width:150px !important; height:150px !important;">
                        </canvas>
                    </div>
                    <div class="content-holder">
                        <ul>
                            <li>
                                <h4>商科教育类<i>50%</i></h4>

                                <div class="coursePro" style="background:#009948 ;width:50%;"></div>
                            </li>
                            <li>
                                <h4>计算机类<i>70%</i></h4>

                                <div class="coursePro" style="background:#00a9d9 ;width:70%;"></div>
                            </li>
                            <li>
                                <h4>文学鉴赏类<i>40%</i></h4>

                                <div class="coursePro" style="background:#99418e ;width:40%;"></div>
                            </li>
                            <li>
                                <h4>工具书类<i>90%</i></h4>

                                <div class="coursePro" style="background:#fa6849 ;width:90%;"></div>
                            </li>
                        </ul>
                    </div>
                    <button type="button"><?=Yii::t('frontend','learn_more')?></button>
                </div>
            </a>
            <a> <span><?=Yii::t('frontend','new_test')?></span> <img src="/static/frontend/images/2.jpeg" width="490" height="350" alt="" title="Image Menu 3"/>
                <div class="myLearning activeSlide">
                    <h1><?=Yii::t('frontend','new_update_test')?></h1>

                    <div id="canvas-holder">
                        <canvas id="column-area" style="width:150px !important; height:150px !important;">
                        </canvas>
                    </div>
                    <div class="content-holder examContent">
                        <ul>
                            <li>
                                <h4>企业财务管理初级测试<i>2015-5-13前</i></h4>
                            </li>
                            <li>
                                <h4>国际营销理论一测试<i>2015-5-13前</i></h4>
                            </li>
                            <li>
                                <h4>高级时间管理课程测试<i>2015-5-13前</i></h4>
                            </li>
                            <li>
                                <h4>网络营销理论<i>2015-5-13前</i></h4>
                            </li>
                            <li>
                                <h4>算法与编程基础<i>2015-5-13前</i></h4>
                            </li>
                            <li>
                                <h4>SWOT分析理论与实践测试<i>2015-5-13前</i></h4>
                            </li>
                        </ul>
                    </div>
                    <button type="button"><?=Yii::t('frontend','all_progress')?></button>
                </div>
            </a>
        </div>
    </div>
</div>
<div class="container">
    <div class="row">
        <div class="col-md-8 miniSlide">
            <div class="panel panel-default">
                <ul class="nav nav-tabs" role="tablist" id="myTab">
                    <li role="presentation" class="active"><a href="#myStudy" aria-controls="myStudy" role="tab"
                                                              data-toggle="tab"><?=Yii::t('frontend','update_statistics')?></a></li>
                    <li role="presentation"><a href="#myExam" aria-controls="myExam" role="tab"
                                               data-toggle="tab"><?=Yii::t('frontend','new_test')?></a>
                    </li>
                </ul>
                <div class="tab-content">
                    <div role="tabpanel" class="tab-pane active" id="myStudy">
                        <div class="content-holder">
                            <ul>
                                <li>
                                    <h4>企业管理课程1<i>50%</i></h4>

                                    <div class="coursePro" style="background:#009948 ;width:50%;"></div>
                                </li>
                                <li>
                                    <h4>企业管理课程1<i>70%</i></h4>

                                    <div class="coursePro" style="background:#00a9d9 ;width:70%;"></div>
                                </li>
                                <li>
                                    <h4>企业管理课程1<i>40%</i></h4>

                                    <div class="coursePro" style="background:#99418e ;width:40%;"></div>
                                </li>
                                <li>
                                    <h4>企业管理课程1<i>90%</i></h4>

                                    <div class="coursePro" style="background:#fa6849 ;width:90%;"></div>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div role="tabpanel" class="tab-pane" id="myExam">
                        <div class="content-holder examContent">
                            <ul>
                                <li>
                                    <h4>企业财务管理初级测试<i>2015-5-13前</i></h4>
                                </li>
                                <li>
                                    <h4>国际营销理论一测试<i>2015-5-13前</i></h4>
                                </li>
                                <li>
                                    <h4>高级时间管理课程测试<i>2015-5-13前</i></h4>
                                </li>
                                <li>
                                    <h4>网络营销理论<i>2015-5-13前</i></h4>
                                </li>
                                <li>
                                    <h4>算法与编程基础<i>2015-5-13前</i></h4>
                                </li>
                                <li>
                                    <h4>SWOT分析理论与实践测试<i>2015-5-13前</i></h4>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="panel panel-default learnForum">
                <div class="panel-heading">
                    <i class="glyphicon glyphicon-comment"></i> <?=Yii::t('frontend','new_course')?>
                    <a class="pull-right" href="<?= Yii::$app->urlManager->createUrl('resource/course/index'); ?>" role="button"><?=Yii::t('frontend','more')?> &raquo;</a>
                </div>
                <div class="panel-body forumList">
                    <a href="<?= Yii::$app->urlManager->createUrl('resource/course/index'); ?>" style="margin:10px 0;display: inline-block"><img src="/static/frontend/images/quicklink1.jpg" style="width:100%;"></a>
                    <?php foreach ($HotCourses as $row) : ?>
                        <a href="<?= Yii::$app->urlManager->createUrl(['resource/course/view', 'id' => $row->kid]); ?>"><?= Html::encode(TStringHelper::subStr(trim($row->course_name), 22, 'utf-8', 0, '...')) ?></a>
                        <span><?= TTimeHelper::toDate($row->created_at) ?></span>
                        <span><?= Yii::t('frontend', 'editor_text') ?>:<?= Html::encode($row->editor) ?></span>
                        <br/>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="panel panel-default learnForum">
                <div class="panel-heading">
                    <i class="glyphicon glyphicon-comment"></i> <?= Yii::t('frontend', 'hot_question') ?>
                    <a class="pull-right" href="<?= Yii::$app->urlManager->createUrl(['question/index']) ?>" role="button"><?=Yii::t('frontend','more')?>  &raquo;</a>
                </div>
                <div class="panel-body forumList">
                    <a href="<?= Yii::$app->urlManager->createUrl(['question/index']) ?>" style="margin:10px 0;display: inline-block"><img src="/static/frontend/images/quicklink2.jpg" style="width:100%;"></a>
                    <?php foreach ($HotQuestion as $val) : ?>
                    <a href="<?= Yii::$app->urlManager->createUrl(['question/detail', 'id' => $val['kid']]) ?>"><?= Html::encode(TStringHelper::subStr(trim($val['title']), 22, 'utf-8', 0, '...')); ?></a>
                    <span><?= TTimeHelper::toDate($val['created_at']) ?></span>
                    <span><?= Yii::t('frontend', 'posted_question_text') ?>:<?php echo Html::encode($val['real_name']);?></span>
                    <br/>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="panel panel-default learnForum">
                <div class="panel-heading">
                    <i class="glyphicon glyphicon-comment"></i> <?=Yii::t('frontend','quilting')?>
                    <a class="pull-right" href="#" role="button"><?=Yii::t('frontend','more')?> &raquo;</a>
                </div>
                <div class="panel-body forumList">
                    <img src="/static/frontend/images/quicklink3.jpg"/ style="width:100%; margin:10px 0;">
                    <a href="#">首款iMoodle在线同步学习平台推出</a>
                    <span>2015-1-13</span>
                    <span>发帖人:曾波</span>
                    <br/>
                    <a href="#">2015年中国在线语言教育行业发展趋势分析预测</a>
                    <span>2015-1-13</span>
                    <span>发帖人:曾波</span>
                    <br/>
                    <a href="#">2015年优秀在线教育课件形式的四大特点</a>
                    <span>2015-1-13</span>
                    <span>发帖人:曾波</span>
                    <br/>
                    <a href="#">网易云课堂:在线教育付费是否可行?</a>
                    <span>2015-1-13</span>
                    <span>发帖人:曾波</span>
                    <br/>
                    <a href="#">惠普的云产品已经进入领先行列</a>
                    <span>2015-1-13</span>
                    <span>发帖人:曾波</span>
                    <br/>
                    <a href="#">2015年中国在线语言教育行业发展趋势分析预测</a>
                    <span>2015-1-13</span>
                    <span>发帖人:曾波</span>
                    <br/>
                </div>
            </div>
        </div>
    </div>
    <hr/>
</div>
<!-- /container -->
<?= html::jsFile('/static/frontend/js/accordionImageMenu-0.4.js') ?>
<?= html::jsFile('/static/frontend/js/Chart.js') ?>
<!-- 加载滑动幻灯片区域的脚本参数 -->
<script type="text/javascript">
    $(document).ready(function () {
        jQuery('#mainMenu').AccordionImageMenu({
            'border': 1,
            'openItem': 0,
            'duration': 400,
            'openDim': 900,
            'closeDim': 400,
            'effect': 'easeOutQuint',
            'fadeInTitle': true,
            'height': 400
        });
    });
</script>
<script>
    // 我的学习图标参数
    var doughnutData = [{
        value: 300,
        color: "#F7464A",
        highlight: "#FF5A5E",
        label: "<?=Yii::t('frontend','complete_status_done')?>"
    }, {
        value: 50,
        color: "#46BFBD",
        highlight: "#5AD3D1",
        label: "<?=Yii::t('frontend','complete_status_doing')?>"
    }, {
        value: 100,
        color: "#FDB45C",
        highlight: "#FFC870",
        label: "<?=Yii::t('frontend','complete_status_nostart')?>"
    }, {
        value: 40,
        color: "#949FB1",
        highlight: "#A8B3C5",
        label: "<?=Yii::t('frontend','due_date')?>"
    }];

    // 我的考试图标参数
    var randomScalingFactor = function () {
        return Math.round(Math.random() * 100)
    };
    var barChartData = {
        labels: ["<?=Yii::t('frontend','january')?>", "<?=Yii::t('frontend','february')?>", "<?=Yii::t('frontend','march')?>"],
        datasets: [{
            fillColor: "rgba(220,220,220,0.5)",
            strokeColor: "rgba(220,220,220,0.8)",
            highlightFill: "rgba(220,220,220,0.75)",
            highlightStroke: "rgba(220,220,220,1)",
            data: [randomScalingFactor(), randomScalingFactor(), randomScalingFactor()]
        }, {
            fillColor: "rgba(151,187,205,0.5)",
            strokeColor: "rgba(151,187,205,0.8)",
            highlightFill: "rgba(151,187,205,0.75)",
            highlightStroke: "rgba(151,187,205,1)",
            data: [randomScalingFactor(), randomScalingFactor(), randomScalingFactor()]
        }]
    }

    // 我的团队图标参数
    var pieData = [{
        value: 300,
        color: "#F7464A",
        highlight: "#FF5A5E",
        label: "<?=Yii::t('frontend','complete_status_done')?>"
    }, {
        value: 50,
        color: "#46BFBD",
        highlight: "#5AD3D1",
        label: "<?=Yii::t('frontend','complete_status_nostart')?>"
    }, {
        value: 100,
        color: "#FDB45C",
        highlight: "#FFC870",
        label: "<?=Yii::t('frontend','complete_status_doing')?>"
    }, {
        value: 40,
        color: "#949FB1",
        highlight: "#A8B3C5",
        label: "<?=Yii::t('frontend','due_date')?>"
    }];


    window.onload = function () {
        var ctx = document.getElementById("chart-area").getContext("2d");
        window.myDoughnut = new Chart(ctx).Doughnut(doughnutData, {
            responsive: false
        });

        var ctx = document.getElementById("column-area").getContext("2d");
        window.myBar = new Chart(ctx).Bar(barChartData, {
            responsive: false
        });

        var ctx = document.getElementById("pie-area").getContext("2d");
        window.myPie = new Chart(ctx).Pie(pieData, {
            responsive: false
        });
    };
</script>