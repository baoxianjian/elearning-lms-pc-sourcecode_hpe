<?php
?>



                <div class=" panel-default scoreList">
                  <div class="panel-body">
                    <div class="infoBlock">
                    
                     <div class="row">
                        <div class="col-md-6 col-sm-6">
                          <div class="form-group form-group-sm">
                            <label class="col-sm-3 control-label"><?= Yii::t('common', 'headimg_url') ?></label>
                            <div class="col-sm-9">
                              <img src="<?=$teacher['teacher_thumb_url'] ?>">
                            </div>
                          </div>
                        </div>
                      </div>


                      <div class="row">
                        <div class="col-md-6 col-sm-6">
                          <div class="form-group form-group-sm">
                            <label class="col-sm-3 control-label"><?= Yii::t('frontend', 'teacher_type') ?></label>
                            <div class="col-sm-9">
                              <div class="form-group field-courseservice-course_type">
                                <select disabled="disabled" class="form-control" id="teacher_type_id">
                                  <?php foreach($teacherTypes as $k=>$v) {?>
                                    <option value="<?=$k?>" <?=$teacherTypesSelected[$k]?> ><?=$v?></option>
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
                              <input readonly="readonly" value="<?=$uinfo['user_name'] ?>" data-alert="<?= Yii::t('frontend', '{value}_not_null',['value'=>Yii::t('common','time_validity')]) ?>" data-delay="1" data-mode="COMMON" id="teacher_to_user_id" class="form-control changeTeacher2" type="text" placeholder="<?= Yii::t('frontend', 'tip_for_teacher_account') ?>">
                            </div>
                          </div>
                        </div>
                      </div>
                    
                      <div class="row">
                        <div class="col-md-6 col-sm-6">
                          <div class="form-group form-group-sm">
                            <label class="col-sm-3 control-label"><?= Yii::t('common', 'real_name') ?></label>
                            <div class="col-sm-9">
                              <input readonly="readonly" value="<?=$teacher['teacher_name'] ?>" class="form-control" type="text" id="formGroupInputSmall" >
                              <div class="help-block hide"></div>
                            </div>
                          </div>
                        </div>
                        <!--
                        <div class="col-md-6 col-sm-6">
                          <div class="form-group form-group-sm">
                            <label class="col-sm-3 control-label">最高学历</label>
                            <div class="col-sm-9">
                              <input readonly="readonly" value="<?=$teacher['degree'] ?>" class="form-control" type="text" id="formGroupInputSmall" >
                            </div>
                          </div>
                        </div>
                        -->
                        <div class="col-md-6 col-sm-6">
                          <div class="form-group form-group-sm">
                            <label class="col-sm-3 control-label"><?=Yii::t('common','teacher_level')?></label>
                            <div class="col-sm-9">
                              <div class="form-group field-courseservice-course_type">
                                <select disabled="disabled" class="form-control" id="teacher_level_id">
                                  <?php foreach($teacherLevels as $k=>$v) {?>
                                    <option kid="<?=$k?>" value="<?=$v['value']?>" <?=$teacherLevelsSelected[$v['value']]?> ><?=$v['name']?></option>
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
                              <input readonly="readonly" value="<?=$teacher['teacher_nick'] ?>" class="form-control" type="text" id="formGroupInputSmall">
                            </div>
                          </div>
                        </div>
                        <div class="col-md-6 col-sm-6">
                          <div class="form-group form-group-sm">
                            <label class="col-sm-3 control-label"><?= Yii::t('frontend', 'highest_degree') ?></label>
                            <div class="col-sm-9">
                              <input readonly="readonly" value="<?=$teacher['degree'] ?>" class="form-control" type="text" id="formGroupInputSmall" >
                            </div>
                          </div>
                        </div>
                        <!--
                        <div class="col-md-6 col-sm-6">
                          <div class="form-group form-group-sm">
                            <label class="col-sm-3 control-label">毕业院校</label>
                            <div class="col-sm-9">
                              <input readonly="readonly" value="<?=$teacher['graduate_school'] ?>" class="form-control" type="text" id="formGroupInputSmall" >
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
                              <?php
                              if ($teacher->gender == 'male'){
                                $sex = Yii::t('common', 'gender_male');
                              }elseif ($teacher->gender == 'female'){
                                $sex = Yii::t('common', 'gender_female');
                              }else{
                                $sex = $teacher->gender;
                              }
                              ?>
                              <input readonly="readonly" value="<?=$sex?>" class="form-control" type="text" id="formGroupInputSmall" >
                            </div>
                          </div>
                        </div>
                        <div class="col-md-6 col-sm-6">
                          <div class="form-group form-group-sm">
                            <label class="col-sm-3 control-label"><?= Yii::t('common', 'teach_year') ?></label>
                            <div class="col-sm-9">
                              <input readonly="readonly" value="<?=$teacher['teach_year'] ?>" class="form-control" type="text" id="formGroupInputSmall">
                            </div>
                          </div>
                        </div>
                      </div>
                      <div class="row">
                        <div class="col-md-6 col-sm-6">
                          <div class="form-group form-group-sm">
                            <label class="col-sm-3 control-label"><?= Yii::t('common', 'birthday') ?></label>
                            <div class="col-sm-9">
                              <input readonly="readonly" value="<?=$teacher['birthday'] ?>" class="form-control" type="text" id="formGroupInputSmall" >
                            </div>
                          </div>
                        </div>
                        <div class="col-md-6 col-sm-6">
                          <div class="form-group form-group-sm">
                            <label class="col-sm-3 control-label"><?= Yii::t('common', 'teach_domain') ?></label>
                            <div class="col-sm-9">
                              <input readonly="readonly" value="<?=$teacher['teach_domain'] ?>" class="form-control" type="text" id="formGroupInputSmall" >
                            </div>
                          </div>
                        </div>
                      </div>
                      <div class="row">
                        <div class="col-md-6 col-sm-6">
                          <div class="form-group form-group-sm">
                            <label class="col-sm-3 control-label"><?= Yii::t('common', 'mobile_no') ?></label>
                            <div class="col-sm-9">
                              <input readonly="readonly" value="<?=$teacher['mobile_no'] ?>" class="form-control" type="text" id="formGroupInputSmall" >
                            </div>
                          </div>
                        </div>
                        <div class="col-md-6 col-sm-6">
                          <div class="form-group form-group-sm">
                            <label class="col-sm-3 control-label"><?= Yii::t('common', 'home_phone_no') ?></label>
                            <div class="col-sm-9">
                              <input readonly="readonly" value="<?=$teacher['home_phone_no'] ?>" class="form-control" type="text" id="formGroupInputSmall" >
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
                              <input class="form-control" type="text" id="formGroupInputSmall" placeholder="李明哲">
                            </div>
                          </div>
                        </div>
                        <div class="col-md-6 col-sm-6">
                          <div class="form-group form-group-sm">
                            <label class="col-sm-3 control-label">语言</label>
                            <div class="col-sm-9">
                              <input class="form-control" type="text" id="formGroupInputSmall" placeholder="李明哲">
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
                              <input readonly="readonly" value="<?=$teacher['teacher_title'] ?>" class="form-control" type="text" id="formGroupInputSmall" >
                            </div>
                          </div>
                        </div>
                       
                      </div>
                      
                      
                       <div class="row">
                        <div class="col-md-12 col-sm-12">
                          <div class="form-group form-group-sm">
                            <label class="col-sm-2 control-label" style="width:12%"><?= Yii::t('frontend', 'teacher_description') ?></label>
                            
                            <div class="col-sm-10" style="width:88%">
                              <textarea readonly="readonly"><?=$teacher['description'] ?></textarea>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                    <div class="infoBlock teacher_EM1  ">
                      <h4><?= Yii::t('frontend', 'business_related') ?>(<?= Yii::t('frontend', 'use_by_internal_teacher') ?>)</h4>
                      <!-- 如果是内部讲师,显示这个 -->
                      <hr/>
                      <div class="row">
                        <div class="col-md-6 col-sm-6">
                          <div class="form-group form-group-sm">
                            <label class="col-sm-3 control-label"><?= Yii::t('frontend', 'business_name') ?></label>
                            <div class="col-sm-9">
                              <input readonly="readonly"  value="<?=$uinfo['company_name'] ?>" class="form-control" type="text" id="formGroupInputSmall" >
                            </div>
                          </div>
                        </div>
                        <div class="col-md-6 col-sm-6">
                          <div class="form-group form-group-sm">
                            <label class="col-sm-3 control-label"><?= Yii::t('frontend', 'belong_to_domain') ?></label>
                            <div class="col-sm-9">
                              <input readonly="readonly" value="<?=$uinfo['domain_name'] ?>" class="form-control" type="text" id="formGroupInputSmall" >
                            </div>
                          </div>
                        </div>
                      </div>
                      <div class="row">
                        <div class="col-md-6 col-sm-6">
                          <div class="form-group form-group-sm">
                            <label class="col-sm-3 control-label"><?= Yii::t('frontend', 'organization_department') ?></label>
                            <div class="col-sm-9">
                              <input readonly="readonly" value="<?=$uinfo['orgnization_name'] ?>" class="form-control" type="text" id="formGroupInputSmall" >
                            </div>
                          </div>
                        </div>
                        <div class="col-md-6 col-sm-6">
                          <div class="form-group form-group-sm">
                            <label class="col-sm-3 control-label"><?= Yii::t('frontend', 'position') ?></label>
                            <div class="col-sm-9">
                              <input readonly="readonly" value="<?=$uinfo['position_name'] ?>" class="form-control" type="text" id="formGroupInputSmall" >
                            </div>
                          </div>
                        </div>
                      </div>
                      <div class="row">
                        <div class="col-md-6 col-sm-6">
                          <div class="form-group form-group-sm">
                            <label class="col-sm-3 control-label"><?= Yii::t('frontend', 'reporting_manager') ?></label>
                            <div class="col-sm-9">
                              <input readonly="readonly"  value="<?=$uinfo['reporting_manager'] ?>" class="form-control" type="text" id="formGroupInputSmall" >
                            </div>
                          </div>
                        </div>
                        <div class="col-md-6 col-sm-6">
                        </div>
                      </div>
                    </div>
                    <!--                     <div class="infoBlock teacher_CW hide">
                      <h4>企业相关(外部讲师用)</h4>
                      <hr/>
                      <div class="row">
                        <div class="col-md-6 col-sm-6">
                          <div class="form-group form-group-sm">
                            <label class="col-sm-3 control-label">公司名称</label>
                            <div class="col-sm-9">
                              <input class="form-control" type="text" id="formGroupInputSmall" placeholder="中国公司">
                            </div>
                          </div>
                        </div>
                        <div class="col-md-6 col-sm-6">
                          <div class="form-group form-group-sm">
                            <label class="col-sm-3 control-label">任职岗位</label>
                            <div class="col-sm-9">
                              <input class="form-control" type="text" id="formGroupInputSmall" placeholder="培训管理域">
                            </div>
                          </div>
                        </div>
                      </div>
                      <div class="row">
                        <div class="col-md-6 col-sm-6">
                          <div class="form-group form-group-sm">
                            <label class="col-sm-3 control-label">公司电话</label>
                            <div class="col-sm-9">
                              <input class="form-control" type="text" id="formGroupInputSmall" placeholder="培训部">
                            </div>
                          </div>
                        </div>
                        <div class="col-md-6 col-sm-6">
                          <div class="form-group form-group-sm">
                            <label class="col-sm-3 control-label">公司邮箱</label>
                            <div class="col-sm-9">
                              <input class="form-control" type="text" id="formGroupInputSmall" placeholder="培训主管">
                            </div>
                          </div>
                        </div>
                      </div>
                      <div class="row">
                        <div class="col-md-6 col-sm-6">
                          <div class="form-group form-group-sm">
                            <label class="col-sm-3 control-label">联系地址</label>
                            <div class="col-sm-9">
                              <input class="form-control" type="text" id="formGroupInputSmall" placeholder="李萌">
                            </div>
                          </div>
                        </div>
                        <div class="col-md-6 col-sm-6">
                        </div>
                      </div>
                    </div>
                    <div class="infoBlock teacher_CW hide">
                      <h4>可供课程(外部讲师用)</h4>
                      <hr/>
                      <div class="row">
                        <div class="col-md-7 col-sm-7">
                          <div class="form-group form-group-sm">
                            <label class="col-sm-12 control-label">[面授]市场策略高管培训</label>
                          </div>
                        </div>
                        <div class="col-md-2 col-sm-2">
                          <div class="form-group form-group-sm">
                            <label class="col-sm-12 control-label">25<?=Yii::t('frontend', 'study_hours')?></label>
                          </div>
                        </div>
                        <div class="col-md-3 col-sm-3">
                          <div class="form-group form-group-sm">
                            <label class="col-sm-12 control-label">价格:100,00</label>
                          </div>
                        </div>
                      </div>
                      <div class="row">
                        <div class="col-md-7 col-sm-7">
                          <div class="form-group form-group-sm">
                            <label class="col-sm-12 control-label">[面授]市场策略高管培训</label>
                          </div>
                        </div>
                        <div class="col-md-2 col-sm-2">
                          <div class="form-group form-group-sm">
                            <label class="col-sm-12 control-label">25<?=Yii::t('frontend', 'study_hours')?></label>
                          </div>
                        </div>
                        <div class="col-md-3 col-sm-3">
                          <div class="form-group form-group-sm">
                            <label class="col-sm-12 control-label">价格:100,00</label>
                          </div>
                        </div>
                      </div>
                      <div class="row">
                        <div class="col-md-7 col-sm-7">
                          <div class="form-group form-group-sm">
                            <label class="col-sm-12 control-label">[面授]服务制胜-连锁销售企业的服务流程</label>
                          </div>
                        </div>
                        <div class="col-md-2 col-sm-2">
                          <div class="form-group form-group-sm">
                            <label class="col-sm-12 control-label">25<?=Yii::t('frontend', 'study_hours')?></label>
                          </div>
                        </div>
                        <div class="col-md-3 col-sm-3">
                          <div class="form-group form-group-sm">
                            <label class="col-sm-12 control-label">价格:100,00</label>
                          </div>
                        </div>
                      </div>
                      <div class="row">
                        <div class="col-md-7 col-sm-7">
                          <div class="form-group form-group-sm">
                            <label class="col-sm-12 control-label">[面授]市场策略高管培训</label>
                          </div>
                        </div>
                        <div class="col-md-2 col-sm-2">
                          <div class="form-group form-group-sm">
                            <label class="col-sm-12 control-label">25<?=Yii::t('frontend', 'study_hours')?></label>
                          </div>
                        </div>
                        <div class="col-md-3 col-sm-3">
                          <div class="form-group form-group-sm">
                            <label class="col-sm-12 control-label">价格:100,00</label>
                          </div>
                        </div>
                      </div>
                    </div> -->
                  </div>
                </div>
            