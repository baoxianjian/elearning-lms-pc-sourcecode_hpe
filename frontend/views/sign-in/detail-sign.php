  <?php
/**
 * User: baoxianjian
 * Date: 2016/4/26
 * Time: 14:52
 */
use yii\helpers\Url;
use common\helpers\TTimeHelper;
use common\models\learning\LnCourse;



?>
<style>
    .longPanel td.leave1:hover a {
        display: block;
    }
</style>
  <div role="tabpanel" class="tab-pane" id="courseAward3">
      <div class=" panel-default scoreList" >
                    <div class="panel-body" style="overflow-x: hidden;padding-bottom:0 !important">
              <div class="longPanel">


                  <div class="signPanel">
                          <div class="col-sm-8 btn-xs" style="margin-top:20px;">
                              <!--<p>今天是<?=$curMonth?>月<?=$curDay?>日，应到<?=$studentCount?>人还有2人未到。第一期课程 5月9日-5月12日</p>-->
                          </div>


                          <div id="student_sign_bar">
                          <div class="col-sm-4" style="margin-top:20px;">
                            <a href="<?=Yii::$app->urlManager->createUrl(['/sign-in/detail-sign-down','id'=>$id])?>" class="btn btn-sm pull-left"><?=Yii::t('common', 'export_all_data')?></a>
                            <?php if($courseModel->open_status!=LnCourse::COURSE_END){?>
                            <a href="###" class="btn btn-sm pull-left" onclick="showSignConfigDiv()"><?=Yii::t('frontend', 'sign_in_configuration')?></a>
                            <?php
                             if($signDateIsToday[TTimeHelper::getDateInt()]) {?>
                            <div class="btn-sm pull-left barCodeTodayLink"><?=Yii::t('frontend', 'sign_in_by_qr_code')?>(<?=Yii::t('common', 'today')?>)
                              <div class="barCodeToday">
                                <span><img id="img_sign_in_today_qr_code" src="<?=Yii::$app->urlManager->createUrl(['/sign-in/qr-scan-code','date'=>TTimeHelper::getDateInt(),'cid'=>$id])?>" height="128" width="128" /></span>
                                  <div>
                                  <span class="miniBtn"><a href="javascript:void(0)" onclick="showSignInBigQrCodeImg('img_sign_in_today_qr_code');"><?=Yii::t('frontend', 'enlarge')?></a></span>
                                  <span class="miniBtn"><a href="javascript:void(0)" onclick="printSignInQrCodeImg('img_sign_in_today_qr_code')"><?=Yii::t('common', 'print')?></a></span>
                                  <span class="miniBtn"><a href="<?=Yii::$app->urlManager->createUrl(['/sign-in/qr-scan-code','date'=>TTimeHelper::getDateInt(),'cid'=>$id,'down'=>1])?>"><?=Yii::t('common', 'download')?></a></span>
                                </div>
                              </div>
                            </div>
                            <?php }}?>
                          </div>
                          <div class="col-sm-12">
                          <div class="form-group" style="margin-bottom:0;">
                              <select id="sel_sign_dates" class="form-control" style="width:20%;">
                              <?php foreach($signDates as $v){?>
                                  <option <?=$signDateSelected[$v['sign_date']]?> value="<?=$v['sign_date']?>"><?=TTimeHelper::FormatTime($v['sign_date'])?><?=$signDateIsToday[$v['sign_date']]?></option>
                              <?php }?>
                              </select>
                          </div>
                          <div class="form-group" style="margin-bottom:0;">
                              <select id="sel_sign_titles" class="form-control" style="width:10%;">
                                  <option value="0"><?=Yii::t('common', 'all_data')?></option>
                                  <?php foreach($signTitles as $v){?>
                                      <option value="<?=$v['kid']?>"><?=$v['title']?></option>
                                  <?php }?>
                              </select>
                          </div>
                          <div class="form-group" style="margin-bottom:0;">
                              <select id="sign_status" class="form-control" style="width:10%;">
                                  <option value="0"><?=Yii::t('common', 'all_data')?></option>
                                  <option value="1"><?=Yii::t('frontend', 'arrived')?></option>
                                  <option value="2"><?=Yii::t('frontend', 'absent')?></option>
                              </select>
                          </div>
                          <div class="form-group" style="margin-bottom:0;">
                              <select id="sign_order" class="form-control" style="width:15%;">
                                  <option value="1"><?= Yii::t('frontend', 'rank_by_position') ?></option>
                                  <option value="2"><?= Yii::t('frontend', 'rank_by_organization') ?></option>
                              </select>
                          </div>
                          <div class="input-group">
                              <input id="input_keyword" type="text" class="form-control search_people" style="height: 30px;" placeholder="<?= Yii::t('common', 'real_name') ?>/<?= Yii::t('frontend', 'position') ?>/<?= Yii::t('frontend', 'department') ?>">
                              <span class="input-group-btn">
                                <button onclick="loadStudentSignList();" class="btn btn-success btn-sm" type="button"><?= Yii::t('frontend', 'top_search_text') ?></button>
                                <button onclick="resetSignSearchForm()" class="btn btn-success btn-sm" type="button" style="margin-left: 10px;"><?= Yii::t('frontend', 'reset') ?></button>
                              </span>
                              
                          </div>
                      </div>
                          </div>

                      <div id="student_sign_list" class="col-sm-12">
                      </div>
                  </div>


                  <div class="signConfigPanel">
                      <a class="backSignList" href="javascript:void(0);"><?=Yii::t('common', 'back_to_{value}',['value'=>Yii::t('common','list')])?></a>
                      <div class="row" id="sign_setting_setps" style="display:none">
                          <div id="sign_setting_setp_1" class="col-sm-12">
                              <p><strong style="font-size: 18px;">1</strong><?=Yii::t('frontend', 'please_enter_sign_in_range_of_ervery_day')?></p>
                              <a href="###" class="btn btn-xs btn-default titleOption addSignTime"><?=Yii::t('common', 'add_{value}',['value'=>Yii::t('frontend','sign_in_time')])?></a>
                              <hr/>
                              <div name="sign_time_list" class="row timeRow">
                                  <div class="col-sm-2" style="text-align: center;">
                                      <input name="sign_time_title" onblur="checkSignTimeTitle(this)" type="text" style="border: 1px solid #ccc;" placeholder="<?=Yii::t('common', 'name')?>" />
                                  </div>
                                  <div class="col-sm-3">
                                      <input name="sign_time_start" onblur="checkSignTimeStart(this)" readonly="readonly" type="text" style="border: 1px solid #ccc;" data-type="rili" data-full="0" data-hms="8:00" placeholder="08:00" value="08:00" />
                                  </div>
                                  <div class="col-sm-1" style="text-align: center;"><?=Yii::t('common', 'to')?></div>
                                  <div class="col-sm-3">
                                      <input name="sign_time_end" onblur="checkSignTimeEnd(this)" readonly="readonly" type="text" style="border: 1px solid #ccc;" data-type="rili" data-full="0" data-hms="10:00" placeholder="10:00" value="10:00" />
                                  </div>
                                  <div class="col-sm-2">
                                      <a href="###" class="btn btn-xs delRowBtn"><?=Yii::t('common', 'delete_button')?></a>
                                  </div>
                                  <div class="col-sm-1" style="text-align: center;"></div>
                              </div>
                              <div name="sign_time_list" class="row timeRow">
                                  <div class="col-sm-2" style="text-align: center;">
                                      <input name="sign_time_title" onblur="checkSignTimeTitle(this)" type="text" style="border: 1px solid #ccc;" placeholder="<?=Yii::t('common', 'name')?>" />
                                  </div>
                                  <div class="col-sm-3">
                                      <input name="sign_time_start" onblur="checkSignTimeStart(this)" readonly="readonly" type="text" style="border: 1px solid #ccc;" data-type="rili" data-full="0" data-hms="14:00" placeholder="14:00" value="14:00" />
                                  </div>
                                  <div class="col-sm-1" style="text-align: center;"><?=Yii::t('common', 'to')?></div>
                                  <div class="col-sm-3">
                                      <input name="sign_time_end" onblur="checkSignTimeEnd(this)" readonly="readonly" type="text" style="border: 1px solid #ccc;" data-type="rili" data-full="0" data-hms="16:00" placeholder="16:00" value="16:00" />
                                  </div>
                                  <div class="col-sm-2">
                                      <a href="###" class="btn btn-xs delRowBtn"><?=Yii::t('common', 'delete_button')?></a>
                                  </div>
                                  <div class="col-sm-1" style="text-align: center;"></div>
                              </div>
                          </div>
                          <div id="sign_setting_setp_2" class="col-sm-12" style="margin-top: 20px;">
                              <p><strong style="font-size: 18px;">2</strong><?=Yii::t('frontend', 'please_enter_sign_in_date_of_current_course')?></p>
                              <hr/>
                              <div name="sign_date_row" class="row timeRow">
                                  <div class="col-sm-2" style="text-align: center;"><?=Yii::t('frontend', 'sign_in_date')?></div>
                                  <div class="col-sm-3">
                                      <input id="sign_date_start" name="sign_date_start" onblur="checkSignDateStart(this,false,true)"  type="text" style="border: 1px solid #ccc;" placeholder="2016-01-01" data-force-right="1" data-type="rili" value="<?=TTimeHelper::FormatTime($courseModel->open_start_time)?>">
                                  </div>
                                  <div class="col-sm-1" style="text-align: center;"><?=Yii::t('common', 'to')?></div>
                                  <div class="col-sm-3">
                                      <input id="sign_date_end" name="sign_date_end" onblur="checkSignDateEnd(this,false,true)" readonly="readonly" style="border: 1px solid #ccc;" type="text" placeholder="2016-01-05" data-force-right="1" data-type="rili" value="<?=TTimeHelper::FormatTime($courseModel->open_end_time)?>">
                                  </div>
                                  <div class="col-sm-1" style="text-align: center;"></div>
                              </div>
                          </div>
                      
                          <div id="sign_setting_setp_3" class="col-sm-12" style="margin-top:20px;">
                            <p><strong style="font-size: 18px;">3</strong><?=Yii::t('frontend', 'generate_all_sign_in_data')?></p>
                            <hr>
                            <div class="centerBtnArea">
                                <a href="javascript:void(0);" class="btn btn-sm btn-default" onclick="generateSignList()"><?=Yii::t('frontend', 'generate_all_sign_in_data')?></a>
                                <a href="javascript:void(0);" style="display:none;" id="btn_generate_sign_data_1" class="btn btn-sm btn-success" onclick="generateSignData()"><?=Yii::t('frontend', 'save_generated_result')?></a>
                            </div>
                          </div>

                          <div class="col-sm-12">
                             <p><?=Yii::t('frontend', 'all_result_of_sign_in_data')?>:</p>
                             <a href="###" class="btn btn-xs btn-default titleOption" data-toggle="modal" data-target="#editDate" onclick="showEditSignDateBox('add')"><?=Yii::t('common', 'add_{value}',['value'=>Yii::t('frontend','sign_in_date')])?></a>
                             <div id="sign_settings">
                             </div>
                             <div class="centerBtnArea">
                                <a href="javascript:void(0);" style="display:none;" id="btn_generate_sign_data_2" class="btn btn-sm btn-success" onclick="generateSignData()"><?=Yii::t('frontend', 'save_generated_result')?></a>
                            </div>
                          </div>
                      </div>


                  </div>
              </div>
      </div>
  </div>
