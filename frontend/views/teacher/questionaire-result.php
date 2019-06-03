<?php $temp = 0;?>
<?php
if (!empty($target)){
?>
<div class="container">
    <div class="row">
    <style>
        .modal-title {margin: 10px 0;}
    </style>
<?php
}
?>
<div class="header">
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
    <h4 class="modal-title" id="myModalLabel">[<?php if($data['answer_type']==0){echo Yii::t('frontend','name_real');} else{echo Yii::t('frontend','name_privacy');}if($data['investigation_type']==0){echo Yii::t('frontend', 'questionnaire');}else{echo Yii::t('frontend', 'vote') ;}?>]<?=$data['title']?></h4>
</div>
<div class="content">
    <div class="courseInfo">
        <div role="tabpanel" class="tab-pane active" id="teacher_info">
            <div class=" panel-default scoreList">
                <div class="panel-body">
                    <div class="infoBlock">
                         <div class="row questionGroup_quest">
                                <p></p>
                                <h4><?=$data['title']?></h4>
                                <p style="text-align:left"><?=$data['description']? Yii::t('frontend', 'brief').' : ' . $data['description']:''?></p>
                            </div>
                            <div class="row questionGroup_quest">
                                <?php
                                if (!empty($data['question'])){
                                    foreach($data['question'] as $k=>$v){
                                ?>
                                    <div class="col-md-12 col-sm-12">
                                        <div class="form-group form-group-sm">
                                            <?php
                                            if($v['question_type'] !=3){
                                            ?>
                                                <label class="col-sm-9 control-label">
                                                    [<?php
                                                    if($v['question_type']== 0){
                                                        echo Yii::t('common', 'question_radio');
                                                    }elseif($v['question_type']== 1){
                                                        echo Yii::t('common', 'question_checkbox');
                                                    }elseif($v['question_type']== 2){
                                                        echo Yii::t('frontend', 'question_answer');
                                                    }
                                                    ?>]<?=Yii::t('frontend', 'question_answer')?><?=$k+1-$temp?>:<?=$v['question_title']?>
                                                </label>
                                            <?php
                                            }else{
                                                $temp++;
                                            }
                                            ?>
                                            <div class="col-sm-3">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-12 col-sm-12">
                                        <?php
                                        if($v['question_type']==0 ||$v['question_type']==1){
                                        ?>
                                            <?php
                                            foreach($v['option']as $key=>$value){
                                                $arr = 1000000;
                                            ?>
                                                <div class="pull-left" style="padding-left: 15px;">
                                                    <label style="margin-right:40px;">
                                                        <?php
                                                        if (!empty($resultdata) && is_array($resultdata[$v['kid']]['sequence_number'])){
                                                            foreach($resultdata[$v['kid']]['sequence_number'] as $ke=>$val){
                                                                if($val==$value['sequence_number']){
                                                                    $arr=$key;
                                                                    if($v['question_type']== 1){
                                                                    ?>
                                                                  <input type="checkbox" checked="checked"  value="1" disabled>
                                                                    <?php
                                                                    }else{
                                                                    ?>
                                                                 <input type="radio" checked="checked"  value="1" disabled>
                                                                    <?php
                                                                    }
                                                                    ?>
                                                                <?php
                                                                }
                                                            }
                                                        }
                                                        if($key!=$arr){
                                                            if($v['question_type']== 1){
                                                            ?>
                                                                <input type="checkbox"  value="1" disabled>
                                                            <?php
                                                            }else{
                                                            ?>
                                                                <input type="radio"  value="1" disabled>
                                                            <?php
                                                            }
                                                        }
                                                        ?>
                                                       <?=$value['option_title']?>
                                                    </label>
                                                </div>
                                            <?php
                                            }
                                        }elseif($v['question_type']==2){
                                        ?>
                                            <textarea readonly="readonly" placeholder=""><?=$resultdata[$v["kid"]]['option_result']?></textarea>
                                        <?php
                                        }
                                        ?>
                                    </div>
                                <?php
                                    }
                                }
                                ?>
                            </div>
                    </div>
                </div>
            </div>
            <!-- <div class="row">
                <div class="col-md-12 col-sm-12 centerBtnArea">
                    <a href="#" class="btn btn-default btn-sm centerBtn btnaddNewChoice" id="detailrenturn" onclick="detailrenturn('<?=$courseid?>','<?=$modresid?>','<?=$inkid?>');$(this).attr('disabled', true);" style="width:30%">返回</a>
                </div>
            </div> -->
        </div>
    </div>
    <div class="c"></div>
</div>
<?php
if (!empty($target)){
?>
    </div>
</div>
<?php
}
?>