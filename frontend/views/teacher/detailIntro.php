<?php
/**
 * User: zhanglei
 * Date: 2015/8/12
 * Time: 13:02
 */
  use common\models\learning\LnComponent;
  use common\models\learning\LnCourseactivity;
  use common\models\learning\LnModRes;
  use components\widgets\TBreadcrumbs;
use yii\helpers\Url;
use common\helpers\TTimeHelper;
use common\helpers\TStringHelper;
use yii\helpers\Html;
use components\widgets\TLinkPager;
use common\models\learning\LnCourse;

$this->pageTitle = Yii::t('frontend', 'self_time_manage');// Yii::t('frontend', 'page_lesson_hot_title');
$this->params['breadcrumbs'][] = $this->pageTitle;
?>

<div class="panel-default scoreList">
    <div class="panel-default scoreList pathBlock offlineCourse">
        <div role="tab">
            <p><?=Yii::t('frontend', 'introduction_course')?>:</p>
            <p>
                <?=Html::decode($courseModel->course_desc)?>
            </p>
        </div>
        <div class="clearfix"></div>
        <hr>
        <div role="tab" id="headingOne">
            <ul class="panel-collapse collapse in" role="tabpanel" aria-labelledby="headingOne" id="collapseExample">
            <?php
            if(!empty($courseMods)){
                foreach ($courseMods as $mod){
            ?>
                    <li class="pathStep">
                        <span class="step "><?= $mod['mod_name']?></span>
                        <?php
                        if($mod['time'] != 0){
                        ?>
                        <span class='stepTime pull-right'><?=Yii::t('frontend', 'study_hours')?>：<?= $mod['time']?><?=Yii::t('frontend', 'time_minute')?></span>
                        <?php
                        }
                        ?>
                        <?php
                        if (!empty($mod['mod_desc'])) {
                        ?>
                        <p><?=Yii::t('frontend', 'module_description')?>：<?=TStringHelper::OutPutBr($mod['mod_desc'])?></p>
                        <?php
                        }
                        ?>
                        <div class="pathTask">
                            <table>
                                <tr>
                                    <td colspan="2">
                                        <ul class="attach">
                                            <?php
                                            if(!empty($mod['courseitems'])) {
                                                foreach ($mod['courseitems'] as $num => $resource){
                                                    $itemId = $resource['itemId'];
                                                    $modResId = $resource['modResId'];
                                                    $componentId = $resource['componentId'];
                                                    $isCourseware = $resource['isCourseware'];
                                                    $modRes = $resource['modRes'];
                                                    $itemName = $resource['itemName'];
                                                    $item = $resource['item'];

                                                    $componentModel = LnComponent::findOne($componentId);
                                                    $componentCode = $componentModel->component_code;
                                                    $componentIcon = $componentModel->icon;
                                                    ?>
                                                    <li>
                                                        <?= $componentIcon ?> <a href="javascript:void(0);" class="playCourse" data-res="<?= $modResId ?>"><?= $itemName ?></a>
                                                        <?php
                                                        if ($courseModel->course_type === LnCourse::COURSE_TYPE_FACETOFACE){
                                                            if ($courseModel->open_status == LnCourse::COURSE_END){
                                                                /**/
                                                            }elseif ($modRes->publish_status == LnModRes::PUBLIC_STATUS_YES){
                                                            ?>
                                                            <a href="#" class="btn btn-sm pull-right" data-toggle="modal" data-target="#published"><?=Yii::t('frontend', 'publish_status_yes')?></a>
                                                            <?php
                                                            }else{
                                                            ?>
                                                            <a href="javascript:void(0);" class="btn btn-sm pull-right lnmodresid" data-cid="<?= $courseModel->kid ?>" data-mid="<?= $mod['kid'] ?>" data-rid="<?= $modResId ?>" data-toggle="modal" data-target="#published"><?= Yii::t('common', 'art_publish') ?></a>
                                                            <?php
                                                            }
                                                        }
                                                        if ($modRes->publish_status == LnModRes::PUBLIC_STATUS_YES){
                                                        ?>
                                                        <a href="javascript:void(0);" class="btn btn-sm pull-right" onclick="LoadCompleteInfo(this, '<?=$courseModel->kid?>','<?=$modResId?>','<?=$itemId?>','<?=$itemName?>','<?= $componentCode ?>');" style="color: #337ab7;"><?=Yii::t('frontend', 'view_result')?></a>
                                                        <?php
                                                        }
                                                        ?>
                                                    </li>
                                                    <?php
                                                }
                                            }
                                            ?>
                                        </ul>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </li>
                <?php
                    }
                }
                ?>
            </ul>
        </div>
    </div>
