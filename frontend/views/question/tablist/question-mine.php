<form class="form-inline pull-left" style="width:40%; margin:20px 0;" id="minesearch">
    <div class="form-group">
        <input type="text" class="form-control" id="searchcontent" value="" placeholder="<?=Yii::t('frontend', 'title_reply_comment')?>">
        <button type="button" class="btn btn-primary pull-right" style="height:32px; line-height: 1rem;" id="searchbutton"><?= Yii::t('frontend', 'top_search_text') ?></button>
    </div>
</form>
<div class="filterBtn pull-right" style="width:50%;" id="minelist">
    <a href="#" class="btnFilter activeBtn" data-num="0" id="questionmine" ><?=Yii::t('frontend', 'i_propose')?></a>
    <a href="#" class="btnFilter" data-num="1" id="icare" ><?=Yii::t('frontend', 'i_follow')?></a>
    <a href="#" class="btnFilter" data-num="2" id="atme" ><?=Yii::t('frontend', 'i_at')?></a>
    <a href="#" class="btnFilter" data-num="3" id="ianswered" ><?=Yii::t('frontend', 'i_reply')?></a>
</div>
<div class="questionLine" id="questionismine">
    <?php use yii\helpers\Html;

    if(!empty($data)){?>
    <?php foreach($data as $k => $v):?>
    <div class="questionBlock" id="<?=$v['kid']?>" type="">
        <h3>
            <a href="detail.html?id=<?= $v['kid']?>"><?php echo Html::encode(mb_substr($v['title'],0,20,"utf-8"));if(mb_strlen($v['title'],'utf-8')>20) echo "..."?></a>
        </h3>
        <span class = "setitle"><?=Yii::t('frontend', 'release_at')?>:  <?php echo date("Y-m-d H:i",$v['created_at'])?></span>
        <span class = "setitle"><?php if($v['question_type']==1&&!empty($coursefinal)):?><?= Yii::t('frontend', 'from_text') ?>: 【<?= Yii::t('common', 'course') ?>】<?php echo $coursefinal["$v[obj_id]"]; endif;?></span>
        <span>
                <a class="pull-right more" href="###"  onclick="subCare('<?= $v['kid']?>')"><span id="carestr<?= $v['kid']?>" class="carecol" now="<?=$v['isCare']?>" count="<?= $v['attention_num']?>"><?=$v['isCare'] ? Yii::t('common', 'cancel_attention') : Yii::t('common', 'attention')?>(<?= $v['attention_num']?>)</span></a>
                <a class="pull-right more" href="###"  onclick="questionid('<?= $v['kid']?>','<?= Html::encode($v['title'])?>','<?= $v['share_num']?>')"><span id="sharestr<?= $v['kid']?>" class="carecol"><?=Yii::t('frontend', 'share')?>(<?= $v['share_num']?>)</span></a>
                <a class="pull-right more" href="###"  onclick="subCollect('<?= $v['kid']?>')"><span id="colstr<?= $v['kid']?>" class="carecol" now="<?=$v['isCollect']?>" count="<?= $v['collect_num']?>"><?=$v['isCollect'] ? Yii::t('common', 'canel_collection') :  Yii::t('common', 'collection')?>(<?= $v['collect_num']?>)</span></a>
                <a class="pull-right more" href="<?=Yii::$app->urlManager->createUrl(['question/detail','id'=> "$v[kid]"])?>" ><?=Yii::t('frontend', 'reanswer')?>(<?= $v['answer_num']?>)</a>
      </span>
        <p><?php echo Html::encode(mb_substr($v['question_content'],0,228,"utf-8"));if(mb_strlen($v['question_content'],'utf-8')>228) echo "..."?></p>
        <div class="labelArea ">
            <?php if (is_array($v['tag_value'])) { foreach($v['tag_value'] as $key => $value):?>
                <span class="label label-info"><?php echo Html::encode($value['tag_value']) ?></span>
            <?php endforeach; }?>
        </div>
        <hr />
        <input type="hidden" id="sharenum<?= $v['kid']?>" value="" />
    </div>
    <?php endforeach;?>
    <?php }else{
        echo '			    <div class="centerBtnArea noData " style="float:none">
				              <i class="glyphicon glyphicon-calendar"></i>
				              <p>'.Yii::t('frontend', 'temp_no_data').'</p>
				            </div>';
    }?>
</div>
