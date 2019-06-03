            <div class="header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                <p class="modal-title" id="myModalLabel"><?= Yii::t('frontend', 'course_direct_rule') ?></p>
            </div>
            <div class="panel-body">
                    <div class="infoBlock">
                        <h4 style=" margin-bottom: 5px; color: #0197d6; font-size: 1.6rem; "><?= Yii::t('frontend', 'direct_way') ?></h4>
                        <hr>
                        <div class="row">
                            <div class="col-md-12">
                                <p style="padding-left:15px; color:#888888; font-size:12px;"><strong style="color:#0197d6">*</strong> <?= Yii::t('frontend', 'warning_for_derict') ?>.</p>
                            </div>
                        </div>
                        <div class="row" id="directlist"></div>
                    </div>
                <div class="infoBlock">
                    <h4 style=" margin-bottom: 5px; color: #0197d6; font-size: 1.6rem; "><?= Yii::t('frontend', 'nomal_way') ?></h4>
                    <hr>
                    <div class="row">
                        <div class="col-md-12">
                            <p style="padding-left:15px; color:#888888; font-size:12px;"><strong style="color:#0197d6">*</strong> <?= Yii::t('frontend', 'warning_for_derict') ?>.</p>
                        </div>
                        <div class="row" id="normallist"></div>
                    </div>
                </div>
            </div>
        <script>
            var dataidlist = [];
            $("input[data-name='config']").each(
                function(){  
                    html  ="<div class=\"col-md-12 col-sm-12\">";
                    html +="<div class=\"form-group form-group-sm\">";
                    html +="<label class=\"col-sm-7 control-label lessWord\">["+decodeURIComponent($(this).attr('data-componet'))+"]"+decodeURIComponent($(this).attr('data-title'))+"</label>";
                    html +="<div class=\"col-sm-5\">";
                    html += '<?= Yii::t('frontend', 'weight_for_score') ?>：<?= Yii::t('frontend', 'do_not_score') ?> &nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp';
                    if($(this).attr('data-score') != 'undefined' && $(this).attr('data-score')!='' && $(this).attr('data-score')!= '0'){
                        html += "<?= Yii::t('frontend', 'minimum_pass_mark') ?>: "+parseInt($(this).attr('data-score'))+"<?= Yii::t('frontend', 'point') ?>";
                    }else{
                        html += "<?= Yii::t('frontend', 'minimum_pass_mark') ?>：<?= Yii::t('frontend', 'page_info_none') ?>";
                    }
                    html += "</div></div></div>";
                    if($(this).attr('data-isfinish')==1){
                        $('#directlist').append(html);
                    }
                }  
            )
            $("input[data-name='config']").each(
                function(){
                    dataidlist.push($(this).attr('data-kid'));
                    var dataid = $(this).attr('data-kid');
                        html2  ="<div class=\"col-md-12 col-sm-12\">";
                        html2 +="<div class=\"form-group form-group-sm\">";
                        html2 +="<label class=\"col-sm-7 control-label lessWord\">["+decodeURIComponent($(this).attr('data-componet'))+"]"+decodeURIComponent($(this).attr('data-title'))+"</label>";
                        html2 +="<div class=\"col-sm-5\">";
                        if($(this).attr('data-iscore') == "0") {
                            html2 += '<?= Yii::t('frontend', 'weight_for_score') ?>：<?= Yii::t('frontend', 'do_not_score') ?> &nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp';
                        }else{
                            if($("#finalscorelist").html().trim() != ''){

                                $("#finalscorelist input").each(
                                    function(){
                                        if($(this).attr('data-id') == dataid && $(this).attr('data-score') != 'undefined' && $(this).attr('data-score')!='' && $(this).attr('data-score')!= '0'){
                                            html2 += "<?= Yii::t('frontend', 'weight_for_score') ?>："+parseInt($(this).attr('data-score'))+"% &nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp";
                                        }
                                    }
                                )
                            }else{
                               html2 += '<?= Yii::t('frontend', 'weight_for_score') ?>：<?= Yii::t('frontend', 'do_not_score') ?> &nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp';
                            }
                        }
                        if($(this).attr('data-score') != 'undefined' && $(this).attr('data-score')!='' && $(this).attr('data-score')!= '0'){
                            html2 += "<?= Yii::t('frontend', 'minimum_pass_mark') ?>："+parseInt($(this).attr('data-score'))+"<?= Yii::t('frontend', 'point') ?>";
                        }else{
                            html2 += "<?= Yii::t('frontend', 'minimum_pass_mark') ?>：<?= Yii::t('frontend', 'page_info_none') ?>"
                        }
                        html += "</div></div></div>";
                        if($(this).attr('data-isfinish') == 0){
                            $('#normallist').append(html2);
                        }


                }
            )
            $("#finalscorelist input").each(
                function(){
                    if(dataidlist.indexOf($(this).attr('data-id')) == '-1'){
                        dataidlist.push($(this).attr('data-id'));
                        var tempid = $(this).attr('data-id');
                        html3  ="<div class=\"col-md-12 col-sm-12\">";
                        html3 +="<div class=\"form-group form-group-sm\">";
                        $("input[class='componentid']").each(
                            function(){
                                if(tempid == $(this).val()){
                                    html3 +="<label class=\"col-sm-7 control-label lessWord\">["+decodeURIComponent($(this).attr('data-compnenttitle'))+"]"+decodeURIComponent($(this).attr('data-restitle'))+"</label>";
                                }
                            }
                        )
                        html3 +="<div class=\"col-sm-5\">";
                        if($(this).attr('data-score') != 'undefined' && $(this).attr('data-score')!='' && $(this).attr('data-score')!= '0'){
                            html3 += "<?= Yii::t('frontend', 'weight_for_score') ?>："+parseInt($(this).attr('data-score'))+"% &nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp";
                        }else{
                            html3 += "<?= Yii::t('frontend', 'weight_for_score') ?>：<?= Yii::t('frontend', 'do_not_score') ?> &nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp"
                        }
                        html3 += '<?= Yii::t('frontend', 'minimum_pass_mark') ?>：<?= Yii::t('frontend', 'page_info_none') ?>';
                        html3 += "</div></div></div>";
                        $('#normallist').append(html3);
                    }
                }
            )
            $("input[class='componentid']").each(
                function(){
                    if(dataidlist.indexOf($(this).val()) == '-1'){
                        html4  ="<div class=\"col-md-12 col-sm-12\">";
                        html4 +="<div class=\"form-group form-group-sm\">";
                        html4 +="<label class=\"col-sm-7 control-label lessWord\">["+decodeURIComponent($(this).attr('data-compnenttitle'))+"]"+decodeURIComponent($(this).attr('data-restitle'))+"</label>";
                        html4 +="<div class=\"col-sm-5\">";
                        html4 +="<?= Yii::t('frontend', 'weight_for_score') ?>：<?= Yii::t('frontend', 'do_not_score') ?> &nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp";
                        html4 +="<?= Yii::t('frontend', 'minimum_pass_mark') ?>：<?= Yii::t('frontend', 'page_info_none') ?>";
                        html4 += "</div></div></div>";
                        $('#normallist').append(html4);
                    }
                }
            )
        </script>