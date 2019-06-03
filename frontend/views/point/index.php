<?php

/**
 * 积分控制器
 * author: 包显建
 * date: 2016/2/29
 * time: 11:35
 */
use yii\helpers\Html;
use yii\helpers\Url;
use components\widgets\TBreadcrumbs;
use components\widgets\TLinkPager;


$this->pageTitle = Yii::t('frontend', 'point_manage');// Yii::t('frontend', 'page_lesson_hot_title');
$this->params['breadcrumbs'][] = ['label'=>Yii::t('common','resource_management'),'url'=>['resource/index']];
$this->params['breadcrumbs'][] = $this->pageTitle;

$range_allow=array(
'Login'=>                   array(0,1,1,1,1,1),
'Search'=>                  array(0,1,0,0,0,0),
'Register-Online-Course'=>  array(0,1,0,0,0,0),
'Register-Face-Course'=>    array(0,1,0,0,0,0),
'Open-Shared-Page'=>        array(0,1,0,0,0,0),
'Open-Shared-Event'=>       array(0,1,0,0,0,0),
'Open-Shared-Book'=>        array(0,1,0,0,0,0),
'Download-Page'=>           array(0,1,0,0,0,0),
'Download-Event'=>          array(0,1,0,0,0,0),
'Download-Book'=>           array(0,1,0,0,0,0),
'Download-Experience'=>     array(0,1,0,0,0,0),
'Complete-Online-Course'=>  array(0,1,0,0,0,0),
'Complete-F2F-Course'=>     array(0,1,0,0,0,0),
'Pass-Exam'=>               array(0,1,0,0,0,0),
'Complete-Investigation'=>  array(0,1,0,0,0,0),
'Complete-Questionare'=>    array(0,1,0,0,0,0),
'Get-Certification'=>       array(0,1,0,0,0,0),
'Revoke-Certification'=>    array(0,1,0,0,0,0),
'Complete-Self-Info'=>      array(0,1,0,0,0,0),
'Attention-Question'=>      array(0,1,0,0,0,0),
'Attention-People'=>        array(0,1,0,0,0,0),
'Collect-Course'=>          array(0,1,0,0,0,0),
'Collect-Question'=>        array(0,1,0,0,0,0),
'Mark-Course'=>             array(0,1,0,0,0,0),
'Comment-Course-Question'=> array(1,1,1,1,1,1),
'Comment-Common-Question'=> array(1,1,1,1,1,1),
'Reply-Course-Question'=>   array(1,1,1,1,1,1),
'Reply-Common-Question'=>   array(1,1,1,1,1,1),
'Publish-Page'=>            array(1,1,1,1,1,1),
'Publish-Event'=>           array(1,1,1,1,1,1),
'Publish-Book'=>            array(1,1,1,1,1,1),
'Publish-Sharing'=>         array(1,1,1,1,1,1),
)
?>
<?= Html::jsFile('/static/frontend/js/key.replace.js') ?>
<style>
  .form-control{
    width: 100%;
  }
  .pagination{
    float:right;
  }
  .form-group input{
    text-align: left !important;
  }
  .centerBtnArea {
    float: left !important;
  }
