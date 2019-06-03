<?php
/**/
use yii\helpers\Html;
use components\widgets\TLinkPager;
use common\models\learning\LnCourse;
use common\services\framework\DictionaryService;
?>
<script>
    var detailScoreUrl = '<?=Yii::$app->urlManager->createUrl(['/teacher/detail-score'])?>?id=<?=$id?>';
    function reloadForm()
    {
        var ajaxUrl = detailScoreUrl;
        ajaxUrl = urlreplace(ajaxUrl,'PageShowAll',$('#PageShowAll_grid').val());
        FmodalLoad('courseAward4', ajaxUrl);
    }
</script>
<div class=" panel-default scoreList">
    <div class="panel-body">
        <div class="col-md-12 col-sm-12" style="margin-top:20px;">
            <div class="col-md-6 col-sm-6"></div>
            <div class="col-md-6 col-sm-6">
                <div class="form-group" style="margin-bottom:0;">
                    <select class="form-control" id="scoresort" style="width:50%;">
                        <option value="1"><?= Yii::t('frontend', 'rank_by_position') ?></option>
                        <option value="2" <?if($param['sort']==2):?> selected <?endif;?>><?= Yii::t('frontend', 'rank_by_organization') ?></option>
                    </select>
                </div>
                <div class="input-group ">
                    <input type="text" id="scorekeyword" class="form-control search_people" style="height: 30px;" placeholder="<?= Yii::t('common', 'real_name') ?>/<?= Yii::t('frontend', 'position') ?>/<?= Yii::t('frontend', 'department') ?>" <?if($param['keyword']):?> value="<?=$param['keyword']?>"<?endif;?>>
                    <span class="input-group-btn"><button id="score_search_btn" class="btn btn-success btn-sm" type="button"><?= Yii::t('frontend', 'top_search_text') ?></button></span>
                </div>
            </div>
        </div>
        <div class="nameList_table list_active">
            <table class="table table-bordered table-hover table-striped table-center">
                <tbody>
                <tr>
                    <td width="10%"><?= Yii::t('common', 'real_name') ?></td>
                    <td width="12%"><?= Yii::t('common', 'department') ?></td>
                    <td width="20%"><?= Yii::t('common', 'position') ?></td>
                    <td width="16%"><?= Yii::t('common', 'user_email') ?></td>
                    <td width="12%"><?= Yii::t('common', 'mobile') ?></td>
                    <?php
                    if ($model->course_type== LnCourse::COURSE_TYPE_FACETOFACE){
                    ?>
                    <td width="16%"><?= Yii::t('frontend', 'attendance')?></td>
                    <?php
                    }
                    ?>
                    <td width="9%"><?= Yii::t('common', 'action') ?></td>
                </tr>
                <?php
                if (!empty($students)){
                    foreach ($students as $stu){
                ?>
                <tr>
                    <td><?=Html::encode($stu['real_name']) ?></td>
                    <?
                    $orgName = '';
                    $orgFullName = '';
                    if ($stu['orgnization_name_path'] && strpos($stu['orgnization_name_path'], '/') !== false) {
                        $orgName = substr(strrchr($stu['orgnization_name_path'], '/'), 1) . '/' . $stu['orgnization_name'];
                        $orgFullName = $stu['orgnization_name_path'] . '/' . $stu['orgnization_name'];
                    } elseif ($stu['orgnization_name_path'] && strpos($stu['orgnization_name_path'], '/') === false) {
                        $orgFullName = $orgName = $stu['orgnization_name_path'] . '/' . $stu['orgnization_name'];
                    } else {
                        $orgFullName = $orgName = $stu['orgnization_name'];
                    }
                    ?>
                    <td><label title="<?= $orgFullName ?>"><?= $orgName ?></label></td>
                    <td><?=$stu['position_name']?></td>
                    <td><?=$stu['email']?></td>
                    <td><?=$stu['mobile_no']?></td>
                    <?php
                    if ($model->course_type== LnCourse::COURSE_TYPE_FACETOFACE){
                    ?>
                    <td><?=$stu['signcount'] ?>/<?=$stu['signall'] ?></td>
                    <?php
                    }
                    ?>
                    <td>
                        <?php
                        if ($stu['approval']){
                        ?>
                        <a href="javascript:void(0);" class="approval" data-courseId="<?=$model->kid?>" data-uid="<?=$stu['user_id']?>"><?= Yii::t('common', 'approval')?></a>
                        <?php
                        }else{
                        ?>
                        <a href="javascript:void(0);" class="score-detail-person"  data-uid="<?=$stu['user_id']?>"><?= Yii::t('frontend', 'detail')?></a>
                        <?php
                        }
                        ?>
                    </td>
                </tr>
                <?php
                    }
                }else{
                ?>
                <tr>
                    <td colspan="<?=$model->course_type== LnCourse::COURSE_TYPE_FACETOFACE?7:6?>"><?=Yii::t('frontend', 'temp_no_record')?></td>
                </tr>
                <?php
                }
                ?>
                </tbody>
            </table>
            <? if ($pages->totalCount > 0): ?>
            <div class="col-md-12">
                <nav style="text-align: right;">
                    <?php
                    echo TLinkPager::widget([
                        'id' => 'page',
                        'pagination' => $pages,
                        'displayPageSizeSelect' => false,
                    ]);
                    ?>
                    <?
                    if ($ShowAll == 'True') {
                        $pageButton = Html::button(Yii::t('common', 'resize_current_button'), [
                            'title' => Yii::t('common', 'resize_current_button'), 'class' => 'btn btn-default resizeBtn',
                            'onclick' => 'ResizeCurrentButton();'
                        ]);
                    } else {
                        $pageButton = Html::button(Yii::t('common', 'resize_full_button'), [
                            'title' => Yii::t('common', 'resize_full_button'), 'class' => 'btn btn-default resizeBtn',
                            'onclick' => 'ResizeFullButton();'
                        ]);
                    }
                    ?>
                    <?echo $pageButton; ?>
                </nav>
                <input type="hidden" id="PageShowAll_grid" value="False"/>
            </div>
            <? endif; ?>
        </div>
    </div>
