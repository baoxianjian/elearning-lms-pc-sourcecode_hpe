<?php
/**
 * Created by PhpStorm.
 * User: adophper
 * Date: 2016/3/8
 * Time: 11:01
 */
use frontend\widgets\CourseLibrary;
use frontend\widgets\QuestionArea;
use frontend\widgets\RecommendCourse;
use yii\helpers\Html;
use yii\helpers\Url;
use frontend\widgets\UserPanel;
use frontend\widgets\ContinueLearning;
use frontend\widgets\QuickChannel;
use components\widgets\TBreadcrumbs;
use common\models\framework\FwUserPointDetail;
/* @var $this yii\web\View */
$current_time = time();

$this->pageTitle = Yii::t('common', 'my_integral');
$this->params['breadcrumbs'][] = $this->pageTitle;

?>
<div class="container">
    <div class="row">
        <?= TBreadcrumbs::widget([
            'tag' => 'ol',
            'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
        ]) ?>
        <div class="col-md-4 wideScreenBlock ">
            <?
            $userPanel = UserPanel::widget();
            $continueLearning = ContinueLearning::widget();
            $courseLibrary = CourseLibrary::widget();
            $questionArea = QuestionArea::widget();
            $recommendCourse = RecommendCourse::widget();
            ?>
            <?
            echo $userPanel;
            ?>
            <?
            echo $continueLearning;
            ?>
            <?
            echo $recommendCourse;
            ?>
            <?
            echo $courseLibrary;
            ?>
            <?
            echo $questionArea;
            ?>
        </div>
        <div class="col-md-8">
            <div class="row score_panel_back topBordered">
                <div class="col-sm-3 score_panel">
                    <p class="top"><?=Yii::t('frontend', 'available_point')?></p>
                    <p class="bottom"><?=$integralAvailable?></p>
                </div>
                <div class="col-sm-3 score_panel">
                    <p class="top"><?=Yii::t('frontend', 'total_point')?></p>
                    <p class="bottom"><?=$integralTotal?></p>
                </div>
                <div class="col-sm-3 score_panel">
                    <p class="top"><?=Yii::t('frontend', 'year_point')?></p>
                    <p class="bottom"><?=$integralYears?></p>
                </div>
                <div class="col-sm-3 score_panel">
                    <p class="top"><?=Yii::t('frontend', 'month_point')?></p>
                    <p class="bottom"><?=$integralMonth?></p>
                </div>
            </div>
            <div class="courseInfo">
                <ul class="nav nav-tabs hotNews" role="tablist" id="myTab">
                    <li role="presentation" class="active"><a href="#score_detail" id="integral_list_btn" aria-controls="allCourse" role="tab" data-toggle="tab"><?=Yii::t('frontend', 'point_detail')?></a></li>
                    <li role="presentation"><a href="#score_rule" id="score_rule_btn" aria-controls="allCourse" role="tab" data-toggle="tab"><?=Yii::t('frontend', 'point_rule')?></a></li>
                    <!--<li role="presentation"><a href="#score_system" id="growth_btn" aria-controls="allCourse" role="tab" data-toggle="tab">成长体系</a></li>-->
                </ul>
                <div class="tab-content topBordered">
                    <div role="tabpanel" class="tab-pane active" id="score_detail">
                        <div class="panel-body scoreList">
                            <div class="actionBar">
                                <div class="form-group">
                                    <form id="integralForm" name="integralForm">
                                        <select class="form-control" id="point_type" style="width: 20%;">
                                            <option value=""><?=Yii::t('common', 'point_total')?></option>
                                            <option value="<?=FwUserPointDetail::POINT_TYPE_GET?>"><?=Yii::t('common', 'get_point')?></option>
                                            <option value="<?=FwUserPointDetail::POINT_TYPE_IN?>"><?=Yii::t('common', 'transfer_in_point')?></option>
                                            <option value="<?=FwUserPointDetail::POINT_TYPE_OUT?>"><?=Yii::t('common', 'transfer_out_point')?></option>
                                            <option value="<?=FwUserPointDetail::POINT_TYPE_MIN?>"><?=Yii::t('common', 'deduct_point')?></option>
                                        </select>
                                        <span class="inputDes"><?=Yii::t('common', 'time')?>:</span>
                                        <input type="text" id="start_time" data-type="rili" data-full="1" class="form-control" placeholder="<?=Yii::t('common', 'start_time')?>" style="width:20%">
                                        <span class="inputDes"><?=Yii::t('common', 'to')?></span>
                                        <input type="text" id="end_time" data-type="rili" data-full="1" class="form-control" placeholder="<?=Yii::t('common', 'end_time')?>" style="width:20%">
                                        <button type="reset" class="btn btn-default pull-right"><?=Yii::t('common', 'reset')?></button>
                                        <button type="submit" id="searchIntegral" class="btn btn-primary pull-right" style="margin-left:10px;"><?=Yii::t('common', 'search')?></button>
                                    </form>
                                </div>
                            </div>
                            <div id="integral_list"></div>
                        </div>
                    </div>
                    <div role="tabpanel" class="tab-pane" id="score_rule">
                        <div class="panel panel-default scoreList">
                            <div class="panel-body scoreList">
                                <div class="actionBar"></div>
                                <div id="integral_point_rule"></div>
                            </div>
                        </div>
                    </div>
                    <div role="tabpanel" class="tab-pane" id="score_system">
                        <div class="panel panel-default scoreList">
                            <div class="panel-body scoreList">
                                <div class="actionBar"></div>
                                <div id="integral_growth"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    var integralListUrl = '<?=Url::toRoute('/student/integral-list')?>';
    var integralPointRuleUrl = '<?=Url::toRoute('/student/integral-point-rule')?>';
    var integralGrowthUrl = '<?=Url::toRoute('/student/integral-growth')?>';
    var integral_btn = false;
    var score_rule_btn = false;
    var growth_btn = false;
    $(function(){
        $("#searchIntegral").bind('click', function(){
            var type = $("#point_type").val();
            var beginDate = $("#start_time").val();
            var endDate = $("#end_time").val();
            if (beginDate != "" && endDate != ""){
                var a = new Date(beginDate);
                var b = new Date(endDate);
                if (a.getTime() > b.getTime()){
                    app.showMsg('<?=Yii::t('frontend','start_time_beyond_end_time')?>！');
                    return false;
                }
            }
            $.get(integralListUrl, {point_type: type, start_time: beginDate, end_time: endDate}, function(data){
                if (data){
                    integral_btn = true;
                    $("#integral_list").html(data);
                } else {
                    app.showMsg('<?=Yii::t('frontend', 'data_fail')?>.');
                    return false;
                }
            });
            return false;
        });
        $.get(integralListUrl, function(data){
            if (data){
                integral_btn = true;
                $("#integral_list").html(data);
            } else {
                app.showMsg('<?=Yii::t('frontend', 'data_fail')?>.');
                return false;
            }
        });
        $("#score_rule_btn").click(function(){
           if (!score_rule_btn){
               $.get(integralPointRuleUrl, function(data){
                  if (data){
                      score_rule_btn = true;
                      $("#integral_point_rule").html(data);
                  } else {
                      app.showMsg('<?=Yii::t('frontend', 'data_fail')?>.');
                      return false;
                  }
               });
           }
        });
        $("#growth_btn").click(function(){
           if (!growth_btn){
               $.get(integralGrowthUrl, function(data){
                  if (data){
                      growth_btn = true;
                      $("#integral_growth").html(data);
                  } else {
                      app.showMsg('<?=Yii::t('frontend', 'data_fail')?>.');
                      return false;
                  }
               });
           }
        });
    });
</script>