</style>
<br/>
 <br/>
    
  <div class="headBanner5"></div>
  <div class="container">
    <div class="row">
        <div class="row">
        <?= TBreadcrumbs::widget([
          'tag' => 'ol',
          'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
        ]) ?>
      <div class="col-md-12">
        <div class="courseInfo">
          <ul class="nav nav-tabs hotNews" role="tablist" id="myTab">
            <li role="presentation" class="active"><a href="#score_rule" aria-controls="allCourse" role="tab" data-toggle="tab"><?=Yii::t('frontend', 'point_rule')?></a></li>
            <!--
            <li role="presentation"><a href="#score_system" aria-controls="allCourse" role="tab" data-toggle="tab">成长体系</a></li>
            -->
          </ul>
          <div class="tab-content topBordered">
            <div role="tabpanel" class="tab-pane active" id="score_rule">
              <div class="panel panel-default scoreList">
                <div class="panel-body scoreList">
                  <div class="actionBar">
                  </div>
                  <table class="table table-bordered table-hover table-striped table-center">
                    <tbody>
                      <tr>
                        <td width="45%"><?=Yii::t('common', 'point_name')?></td>
                        <td width="20%"><?=Yii::t('common', 'cycle_range')?></td>
                        <td width="10%"><?=Yii::t('common', 'standard_value')?>(<?=Yii::t('frontend', 'point')?>)</td>
                        <td width="10%"><?=Yii::t('common', 'is_in_using')?></td>
                        <!--td width="15%">操作</td-->
                      </tr>
                      
                      <?php foreach($list as $v){?>
                      <tr id="<?=$v['kid']?>">
                        <td id="point_name_<?=$v['kid']?>" align="left"><span class="preview"><?=$v['point_name']?></span></td>
                        <td id="cycle_range_<?=$v['kid']?>" v="<?=$v['cycle_range']?>">
                        <div style="display: block;" id="val_cycle_range_<?=$v['kid']?>"><?=$cycleRanges[$v['cycle_range']]?></div>
                        <div style="display: none;" id="dsel_cycle_range_<?=$v['kid']?>" class="form-group" style="margin-bottom:0;">
                      
                            <select class="form-control" style="width:100%; margin:0; height:25px;">
                                <?php foreach ($cycleRanges as $k2=>$v2) {if($range_allow[$v['point_code']][$k2]){
                                ?><option value="<?=$k2?>"><?=$v2?></option><?php }}?>
                            </select>
                        </div>
                        </td>
                        <td id="standard_value_<?=$v['kid']?>"><?=$v['point_op'].$v['standard_value']?></td>
                        <td id="status_<?=$v['kid']?>"><?=$statuses[$v['status']]?></td>
                        <?php /* ?>
                        <td>
                          <a href="###" class="btn-xs glyphicon glyphicon-play-circle" t="start" title="启用"></a>
                          <a href="###" class="btn-xs glyphicon glyphicon-off" t="stop" title="停用"></a>
                          <a href="###" class="btn-xs icon iconfont editMode" t="edit" title="编辑">&#x1001;</a>
                        </td>
                       <?php */ ?>
                      </tr>
                      <?php }?>
                    </tbody>
                  </table>
                  
                  <nav>
                  <!--
                    <ul class="pagination pull-right">
                      <li>
                        <a href="#" aria-label="Previous">
                          <span aria-hidden="true">«</span>
                        </a>
                      </li>
                      <li class="active"><a href="#">1</a></li>
                      <li><a href="#">2</a></li>
                      <li><a href="#">3</a></li>
                      <li><a href="#">4</a></li>
                      <li><a href="#">5</a></li>
                      <li>
                        <a href="#" aria-label="Next">
                          <span aria-hidden="true">»</span>
                        </a>
                      </li>
                    </ul>
                     -->
                    <nav>
                        <?php
                        echo TLinkPager::widget([
                            'id' => 'page',
                            'pagination' => $pages,
                            'displayPageSizeSelect'=>true
                        ]);
                        ?>
                    </nav>
 <script>
    $(function(){
        $("#content .pagination").on('click', 'a', function(e){
            e.preventDefault();
            ajaxGet($(this).attr('href'), 'content');
        });
    });
