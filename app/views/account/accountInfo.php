 <div class="container">
     <div class="row">
         <div class="courseInfo">
             <div class="tab-content">
                 <div role="tabpanel" class="tab-pane active" id="teacher_info">
                     <div class=" panel-default scoreList">
                         <div class="panel-body">
                             <div class="infoBlock newStyle">
                                 <h4>个人相关</h4>
                                 <hr/>
                                 <div class="row">
                                     <div class="col-md-6 col-sm-6">
                                         <div class="form-group form-group-sm">
                                             <label class="col-sm-3 control-label">姓名</label>
                                             <div class="col-sm-9">
                                                 <input class="form-control" type="text" id="formGroupInputSmall" value="<?= $userModel -> real_name ?>" readOnly="true">
                                             </div>
                                         </div>
                                     </div>
                                     <div class="col-md-6 col-sm-6">
                                         <div class="form-group form-group-sm">
                                             <label class="col-sm-3 control-label">手机号</label>
                                             <div class="col-sm-9">
                                                 <input class="form-control" type="text" id="formGroupInputSmall" value="<?= $userModel->mobile_no?>" readOnly="true">
                                             </div>
                                         </div>
                                     </div>
                                 </div>
                                 <div class="row">
                                     <div class="col-md-6 col-sm-6">
                                         <div class="form-group form-group-sm">
                                             <label class="col-sm-3 control-label">邮箱</label>
                                             <div class="col-sm-9">
                                                 <input class="form-control" type="text" id="formGroupInputSmall" value="<?= $userModel->email ?>" readOnly="true">
                                             </div>
                                         </div>
                                     </div>
                                     <div class="col-md-6 col-sm-6">
                                         <div class="form-group form-group-sm">
                                             <label class="col-sm-3 control-label">身份证</label>
                                             <div class="col-sm-9">
                                                 <input class="form-control" type="text" id="formGroupInputSmall" value="<?=$userModel->id_number?>" readOnly="true">
                                             </div>
                                         </div>
                                     </div>
                                 </div>
                             </div>
                             <h4>企业相关</h4>
                             <hr/>
                             <div class="infoBlock newStyle">
                                 <div class="row">
                                     <div class="col-md-6 col-sm-6">
                                         <div class="form-group form-group-sm">
                                             <label class="col-sm-3 control-label">公司名称</label>
                                             <div class="col-sm-9">
                                                 <input class="form-control" type="text" id="formGroupInputSmall" value="<?=$companyName?>" readOnly="true">
                                             </div>
                                         </div>
                                     </div>
                                     <div class="col-md-6 col-sm-6">
                                         <div class="form-group form-group-sm">
                                             <label class="col-sm-3 control-label">所属域名</label>
                                             <div class="col-sm-9">
                                                 <input class="form-control" type="text" id="formGroupInputSmall" value="<?=$domainName ?>" readOnly="true">
                                             </div>
                                         </div>
                                     </div>
                                 </div>
                                 <div class="row">
                                     <div class="col-md-6 col-sm-6">
                                         <div class="form-group form-group-sm">
                                             <label class="col-sm-3 control-label">组织部门</label>
                                             <div class="col-sm-9">
                                                 <input class="form-control" type="text" id="formGroupInputSmall" value="<?=$orgnizationName ?>" readOnly="true">
                                             </div>
                                         </div>
                                     </div>
                                     <div class="col-md-6 col-sm-6">
                                         <div class="form-group form-group-sm">
                                             <label class="col-sm-3 control-label">岗位</label>
                                             <div class="col-sm-9">
                                                 <input class="form-control" type="text" id="formGroupInputSmall" value="<?//=$positionModel->position_name ?>" readOnly="true">
                                             </div>
                                         </div>
                                     </div>
                                 </div>
                                 <div class="row">
                                     <div class="col-md-6 col-sm-6">
                                         <div class="form-group form-group-sm">
                                             <label class="col-sm-3 control-label">直线经理</label>
                                             <div class="col-sm-9">
                                                 <input class="form-control" type="text" id="formGroupInputSmall" value="" readOnly="true">
                                             </div>
                                         </div>
                                     </div>
                                     <div class="col-md-6 col-sm-6">
                                     </div>
                                 </div>
                             </div>
                         </div>
                     </div>
                 </div>
             </div>
         </div>
     </div>
 </div>