</div>
<script>
    app.extend("alert");
    function detail(userId,courseId,modResId,itemId){
        var modalId = "questionairedetailone";
        var ajaxUrl = "<?= Yii::$app->urlManager->createUrl('/teacher/questionaire-result')?>";
        ajaxUrl = urlreplace(ajaxUrl, 'courseId', courseId);
        ajaxUrl = urlreplace(ajaxUrl, 'modResId', modResId);
        ajaxUrl = urlreplace(ajaxUrl, 'itemId', itemId);
        ajaxUrl = urlreplace(ajaxUrl, 'userId', userId);
        modalTotalClear(modalId);
        app.alertWideAgain('#'+modalId);
        loadMessage(ajaxUrl, modalId);

    }
    function detailrenturn(courseId,modResId,inkid){
        var modalId = "questionairedetailone";

        if(window._isPWatch)
        {
            window._isPWatch = false;
            return app.hideAlert("#questionairedetailone");
        }
        modalTotalClear(modalId);
        modalHidden(modalId);
    }

    function loadMessage(ajaxUrl, container) {
        ajaxGet(ajaxUrl, container);
    }

    $("document").ready(function (){
        $('#score_search_btn').bind('click', function() {
            var sort = $('#scoresort').val();
            var keyword = $('#scorekeyword').val();
            var inputdata = {sort:sort,keyword:keyword};
            detailScoreUrl = urlreplace(detailScoreUrl,'PageShowAll',$('#PageShowAll_grid').val());
            ajaxGet(detailScoreUrl, "courseAward4",null,inputdata);
        });

        var scoreDetailUrl_s = '<?=Yii::$app->urlManager->createUrl(['/teacher/detail-score-person', 'id' => $id, 'iframe' => $iframe, 'header' => 'show', 'showHomework' => $showHomework])?>';
        $('.score-detail-person').bind('click', function () {
            var userId = $(this).attr("data-uid");
            app.get(scoreDetailUrl_s + "&userId=" + userId, function (r) {
                if (r) {
                    $('#scoreDetails').html(r);
                    app.alertWideAgain('#scoreDetails');
                }
            });
        });

        $("#scorePage .pagination,#courseAward4 .pagination").on('click', 'a', function(e){
            e.preventDefault();
            var parent = $(this).parents('.tab-pane').attr('id');
            ajaxGet($(this).attr('href'), parent);
        });

        /*审批*/
        $(".approval").bind('click', function(){
            var courseId = $(this).attr('data-courseId');
            var userId = $(this).attr('data-uid');
            $.get('<?=\yii\helpers\Url::toRoute('/resource/course/course-approval')?>', {courseId: courseId, userId: userId}, function(data){
                if (data.result == 'success'){
                    location.reload();
                }else{
                    app.showMsg(data.errmsg);
                    return false;
                }
            }, 'json');
        });
    });
</script>

               