</script>                  
                  
                  
                </div>
              </div>
            </div>
            <div role="tabpanel" class="tab-pane" id="score_system">
              <div class="panel panel-default scoreList">
                <div class="panel-body scoreList">
                  <div class="actionBar">
                    <a class="btn btn-success  pull-left" href="###" data-toggle="modal" data-target="#newLevel"><?=Yii::t('frontend', 'new_level')?></a>
                  </div>
                  <table class="table table-bordered table-hover table-striped table-center">
                    <tbody>
                      <tr>
                        <td width="15%"><?=Yii::t('common', 'stage_name')?></td>
                        <td width="20%"><?=Yii::t('common', 'level_name')?></td>
                        <td width="35%"><?= Yii::t('common', 'description') ?></td>
                        <td width="10%"><?=Yii::t('common', 'audience_code')?></td>
                        <td width="10%"><?=Yii::t('common', 'point')?></td>
                        <td width="10%"><?= Yii::t('common', 'action') ?></td>
                      </tr>
                      <tr>
                        <td><?=Yii::t('frontend', '{value}_stage'),['value'=>Yii::t('frontend','school_primary')]?></td>
                        <td><?=Yii::t('frontend', 'school_primary')?><?=Yii::t('frontend', 'grade_1')?></td>
                        <td align="left"><span class="preview" title="<?=Yii::t('frontend', 'point_get_n')?>"><?=Yii::t('frontend', 'point_get_n')?></span></td>
                        <td>1</td>
                        <td>212</td>
                        <td>
                          <a href="###" class="btn-xs icon iconfont editMode" title="<?=Yii::t('frontend', 'editor_text')?>">&#x1001;</a>
                          <a href="###" class="btn-xs icon iconfont" title="<?=Yii::t('common', 'delete_button')?>">&#x1006;</a>
                        </td>
                      </tr>
                      <tr>
                        <td></td>
                        <td><?=Yii::t('frontend', 'school_primary')?><?=Yii::t('frontend', 'grade_2')?></td>
                        <td align="left"><span class="preview" title="<?=Yii::t('frontend', 'point_get_n')?>"><?=Yii::t('frontend', 'point_get_n')?></span></td>
                        <td>1</td>
                        <td>212</td>
                        <td>
                          <a href="###" class="btn-xs icon iconfont editMode" title="<?=Yii::t('frontend', 'editor_text')?>">&#x1001;</a>
                          <a href="###" class="btn-xs icon iconfont" title="<?=Yii::t('common', 'delete_button')?>">&#x1006;</a>
                        </td>
                      </tr>
                      <tr>
                        <td></td>
                        <td><?=Yii::t('frontend', 'school_primary')?><?=Yii::t('frontend', 'grade_3')?></td>
                        <td align="left"><span class="preview" title="<?=Yii::t('frontend', 'point_get_n')?>"><?=Yii::t('frontend', 'point_get_n')?></span></td>
                        <td>1</td>
                        <td>212</td>
                        <td>
                          <a href="###" class="btn-xs icon iconfont editMode" title="<?=Yii::t('frontend', 'editor_text')?>">&#x1001;</a>
                          <a href="###" class="btn-xs icon iconfont" title="<?=Yii::t('common', 'delete_button')?>">&#x1006;</a>
                        </td>
                      </tr>
                      <tr>
                        <td></td>
                        <td><?=Yii::t('frontend', 'school_primary')?><?=Yii::t('frontend', 'grade_4')?></td>
                        <td align="left"><span class="preview" title="<?=Yii::t('frontend', 'point_get_n')?>"><?=Yii::t('frontend', 'point_get_n')?></span></td>
                        <td>1</td>
                        <td>212</td>
                        <td>
                          <a href="###" class="btn-xs icon iconfont editMode" title="<?=Yii::t('frontend', 'editor_text')?>">&#x1001;</a>
                          <a href="###" class="btn-xs icon iconfont" title="<?=Yii::t('common', 'delete_button')?>">&#x1006;</a>
                        </td>
                      </tr>
                      <tr>
                        <td></td>
                        <td><?=Yii::t('frontend', 'school_primary')?><?=Yii::t('frontend', 'grade_finish')?></td>
                        <td align="left"><span class="preview" title="<?=Yii::t('frontend', 'point_get_n')?>"><?=Yii::t('frontend', 'point_get_n')?></span></td>
                        <td>1</td>
                        <td>212</td>
                        <td>
                          <a href="###" class="btn-xs icon iconfont editMode" title="<?=Yii::t('frontend', 'editor_text')?>">&#x1001;</a>
                          <a href="###" class="btn-xs icon iconfont" title="<?=Yii::t('common', 'delete_button')?>">&#x1006;</a>
                        </td>
                      </tr>
                      <tr>
                        <td><?=Yii::t('frontend', '{value}_stage'),['value'=>Yii::t('frontend','school_junior')]?></td>
                        <td><?=Yii::t('frontend', 'school_junior')?><?=Yii::t('frontend', 'grade_1')?></td>
                        <td align="left"><span class="preview" title="<?=Yii::t('frontend', 'point_get_n')?>"><?=Yii::t('frontend', 'point_get_n')?></span></td>
                        <td>1</td>
                        <td>212</td>
                        <td>
                          <a href="###" class="btn-xs icon iconfont editMode" title="<?=Yii::t('frontend', 'editor_text')?>">&#x1001;</a>
                          <a href="###" class="btn-xs icon iconfont" title="<?=Yii::t('common', 'delete_button')?>">&#x1006;</a>
                        </td>
                      </tr>
                      <tr>
                        <td></td>
                        <td><?=Yii::t('frontend', 'school_junior')?><?=Yii::t('frontend', 'grade_2')?></td>
                        <td align="left"><span class="preview" title="<?=Yii::t('frontend', 'point_get_n')?>"><?=Yii::t('frontend', 'point_get_n')?></span></td>
                        <td>1</td>
                        <td>212</td>
                        <td>
                          <a href="###" class="btn-xs icon iconfont editMode" title="<?=Yii::t('frontend', 'editor_text')?>">&#x1001;</a>
                          <a href="###" class="btn-xs icon iconfont" title="<?=Yii::t('common', 'delete_button')?>">&#x1006;</a>
                        </td>
                      </tr>
                      <tr>
                        <td></td>
                        <td><?=Yii::t('frontend', 'school_junior')?><?=Yii::t('frontend', 'grade_3')?></td>
                        <td align="left"><span class="preview" title="<?=Yii::t('frontend', 'point_get_n')?>"><?=Yii::t('frontend', 'point_get_n')?></span></td>
                        <td>1</td>
                        <td>212</td>
                        <td>
                          <a href="###" class="btn-xs icon iconfont editMode" title="<?=Yii::t('frontend', 'editor_text')?>">&#x1001;</a>
                          <a href="###" class="btn-xs icon iconfont" title="<?=Yii::t('common', 'delete_button')?>">&#x1006;</a>
                        </td>
                      </tr>
                      <tr>
                        <td><?=Yii::t('frontend', '{value}_stage'),['value'=>Yii::t('frontend','school_high')]?></td>
                        <td><?=Yii::t('frontend', 'school_high')?><?=Yii::t('frontend', 'grade_1')?></td>
                        <td align="left"><span class="preview" title="<?=Yii::t('frontend', 'point_get_n')?>"><?=Yii::t('frontend', 'point_get_n')?></span></td>
                        <td>1</td>
                        <td>212</td>
                        <td>
                          <a href="###" class="btn-xs icon iconfont editMode" title="<?=Yii::t('frontend', 'editor_text')?>">&#x1001;</a>
                          <a href="###" class="btn-xs icon iconfont" title="<?=Yii::t('common', 'delete_button')?>">&#x1006;</a>
                        </td>
                      </tr>
                      <tr>
                        <td></td>
                        <td><?=Yii::t('frontend', 'school_high')?><?=Yii::t('frontend', 'grade_2')?></td>
                        <td align="left"><span class="preview" title="<?=Yii::t('frontend', 'point_get_n')?>"><?=Yii::t('frontend', 'point_get_n')?></span></td>
                        <td>1</td>
                        <td>212</td>
                        <td>
                          <a href="###" class="btn-xs icon iconfont editMode" title="<?=Yii::t('frontend', 'editor_text')?>">&#x1001;</a>
                          <a href="###" class="btn-xs icon iconfont" title="<?=Yii::t('common', 'delete_button')?>">&#x1006;</a>
                        </td>
                      </tr>
                      <tr>
                        <td></td>
                        <td><?=Yii::t('frontend', 'school_high')?><?=Yii::t('frontend', 'grade_3')?></td>
                        <td align="left"><span class="preview" title="<?=Yii::t('frontend', 'point_get_n')?>"><?=Yii::t('frontend', 'point_get_n')?></span></td>
                        <td>1</td>
                        <td>212</td>
                        <td>
                          <a href="###" class="btn-xs icon iconfont editMode" title="<?=Yii::t('frontend', 'editor_text')?>">&#x1001;</a>
                          <a href="###" class="btn-xs icon iconfont" title="<?=Yii::t('common', 'delete_button')?>">&#x1006;</a>
                        </td>
                      </tr>
                      <tr>
                        <td><?=Yii::t('frontend', '{value}_stage'),['value'=>Yii::t('frontend','school_college')]?></td>
                        <td><?=Yii::t('frontend', 'school_college')?><?=Yii::t('frontend', 'grade_1')?></td>
                        <td align="left"><span class="preview" title="<?=Yii::t('frontend', 'point_get_n')?>"><?=Yii::t('frontend', 'point_get_n')?></span></td>
                        <td>1</td>
                        <td>212</td>
                        <td>
                          <a href="###" class="btn-xs icon iconfont editMode" title="<?=Yii::t('frontend', 'editor_text')?>">&#x1001;</a>
                          <a href="###" class="btn-xs icon iconfont" title="<?=Yii::t('common', 'delete_button')?>">&#x1006;</a>
                        </td>
                      </tr>
                    </tbody>
                  </table>
                  <nav>
                    <ul class="pagination pull-right">
                      <li>
                        <a href="#" aria-label="Previous">
                          <span aria-hidden="true">«</span>
                        </a>
                      </li>
                      <li class="active"><a href="#">1</a></li>
                      <li><a href="#">2</a></li>
                      <li><a href="#">3</a></li>
                      <li><a href="#">4</a></li>
                      <li><a href="#">5</a></li>
                      <li>
                        <a href="#" aria-label="Next">
                          <span aria-hidden="true">»</span>
                        </a>
                      </li>
                    </ul>
                  </nav>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>     
  </div>

  <!-- 消息框弹出界面 -->
  <div class="modal fade" id="task1" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
          <h4 class="modal-title" id="myModalLabel"><?=Yii::t('frontend', 'task_to_do')?></h4>
        </div>
        <div class="modal-body">
          ...
        </div>
      </div>
    </div>
  </div>
  <!-- 成绩单弹出窗口 -->
  <div class="modal fade bs-example-modal-md" id="newLevel" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="false">
    <div class="modal-dialog modal-md">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
          <h4 class="modal-title" id="myModalLabel"><?=Yii::t('frontend', 'new_level')?></h4>
        </div>
        <div class="modal-body">
          <div class="courseInfo">
            <div role="tabpanel" class="tab-pane active" id="teacher_info">
              <div class=" panel-default scoreList">
                <div class="panel-body">
                  <div class="infoBlock">
                    <div class="row">
                      <div class="col-md-12 col-sm-12">
                        <div class="form-group form-group-sm">
                          <label class="col-sm-3 control-label"><?=Yii::t('frontend', 'stage_name')?></label>
                          <div class="col-sm-9">
                            <div class="form-group" style="margin-bottom:0;">
                              <select class="form-control" style="width:100%; margin:0; height:25px;">
                                <option value="0"><?=Yii::t('frontend', '{value}_stage'),['value'=>Yii::t('frontend','school_primary')]?></option>
                                <option value="1"><?=Yii::t('frontend', '{value}_stage'),['value'=>Yii::t('frontend','school_college')]?></option>
                                <option value="2"><?=Yii::t('frontend', '{value}_stage'),['value'=>Yii::t('frontend','school_master')]?></option>
                                <option value="3"><?=Yii::t('frontend', '{value}_stage'),['value'=>Yii::t('frontend','school_doctor')]?></option>
                              </select>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-md-12 col-sm-12">
                        <div class="form-group form-group-sm">
                          <label class="col-sm-3 control-label"><?=Yii::t('frontend', 'level_name')?></label>
                          <div class="col-sm-9">
                            <input class="form-control" type="text">
                          </div>
                        </div>
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-md-12 col-sm-12">
                        <div class="form-group form-group-sm">
                          <label class="col-sm-3 control-label"><?= Yii::t('common', 'description') ?></label>
                          <div class="col-sm-9">
                            <textarea></textarea>
                          </div>
                        </div>
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-md-12 col-sm-12">
                        <div class="form-group form-group-sm">
                          <label class="col-sm-3 control-label"><?=Yii::t('frontend', 'audience_code')?></label>
                          <div class="col-sm-9">
                            <input class="form-control" type="text">
                          </div>
                        </div>
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-md-12 col-sm-12">
                        <div class="form-group form-group-sm">
                          <label class="col-sm-3 control-label"><?=Yii::t('common', 'score_detail')?></label>
                          <div class="col-sm-9">
                            <input class="form-control" type="text">
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="centerBtnArea groupAddMember">
                    <a href="###" class="btn centerBtn"><?=Yii::t('common', 'save')?></a>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!-- /container -->
  <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
  <script src="dist/js/jquery.min.js"></script>
  <!-- Include all compiled plugins (below), or include individual files as needed -->
  <script src="dist/js/bootstrap.min.js"></script>
  <script src="dist/js/jquery-ui.min.js"></script>
  <!-- 编辑模式切换脚本 -->
  <script type="text/javascript">
 
  $('.btn-xs').bind('click', function() {
      var edit_statu = $(this).attr('t');
      //valueMap = ["每天", "每周", "每月", "每年", "一次性", "不限制"],
      var id=$(this).parent().parent().attr("id");
      
      score = $("#standard_value_"+id);
      range = $("#cycle_range_"+id);
      
      var val_cycle_range=$("#val_cycle_range_"+id);
      var dsel_cycle_range=$("#dsel_cycle_range_"+id);
      var sel_cycle_range=dsel_cycle_range.find("select");
      
      var content_score = $(score).text();
      var content_range = $(range).find('select');
      
      
      
    // 判断状态码是否为0,是的话就调用编辑模式,不是的话就调用保存模式
    if (edit_statu == "edit") {
      var replace_score = '<div class="input-group "> <input onkeyup="replaceToFloat(this,2);" type="text" class="form-control" style="height: 25px;" value="' + content_score + '"></div>';
      //var replace_range = '<div class="form-group" style="margin-bottom:0;"> <select id="sel_cycle_range"  class="form-control" style="width:100%; margin:0; height:25px;"><?php foreach ($cycleRanges as $k=>$v) { ?><option value="<?=$k?>"><?=$v?></option><?php }?></select> </div>'; 
      //var  select_value = range.attr('v');
      $(score).html(replace_score);
      val_cycle_range.hide();
      dsel_cycle_range.show();
      
      //$(range).html(replace_range).find('select').find("option[value='" + select_value + "']").attr("selected", true);
      $(this).attr("title", "<?=Yii::t('common', 'save')?>").text("<?=Yii::t('common', 'save')?>").css('fontSize', '12px');
      $(this).attr('t','save');
    } 
    else if(edit_statu == "save")
    {
        score_val = $(score).find('input').val();
        
        val_cycle_range.show();
        dsel_cycle_range.hide();
      
        range_val = sel_cycle_range.val();
             
        var url = "<?=Url::toRoute(['point/save-rule'])?>";
        $.post(url, {id:id,cr:range_val,sv:score_val},function (data) {
        //var result = data.result;

        
        $(score).html(data.row.standard_value);
        $(val_cycle_range).html(data.row.cycle_range_text);
        
        /*
        if (result === 'fail') {
            app.showMsg(data.msg,1000) ;
            return false ;
        }
        else if (result === 'success') {
            //app.showMsg(data.msg,1000) ;
            //setTimeout('window.location.reload()', 1500);
            alert(result)
            
            return true ;
        }
        */
        }, "json");
        
        
        
        

      $(this).attr("title", "<?=Yii::t('common', 'delete_button')?>").html("&#x1001;").css('fontSize', '16px');
      $(this).attr("t", "edit");
    }
    else if(edit_statu == "stop")
    {
        changePointStatus(id,0);
    }
    else
    {
        changePointStatus(id,1);
    }
  })

  function changePointStatus(id,status)
  {
        var url = "<?=Url::toRoute(['point/start'])?>";
        var td_status=$("#status_"+id);
        
        
        $.post(url, {id:id,start:status},function (data) {
            
            if(data.result=='success')
            {
                td_status.html(data.row.status_text);
            }
        }, "json"); 

  }

                                                 

  
  </script>
