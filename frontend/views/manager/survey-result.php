<?php
/**
 * Created by PhpStorm.
 * User: Liu Cheng
 * Date: 2016/1/6
 * Time: 20:19
 */
use components\widgets\TBreadcrumbs;
use yii\helpers\Url;

$this->pageTitle = Yii::t('frontend', 'home_myteam_text');// Yii::t('frontend', 'page_lesson_hot_title');
$this->params['breadcrumbs'][] = ['url' => Yii::$app->urlManager->createUrl('manager/my-team'), 'label' => $this->pageTitle];
$this->params['breadcrumbs'][] = Yii::t('common', 'investigation_result');

?>
<style>
    .question_results {
        width:80%
    }
</style>
<div class="container">
    <div class="row">
        <?= TBreadcrumbs::widget([
            'tag' => 'ol',
            'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
        ]) ?>
        <div class="col-md-12 col-sm-12">
            <div class="courseInfo">
                <ul class="nav nav-tabs hotNews" role="tablist" id="myTab">
                    <li role="presentation" class="active"><a href="#survey_stat" aria-controls="survey_stat" role="tab" data-toggle="tab"><?=Yii::t('frontend', 'statistical_result')?></a></li>
                    <li role="presentation"><a href="#survey_stat_detail" aria-controls="survey_stat_detail" role="tab" data-toggle="tab"><?=Yii::t('common', 'investigation_detail')?></a></li>
                </ul>
                <div class="tab-content">
                    <div role="tabpanel" class="tab-pane active" id="survey_stat">
                        <div class=" panel-default scoreList">
                            <div id="statistical" class="panel-body">
                            </div>
                        </div>
                    </div>
                    <div role="tabpanel" class="tab-pane" id="survey_stat_detail">
                        <div class=" panel-default scoreList">
                            <div id="list" class="panel-body">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<input type="hidden" id="investigation_id" value="<?=$id ?>"/>
<!-- 查看 -->
<div class="ui modal" id="view_survey_show" >
</div>

<script>
    var statistical_url = "<?=Url::toRoute(['manager/survey-result-statistical', 'id' => $id])?>";
    var list_url = "<?=Url::toRoute(['manager/survey-result-list', 'id' => $id, 'uid' => $uid, 'type' => $type])?>";

    var score_list_url = "<?=Url::toRoute(['common/person-score-list', 'uid' => $member_id]);?>";

    $(function () {
        loadTab(statistical_url, 'statistical');
        loadTab(list_url, 'list');

        $("#myTab a").click(function () {
            var tab = $(this).attr("aria-controls");
            readTab(tab);
        });

        $('.tab-content').delegate('.score', 'click', function () {
            var cid = $(this).attr("data-cid");
            $('#score_list').empty();
            loadScore(score_list_url + "&cid=" + cid);
            app.alertWide('#scoreDetails');
        });
    });

    function loadScore(ajaxUrl) {
        $('#scoreDetails .loadingWaiting').removeClass('hide');
        $('#score_list').empty();
        app.get(ajaxUrl, function (r) {
            if (r) {
                bind('score_list', r);
            }
        });
    }
    function bind(target, data) {
        $('#scoreDetails .loadingWaiting').addClass('hide');
        $("#" + target).html(data);
        $("#" + target + ' .pagination a').bind('click', function () {
            var url = $(this).attr('href');
            loadScore(url);
            return false;
        });
    }

    function loadTab(ajaxUrl, container) {
//        $("#" + container).parent().next().find(".loadingWaiting").removeClass('hide');
        ajaxGet(ajaxUrl, container);
    }

    function readTab(tab) {
        type = tab;
        if (!statistical_read && tab === 'tab_statistical') {
            statistical_read = true;
            loadTab(statistical_url, 'statistical');
        }
        else if (!exam_read && tab === 'tab_exam') {
            exam_read = true;
            loadTab(exam_url + exam_page, 'exam_list');
        }
    }

    function  view_survey_show(id){
        var investigation_id= $("#investigation_id").val();
        var url="<?=Url::toRoute(['investigation-result/st-survey',])?>"+"?investigation_id="+investigation_id+"&user_id="+id;
        FmodalLoadData("view_survey_show",url);
    }


    function FmodalLoadData(target, url)
    {
        if(url){
            $('#'+target).empty();
            $('#'+target).load(url, function (){
                app.alertWide("#"+target,{
                    afterHide: function (){
                        $('#'+target).empty();
                    }
                });
            });
        }
    }
</script>
