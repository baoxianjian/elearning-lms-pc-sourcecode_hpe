            <div class="header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                <p class="modal-title" id="myModalLabel"><?= Yii::t('frontend', 'final_score_rule') ?></p>
            </div>
            <div class="content">
                <div class="infoBlock">
                    <div class="row">
                        <!--div class="col-md-12 col-sm-12">
                            <div class="form-group form-group-sm">
                                <label class="col-sm-3 control-label lessWord">是否使用记分规则:</label>
                                <div class="col-sm-9">
                                    <div class="form-group" style="width:30%">
                                        <select  id="ifScored" class="form-control">
                                            <option value="0">否</option>
                                            <option value="1">是</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div-->
                        <div class="col-md-12">
                            <p style="padding-left:15px; color:#999999; font-size:12px;"><?= Yii::t('frontend', 'warning_for_score_rule1') ?></p>
                            <p style="padding-left:15px; color:#999999; font-size:12px;"><?= Yii::t('frontend', 'warning_for_score_rule2') ?></p>
                            <p style="padding-left:15px; color:#999999; font-size:12px;"><?= Yii::t('frontend', 'warning_for_score_rule3') ?></p>
                        </div>
                        <div id="ifScoredYes" class="col-md-12 col-sm-12">
                            <table class="table table-bordered table-hover table-teacher table-center">
                                <tbody>
                                <tr id="scorelist">
                                    <td width="50%"><?= Yii::t('frontend', 'resources_name') ?></td>
                                    <!--td width="25%">课程完成相关</td-->
                                    <td width="25%"><?= Yii::t('frontend', 'weight_and_score') ?></td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 col-sm-12 centerBtnArea">
                            <a href="###" class="btn btn-success btn-sm centerBtn" onclick="saveConfig()" style="width:20%;"><?= Yii::t('common', 'save') ?></a>
                            <a href="###" class="btn btn-success btn-sm centerBtn" onclick="inputClear()" style="width:20%;"><?= Yii::t('frontend', 'reset') ?></a>
                        </div>
                    </div>
                </div>
            </div>
        <script>
            
      //      var ifScored = $.trim(document.getElementById('finalscorelist').innerHTML) != '';

          //  $("#ifScored option:eq("+(ifScored?1:0)+")").attr("selected", true);
          //  ifScored ? $('#ifScoredYes').show():$('#ifScoredYes').hide();

    /*        $('#ifScored').change(
                function(){
                    if("0" == $(this).val())
                    {
                        $('#ifScoredYes').hide();
                        app.refreshAlert("#addModal");
                        return;
                    }
                    $('#ifScoredYes').show();
                    app.refreshAlert("#addModal");
            });

            function checkboxChange(THIS){
                if($(THIS).parent().parent().next().next().children().attr('disabled') == "disabled"){
                   $(THIS).parent().parent().next().next().children().removeAttr('disabled');
                }else{ 
                   $(THIS).parent().parent().next().next().children().attr('disabled','disabled');
                   $(THIS).parent().parent().next().next().children().val('');
                }
            }
            */
            function inputClear(){
                $("input[name='percent']").val('');
            }
            function saveConfig(){
                var sumper = 0;
                 $("input[name='percent']").each(  
                    function(){
                        if($(this).val()!=''){
                           sumper = sumper + parseInt($(this).val(),10);
                        }
                    }
                )

                 if(sumper != '' && sumper>100){
                    app.showMsg('<?= Yii::t('frontend', 'alert_warning_weight_beyond_100') ?>');
                    return false;
                 }
                 if(sumper != '' && sumper<100){
                    app.showMsg('<?= Yii::t('frontend', 'alert_warning_weight_less_100') ?>');
                    return false;
                 }
                var html;   
                html = "";
                $("input[name='score']").each(function(){
                    html += "<input id=\"socl_"+$(this).attr('data-modnum')+"_"+$(this).attr('data-id')+"\" data-score=\""+$('#sco_'+$(this).attr('data-modnum')+'_'+$(this).attr('data-id')).val()+"\" data-id=\""+$(this).attr('data-id')+"\" data-modnum="+$(this).attr('data-modnum')+" name=\"resource["+$(this).attr('data-modnum')+"][rescore]["+$(this).attr('data-id')+"]\" value='{\"score\":\""+$('#sco_'+$(this).attr('data-modnum')+'_'+$(this).attr('data-id')).val()+"\",\"id\":\""+$(this).attr('data-id')+"\",\"modnum\":\""+$(this).attr('data-modnum')+"\",\"comrul\":\""+$(this).attr('data-comrule')+"\"}'/>"; 

                })

                $("#finalscorelist").empty().append(html);
                if(sumper == '' || sumper == 0 || $('#ifScored').val() == 0){
                    $('#finalscorelist').empty();
                }
                app.hideAlert("#addModal");
            }
            var html =""
            var arr = [];
            $("input[name='direct']").each( 
                function(){

                    arr.push($(this).val());
                }
            );
            $("input[class='componentid']").each(  
                function(){  
                    if($(this).attr('data-isscore')=="1" && arr.indexOf($(this).val())==-1){
                        html += "<tr><td align=\"left\"><span class=\"lessWord\" style=\"width:100%;\">";
                    //    if($('#socl_'+$(this).attr('data-modnum')+'_'+$(this).val()).attr('data-score') != "" && document.getElementById('socl_'+$(this).attr('data-modnum')+'_'+$(this).val()) != null){
                            html +="<input  data-comrule=\""+$(this).attr('data-completerule')+"\" checked=\"checked\" type=\"checkbox\" name=\"score\" data-id=\""+$(this).val()+"\" data-modnum=\""+$(this).attr('data-modnum')+"\" value=\"0\" style=\"display:none\">";
                      //  }else{
                         //   html +="<input  onChange=\"checkboxChange(this)\" type=\"checkbox\" data-comrule=\""+$(this).attr('data-completerule')+"\" name=\"score\" data-id=\""+$(this). val()+"\" data-modnum=\""+$(this).attr('data-modnum')+"\" value=\"0\">";
                    //    }
                    
                        html += "【"+$(this).attr('data-compnenttitle')+"】"+$(this).attr('data-restitle')+"";
                        html +="</span></td>";
                     /*   if($('#con_'+$(this).attr('data-modnum')+'_'+$(this).val()).attr('data-isfinish') == 1){
                            html += "<td>是</td>";
                        }else{
                            html += "<td>否</td>";
                        }
                        */
                      //  if($('#socl_'+$(this).attr('data-modnum')+'_'+$(this).val()).attr('data-score') != "" && document.getElementById('socl_'+$(this).attr('data-modnum')+'_'+$(this).val()) != null){
                        var valuepercent  ='';
                        if($('#socl_'+$(this).attr('data-modnum')+'_'+$(this).val()).attr('data-score') != undefined){
                            valuepercent = $('#socl_'+$(this).attr('data-modnum')+'_'+$(this).val()).attr('data-score');
                        }
                              html += "<td><input type=\"text\" onkeyup=\"if(window.event.keyCode != 8 && window.event.keyCode !=37 && window.event.keyCode !=38 && window.event.keyCode !=30 && window.event.keyCode !=40 && window.event.keyCode != 46){this.value=this.value.replace(/[^\\d]/g,'')}\" id=\"sco_"+$(this).attr('data-modnum')+"_"+$(this).val()+"\" value=\""+valuepercent+"\" name=\"percent\" maxlength=\"3\" placeholder=\"<?= Yii::t('frontend', 'percent') ?>\" style=\"width:45%;\">&nbsp; %</td></tr>";
                //        }else{
                      //        html += "<td><input type=\"text\" id=\"sco_"+$(this).attr('data-modnum')+"_"+$(this).val()+"\" value=\"\" placeholder=\"百分比\" disabled=\"disabled\" onkeyup=\"this.value=this.value.replace(/[^\\d]/g,'')\" name=\"percent\" maxlength=\"3\" style=\"width:45%;\">&nbsp; %</td></tr>";
              //          }

                    };
                }  
            )
           $('#scorelist').after(html);
         </script>