<?php
/**
 * Created by PhpStorm.
 * User: tangming
 * Date: 7/10/2015
 * Time: 8:42 PM
 */
use common\models\learning\LnCourseware;
use common\services\learning\CourseService;
use common\services\scorm\ScormService;
use common\helpers\TStringHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use common\helpers\TFileModelHelper;

?>

<ul class='panel-collapse collapse in' role='tabpanel' aria-labelledby='headingOne' id='collapseExample'>
        <?=Yii::t('common', 'scorm_course_unit')?>  </h4>

    <? foreach ($catalogMenu as $modNum => $mod) {?>
        <li class='pathStep'>
            <span class='step'>
                <?=$mod['mod_name']?>
            </span>

            <? if ($mod['time'] != 0) { ?>
                <span class='stepTime pull-right'><?= Yii::t('frontend', 'study_hours') ?>：<?=$mod['time']?><?= Yii::t('common', 'time_minute') ?></span>
            <? }?>

            <? if (!empty($mod['mod_desc'])) { ?>
                <p><?= Yii::t('frontend', 'module_description') ?>：<?=TStringHelper::OutPutBr($mod['mod_desc'])?></p>
            <? }?>

            <ul class='pathTask'>
                <?
                if (!empty($mod['courseitems'])) {
                    $scormService = new ScormService();
                    foreach ($mod['courseitems'] as $num => $resource) {

                        $displayItem = $resource['displayItem'];

                        if ($displayItem) {
                            $learnStatus = $resource['learning_status'];
                            if ($learnStatus == "learned") {
                                echo "<li class='learned'>";
                            } else if ($learnStatus == "learning") {
                                echo "<li class='learning'>";
                            } else {
                                echo "<li class=''>";
                            }
                            $itemId = $resource['itemId'];
                            $canRun = $resource['canRun'];
                            $mode = $resource['mode'];
                            $itemName = $resource['itemName'];
                            $componentName = $resource['componentName'];
                            $componentCode = $resource['componentCode'];
                            $componentIcon = $resource['componentIcon'];
                            $modResId = $resource['modResId'];
                            $item = $resource['item'];
                            $isCourseware = $resource['isCourseware'];
                            $scorm = null;
                            if ($canRun || $mode == \common\models\learning\LnCourse::PLAY_MOD_PREVIEW) {
                                if ($scormService->isScormComponent($componentCode)) {
                                    $scorm = $scormService->getScormByCoursewareId($itemId);
                                    $link = Html::a($itemName, "javascript:reloadplayer('" . $componentCode . "','" . $modResId . "','" . $scorm->launch_scorm_sco_id . "');",
                                        ['title' => $itemName]);
                                } else {
                                    $link = Html::a($itemName, "javascript:reloadplayer('" . $componentCode . "','" . $modResId . "','');", ['title' => $itemName]);
                                }
                            } else {
                                $link = $itemName;
                            }

//                            echo "<span class='taskTime pull-right'>" . $componentName . "</span>";

                            if (!$scormService->isScormComponent($componentCode) && $modResId == $currentModResId) {
                                echo "<span class='taskName bg'>";
                            } else {
                                echo "<span class='taskName'>";
                            }

                            echo "<i class='unLearn'></i>" . $componentIcon . "&nbsp;" . $link;

                            if ($isCourseware && $canRun) {
                                if (!$scormService->isScormComponent($componentCode)) {
                                    $coursewareModel = $item;//LnCourseware::findOne($itemId);
                                    $isAllowDownload = $coursewareModel->is_allow_download == LnCourseware::ALLOW_DOWNLOAD_YES ? true : false;
                                    if ($isAllowDownload) {
                                        if (!empty($coursewareModel->file_id)) {
                                            //$downloadUrl = Url::toRoute(['/resource/down', 'id' => $coursewareModel->file_id, 'file_name' => $itemName]);
                                            $downloadUrl = TFileModelHelper::getFileSecureLink($coursewareModel->file_id);/*防盗链*/
                                            echo " <a href='" . $downloadUrl . "' target='_blank'><span class='glyphicon glyphicon-download-alt'></span></a>";
                                        }
                                    }
                                }
                            }

                            echo "</span></li>";

                            if ($scormService->isScormComponent($componentCode) && $modResId == $currentModResId) {
                                $currentOrg = "";
                                $scoId = "";
                                $withSession = true;
                                $play = true;
                                $userId = Yii::$app->user->getId();
                                $organizationsco = null;
                                //$mode = "normal";

                                if ($currentScoId == null || $currentScoId == "") {
                                    $currentScoId = $scorm->launch_scorm_sco_id;
                                }

                                $catalogInfo = $scormService->scorm_get_toc($courseRegId, $userId, $modResId, $scorm, $currentOrg, $scoId, $mode, $attempt, true, $currentScoId, $itemId,$withSession);

                                echo $catalogInfo->toc;
                            }

                            if ($modResId == $currentModResId) {
                                echo Html::hiddenInput("currentlearnStatus",$learnStatus,['id'=>'currentlearnStatus']);
                            }
                        }
                    }
                }
                ?>

            </ul>
        </li>
    <? } ?>
</ul>

<script>
    $(document).ready(function() {
        var currentlearnStatus = $('#currentlearnStatus').val();
//        alert(currentlearnStatus);
        if (currentlearnStatus == "learned") {
            changeCourseWareStatus("2");
        }
        else {
            changeCourseWareStatus("1");
        }
    });


</script>

<?= Html::hiddenInput("currentCourseCompleteProcessId",$courseCompleteProcessId,['id'=>'currentCourseCompleteProcessId'])?>
<?= Html::hiddenInput("currentCourseCompleteFinalId",$courseCompleteFinalId,['id'=>'currentCourseCompleteFinalId'])?>
<?= Html::hiddenInput("currentResCompleteProcessId",$resCompleteProcessId,['id'=>'currentResCompleteProcessId'])?>
<?= Html::hiddenInput("currentResCompleteFinalId",$resCompleteFinalId,['id'=>'currentResCompleteFinalId'])?>
<?= Html::hiddenInput("currentAttempt",$attempt,['id'=>'currentAttempt'])?>
<script type="text/javascript">scorePointEffect("<?=$pointResult['show_point']?>","<?=$pointResult['point_name']?>","<?=$pointResult['available_point']?>");</script>
