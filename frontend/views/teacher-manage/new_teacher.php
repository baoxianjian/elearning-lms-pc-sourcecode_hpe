<?php
use yii\widgets\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;
use common\helpers\TStringHelper;
use common\models\learning\LnTeacher;

?>


<!-- 新建教师信息的弹出窗口 -->
  
   
        <div class="header">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
          <h4 class="modal-title" id="myModalLabel"><?= Yii::t('frontend', 'build_teacher') ?></h4>
        </div>
        <div class="content">
          <div class="courseInfo">
            <ul class="nav nav-tabs hotNews" role="tablist" id="myTab">
              <li role="presentation" class="active"><a href="#teacher_info" aria-controls="teacher_info" role="tab" data-toggle="tab"><?= Yii::t('frontend', 'personal_detail') ?></a></li>
            </ul>
            <div class="tab-content">
              <div role="tabpanel" class="tab-pane active" id="teacher_info">
                <div class=" panel-default scoreList">
                  <div class="panel-body">
                    <div class="infoBlock">
                    <form id="new_teacher_form">
                    
                      <input type="hidden" id="teacher_thumb_url"/>
                       <input type="hidden" id="t_company_id"/>
                        <input type="hidden" id="t_user_id"/>
           
          <div class="uploadFileTablePW row" style="border: 1px solid #eee;padding: 10px;margin-left: 15%;width: 70%;">
            <div class="col-md-12 col-sm-12">
                <div class="col-md-6 col-sm-6"><img id="img_thumb" src="<?= TStringHelper::Thumb($model->thumb,$model->gender) ?>" /></div>
                <div class="col-md-6 col-sm-6">
                    <p><?= Yii::t('common', 'upload_thumb') ?></p>
                    <p><?= Yii::t('frontend', 'tip_for_img_size') ?></p>
                    <p><?= Yii::t('frontend', 'tip_for_img_type') ?></p>
                    
                     <div class="row">
		                <input type="hidden" id="x" name="x"/>
		                <input type="hidden" id="y" name="y"/>
		                <input type="hidden" id="w" name="w"/>
		                <input type="hidden" id="h" name="h"/>
		                <input type="hidden" id="f" name="f"/>
		                <?=
		                Html::button(Yii::t('common', 'clear_setting'),
		                    ['id' => 'clear', 'class' => 'btn btn-success pull-right'])
		                ?>
		                <?=
		                Html::button(Yii::t('common', 'upload_thumb'),
		                    ['id' => 'upload', 'class' => 'btn btn-success pull-right'])
		                ?>
		                <?=
		                Html::button(Yii::t('common', 'confirm_crop'),
		                    ['id' => 'crop', 'class' => 'btn btn-success pull-right'])
		                ?>
		            </div>
                </div>
            </div>
           
        </div>
       
        <div class="upload-info"></div>
        <div class="pic-display"></div>
        <div class="text-info"></div>
                        
                      <div class="row">
                        <div class="col-md-6 col-sm-6">
                          <div class="form-group form-group-sm">
                            <label class="col-sm-3 control-label"><?= Yii::t('frontend', 'teacher_type') ?></label>
                            <div class="col-sm-9">
                              <div class="form-group field-courseservice-course_type">
                                <select class="form-control" id="teacher_type_id">
                                      <?php foreach($teacherTypes as $k=>$v) {?>
                                          <option value="<?=$k?>" ><?=$v?></option>
                                      <?php }?>
                                  </select>
                              </div>
                            </div>
                          </div>
                        </div>
                        <div class="col-md-6 col-sm-6">
                          <div class="form-group form-group-sm">
                            <label class="col-sm-3 control-label"><?= Yii::t('frontend', 'teacher_account') ?></label>
                            <div class="col-sm-9">
                              <input data-alert="<?= Yii::t('frontend', '{value}_not_null',['value'=>Yii::t('common','time_validity')]) ?>" data-delay="1" data-mode="COMMON" id="teacher_to_user_id" class="form-control changeTeacher2" type="text" style="width: 79%;margin-right: 1%;"  placeholder="<?= Yii::t('frontend', 'tip_for_teacher_account') ?>">
                              <input class="btn btn-default btn-sm" type="button" id="teacher_syn_from_user" style="width: 20%;" value="<?= Yii::t('frontend', 'sync') ?>" >
                            </div>
                          </div>
                        </div>
                      </div>





                      <div class="row">
                        <div class="col-md-6 col-sm-6">
                          <div class="form-group form-group-sm">
                            <label class="col-sm-3 control-label"><?= Yii::t('common', 'real_name') ?></label>
                            <div class="col-sm-9">
                              <input  data-mode="COMMON" data-condition="^\S{1,25}$" data-alert="<?= Yii::t('frontend', 'name_not_null_or_not_beyond_25') ?>" class="form-control" type="text" name="teacher_name" id="teacher_name_id" >
                              <div class="help-block hide"></div>
                            </div>
                          </div>
                        </div>
                        <div class="col-md-6 col-sm-6">
                          <div class="form-group form-group-sm">
                            <label class="col-sm-3 control-label"><?=Yii::t('common','teacher_level')?></label>
                            <div class="col-sm-9">
                              <div class="form-group field-courseservice-course_type">
                                  <select class="form-control" id="teacher_level_id">
                                      <?php foreach($teacherLevels as $k=>$v) {?>
                                          <option kid="<?=$k?>" value="<?=$v['value']?>" ><?=$v['name']?></option>
                                      <?php }?>
                                  </select>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                      <div class="row">
                        <div class="col-md-6 col-sm-6">
                          <div class="form-group form-group-sm">
                            <label class="col-sm-3 control-label"><?= Yii::t('common', 'nick_name') ?></label>
                            <div class="col-sm-9">
                              <input data-mode="COMMON" data-condition="^.{0,25}$" data-alert="<?=Yii::t('frontend', '{value}_limit_25_word',['value'=>Yii::t('common', 'nick_name') ]) ?>" class="form-control" type="text" name="teacher_nick" id="teacher_nick_id" >
                            </div>
                          </div>
                        </div>
                        <div class="col-md-6 col-sm-6">
                          <div class="form-group form-group-sm">
                            <label class="col-sm-3 control-label"><?= Yii::t('frontend', 'highest_degree') ?></label>
                            <div class="col-sm-9">
                              <input value="<?=$teacher['degree'] ?>" data-mode="COMMON" data-condition="^.{0,15}$" data-alert="<?=Yii::t('frontend', '{value}_limit_15_word',['value'=>Yii::t('common', 'highest_degree') ]) ?>" class="form-control" type="text" name="degree" id="degree_id" >
                            </div>
                          </div>
                        </div>
                        <!--
                        <div class="col-md-6 col-sm-6">
                          <div class="form-group form-group-sm">
                            <label class="col-sm-3 control-label">毕业院校</label>
                            <div class="col-sm-9">
                              <input  data-mode="COMMON" data-condition="^.{0,125}$" data-alert="毕业院校超过125" class="form-control" type="text" name="graduate_school" id="graduate_school_id" >
                            </div>
                          </div>
                        </div>
                        -->
                      </div>
                      <div class="row">
                        <div class="col-md-6 col-sm-6">
                          <div class="form-group form-group-sm">
                            <label class="col-sm-3 control-label"><?= Yii::t('common', 'sex') ?></label>
                            <div class="col-sm-9">
                               
                              <div class="form-group field-courseservice-course_type">
                                <select class="form-control" id="gender_id">
                                  <option value="male" selected="selected"><?= Yii::t('common', 'gender_male') ?></option>
                                  <option value="female"><?= Yii::t('common', 'gender_female') ?></option>
                                 
                                </select>
                              </div>
                            
                            </div>
                          </div>
                        </div>
                        <div class="col-md-6 col-sm-6">
                          <div class="form-group form-group-sm">
                            <label class="col-sm-3 control-label"><?= Yii::t('common', 'teach_year') ?></label>
                            <div class="col-sm-9">
                              <input data-alert="<?= Yii::t('frontend', '{value}_not_null',['value'=>Yii::t('common','time_validity')]) ?>" data-delay="1" data-mode="COMMON" class="form-control" type="text" name="teach_year" id="teach_year_id" >
                            </div>
                          </div>
                        </div>
                      </div>
                      <div class="row">
                        <div class="col-md-6 col-sm-6">
                          <div class="form-group form-group-sm">
                            <label class="col-sm-3 control-label"><?= Yii::t('common', 'birthday') ?></label>
                            <div class="col-sm-9">
                              <input  readonly="readonly" data-mode="COMMON" data-range=",<?=$data_range ?>" data-condition="required" data-alert="<?= Yii::t('frontend', '{value}_not_null',['value'=>Yii::t('common','birthday')]) ?>" class="form-control" type="text" id="birthday_id" name="birthday" data-type="rili" placeholder="yyyy-mm-dd">
                            </div>
                          </div>
                        </div>
                        <div class="col-md-6 col-sm-6">
                          <div class="form-group form-group-sm">
                            <label class="col-sm-3 control-label"><?= Yii::t('common', 'teach_domain') ?></label>
                            <div class="col-sm-9">
                              <input data-mode="COMMON" data-condition="^.{0,125}$" data-alert="<?=Yii::t('frontend', '{value}_limit_125_word',['value'=>Yii::t('common', 'teach_domain') ]) ?>" class="form-control" type="text" name="teach_domain" id="teach_domain_id" >
                            </div>
                          </div>
                        </div>
                      </div>
                      <div class="row">
                        <div class="col-md-6 col-sm-6">
                          <div class="form-group form-group-sm">
                            <label class="col-sm-3 control-label"><?= Yii::t('common', 'mobile_no') ?></label>
                            <div class="col-sm-9">
                              <input data-alert="<?= Yii::t('frontend', '{value}_not_null',['value'=>Yii::t('common','time_validity')]) ?>" data-delay="1" data-mode="COMMON" class="form-control" type="text" name="mobile_no" id="mobile_no_id" >
                            </div>
                          </div>
                        </div>
                        <div class="col-md-6 col-sm-6">
                          <div class="form-group form-group-sm">
                            <label class="col-sm-3 control-label"><?= Yii::t('common', 'home_phone_no') ?></label>
                            <div class="col-sm-9">
                              <input data-alert="<?= Yii::t('frontend', '{value}_not_null',['value'=>Yii::t('common','time_validity')]) ?>" data-delay="1" data-mode="COMMON" class="form-control" type="text" name="home_phone_no" id="home_phone_no_id" >
                            </div>
                          </div>
                        </div>
                      </div>
                      <!--  
                      <div class="row">
                     
                        <div class="col-md-6 col-sm-6">
                          <div class="form-group form-group-sm">
                            <label class="col-sm-3 control-label">时区</label>
                            <div class="col-sm-9">
                              <input data-mode="COMMON" data-condition="^.{0,25}$" data-alert="时区超过25" class="form-control" type="text" name="timezone" id="timezone_id" placeholder="上海">
                            </div>
                          </div>
                        </div>
                        <div class="col-md-6 col-sm-6">
                          <div class="form-group form-group-sm">
                            <label class="col-sm-3 control-label">语言</label>
                            <div class="col-sm-9">
                              <input data-mode="COMMON" data-condition="^.{0,25}$" data-alert="语言超过25" class="form-control" type="text" name="language" id="language_id" placeholder="汉语">
                            </div>
                          </div>
                        </div>
                       
                      </div>
                      -->


                        <div class="row">
                       <div class="col-md-6 col-sm-6">
                          <div class="form-group form-group-sm">
                            <label class="col-sm-3 control-label"><?= Yii::t('frontend', 'teacher_title') ?></label>
                            <div class="col-sm-9">
                              <input data-mode="COMMON" data-condition="^.{0,125}$" data-alert="<?=Yii::t('frontend', '{value}_limit_125_word',['value'=>Yii::t('common', 'teacher_title') ]) ?>" class="form-control" type="text" name="teacher_title" id="teacher_title_id" >
                          </div>
                          </div>
                        </div>
                       
                        
                      </div>
                      
                       <div class="row">
                        <div class="col-md-12 col-sm-12">
                          <div class="form-group form-group-sm">
                            <label class="col-sm-2 control-label" style="width:12%"><?= Yii::t('frontend', 'teacher_description') ?></label>
                            
                            <div class="col-sm-10" style="width:88%">
                              <textarea id="description"></textarea>
                            </div>
                          </div>
                        </div>
                      </div>
                      
                      
                      </form>
                    </div>
                    <div id="n_b_teacher_show_div" class="infoBlock teacher_EM2 hide">
                      <h4><?= Yii::t('frontend', 'business_related') ?>(<?= Yii::t('frontend', 'use_by_internal_teacher') ?>)</h4>
                      <!-- 如果是内部讲师,显示这个 -->
                      <hr/>
                      <div class="row">
                        <div class="col-md-6 col-sm-6">
                          <div class="form-group form-group-sm">
                            <label class="col-sm-3 control-label"><?= Yii::t('frontend', 'business_name') ?></label>
                            <div class="col-sm-9">
                              <input readonly="readonly" class="form-control" type="text" id="company_id" placeholder="<?= Yii::t('frontend', 'business_name') ?>">
                            </div>
                          </div>
                        </div>
                        <div class="col-md-6 col-sm-6">
                          <div class="form-group form-group-sm">
                            <label class="col-sm-3 control-label"><?= Yii::t('frontend', 'belong_to_domain') ?></label>
                            <div class="col-sm-9">
                              <input readonly="readonly" class="form-control" type="text" id="domain_id" placeholder="<?= Yii::t('frontend', 'train_manage_domain') ?>">
                            </div>
                          </div>
                        </div>
                      </div>
                      <div class="row">
                        <div class="col-md-6 col-sm-6">
                          <div class="form-group form-group-sm">
                            <label class="col-sm-3 control-label"><?= Yii::t('frontend', 'organization_department') ?></label>
                            <div class="col-sm-9">
                              <input readonly="readonly" class="form-control" type="text" id="orgnization_id" placeholder="<?= Yii::t('frontend', 'train_partment') ?>">
                            </div>
                          </div>
                        </div>
                        <div class="col-md-6 col-sm-6">
                          <div class="form-group form-group-sm">
                            <label class="col-sm-3 control-label"><?= Yii::t('frontend', 'position') ?></label>
                            <div class="col-sm-9">
                              <input readonly="readonly" class="form-control" type="text" id="position_id" placeholder="<?= Yii::t('frontend', 'train_manager') ?>">
                            </div>
                          </div>
                        </div>
                      </div>
                      <div class="row">
                        <div class="col-md-6 col-sm-6">
                          <div class="form-group form-group-sm">
                            <label class="col-sm-3 control-label"><?= Yii::t('frontend', 'reporting_manager') ?></label>
                            <div class="col-sm-9">
                              <input readonly="readonly" class="form-control" type="text" id="reporting_manager_id" placeholder="">
                            </div>
                          </div>
                        </div>
                        <div class="col-md-6 col-sm-6">
                        </div>
                      </div>
                    </div>
                    
                     <div class="centerBtnArea">
                      <a href="#" id="btn_save_teacher" class="btn btn-success btn-sm centerBtn" style="width:20%"><?=Yii::t('common', 'save')?></a>
                    </div>
                    
                    <div class="infoBlock teacher_CW hide">
                      <h4><?= Yii::t('frontend', 'business_related') ?>(<?= Yii::t('frontend', 'use_by_external_teacher') ?>)</h4>
                      <!-- 如果是外部讲师,显示这个 -->
                      <hr/>
                      <div class="row">
                        <div class="col-md-6 col-sm-6">
                          <div class="form-group form-group-sm">
                            <label class="col-sm-3 control-label"><?= Yii::t('frontend', 'business_name') ?></label>
                            <div class="col-sm-9">
                              <input class="form-control" type="text" id="formGroupInputSmall" placeholder="<?= Yii::t('frontend', 'business_name') ?>">
                            </div>
                          </div>
                        </div>
                        <div class="col-md-6 col-sm-6">
                          <div class="form-group form-group-sm">
                            <label class="col-sm-3 control-label"><?= Yii::t('frontend', 'position') ?></label>
                            <div class="col-sm-9">
                              <input class="form-control" type="text" id="formGroupInputSmall" placeholder="<?= Yii::t('frontend', 'train_manage_domain') ?>">
                            </div>
                          </div>
                        </div>
                      </div>
                      <div class="row">
                        <div class="col-md-6 col-sm-6">
                          <div class="form-group form-group-sm">
                            <label class="col-sm-3 control-label"><?= Yii::t('frontend', 'business_no') ?></label>
                            <div class="col-sm-9">
                              <input class="form-control" type="text" id="formGroupInputSmall" placeholder="<?= Yii::t('frontend', 'train_partment') ?>">
                            </div>
                          </div>
                        </div>
                        <div class="col-md-6 col-sm-6">
                          <div class="form-group form-group-sm">
                            <label class="col-sm-3 control-label"><?= Yii::t('frontend', 'business_mail') ?></label>
                            <div class="col-sm-9">
                              <input class="form-control" type="text" id="formGroupInputSmall" placeholder="<?= Yii::t('frontend', 'train_manager') ?>">
                            </div>
                          </div>
                        </div>
                      </div>
                      <div class="row">
                        <div class="col-md-6 col-sm-6">
                          <div class="form-group form-group-sm">
                            <label class="col-sm-3 control-label"><?= Yii::t('common', 'address_name') ?></label>
                            <div class="col-sm-9">
                              <input class="form-control" type="text" id="formGroupInputSmall" placeholder="">
                            </div>
                          </div>
                        </div>
                        <div class="col-md-6 col-sm-6">
                        </div>
                      </div>
                    </div>
                    <div class="infoBlock teacher_CW hide">
                      <h4><?= Yii::t('frontend', 'course_can_use') ?>(<?= Yii::t('frontend', 'use_by_external_teacher') ?>)</h4>
                      <!-- 如果是外部讲师,显示这个 -->
                      <hr/>
                      <div class="row">
                        <div class="col-md-7 col-sm-7">
                          <div class="form-group form-group-sm">
                            <label class="col-sm-12 control-label">[<?=Yii::t('common', 'face_to_face')?>]市场策略高管培训</label>
                          </div>
                        </div>
                        <div class="col-md-2 col-sm-2">
                          <div class="form-group form-group-sm">
                            <label class="col-sm-12 control-label">25<?=Yii::t('frontend', 'study_hours')?></label>
                          </div>
                        </div>
                        <div class="col-md-3 col-sm-3">
                          <div class="form-group form-group-sm">
                            <label class="col-sm-12 control-label"><?= Yii::t('frontend', 'course_price') ?>:100,00</label>
                          </div>
                        </div>
                      </div>
                      <div class="row">
                        <div class="col-md-7 col-sm-7">
                          <div class="form-group form-group-sm">
                            <label class="col-sm-12 control-label">[<?=Yii::t('common', 'face_to_face')?>]市场策略高管培训</label>
                          </div>
                        </div>
                        <div class="col-md-2 col-sm-2">
                          <div class="form-group form-group-sm">
                            <label class="col-sm-12 control-label">25<?=Yii::t('frontend', 'study_hours')?></label>
                          </div>
                        </div>
                        <div class="col-md-3 col-sm-3">
                          <div class="form-group form-group-sm">
                            <label class="col-sm-12 control-label"><?= Yii::t('frontend', 'course_price') ?>:100,00</label>
                          </div>
                        </div>
                      </div>
                      <div class="row">
                        <div class="col-md-7 col-sm-7">
                          <div class="form-group form-group-sm">
                            <label class="col-sm-12 control-label">[<?=Yii::t('common', 'face_to_face')?>]服务制胜-连锁销售企业的服务流程</label>
                          </div>
                        </div>
                        <div class="col-md-2 col-sm-2">
                          <div class="form-group form-group-sm">
                            <label class="col-sm-12 control-label">25<?=Yii::t('frontend', 'study_hours')?></label>
                          </div>
                        </div>
                        <div class="col-md-3 col-sm-3">
                          <div class="form-group form-group-sm">
                            <label class="col-sm-12 control-label"><?= Yii::t('frontend', 'course_price') ?>:100,00</label>
                          </div>
                        </div>
                      </div>
                      <div class="row">
                        <div class="col-md-7 col-sm-7">
                          <div class="form-group form-group-sm">
                            <label class="col-sm-12 control-label">[<?=Yii::t('common', 'face_to_face')?>]市场策略高管培训</label>
                          </div>
                        </div>
                        <div class="col-md-2 col-sm-2">
                          <div class="form-group form-group-sm">
                            <label class="col-sm-12 control-label">25<?=Yii::t('frontend', 'study_hours')?></label>
                          </div>
                        </div>
                        <div class="col-md-3 col-sm-3">
                          <div class="form-group form-group-sm">
                            <label class="col-sm-12 control-label"><?= Yii::t('frontend', 'course_price') ?>:100,00</label>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          
          	<div class="c"></div>
          </div>
       
   <script type="text/javascript">
   app.genCalendar();

   var teach_year_submit=true;
   var mobile_no_submit=true;
   var home_phone_no_submit=true;
   
   $(function(){
       // 
       window.validation_new_teacher =app.creatFormValidation($("#new_teacher_form"));   

       $("#teach_year_id").blur(function(){

    	   teach_year_submit= checkTeachYear("teach_year_id",validation_new_teacher);

       });

       $("#mobile_no_id").blur(function(){

    	   mobile_no_submit= checkMobile("mobile_no_id",validation_new_teacher);

       });

       $("#home_phone_no_id").blur(function(){

    	   home_phone_no_submit=  checkPhone("home_phone_no_id",validation_new_teacher);

       });

       $("#teacher_to_user_id").blur(function(){
    	   console.log("teacher_to_user_id");
           getUser();
       });
       

       $("#teacher_syn_from_user").click(function(){
           console.log("teacher_syn_from_user");
           if($.trim($("#teacher_to_user_id").val())!=""){
               $.get("<?=Url::toRoute(['teacher-manage/syn-user'])?>",{user_name:$.trim($("#teacher_to_user_id").val())},function(data){
                   console.log(data.result);

                   if(!data.result)
                   {
                       validation_new_teacher.showAlert("#teacher_to_user_id","<?=Yii::t('frontend', 'account_not_exist')?>");
                       $("#n_b_teacher_show_div").addClass('hide');
                       app.refreshAlert("#new_teacher");
                       $("#t_company_id").val("");
                       $("#t_user_id").val("");
                   }
                       
                   if($("#teacher_type_id").val()=="<?=LnTeacher::TEACHER_TYPE_INTERNAL?>" && !data.can_bind) 
                   {
                       validation_new_teacher.showAlert("#teacher_to_user_id", "<?=Yii::t('frontend', 'account_belong_other_teacher')?>");
                       $("#n_b_teacher_show_div").addClass('hide');
                       app.refreshAlert("#new_teacher");
                       $("#t_company_id").val("");
                       $("#t_user_id").val("");
                       return;
                   }

                   $("#teacher_name_id").val(data.result.real_name);
                   $("#gender_id").val(data.result.gender);
                   $("#teacher_nick_id").val(data.result.nick_name);
                   $("#birthday_id").val(data.result.birthday);
                   $("#mobile_no_id").val(data.result.mobile_no);
                   $("#home_phone_no_id").val(data.result.home_phone_no);

                   $("#n_b_teacher_show_div").removeClass('hide');
                   app.refreshAlert("#new_teacher");
                   validation_new_teacher.hideAlert("#teacher_to_user_id");
               });
           }else{

               validation_new_teacher.showAlert("#teacher_to_user_id","<?=Yii::t('frontend', '{value}_not_null',['value'=>Yii::t('frontend','teacher_account')])?>");

           }
       });



       $("#btn_save_teacher").click(function(){

    	   save_new_teacher("<?=Url::toRoute(['teacher-manage/save-teacher',])?>");

       });
       
       
       

//
   });

   function getUser()
   {
       if($.trim($("#teacher_to_user_id").val())!=""){
           $.get("<?=Url::toRoute(['teacher-manage/get-user'])?>",{user_name:$.trim($("#teacher_to_user_id").val()),tid:null},function(data){
               console.log(data.result);

               if(!data.result.company_id){
                   validation_new_teacher.showAlert("#teacher_to_user_id","<?=Yii::t('frontend', 'account_not_exist')?>");
                   $("#n_b_teacher_show_div").addClass('hide');
                   app.refreshAlert("#new_teacher");
                   $("#t_company_id").val("");
                   $("#t_user_id").val("");
                   return;
               }

               if($("#teacher_type_id").val()=="<?=LnTeacher::TEACHER_TYPE_INTERNAL?>")
               {
                   if(!data.can_bind)
                   {
                       validation_new_teacher.showAlert("#teacher_to_user_id", "<?=Yii::t('frontend', 'account_belong_other_teacher')?>");
                       $("#n_b_teacher_show_div").addClass('hide');
                       app.refreshAlert("#new_teacher");
                       $("#t_company_id").val("");
                       $("#t_user_id").val("");
                       return;
                   }

                   $("#company_id").val(data.result.company_name);
                   $("#t_company_id").val(data.result.company_id);
                   $("#t_user_id").val(data.result.user_id);
                   $("#domain_id").val(data.result.domain_name);
                   $("#orgnization_id").val(data.result.orgnization_name);
                   $("#position_id").val(data.result.position_name);
                   $("#reporting_manager_id").val(data.result.reporting_manager);
                   $("#n_b_teacher_show_div").removeClass('hide');
                   app.refreshAlert("#new_teacher");
                   validation_new_teacher.hideAlert("#teacher_to_user_id");
               }
               else
               {
                   $("#t_company_id").val(data.result.company_id);
                   $("#t_user_id").val(data.result.user_id);
                   $("#company_id").val("");
                   //$("#t_company_id").val("");
                   //$("#t_user_id").val("");
                   $("#domain_id").val("");
                   $("#orgnization_id").val("");
                   $("#position_id").val("");
                   $("#reporting_manager_id").val("");
                   $("#n_b_teacher_show_div").addClass('hide');
                   app.refreshAlert("#new_teacher");
               }
           });
       }else{

           validation_new_teacher.showAlert("#teacher_to_user_id","<?=Yii::t('frontend', '{value}_not_null',['value'=>Yii::t('frontend','teacher_account')])?>");

       }
   }

   var submit_no=0;

   function save_new_teacher(url){
	 if(!validation_new_teacher.validate()){
		        return;
			  }


	    if(!teach_year_submit){
	    	validation_new_teacher.showAlert("#teach_year_id","<?=Yii::t('frontend', '{value}_input_wrong',['value'=>Yii::t('common','teach_year')])?>");
	    	return;
		}

		if(!mobile_no_submit){
	    	validation_new_teacher.showAlert("#mobile_no_id","<?=Yii::t('frontend', '{value}_input_wrong',['value'=>Yii::t('common','mobile_no')])?>");
	    	return;
		}


		if(!home_phone_no_submit){
	    	validation_new_teacher.showAlert("#home_phone_no_id","<?=Yii::t('frontend', '{value}_input_wrong',['value'=>Yii::t('common','telephone_no')])?>");
	    	return;
		}
			    
        var teacher_type=$("#teacher_type_id").val();
	
	    var teacher_name=$("#teacher_name_id").val();
		var degree=$("#degree_id").val();
        var teacher_nick=$("#teacher_nick_id").val();
		var graduate_school=$("#graduate_school_id").val();		
	    var gender=$("#gender_id").val();
	    
	    var teach_year=$("#teach_year_id").val();
	    var birthday=$("#birthday_id").val();
	    var teach_domain=$("#teach_domain_id").val();
	    var mobile_no=$("#mobile_no_id").val();

	    var home_phone_no=$("#home_phone_no_id").val();
	    var timezone=$("#timezone_id").val();
	    var language=$("#language_id").val();

	    var company_name=$("#company_id").val();

	    var teacher_title=$("#teacher_title_id").val();
        var teacher_level=$("#teacher_level_id").val();
        var teacher_level_id=$("#teacher_level_id option:selected").attr("kid");
	    
	    

	    if($.trim($("#teacher_to_user_id").val())==""){
	    	validation_new_teacher.showAlert("#teacher_to_user_id","<?=Yii::t('frontend', '{value}_not_null',['value'=>Yii::t('frontend','teacher_account')])?>");
	    	return;
		}

	    var company_id=$("#t_company_id").val();
        if($("#teacher_type_id").val()=="<?=LnTeacher::TEACHER_TYPE_INTERNAL?>" && company_id==""){
           validation_new_teacher.showAlert("#teacher_to_user_id","<?=Yii::t('frontend', '{value}_is_wrong',['value'=>Yii::t('frontend','teacher_account')])?>");
           return;
        }
	    var user_id=$("#t_user_id").val();   
        if(user_id==""){
            validation_new_teacher.showAlert("#teacher_to_user_id","<?=Yii::t('frontend', '{value}_is_wrong',['value'=>Yii::t('frontend','teacher_account')])?>");
            return;
        }
	    
	    var teacher_obj={};
	    
	    teacher_obj.teacher_name=teacher_name;
	    teacher_obj.degree=degree;
	    teacher_obj.teacher_nick=teacher_nick;
	    teacher_obj.graduate_school=graduate_school;
	    teacher_obj.gender=gender;
	    
	    teacher_obj.teach_year=teach_year;
	    teacher_obj.birthday=birthday;
	    teacher_obj.teach_domain=teach_domain;
	    teacher_obj.mobile_no=mobile_no;

	    teacher_obj.home_phone_no=home_phone_no;
	    //teacher_obj.timezone=timezone;
	   // teacher_obj.language=language;
	    teacher_obj.teacher_type=teacher_type;
	    teacher_obj.user_id=user_id;
	    teacher_obj.company_id=company_id;
	    teacher_obj.company_name=company_name;
	    teacher_obj.teacher_thumb_url=$("#teacher_thumb_url").val();
	    teacher_obj.description=$("#description").val();

	    teacher_obj.teacher_title=teacher_title;
        teacher_obj.teacher_level=teacher_level;
        teacher_obj.teacher_level_id=teacher_level_id;
		 
		  console.log(url);

		  if(submit_no==0){
	 		   submit_no++;
			   $.ajax({
				   type: "POST",
				   url: url,
				   data: teacher_obj,
				   success: function(msg){
					 loadList();
					 //$("#new_teacher").modal('hide');
					 app.hideAlert("#new_teacher");
				   }
				 });
		  }
	}

   </script>
   
   
   <?= Html::cssFile('/static/common/css/jquery.Jcrop.css') ?>
