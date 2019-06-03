<div class="questionLine" id="questionismine">
    <?php use yii\helpers\Html;

    foreach($data as $k => $v):?>
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
</div>

<input type="hidden" value="<?=$countsolved?>" id="hidesolved<?=$page?>" />
<input type="hidden" value="<?=$countunsolved?>" id="hideunsolved<?=$page?>" />