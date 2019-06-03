<?php
/**
 * Created by PhpStorm.
 * User: adophper
 * Date: 2015/7/9
 * Time: 13:22
 */
use yii\helpers\Html;
use yii\helpers\Url;
use common\helpers\TStringHelper;
if (!empty($result)){
    foreach ($result as $val) {
        ?>
        <div class="answerBlock ">
            <div class="answerUser mini_answerUser">
                <a href="javascript:;" data-thumb="<?=$val['thumb']?>"><img src="<?= TStringHelper::Thumb($val['thumb'],$val['gender']) ?>" /></a>
                <span>
                    <?= $val['real_name'] ?>
                    <?php
                    if ($uid != $val['user_id']) {
                    ?><a href="javascript:;" class="pull-right member_attention" data-id="<?= $val['user_id'] ?>" onclick="memberAttention('<?= $val['user_id'] ?>');"><?=$soUserAttention->getAttentionText($val['user_id'])?></a>
                    <?php
                    }
                    ?>
                </span>
            </div>
            <div class="answerDetail">
                <p><?= Html::encode($val['answer_content']) ?></p>
                <div class="answerInteract">
                    <span><a href="javascript:;" class="questionAnswerComments" data-id="<?=$val['kid']?>" onclick="showComment(this,'<?=$val['kid']?>');"><?= Yii::t('frontend', 'comment') ?>(<font id="questionAnswerCommentsNumber_<?=$val['kid']?>"><?=$val['comment_num']>0?$val['comment_num']:'0'?></font>)</a> <?= Yii::t('common', 'time') ?>:<i><?= date('Y-m-d H:i:s', $val['created_at']) ?></i></span>
                    <div class="commentInput subcomments" style="display: none;">
                        <div id="questionAnswerCommentList_<?=$val['kid']?>"></div>
                        <textarea id="comment_<?=$val['kid']?>"></textarea>
                        <span><a href="javascript:;" class="btn btn-success pull-right" data-id="<?=$val['kid']?>" onclick="replyComment('<?=$val['kid']?>');"><?= Yii::t('frontend', 'comment') ?></a></span>
                    </div>
                </div>
            </div>
        </div>
        <script>
            var comment_url = "<?=Url::toRoute(['resource/course/get-answer-comments'])?>";
            var comment_save = "<?=Url::toRoute(['resource/course/set-answer-comments'])?>";
            function showComment(obj,id){
                var box = $(obj).parent().next();
                if (box.is(":hidden")){
                    app.get(comment_url+'?answer_id='+id, function(data){
                        if (data){
                            $("#questionAnswerCommentList_"+id).html(data);
                        }
                    });
                    box.show();
                }else{
                    box.hide();
                }
            }
            function replyComment(id){
                var content = $("#comment_"+id).val().replace(/\s+/g,'');
                if (content == ""){
                    app.showMsg('<?= Yii::t('frontend', '{value}_not_null',['value'=>Yii::t('frontend','comment_content')]) ?>');
                    return false;
                }
                $.post(comment_save+'?answer_id='+id,{content: content}, function(json){
                    if (json.result == 'success') {
                        if (checkPointResult(json.pointResult)){
                            //score-Effect(json.score);
                            scorePointEffect(json.pointResult.show_point,json.pointResult.point_name,json.pointResult.available_point);
                        }else{
                            app.showMsg('<?= Yii::t('frontend', 'comment_sucess') ?>');
                        }
                        $("#questionAnswerCommentsNumber_"+id).html(parseInt($("#questionAnswerCommentsNumber_"+id).html())+1);
                        $("#comment_"+id).val('');
                        app.get(comment_url+'?answer_id='+id, function (data) {
                            if (data) {
                                $("#questionAnswerCommentList_" + id).html(data);
                            }
                        });

                    }else{
                        app.showMsg('<?= Yii::t('frontend', 'comment_failed') ?>');
                    }
                },'json');
            }
        </script>
        <?php
    }
}
?>