<?= Html::jsFile('/static/common/js/ajaxupload.js') ?>
<?= Html::jsFile('/static/common/js/jquery.Jcrop.min.js') ?>


<script>
    $(document).ready(function () {
        $("#crop").hide();
    });
    var ajaxUploadUrl = "<?=Url::toRoute(['common/upload'])?>";
    var g_oJCrop = null;
    //alert(ajaxUploadUrl);
    //异步上传文件
    new AjaxUpload("#upload", {
        action: ajaxUploadUrl,
        type: "POST",
        name: 'myfile',
        data: {'_csrf': '<?= Yii::$app->request->csrfToken ?>'},
        onSubmit: function (file, ext) {
            if ($(".text-info img").length > 0) {
                $(".upload-info").html("<div style='color:#E3583B;margin:5px;'>" + "<?=Yii::t('common', 'file_cropped')?>" + "</div>");
                return false;
            }
            $(".upload-info").html("<div style='color:#008000;margin:5px;'>" + "<?=Yii::t('common', 'uploading')?>" + "</div>");
        },
        onComplete: function (file, response) {
            if (g_oJCrop != null) {
                g_oJCrop.destroy();
            }

            if (response == "<?=Yii::t('common', 'file_type_error')?>" || response == "<?=Yii::t('common', 'upload_error')?>") {
                $(".upload-info").html("<div style='color:red;margin:5px;'>" + response + "</div>");
                $("#crop").hide();
            }
            else {
                //生成元素
                $(".pic-display").html("<div class='thum'><img id='target' src='" + response + "'/></div>");

                //初始化裁剪区
                $('#target').Jcrop({
                    onChange: updatePreview,
                    onSelect: updatePreview,
                    aspectRatio: 1
                }, function () {
                    g_oJCrop = this;

                    //插入略缩图
                    $(".jcrop-holder").append("<div id='preview-pane'><div class='preview-container'><img  class='jcrop-preview' src='" + response + "' /></div></div>");

                    var bounds = g_oJCrop.getBounds();
                    var x1, y1, x2, y2;
                    if (bounds[0] / bounds[1] > 150 / 150) {
                        y1 = 0;
                        y2 = bounds[1];

                        x1 = (bounds[0] - 150 * bounds[1] / 150) / 2;
                        x2 = bounds[0] - x1;
                    }
                    else {
                        x1 = 0;
                        x2 = bounds[0];

                        y1 = (bounds[1] - 150 * bounds[0] / 150) / 2;
                        y2 = bounds[1] - y1;
                    }


                    g_oJCrop.setSelect([x1, y1, x2, y2]);

                });
                //传递参数上传
                $("#f").val(response);

                //更新提示信息
                $(".upload-info").html("<div style='color:#008000;margin:5px;'>" + "<?=Yii::t('common', 'prepare_crop')?>" + "</div>");

                $("#crop").show();
            }
        }
    });

    //更新裁剪图片信息
    function updatePreview(c) {

        if (parseInt(c.w) > 0) {
            $('#x').val(c.x);
            $('#y').val(c.y);
            $('#w').val(c.w);
            $('#h').val(c.h);
            var bounds = g_oJCrop.getBounds();

            var rx = 150 / c.w;
            var ry = 150 / c.h;

            $('.preview-container img').css({
                width: Math.round(rx * bounds[0]) + 'px',
                height: Math.round(ry * bounds[1]) + 'px',
                marginLeft: '-' + Math.round(rx * c.x) + 'px',
                marginTop: '-' + Math.round(ry * c.y) + 'px'
            });
        }
    }


    var ajaxCutPicUrl = "<?=Url::toRoute(['teacher-manage/cut-pic'])?>";

    //表单异步提交后台裁剪
    $("#crop").click(function () {
        var w = parseInt($("#w").val());
        if (!w) {
            w = 0;
        }
        if (w > 0) {
            $.post(
                ajaxCutPicUrl,
                {
                    'x': $("input[name=x]").val(),
                    'y': $("input[name=y]").val(),
                    'w': $("input[name=w]").val(),
                    'h': $("input[name=h]").val(),
                    'f': $("input[name=f]").val(),
                    '_csrf': '<?= Yii::$app->request->csrfToken ?>'
                },
                function (data) {
                    //  alert(data.status);
                    if (data.status == 1) {
                        $(".pic-display").html("");
                        $(".upload-info").html("<div style='color:#008000;margin:5px;'>" + "<?=Yii::t('common', 'thumb_upload_ok')?>" + "</div>")
                        //                    $(".text-info").html("<img src='"+data.data+"'>");
                        $(".text-info").html("");
                        $("#img_thumb").attr('src', data.data);
                        $("#teacher_thumb_url").val(data.data);
                        $("#crop").hide();
                        //$("#upload").hide();
                    }

                }, 'json');
        } else {
            $(".upload-info").html("<div style='color:#E3583B;margin:5px;'>" + "<?=Yii::t('common', 'crop_area_select')?>" + "</div>");
        }
    });


    var ajaxClearPicUrl = "<?=Url::toRoute(['teacher-manage/clear-pic'])?>";

    //表单异步提交后台裁剪
    $("#clear").click(function () {
        var msg = "<?=Yii::t('common','operation_confirm')?>";
        var gender = "<?=$model->gender ?>";
        var thumbUrl = '/static/common/images/man.jpeg';
        if (gender == <?=Yii::t('common', 'gender_female')?>) {
          thumbUrl = '/static/common/images/woman.jpeg';
        }
        NotyConfirm(msg, function (data) {
        	var msg = "<?=Yii::t('common','operation_success')?>";
        	app.showMsg(msg);
            $("#img_thumb").attr('src', thumbUrl);
            $(".info").html("");
            $(".pic-display").html("");
            $(".text-info").html("");
            $("#teacher_thumb_url").val("");
        });

    });
    
    $("#teacher_type_id").change(function(){
        showSynButton($(this).val());

        if($("#teacher_to_user_id").val())
        {
            //getUser();
        }
    }
    );

    function showSynButton(teacherType)
    {
        if(teacherType=='<?=LnTeacher::TEACHER_TYPE_INTERNAL?>')
        {
            $("#teacher_syn_from_user").css("display","block");
            $("#teacher_to_user_id").attr("style","width: 79%;margin-right: 1%;");
            /*
             $("#n_b_teacher_show_div").removeClass('hide');
             app.refreshAlert("#new_teacher");
             validation_new_teacher.hideAlert("#teacher_to_user_id");
             */
        }
        else
        {
            $("#teacher_syn_from_user").css("display","none");
            $("#teacher_to_user_id").attr("style","");

            $("#n_b_teacher_show_div").addClass('hide');
            validation_new_teacher.hideAlert("#teacher_to_user_id");
        }
        setTimeout(function (){app.refreshAlert("#new_teacher");}, 0);
    }

    showSynButton($("#teacher_type_id").val());
</script>