<script type="text/javascript">
    <?php if(!$signDates){ ?>
    $("#student_sign_bar").hide();
    <?php }?>

</script>

  <script>
    $("#sel_sign_dates").change(function()
    {
       // var url=
//alert(this.value);

        $.get('<?=Url::toRoute(['/sign-in/get-sign-titles','cid'=>$id])?>', {d:this.value}, function(data){
          //alert(data.signTitles.length);
            var option_html="";
            option_html+='<option value="0"><?=Yii::t('common', 'all_data')?></option>';
            for(var i=0;i<data.signTitles.length;i++)
            {
                option_html+='<option value="'+data.signTitles[i].kid+'">'+data.signTitles[i].title+'</option>';
            }
            $("#sel_sign_titles").html(option_html);

        }, 'json');
        }
    );
  </script>

  <script type="text/javascript">
      var sign_settings_is_temp=true;
      var sign_settings_delete_ids=[];

      //输入日期是否小于今天
      function dateIsLTToday(date)
      {
          return false;
          //$("#sign_date_start").val();
          if(isNaN(date))
          {
              date=Date.parse(date)/ 1000;
          }

          if(date < parseInt('<?=TTimeHelper::getDateInt()?>'))
          {
             // app.showMsg('签到开始日期必须大于今天', 3000);
              return true;
          }
          return false;
      }



      function generateSignList()
      {
          if(!checkInputAllForGenerate()){return false;}

          if(dateIsLTToday($("#sign_date_start").val()))
          {
             // app.showMsg('签到开始日期必须大于今天', 3000);
              return false;
          }


          var list = $("div[name = sign_time_list]")
          var list_size = list.size();

          var sign_time_data = [];
          //sign_time_list.push({"title":"上午","start":"8:30","end":"11:30"});

          for (var i = 0; i < list_size; i++) {
              var inputs = list.get(i).getElementsByTagName("input");
              var inputs_size = inputs.length;
              var time_obj = {};

              for (var j = 0; j < inputs_size; j++) {
                  var input_name = inputs[j].getAttribute("name");
                  switch (input_name) {
                      case "sign_time_title":
                      {
                          //time_obj.title = inputs[j].getAttribute("value");
                          time_obj.title = inputs[j].value;
                           //alert(inputs[j].getAttribute("value"));
                          //alert(inputs[j].value);
                          break;
                      }
                      case "sign_time_start":
                      {
                          time_obj.start = inputs[j].value;
                          break;
                      }
                      case "sign_time_end":
                      {
                          time_obj.end = inputs[j].value;
                          break;
                      }
                      default:
                      {
                          break;
                      }
                  }
              }
              sign_time_data.push(time_obj);
          }


          var sign_date_start = new Date($("#sign_date_start").val()); //开始时间
          var sign_date_end = new Date($("#sign_date_end").val()); //结束时间
          var date_temp = sign_date_start;

          diff_days=(sign_date_end - sign_date_start) / 1000 / 60 / 60 / 24;
          diff_days=parseInt(diff_days)+1;

         // alert("间隔天数为:" + diff_days + "天");

          var setting_html="";
          var sign_setting_data=[];



          for(var i=0;i<diff_days;i++)
          {
              var sign_obj={};

               var timestamp=Date.parse(date_temp);
               timestamp = timestamp / 1000;

              setting_html+=
                  '<div idx="'+i+'" name="sign_setting_list" class="row timeRow timeRowResult">'+
                    '<div id="sign_setting_date_'+i+'" class="col-sm-2">'+date_temp.getFullYear()+'-'+(date_temp.getMonth()+1)+'-'+date_temp.getDate()+'</div>'+
                    '<div id="sign_setting_time_'+i+'" class="col-sm-7">';

              for(var j=0;j<sign_time_data.length;j++) {
                  setting_html +=
                      '<span idx="'+i+j+'" name="sign_time_items_'+i+'">'+
                        '<span id="sign_time_item_title_'+i+j+'">'+sign_time_data[j].title+'</span>'+
                        '<span id="sign_time_item_start_'+i+j+'">'+sign_time_data[j].start+'</span>'+
                        '~<span id="sign_time_item_end_'+i+j+'">'+sign_time_data[j].end+'</span>'+
                      '</span>&nbsp;&nbsp;';
              }
              setting_html+='</div>'+
                    '<div class="col-sm-3" style="text-align: center;">'+
                      '<a href="###" class="btn btn-xs" data-toggle="modal" data-target="#editDate" onclick="showEditSignDateBox(\'edit\',\''+i+'\')" ><?=Yii::t('common', 'edit_button')?></a>'+
                      '<a href="###" class="btn btn-xs delRowBtn" onclick="removeSignSettingFromList(this)" ><?=Yii::t('common', 'delete_button')?></a>'+
                    '</div>'+
                  '</div>';
              date_temp.setDate(date_temp.getDate()+1);
          }
              

          $("#sign_settings").html(setting_html);
          sign_settings_is_temp=true;

          $("#btn_generate_sign_data_1").show();
          $("#btn_generate_sign_data_2").show();
      }

      function generateSignData() {

          if(dateIsLTToday($("#sign_date_start").val()))
          {
              //app.showMsg('签到开始日期必须大于今天', 3000);
              return false;
          }

          var list = $("div[name = sign_setting_list]");
          var list_size = list.size();

          var sign_settings_data = [];
          //sign_time_list.push({"title":"上午","start":"8:30","end":"11:30"});

          for (var i = 0; i < list_size; i++) {
              var idx=list.get(i).getAttribute("idx");

              var sub_sign_settings_data=getSettingObjsByIdx(idx);

              for(var j=0;j<sub_sign_settings_data.length;j++)
              {
                  sign_settings_data.push(sub_sign_settings_data[j]);
              }
          }

          // alert(sign_settings_data[0].title);

          $.ajax({
              type: "POST",
              url: "<?=Yii::$app->urlManager->createUrl(['/sign-in/generate-sign-settings', 'cid' => $id,'t'=>1])?>",
              data: {ssd:JSON.stringify(sign_settings_data)},
              success: function(data){
                  if(data.result=='success')
                  {
                      app.showMsg('<?=Yii::t('common', 'save_{value}',['value'=>Yii::t('common','success')])?>');
                  }
                  else
                  {
                      if(data.successCount>0)
                      {
                          app.showMsg('<?=Yii::t('frontend', 'save_failed_partly')?>(<?=Yii::t('frontend', 'sign_in_settings_signed_in_can_not_be_overridden')?>)');
                      }
                      else
                      {
                          app.showMsg('<?=Yii::t('common', 'save_{value}',['value'=>Yii::t('common','failed')])?>');
                      }
                  }
                  $("#btn_generate_sign_data_1").hide();
                  $("#btn_generate_sign_data_2").hide();
                  loadSignSettingList();
              }
          });
      }

      function loadSignSettingList()
      {
          $.get('<?=Yii::$app->urlManager->createUrl(['/sign-in/get-sign-settings', 'cid' => $id])?>',function(data){
            if(data)
            {
                if(data.length>50)
                {
                    $("#sign_settings").html(data);
                    sign_settings_is_temp=false;
                    $("#sign_setting_setp_1").remove();
                    $("#sign_setting_setp_2").remove();
                    $("#sign_setting_setp_3").remove();
                    $("#sign_setting_setps").show();
                }
                else
                {
                    $("#sign_setting_setps").show();
                }
            }
          });
      }

      function loadStudentSignList(showAll)
      {
          if(typeof showAll == 'undefined'){showAll=0;}
          
          var sign_date=$("#sel_sign_dates").val();
          var sign_setting_ids=$("#sel_sign_titles").val();
          var sign_status=$("#sign_status").val();
          var sign_order=$("#sign_order").val();
          var keyword=$("#input_keyword").val();

          $.ajax({
              type: "GET",
              url: "<?=Yii::$app->urlManager->createUrl(['/sign-in/get-student-signs', 'cid' => $id])?>",
              data: {sd:sign_date,ssids:sign_setting_ids,ss:sign_status,kw:keyword,so:sign_order,sa:showAll},
              success: function(data){
                  if(data)
                  {
                      $("#student_sign_list").html(data);
                  }
              }
          });
      }

      function resetSignSearchForm()
      {
          $('#sel_sign_dates').val('<?=TTimeHelper::getDateInt()?>');
          $('#sel_sign_titles').val('0');
          $('#sign_status').val('0');
          $('#sign_order').val('1');
          $('#input_keyword').val('');    
      }
      
      
      
      function showEditSignDateBox(type,idx)
      {
          var list=[];
          if(type=='edit')
          {
              list=getSettingObjsByIdx(idx);
          }
          else
          {
              idx=Date.parse(new Date())/ 1000 %1000+(parseInt(Math.random()*Math.random()*10000));

              var setting_obj={};
              //setting_obj.kid="";
              var today=new Date();

              setting_obj.sign_date=today.getFullYear()+"-"+(today.getMonth()+1)+"-"+today.getDate();
              setting_obj.title="";
              setting_obj.start_at_str="08:00";
              setting_obj.end_at_str="10:00";
              setting_obj.use_count=0;

              list.push(setting_obj);
          }

          var html_temp="";
          var sign_date_disabled=false;
          for(var i=0;i<list.length;i++)
          {
              var disabled_str="";
              if(list[i].use_count>0){disabled_str=' disabled="disabled"';sign_date_disabled='disabled'}
              html_temp+=
                  '<div name="edit_sign_time_list" class="row timeRow">'+
                  '<div class="col-sm-12">'+
                  '<div class="form-group form-group-sm">'+
                  '<div class="col-sm-3 ">'+
                  '<input name="edit_sign_time_use_count" type="hidden" value="'+list[i].use_count+'">'+
                  '<input name="edit_sign_time_kid" type="hidden" value="'+list[i].kid+'">'+
                  '<input'+disabled_str+' name="edit_sign_time_title" onblur="checkEditSignTimeTitle(this)" type="text" placeholder="<?=Yii::t('common', 'name')?>" class="form-control" value="'+list[i].title+'">'+
                  '</div>'+
                  '<div class="col-sm-9">'+
                  '<input'+disabled_str+' name="edit_sign_time_start" onblur="checkEditSignTimeStart(this)" readonly="readonly" type="text" data-type="rili" data-full="0" data-hms="'+list[i].start_at_str+'" placeholder="8:00" class="form-control pull-left" style="width: 45%" value="'+list[i].start_at_str+'">'+
                  '<input'+disabled_str+' name="edit_sign_time_end" onblur="checkEditSignTimeEnd(this)" readonly="readonly" type="text" data-type="rili" data-full="0" data-hms="'+list[i].end_at_str+'" placeholder="10:00" class="form-control pull-left" style="width: 45%" value="'+list[i].end_at_str+'">'+
                  '<a href="###" class="btn btn-xs pull-right delTimeBtn" onclick="removeSignSettingFromDateBox(this,\''+list[i].kid+'\',\''+list[i].use_count+'\')" style=" position: absolute; top: 5px;"><?=Yii::t('common', 'delete_button')?></a>'+
                  '</div>'+
                  '</div>'+
                  '</div>'+
                  '</div>';
          }
          app.alert('#editDate');
          

          $("#edit_sign_date").attr('disabled',sign_date_disabled);
          $("#edit_sign_date").val(list[0].sign_date);
          $("#edit_sign_type").val(type);
          $("#edit_sign_idx").val(idx);
          $("#edit_sign_time").html(html_temp);
          app.genCalendar();
      }

      function closeEditSignDateBox()
      {
          if(!checkInputAllForEdit()){return false;}

          var type=$("#edit_sign_type").val();
          var idx=$("#edit_sign_idx").val();
          var sign_date=$("#edit_sign_date").val();

          /*
          if(dateIsLTToday(sign_date))
          {
              app.showMsg('签到开始日期必须大于今天', 3000);
              return false;
          }
          */

          var list = $("div[name = edit_sign_time_list]");
          var list_size = list.size();

          var sign_time_data = [];
          for (var i = 0; i < list_size; i++) {
              var inputs = list.get(i).getElementsByTagName("input");
              var inputs_size = inputs.length;
              var time_obj = {};

              for (var j = 0; j < inputs_size; j++) {
                  var input_name = inputs[j].getAttribute("name");
                  switch (input_name)
                  {
                      case "edit_sign_time_use_count":
                      {
                          time_obj.use_count = inputs[j].value;
                          break;
                      }
                      case "edit_sign_time_kid":
                      {
                          time_obj.kid = inputs[j].value;
                          break;
                      }
                      case "edit_sign_time_title":
                      {
                          time_obj.title = inputs[j].value;
                          break;
                      }
                      case "edit_sign_time_start":
                      {
                          time_obj.start_at_str = inputs[j].value;
                          break;
                      }
                      case "edit_sign_time_end":
                      {
                          time_obj.end_at_str = inputs[j].value;
                          break;
                      }
                      default:
                      {
                          break;
                      }
                  }
                  time_obj.sign_date=sign_date;
              }
              sign_time_data.push(time_obj);
          }


          var setting_html="";
          var idx2="";
          var setting_time_html="";



          for (var j = 0; j < sign_time_data.length; j++)
          {
              idx2=idx +""+ j;
              setting_time_html +=
                  '<span idx="' +idx2 + '" name="sign_time_items_' +idx + '">' +
                  '<span id="sign_time_item_title_' +idx2 + '">' + sign_time_data[j].title + '</span>' +
                  '<span id="sign_time_item_start_' +idx2 + '">' + sign_time_data[j].start_at_str + '</span>' +
                  '~<span id="sign_time_item_end_' +idx2 + '">' + sign_time_data[j].end_at_str + '</span>' +
                  '</span>&nbsp;&nbsp;';
          }

          if(type=='edit')
          {
              $("#sign_setting_date_"+idx).text(sign_date);
              $("#sign_setting_time_"+idx).html(setting_time_html);
          }
          else
          {
              setting_html+=
                  '<div idx="'+idx+'" name="sign_setting_list" class="row timeRow timeRowResult">'+
                  '<div id="sign_setting_date_'+idx+'" class="col-sm-2">'+sign_date+'</div>'+
                  '<div id="sign_setting_time_'+idx+'" class="col-sm-7">';

              setting_html+=setting_time_html;

              setting_html+='</div>'+
                  '<div class="col-sm-3" style="text-align: center;">'+
                  '<a href="###" class="btn btn-xs" data-toggle="modal" data-target="#editDate" onclick="showEditSignDateBox(\'edit\',\''+idx+'\')" ><?=Yii::t('common', 'edit_button')?></a>'+
                  '<a href="###" class="btn btn-xs delRowBtn" onclick="removeSignSettingFromList(this)"><?=Yii::t('common', 'delete_button')?></a>'+
                  '</div>'+
                  '</div>';

              $("#sign_settings").append(setting_html);
          }

          app.hideAlert("#editDate");

          if(!sign_settings_is_temp)
          {
              $.ajax({
                  type: "POST",
                  url: "<?=Yii::$app->urlManager->createUrl(['/sign-in/save-sign-settings', 'cid' => $id])?>",
                  data: {ssd:JSON.stringify(sign_time_data),dids:JSON.stringify(sign_settings_delete_ids)},
                  success: function(data){
                      //shift()
                      if(data.result=='success')
                      {
                          app.showMsg('<?=Yii::t('common', 'save_{value}',['value'=>Yii::t('common','success')])?>');
                      }
                      else
                      {
                          app.showMsg('<?=Yii::t('common', 'save_{value}',['value'=>Yii::t('common','success')])?>');
                      }
                      sign_settings_delete_ids=[];
                      loadSignSettingList();
                      //$("#edit_teacher").modal('hide');
                      // app.hideAlert("#edit_teacher");
                  }
              });
          }

      }

      function getSettingObjsByIdx(idx)
      {
          var sub_sign_settings_data=[];
          var sign_date=$("#sign_setting_date_"+idx).text();

          // var idx2 =$("div[name = sign_setting_list]");
          var time_list = $("span[name = sign_time_items_"+idx+"]");
          var time_list_size = time_list.size();
          for(var j=0;j<time_list_size;j++)
          {
              var idx2=time_list.get(j).getAttribute("idx");
              var setting_obj={};
              //setting_obj.course_id='';
              setting_obj.kid=time_list.get(j).getAttribute("kid");
              setting_obj.sign_date=sign_date;
              setting_obj.title=$("#sign_time_item_title_"+idx2).text();
              setting_obj.start_at_str=$("#sign_time_item_start_"+idx2).text();
              // setting_obj.start_at
              setting_obj.end_at_str=$("#sign_time_item_end_"+idx2).text();
              setting_obj.use_count=time_list.get(j).getAttribute("use-count");
              //setting_obj.end_at
              //setting_obj.is_all_day
              sub_sign_settings_data.push(setting_obj);
          }
          return sub_sign_settings_data;
      }

      function studentSignIn(cid,ssid,uid)
      {
          $.ajax({
              type: "POST",
              url: "<?=Yii::$app->urlManager->createUrl(['/sign-in/student-sign-in'])?>",
              dataType: 'json',
              data: {cid:cid,ssid:ssid,uid:uid},
              success: function(data){
                  if(data.result=='success')
                  {
                      $("#sign_in_link_"+uid+ssid).css("display","none");
                      var revoke_obj=$("#sign_in_revoke_link_"+uid+ssid);
                      revoke_obj.css("display","inline");
                      revoke_obj.attr('sign_in_id',data.kid);

                      $("#leave_revoke_link_"+uid+ssid).css("display","none");
                  }
                  else
                  {
                      app.showMsg(data.msg);
                  }
                  //loadSignSettingList();
                  //$("#edit_teacher").modal('hide');
                  // app.hideAlert("#edit_teacher");
              }
          });
      }

      function studentSignInRevoke(ssid,uid,type)
      {
          var revoke_obj=$("#sign_in_revoke_link_"+uid+ssid);
          if(type==2)
          {
              revoke_obj=$("#leave_revoke_link_"+uid+ssid);
          }

          sid=revoke_obj.attr("sign_in_id");
          $.ajax({
              type: "POST",
              url: "<?=Yii::$app->urlManager->createUrl(['/sign-in/student-sign-in-revoke'])?>",
              dataType: 'json',
              data: {id:sid},
              success: function(data){

                  if(data.result=='success')
                  {
                      $("#sign_in_link_"+uid+ssid).css("display","inline");
                      revoke_obj.css("display","none");
                      $("#leave_revoke_link_"+uid+ssid).css("display","none");
                      //                     $('.refreshBtn').bind('click', resetBtn);
                  }
                  
                  //loadSignSettingList();
                  //$("#edit_teacher").modal('hide');
                  // app.hideAlert("#edit_teacher");
              }
          });
      }

      function studentLeave(cid,ssid,uid)
      {
          $.ajax({
              type: "POST",
              url: "<?=Yii::$app->urlManager->createUrl(['/sign-in/student-leave'])?>",
              dataType: 'json',
              data: {cid:cid,ssid:ssid,uid:uid},
              success: function(data){
                  if(data.result=='success')
                  {
                      $("#sign_in_link_"+uid+ssid).css("display","none");
                      var revoke_obj=$("#leave_revoke_link_"+uid+ssid);

                      revoke_obj.css("display","inline");
                      revoke_obj.attr('sign_in_id',data.kid);

                      $("#sign_in_revoke_link_"+uid+ssid).css("display","none");
                  }
                  else
                  {
                      app.showMsg(data.msg);
                  }
              }
          });
      }


      function batchSignIn(ssid) {
          var count = 0;
          $("input[name='sign_student_chk'][type='checkbox']:checked").each(function () {
              var chk = $(this);
              //sendUserArray.push(chk.val());
              var uid=chk.val();

              studentSignIn('<?=$id?>',ssid,uid);
              count++;
          });

          if (count === 0) {
              app.showMsg('<?=Yii::t('frontend', 'please_choose_students_first_to_sign_in')?>');
              return false;
          }
      }


      function deleteSignInSettings(d)
      {
          if(dateIsLTToday(d))
          {
              //app.showMsg('删除失败,不能删除今天之前的签到配置');
              return false;
          }

          $.ajax({
              type: "POST",
              url: "<?=Yii::$app->urlManager->createUrl(['/sign-in/delete-sign-settings', 'cid' => $id])?>",
              data: {d:d},
              success: function(data){
                  if(data.result=='success')
                  {
                      app.showMsg('<?= Yii::t('frontend', 'delete_sucess') ?>');
                  }
                  else
                  {
                      if(data.successCount>0)
                      {
                          app.showMsg('<?=Yii::t('frontend', 'delete_failed_partly')?>(<?=Yii::t('frontend', 'sign_in_settings_signed_in_can_not_be_deleted')?>)');
                      }
                      else
                      {
                          app.showMsg('<?=Yii::t('frontend', 'delete_failed')?>(<?=Yii::t('frontend', 'sign_in_settings_signed_in_can_not_be_deleted')?>)');
                      }
                  }
                  loadSignSettingList();
              }
          });
      }

      function removeSignSettingFromDateBox(obj,id,uc)
      {
          if(id || id!='undefined')
          {
              if(dateIsLTToday($("#edit_sign_date").val()))
              {
                  //app.showMsg('删除失败,不能删除今天之前的签到配置', 3000);
                  return false;
              }
              if(uc>0)
              {
                  app.showMsg('<?=Yii::t('frontend', 'delete_failed')?>(<?=Yii::t('frontend', 'sign_in_settings_signed_in_can_not_be_deleted')?>)');
                  return false;
              }
              sign_settings_delete_ids.push(id);
          }
          $(obj).parents('.timeRow').remove();
      }

      function addSignTimeToDateBox()
      {
          var addTime = '<div name="edit_sign_time_list" class="row timeRow"><div class="col-sm-12"><div class="form-group form-group-sm"><div class="col-sm-3 "><input name="edit_sign_time_title" type="text" placeholder="<?=Yii::t('common', 'name')?>" class="form-control" value=""></div><div class="col-sm-9"><input name="edit_sign_time_start" onblur="checkEditSignTimeStart(this)" readonly="readonly" type="text" data-type="rili" data-full="0" data-hms="8:00" placeholder="8:00" class="form-control pull-left" style="width: 45%" value=""><input name="edit_sign_time_end" onblur="checkEditSignTimeEnd(this)" readonly="readonly" type="text" data-type="rili" data-full="0" data-hms="10:00" placeholder="10:00" class="form-control pull-left" style="width: 45%" value=""><a href="###" class="btn btn-xs pull-right delTimeBtn" onclick="removeSignSettingFromDateBox(this)" style=" position: absolute; top: 5px;"><?=Yii::t('common', 'delete_button')?></a></div></div></div></div>';
          // $(this).parents('.row').before(addTime);

          $("#edit_sign_time").append(addTime);

          $('.delRowBtn').bind('click', function () {
              $(this).parents('.timeRow').remove();
          });
      }
      
      function removeSignSettingFromList(obj)
      {
        $(obj).parents('.timeRow').remove();
      }
      
      
      loadStudentSignList();
      app.genCalendar();
  </script>

 <!--签到验证-->
  <script type="text/javascript">
      var chk_sign_name='<?=Yii::t('frontend', 'sign_in_name')?>';
      var chk_start_time='<?=Yii::t('common', 'start_time')?>';
      var chk_end_time='<?=Yii::t('common', 'end_time')?>';
      var chk_start_date='<?=Yii::t('common', 'start_date')?>';
      var chk_end_date='<?=Yii::t('common', 'end_date')?>';

      function checkInputAllForGenerate()
      {
          var sign_time_title_result = true;
          var sign_time_start_result = true;
          var sign_time_end_result = true;

          $("input[name = sign_time_title]").each(function () {
              if (!checkSignTimeTitle(this,true)) {
                  sign_time_title_result = false;
                  return false;
              }
          });
          if(sign_time_title_result) {
              $("input[name = sign_time_start]").each(function () {
                  if (!checkSignTimeStart(this,true)) {
                      sign_time_start_result = false;
                      return false;
                  }
              });
          }
          if(sign_time_title_result && sign_time_start_result) {
              $("input[name = sign_time_end]").each(function () {
                  if (!checkSignTimeEnd(this,true)) {
                      sign_time_end_result = false;
                      return false;
                  }
              });
          }

          return sign_time_title_result && sign_time_start_result && sign_time_end_result && checkSignDateStart($o("sign_date_start"),true,true) && checkSignDateEnd($o("sign_date_end"),true,true);
      }

      function checkInputAllForEdit()
      {
          var check_title = true;
          var check_start = true;
          var check_end = true;

          $("input[name = edit_sign_time_title]").each(function () {
              if (!checkEditSignTimeTitle(this,true)) {
                  check_title = false;
                  return false;
              }
          });

          if(check_title) {
              $("input[name = edit_sign_time_start]").each(function () {
                  if (!checkEditSignTimeStart(this,true)) {
                      check_start = false;
                      return false;
                  }
              });
          }

          if(check_title && check_start) {
              $("input[name = edit_sign_time_end]").each(function () {
                  if (!checkEditSignTimeEnd(this,true)) {
                      check_end = false;
                      return false;
                  }
              });
          }

          return check_title && check_start && check_end;
      }

      function $o(id)
      {
          return document.getElementById(id);
      }
      
      function checkSignTimeTitle(obj,sem)
      {
          return checkInputUniqueByName(obj,'sign_time_title',chk_sign_name,sem);
      }
      
      function checkSignTimeStart(obj,sem)
      {
          return checkInputUniqueByName(obj,'sign_time_start',chk_start_time,sem)&&
          compareInputOnRow(obj,chk_start_time,'sign_time_end',chk_end_time,"sign_time_list",'<',sem);
      }
      
      function checkSignTimeEnd(obj,sem)
      {
          return checkInputUniqueByName(obj,'sign_time_end',chk_end_time,sem) &&
          compareInputOnRow(obj,chk_end_time,'sign_time_start',chk_start_time,"sign_time_list",'>',sem);
      }

      function checkSignDateStart(obj,sem,ce)
      {
          return compareInputOnRow(obj,chk_start_date,'sign_date_end',chk_end_date,"sign_date_row",'<',sem,ce);
      }

      function checkSignDateEnd(obj,sem,ce)
      {
          return compareInputOnRow(obj,chk_end_date,'sign_date_start',chk_start_date,"sign_date_row",'>',sem,ce);
      }

      function checkEditSignTimeTitle(obj,sem)
      {
          return checkInputUniqueByName(obj,'edit_sign_time_title',chk_sign_name,sem);
      }

      function checkEditSignTimeStart(obj,sem)
      {
          return checkInputUniqueByName(obj,'edit_sign_time_start',chk_start_time,sem) &&
          compareInputOnRow(obj, chk_start_time, 'edit_sign_time_end', chk_end_time, "edit_sign_time_list",'<',sem);
      }

      function checkEditSignTimeEnd(obj,sem)
      {
          return checkInputUniqueByName(obj,'edit_sign_time_end',chk_end_time,sem) &&
          compareInputOnRow(obj, chk_end_time, 'edit_sign_time_start', chk_start_time, "edit_sign_time_list",'>',sem);
      }






      function checkInputUniqueByName(obj,name,desc,sem)
      {
          if(typeof sem=='undefined'){sem=false;}    //sem show error message
      //    alert(name +$("input[name = sign_time_title]").size());
          var list = $("input[name = "+name+"]");
          var list_size = list.size();
       //   alert(list_size);

          var repeat_count=0;
          for(var i=0;i<list_size;i++)
          {
              var val=list.get(i).value;
              if(val=="")
              {
                  if(sem){app.showMsg(desc+"<?=Yii::t('frontend', 'no_empty')?>");}
                  return false;
              }
              if(val==obj.value)
              {
                  repeat_count++;
              }
          }
          if(repeat_count>1)
          {
              input_valid_is_passed=false;
              app.showMsg(desc+"<?=Yii::t('frontend', 'no_repeat')?>");
              return false;
          }
          return true;
      }

      function compareInputOnRow(obj,obj_desc,compare_name,compare_desc,parent_name,op,sem,ce)
      {

            if(typeof sem=='undefined'){sem=false;}    //sem show error message
            var list=$(obj).parents("div[name = "+parent_name+"]").find("input[name = "+compare_name+"]");
           // if(list.size()==0) {return true;}
          //alert(obj.value);
          //alert(list.get(0).value);
            
            //can equal
            if(typeof ce=='undefined') {ce=false; }
      
            if(obj.value==list.get(0).value)
            {
                if(ce)
                {
                    return true;
                }
                else
                {
                    if(sem){app.showMsg(obj_desc+"<?=Yii::t('frontend', 'no_equal')?>"+compare_desc);}
                    return false;
                }
            }

            
            if(typeof op!='undefined')  
            {
              var stdt=null;
              var etdt=null;
              if(obj.value.indexOf(':')==-1)
              {  
                  stdt=new Date(obj.value);
                  etdt=new Date(list.get(0).value);
              }
              else
              {
                  var today=new Date();
                  var todayStr=today.getFullYear()+'-'+today.getMonth()+'-'+today.getDate();   
                    
                  stdt=new Date(todayStr+' '+obj.value);
                  etdt=new Date(todayStr+' '+list.get(0).value);
              }
              
              
              if(op=='>')
              {
                 if(stdt <= etdt)
                 {
                     //{value1}_must_{value2}_{value3}
                     if(sem){app.showMsg(obj_desc+"<?=Yii::t('frontend', 'must_geater_than')?>"+compare_desc);}
                     return false;
                 } 
              }
              else if(op=='<')
              {
                 if(stdt >= etdt)
                 {
                     if(sem){app.showMsg(obj_desc+"<?=Yii::t('frontend', 'must_less_than')?>"+compare_desc);}
                     return false;
                 }   
              }
            }

          return true;
          
      }
  </script>


  <!-- 签到配置相关脚本 -->
  <script type="text/javascript">
  // 添加签到时间1
  $('.addSignTime').bind('click', function() {
    var addTime = '<div name="sign_time_list" class="row timeRow"><div class="col-sm-2" style="text-align: center;"><input name="sign_time_title" onblur="checkSignTimeTitle(this)" type="text" style="border: 1px solid #ccc;" placeholder="<?=Yii::t('common', 'name')?>"/></div><div class="col-sm-3"><input name="sign_time_start" onblur="checkSignTimeStart(this)"  readonly="readonly" type="text" style="border: 1px solid #ccc;" data-type="rili" data-full="0" data-hms="19:00" placeholder="19:00" value="19:00" /></div><div class="col-sm-1" style="text-align: center;"><?=Yii::t('common', 'to')?></div><div class="col-sm-3"><input name="sign_time_end" onblur="checkSignTimeEnd(this)" readonly="readonly" type="text" style="border: 1px solid #ccc;" data-type="rili" data-full="0" data-hms="21:00" placeholder="21:00" value="21:00" /></div><div class="col-sm-2"><a href="javascript:void(0);" class="btn btn-xs" onclick="removeSignSettingFromDateBox(this,null)"><?=Yii::t('common', 'delete_button')?></a></div><div class="col-sm-1" style="text-align: center;"></div></div>';
    $(this).parent().append(addTime);
    
    /*
    $('.delRowBtn').bind('click', function() {
        alert('a');
      $(this).parents('.timeRow').remove();
    });
    */
   app.genCalendar();
  });



