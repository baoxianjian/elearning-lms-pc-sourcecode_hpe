  <?php
/**
 * User: baoxianjian
 * Date: 2016/5/9
 * Time: 10:41
 */
use components\widgets\TBreadcrumbs;
use yii\helpers\Url;
use common\helpers\TTimeHelper;
use common\helpers\TStringHelper;
use yii\helpers\Html;
use components\widgets\TLinkPager;
use common\models\learning\LnCourseSignIn; 
use common\models\learning\LnCourse;

?>

  <?php if($student_sign_list){ foreach($student_sign_list as $k=>$v) { $cur_sign_settings=$v['sign_settings'];?>
          <table class="table table-bordered table-hover table-striped table-center">
              <tbody>
              <tr>
                  <td width="5%"><input type="checkbox" id="sign_check_all" style="text-align: left; margin-right: 10px;"></td>
                  <td width="15%"><?=Yii::t('common', 'real_name')?></td>
                  <td width="15%"><?=Yii::t('common', 'department')?></td>
                  <td width="15%"><?= Yii::t('frontend', 'position') ?></td>
                  <td width="15%"><?=Yii::t('common', 'email')?></td>

                  <?php foreach($v['sign_settings'] as $k2=>$v2) {?>
                      <td width="10%"><?=$v2['title']?><br/><?=TTimeHelper::FormatTime($v2['start_at'],4)?>~<?=TTimeHelper::FormatTime($v2['end_at'],4)?></td>
                  <?php }?>
                  <?php if($courseModel->open_status!=LnCourse::COURSE_END){?>
                  <td width="9%"><?=Yii::t('common', 'special_operation')?></td>
                  <?php }?>
              </tr>

              <?php if($v['students']){ foreach($v['students'] as $stu){?>
                  <tr>
                      <td><input type="checkbox" name="sign_student_chk" value="<?=$stu['user_id']?>" style="text-align: left; margin-right: 10px;"></td>
                      <td><?=$stu['real_name']?></td>
                      <td><?=$stu['orgnization_name']?></td>
                      <td><span title="<?=$stu['position_name']?>" class="lessWord" style=" width: 100%;"><?=$stu['position_name']?></span></td>
                      <td><span title="<?=$stu['email']?>" class="lessWord" style=" width: 100%;"><?=$stu['email']?></span></td>
                      <?php foreach($stu['sign_data'] as $k3=>$signRecord){?>
                          <!--
                          course_id: $id
                          sign_in_setting_id: $k3
                          user_id:$stu['user_id']
                          sign_user_id: 当前操作者

                          -->
                          <td class="leave1">

                              <?php 
                              if($courseModel->open_status!=LnCourse::COURSE_END)
                              {?>
                                  <span id="sign_in_revoke_link_<?=$stu['user_id'].$k3?>" <?php if($signRecord && $signRecord['sign_flag']==LnCourseSignIn::SIGN_FLAG_SIGN_IN){echo 'style="display: inline"'; }else{echo 'style="display: none"';}?> sign_in_id="<?=$signRecord['kid']?>" class="statu"><?=Yii::t('frontend', 'signined')?><a href="javascript:void(0);" onclick="studentSignInRevoke('<?=$k3?>','<?=$stu['user_id']?>',1)" class="glyphicon glyphicon-refresh refreshBtn" title="<?=Yii::t('frontend', 'reset')?>"></a></span>
                                  <span id="leave_revoke_link_<?=$stu['user_id'].$k3?>" <?php if($signRecord && $signRecord['sign_flag']==LnCourseSignIn::SIGN_FLAG_LEAVE){echo 'style="display: inline"'; }else{echo 'style="display: none"';}?> sign_in_id="<?=$signRecord['kid']?>" class="statu"><?=Yii::t('frontend', 'sign_in_left')?><a href="javascript:void(0);" onclick="studentSignInRevoke('<?=$k3?>','<?=$stu['user_id']?>',2)" class="glyphicon glyphicon-refresh refreshBtn" title="<?=Yii::t('frontend', 'reset')?>"></a></span>
                                  <a id="sign_in_link_<?=$stu['user_id'].$k3?>" <?php if($signRecord){echo 'style="display: none"'; }else{echo 'style="display: inline"';}?> href="javascript:void(0);" onclick="studentSignIn('<?=$id?>','<?=$k3?>','<?=$stu['user_id']?>')" class="btn btn-xs btn-default statuBtn"><?=Yii::t('frontend', 'signin_btn')?></a>
                              <?php
                              }
                              else
                              {
                                 if($signRecord)
                                 {
                                    if($signRecord['sign_flag']==LnCourseSignIn::SIGN_FLAG_LEAVE)
                                    {
                                        echo Yii::t('frontend', 'sign_in_left');;
                                    }
                                    else
                                    {
                                        echo Yii::t('frontend', 'signined');
                                    }
                                 }
                                 else
                                 {
                                    echo Yii::t('frontend', 'signined_not');
                                 }
                              }?>
                              
                          </td>
                          <?php $i++; }?>
                          
                      <?php if($courseModel->open_status!=LnCourse::COURSE_END){?>
                      <td>
                        <div class="btn-group">
                          <button type="button" class="btn btn-xs btn-warning dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><?=Yii::t('frontend', 'ask_for_leave')?><span class="caret stBtn"></span>
                          </button>
                          <ul class="dropdown-menu" style=" min-width: 50px; ">
                            <?php foreach($v['sign_settings'] as $k2=>$v2) {?>
                                <li><a href="javascript:void(0);" class="leaveBtn" onclick="studentLeave('<?=$id?>','<?=$v2['kid']?>','<?=$stu['user_id']?>')" data-statu="leave2"><?=Yii::t('frontend', 'ask_for_leave')?>(<?=$v2['title']?>)</a></li>
                            <?php }?>
                          </ul>
                        </div>
                      </td>
                      <?php }?>
                  </tr>
              <?php }}else{?>
                <tr><td colspan="8"><?=Yii::t('frontend', 'can_not_find_data')?></td></tr>   
              <?php }?>
              </tbody>
          </table>
      <?php if($courseModel->open_status!=LnCourse::COURSE_END){?>
      <div class="btn-group dropup btn-sm pull-left" style="margin: 15px 10px 0 0;">
          <button type="button" class="btn btn-sm btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><?=Yii::t('frontend', 'batch_sign_in')?></button>
          <ul class="dropdown-menu" style=" min-width: 50px; ">
              <?php foreach($cur_sign_settings as $k2=>$v2) {?>
                  <li><a href="javascript:void(0);" onclick="batchSignIn('<?=$v2['kid']?>')" class="leaveBtn" data-statu="leave1"><?=$v2['title']?></a></li>
              <?php }?>
          </ul>
      </div>
      <?php }?>


  <nav style="margin-top: 15px; text-align: right;">
    <?php   
    if (!empty($v['pages'])) {
        echo TLinkPager::widget([
            'id' => 'page',
            'pagination' => $v['pages'],
            'displayPageSizeSelect' => false
        ]);
    }
    if ($show_all){
    ?>
    <button type="button" class="btn btn-default resizeBtn" title="<?= Yii::t('frontend', 'resize_current_button') ?>" onclick="loadStudentSignList(0)"><?= Yii::t('frontend', 'resize_current_button') ?></button>
    <?php
    }else{
    ?>
    <button type="button" class="btn btn-default resizeBtn" title="<?= Yii::t('frontend', 'resize_full_button') ?>" onclick="loadStudentSignList(1)"><?= Yii::t('frontend', 'resize_full_button') ?></button>
    
    <?php
    }
    ?>
</nav>
  
  
  
  <?php }}else{?>
    <a href="javascript:void(0);" class="btn btn-sm pull-left" onclick="showSignConfigDiv()"><?=Yii::t('frontend', 'please_click_here_to_set_sign_in_configuration')?></a>
  <?php }?>

  <script type="text/javascript">
      $(function(){
          $(".pagination").on('click', 'a', function(e){
              e.preventDefault();
              ajaxGet($(this).attr('href'), "student_sign_list");
          });
      });

      $("#sign_check_all").click(function () {

          var status = $(this).is(':checked');

          $("input[name='sign_student_chk'][type='checkbox']").prop("checked", status);
      });


  </script>
