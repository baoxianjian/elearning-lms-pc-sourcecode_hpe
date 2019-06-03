<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/9/9
 * Time: 16:46
 */
use components\widgets\TLinkPager;
use yii\helpers\Url;
use common\models\learning\LnCourseEnroll;
use common\services\framework\DictionaryService;
use common\helpers\TTimeHelper;
use common\models\learning\LnInvestigation;
use common\models\learning\LnResComplete;

$dictionaryService = new DictionaryService();
?>
<div class="header">
    <h4>
        <?php
        if($type==0){
            echo Yii::t('frontend','name_real');
        }else{
            echo Yii::t('frontend','name_privacy');
        }
        if($category=='questionaire'){
            echo Yii::t('frontend', 'questionnaire');
        }else{
            echo Yii::t('frontend', 'vote');
        }?>

        <?=Yii::t('common', 'user_{value}',['value'=>''])?>
    </h4>
</div>
<div class="content">
    <div class="actionBar" style="margin-top: 0px;">
        <form class="form-inline" id="-form">
            <div class="form-group">
                <span class="pull-left" style="line-height: 40px;"><?=Yii::t('common', 'status')?>: &nbsp;&nbsp;</span>
                <select name="status" class="form-control" style="width: 100px;">
                    <option value=""><?= Yii::t('common', 'select_{value}',['value'=>'']) ?></option>
                    <option value="0" <?=$status==LnResComplete::COMPLETE_STATUS_NOTSTART?'selected':''?>><?=Yii::t('common', 'complete_status_0')?></option>
                    <option value="1" <?=$status==LnResComplete::COMPLETE_STATUS_DOING?'selected':''?>><?=Yii::t('common', 'complete_status_1')?></option>
                    <option value="2" <?=$status==LnResComplete::COMPLETE_STATUS_DONE?'selected':''?>><?=Yii::t('common', 'complete_status_2')?></option>
                </select>
                <input name="keywords" type="text" value="<?=$keywords?>" class="form-control" placeholder="<?=Yii::t('frontend', 'input_name_email')?>" style="width: 200px;">
                <button type="button" class="btn btn-primary search" style="margin-left:10px;"><?=Yii::t('common', 'search')?></button>
                <button type="reset" class="btn btn-default reset"><?=Yii::t('frontend', 'reset')?></button>
            </div>
        </form>
    </div>
    <div class="Result_noName_survey">
        <div class="row">
            <div class="col-md-12 col-sm-12">
                <table class="table table-bordered table-hover table-center">
                    <tbody>
                    <tr>
                        <?php
                        if($type==LnInvestigation::ANSWER_TYPE_REALNAME){
                        ?>
                            <td><?= Yii::t('common', 'real_name') ?></td>
                            <td><?= Yii::t('common', 'department') ?></td>
                            <td><?= Yii::t('frontend', 'position') ?></td>
                            <td><?= Yii::t('common', 'examination_submit_at') ?></td>
                            <td><?= Yii::t('common', 'status') ?></td>
                            <td><?= Yii::t('frontend', 'result') ?></td>
                        <?php
                        }elseif($type==LnInvestigation::ANSWER_TYPE_ANONYMOUS){
                        ?>
                            <td><?= Yii::t('common', 'real_name') ?></td>
                            <td><?= Yii::t('common', 'examination_submit_at') ?></td>
                            <td><?= Yii::t('frontend', 'result') ?></td>
                        <?php
                        }
                        ?>
                    </tr>
                    <?php
                    $user_array=[];
                    if(!empty($data)){
                        foreach($data as $k=>$v){
                        ?>
                        <tr>
                            <?php
                            if(!in_array($v['user_id'],$user_array)){
                                $user_array[] = $v['user_id'];
                                if($type==LnInvestigation::ANSWER_TYPE_REALNAME){
                            ?>
                                <td><?=$v['name']?></td>
                                <td>
                                    <?
                                    $orgName = '';
                                    $orgFullName = '';
                                    if ($v['orgnization_name_path'] && strpos($v['orgnization_name_path'], '/') !== false) {
                                        $orgName = substr(strrchr($v['orgnization_name_path'], '/'), 1) . '/' . $v['orgnization_name'];
                                        $orgFullName = $v['orgnization_name_path'] . '/' . $v['orgnization_name'];
                                    } elseif ($v['orgnization_name_path'] && strpos($v['orgnization_name_path'], '/') === false) {
                                        $orgFullName = $orgName = $v['orgnization_name_path'] . '/' . $v['orgnization_name'];
                                    } else {
                                        $orgFullName = $orgName = $v['orgnization_name'];
                                    }
                                    ?>
                                    <label title="<?= $orgFullName ?>"><?= $orgName ?></label>
                                </td>
                                <td><?=$v['position_name']?></td>
                                <td><?=!empty($v['created_at'])?TTimeHelper::FormatTime($v['created_at'],2):"--"?></td>
                                <td>
                                    <!-- 状态 -->
                                    <?=Yii::t('common', 'complete_status_'.intval($v['complete_status']));?>
                                </td>
                                <?php
                                if($category=='vote'){
                                ?>
                                    <td id="<?=$v['user_id']?>">
                                        <?=!empty($v['option'])?chr(64+$v['option']):'--'?>
                                    </td>
                                <?php
                                }elseif($category=='questionaire'){
                                ?>
                                <td>
                                <?php
                                if ($v['complete_status'] == LnResComplete::COMPLETE_STATUS_DONE){
                                ?>
                                    <a href="<?= Url::toRoute(['teacher/questionaire-result', 'courseId' => $courseid, 'modResId' => $modresid, 'userId' => $v['user_id'], 'itemId' => $inkid, 'target' => 1])?>" class="btn-xs" target="_blank"><?= Yii::t('common', 'view_button') ?></a>
                                <?php
                                }else{
                                    echo '--';
                                }
                                ?>
                                </td>
                                <?php
                                }
                                }elseif($type==LnInvestigation::ANSWER_TYPE_ANONYMOUS){
                                ?>
                                <td><?= Yii::t('frontend', 'joiner') ?><?=($k+1)?></td>
                                <td><?=!empty($v['created_at'])?TTimeHelper::FormatTime($v['created_at'],2):"--"?></td>
                                <?php
                                if($category=='vote'){
                                ?>
                                <td id="<?=$v['user_id']?>">
                                    <?=!empty($v['option'])?chr(64+$v['option']):'--'?>
                                </td>
                                <?php
                                }elseif($category=='questionaire'){
                                ?>
                                <td>
                                <?php
                                if ($v['complete_status'] == LnResComplete::COMPLETE_STATUS_DONE){
                                ?>
                                <a href="<?= Url::toRoute(['teacher/questionaire-result', 'courseId' => $courseid, 'modResId' => $modresid, 'userId' => $v['user_id'], 'itemId' => $inkid, 'target' => 1])?>" class="btn-xs" target="_blank"><?= Yii::t('common', 'view_button') ?></a>
                                <?php
                                }else{
                                    echo '--';
                                }
                                ?>
                                </td>
                            <?php
                                }
                                }
                            }else{
                            ?>
                            <script>
                                $('#<?=$v['user_id']?>').append(',<?=chr(64+$v['option'])?>');
                            </script>
                            <?
                            }
                            ?>
                        </tr>
                        <?php
                        }
                    }else{
                        if($type==0){
                        ?>
                            <tr>
                                <td colspan="6"><?= Yii::t('common', 'no_data') ?></td>
                            </tr>
                        <?php
                        }elseif($type==1){
                        ?>
                            <tr>
                                <td colspan="3"><?= Yii::t('common', 'no_data') ?></td>
                            </tr>
                        <?php
                        }
                    }
                    ?>
                    </tbody>
                </table>
                <nav>
                    <?php
                    if (!empty($pages)) {
                        echo TLinkPager::widget([
                            'id' => 'page',
                            'pagination' => $pages,
                            'displayPageSizeSelect' => false
                        ]);
                    }
                    ?>
                </nav>
                <script>
                    $(function(){
                        $("#resultform .pagination").on('click', 'a', function(e){
                            e.preventDefault();
                            if ($(this).parents("#teacher_survery").length > 0){
                                ajaxGet($(this).attr('href'), 'teacher_survery');
                            }else {
                                ajaxGet($(this).attr('href'), 'resultform');
                            }
                        });

                        $(".search").on('click', function(e){
                            var form = $(this).parent();
                            var status = form.find("select[name='status'] option:selected").val();
                            var keyword = form.find("input[name='keywords']").val().trim();
                            var url = '<?=Url::toRoute(['/teacher/get-vote-result','courseId'=>$courseid, 'modResId'=>$modresid, 'itemId' => $inkid, 'type' => $type, 'category' => $category])?>';
                            $.get(url, {status: status, keywords: keyword}, function(r){
                                if (r){
                                    if (form.parents("#teacher_survery").length > 0){
                                        $("#teacher_survery").html(r);
                                    }else{
                                        $("#resultform").html(r);
                                    }
                                }else{
                                    app.showMsg('<?=Yii::t('common', 'loading_fail')?>');
                                    return false;
                                }
                            });
                        });
                        $(".reset").on('click', function(){
                            var form = $(this).parent();
                            form.find("select[name='status']").find("option").attr('selected', false);
                            form.find("select[name='status']").find("option").eq(0).attr('selected', true);
                            form.find("input[name='keywords']").attr('value', '');
                        });
                    });
                </script>

            </div>
        </div>
    </div>

    <div class="c"></div>
</div>