</div>
<div class="ui modal" id="playCourse"></div>
<!-- 课件完成情况弹出窗口 -->
 <script>
     app.extend("alert");
     $(function(){
         $(".playCourse").on('click', function(e){
             e.preventDefault();
             $("#playCourse").empty();
             $.get("<?= Yii::$app->urlManager->createUrl('teacher/play-course')?>",{modResId: $(this).attr('data-res')},function(data){
                 if (data){
                     $("#playCourse").html(data);
                     app.alertWide($("#playCourse"), {
                         onVisible: function(){
                             $("#iframe-player").css({
                                 width: '100%',
                                 minHeight: '500px',
                                 height: 'auto'
                             });

                         }
                     });
                     app.refreshAlert($("#playCourse"));
                 }else{
                     app.showMsg('<?=Yii::t('frontend', 'resources_load_failed')?>');
                     return false;
                 }
             });
         })
     });
     function miniScreen(){}
     function diffTemp(){}
     function resizeIframe(){
         $("#iframe-player").css({
             width: '100%',
             minHeight: '500px',
             height: 'auto'
         });
         app.refreshAlert($("#playCourse"));
     }
     function detailrenturn(courseId,modResId,itemId){
         var url = "<?= Yii::$app->urlManager->createUrl('teacher/vote-and-questionaire')?>";
         url = urlreplace(url, 'courseId', courseId);
         url = urlreplace(url, 'modResId', modResId);
         url = urlreplace(url, 'itemId', itemId);
         app.get(url, function (r)
         {
             r ? app.alertWide($("#checksurvay").html(r)) : app.showMsg(app.msg.NETWORKERROR);
         });
     }
    function bindLoadCompleteInfo(modalId)
    {
        $("#" + modalId + ' .pagination a').bind('click', function ()
            {
                var url = $(this).attr('href');
                loadMessage(url, target);
                return false;
        });
    }

    function loadMessage(ajaxUrl, container) {
        ajaxGet(ajaxUrl, container);
    }

     function bindPageChange(target, data) {
         $("#" + target).html(data);
         $("#" + target + ' .pagination a').bind('click', function () {
             var url = $(this).attr('href');
             loadMessage(url, target);
             return false;
         });
     }

$("document").ready(function (){
    $(".lnmodresid").unbind('click').bind('click',function(){
        var obj = $(this);
        cid = obj.attr('data-cid');
        mid = obj.attr('data-mid');
        rid = obj.attr('data-rid');

        var url = "<?=Url::toRoute(['teacher/release'])?>";
        $.post(url, {"cid": cid, 'mid': mid,'resid':rid},
            function (data) {
                var result = data.result;
                if (result === 'fail') {
                    app.showMsg("<?=Yii::t('frontend', 'releate_failed')?>",1000) ;
                }
                else if (result === 'success') {
                    obj.unbind('click');
                    obj.text('<?=Yii::t('frontend', 'publish_status_yes')?>');
                    app.showMsg("<?= Yii::t('frontend', 'issue_sucess') ?>",1000);
                }
            }, "json");
        return false;
    });
});
</script>