<?
use yii\helpers\Url;
?>
<?php $temp = 0;?>
<div class="header">
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
    <h4 class="modal-title" id="myModalLabel">[<?php if($data['answer_type']==0){echo Yii::t('frontend','name_real');} else{echo Yii::t('frontend','name_privacy');}if($data['investigation_type']==0){echo Yii::t('frontend', 'questionnaire');}else{echo Yii::t('frontend', 'vote');}?>]<?=$data['title']?></h4>
</div>
        <div role="tabpanel" class="tab-pane active" id="teacher_info">
            <div class=" panel-default scoreList">
                <div class="panel-body">
                    <div class="infoBlock">
                        <div class="row addNewChoice">
                            <?php if($data['investigation_type']==1):?>
                                <div id="votecount">
                                    <div class="Result_noName_vote">
                                        <h4><?php if($data['answer_type']==0){echo Yii::t('frontend','name_real');} else{echo Yii::t('frontend','name_privacy');}?><?= Yii::t('frontend', 'vote_result') ?></h4>
                                        <hr>
                                        <?php foreach($data['question'][0]['option'] as $k=>$v): ?>
                                            <div class="row">
                                                <div class="col-md-12 col-sm-12">
                                                    <div class="form-group form-group-sm">
                                                        <label class="col-sm-12 control-label"><?=chr($k+65);?>. <?=$v['option_title']?></label>
                                                        <div class="col-sm-12">
                                                            <div class="col-sm-8 voteBack"><span class="voteValue" style="width:<?=$count[$v[kid]]/$sumcount*100?>%;"></span></div>
                                                            <div class="col-sm-4 voteNum"><?=$count[$v["kid"]]?>(<?=number_format($count[$v["kid"]]/$sumcount*100, 2, '.', '');?>%)</div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach;?>
                                    </div>
                                </div>
                            <?php endif;?>
                            <div id="resultform"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="c"></div>

<!-- 评论弹出组件 -->
<script type="text/javascript">
    (function ()
    {
        var isVote = Number('<?=$data['investigation_type']?>')
            ,url = "<?=Url::toRoute(['teacher/get-vote-result'])?>?courseId=<?=$courseid?>&modResId=<?=$modresid?>&itemId=<?=$_GET['itemId']?>&type=<?=$data['answer_type']?>&category=<?=($data['investigation_type']==1?'vote':'questionaire')?>"
        ;
        $('.btnaddNewChoice').unbind('click').bind('click', function() {
            var thisBtn = $(this);
            var parentDiv = thisBtn.parent().parent().next();
            $(parentDiv).removeClass('hide');
            app.refreshAlert("#courseware");
            app.refreshAlert("#questionairedetail");
        });

        $('.btnaddNewQuestion').unbind('click').bind('click', function() {
            $('.addNewQuestion').removeClass('hide');
            app.refreshAlert("#courseware");
            app.refreshAlert("#questionairedetail");
        });

        $('.cancelBtn').unbind('click').bind('click', function(){
            $(this).parent().addClass('hide');
        });

        $("#resultform").html(app.msg.LOADING);
        app.get(url, function (r)
        {
            //r是参与人员名单列表，如果是问卷调查，则需额外加载问卷调查汇总结果
            if(!r)
            {
                return app.showMsg(app.msg.NETWORKERROR);
            }
            if(isVote)
            {
                $("#resultform").html(r);
            }
            else
            {
                app.get("<?=Url::toRoute(['investigation/course-play-survey-result'])?>?id=<?=$itemId?>&course_id=<?=$courseid?>&mod_res_id=<?=$modresid?>", function (r2)
                {
                    //问卷调查汇总结果
                    r2 && (r = r2.replace('<a onclick="closeFun()" class="btn btn-sm pull-right cancelBtn"><?= Yii::t('common', 'close') ?></a>', '') + '<div id="teacher_survery">'+r+'</div>');
                    $("#resultform").html(r);
                });
            }
        });
    })();
</script>