/**/
  // 删除签到时间
  $('.delRowBtn').bind('click', function() {
    $(this).parents('.timeRow').remove();
  });

  function showSignConfigDiv()
  {
      $('.longPanel').addClass('signConfig');
      loadSignSettingList();
  }

  // 返回签到列表
  $('.backSignList').bind('click', function() {
    $('.longPanel').removeClass('signConfig');
    
    if(sign_settings_is_temp)
    {
        //提示
    }
    backToStudentSignList();


  });

  // 重置按钮
  /*
  function resetBtn(){
    var html = '<a href="###" class="btn btn-xs btn-default statuBtn">签到</a>';
    $(this).parents('td').html(html);
    $('.statuBtn').bind('click', signBtn);
  }
  */

  // 签到按钮
  /*
  function signBtn(){
    var html = '<span class="statu">已签到<a href="###" class="glyphicon glyphicon-refresh refreshBtn" title="重置"></a> </span>';
    $(this).parents('td').html(html);
    $('.refreshBtn').bind('click', resetBtn);
  }
  */

  // 签到按钮绑定
 // $('.statuBtn').bind('click', signBtn);

  // 请假按钮
  /*
  $('.leaveBtn').bind('click', function() {
    var targetClass = "." + $(this).attr('data-statu');
    var html = '<span class="statu">已请假<a href="###" class="glyphicon glyphicon-refresh refreshBtn" title="重置"></a> </span>'
    $(this).parents('tr').find(targetClass).html(html);
    $('.refreshBtn').bind('click', resetBtn);
  })
  */
  </script>
