<?php
use yii\helpers\Html;
use components\widgets\TLinkPager;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use components\widgets\TBreadcrumbs;

if($is_course=="yes"){
	//在线
	if($course_type=='0'){
		$this->params['breadcrumbs'][] = ['label'=>Yii::t('common','resource_management'),'url'=>['/resource/index']];
		$this->params['breadcrumbs'][] = ['label'=>Yii::t('common','course_online_manager'),'url'=>['/resource/course/manage']];
		$this->params['breadcrumbs'][] = ['label'=>Yii::t('frontend', 'inv_result_kechengwenjuanceshi'),'url'=>Yii::$app->urlManager->createUrl(['resource/course/online-detail','id'=>$id])];
		$this->params['breadcrumbs'][] = ['label'=>Yii::t('frontend', 'inv_result_nimingwenjuanjieguo'),];
		$this->params['breadcrumbs'][] = Yii::t('frontend', 'inv_result_diaochajieguo');
		$this->params['breadcrumbs'][] = '';
	}else if($course_type=='1'){
		$this->params['breadcrumbs'][] = ['label'=>Yii::t('common','resource_management'),'url'=>['/resource/index']];
		$this->params['breadcrumbs'][] = ['label'=>Yii::t('frontend', 'inv_result_mianshoukechengguanli'),'url'=>['/resource/course/manage-face']];
		$this->params['breadcrumbs'][] = ['label'=>Yii::t('frontend', 'inv_result_mianshoudiaochawenjuan'),'url'=>Yii::$app->urlManager->createUrl(['resource/course/offline-sub-detail','id'=>$id])];
		$this->params['breadcrumbs'][] = ['label'=>Yii::t('frontend', 'inv_result_nimingwenjuanjieguo'),];
		$this->params['breadcrumbs'][] = Yii::t('frontend', 'inv_result_diaochajieguo');
		$this->params['breadcrumbs'][] = '';
	}else if($course_type=='3'){
		
		$this->params['breadcrumbs'][] = ['label'=>Yii::t('frontend', 'inv_result_jiangshimenhu'),'url'=>['/teacher/index']];
		$this->params['breadcrumbs'][] = ['label'=>Yii::t('frontend', 'inv_result_mianshoudiaochawenjuan'),'url'=>Yii::$app->urlManager->createUrl(['/teacher/detail','id'=>$id])];
		$this->params['breadcrumbs'][] = ['label'=>Yii::t('frontend', 'inv_result_nimingwenjuanjieguo'),];
		$this->params['breadcrumbs'][] = Yii::t('frontend', 'inv_result_diaochajieguo');
		$this->params['breadcrumbs'][] = '';
	}
	

}else{
$this->params['breadcrumbs'][] = ['label'=>Yii::t('common','resource_management'),'url'=>['/resource/index']];
$this->params['breadcrumbs'][] = ['label'=>Yii::t('frontend', 'inv_result_diaocha'),'url'=>['/investigation/index']];
$this->params['breadcrumbs'][] = ['label'=>Yii::t('frontend', 'inv_result_nimingwenjuanjieguo'),];
$this->params['breadcrumbs'][] = Yii::t('frontend', 'inv_result_diaochajieguo');
$this->params['breadcrumbs'][] = '';
	}
?>

<style>
<!--
.question_results {
  width:80%
}
-->
</style>

<div class="container">
    <div class="row">
      
      
      <?= TBreadcrumbs::widget([
            'tag' => 'ol',
            'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
        ]) ?>
        
      
      <div class="col-md-12 col-sm-12">
        <div class="courseInfo">
          <ul class="nav nav-tabs hotNews" role="tablist" id="myTab">
            <li role="presentation" class="active"><a href="#survey_stat" aria-controls="survey_stat" role="tab" data-toggle="tab"><?=Yii::t('frontend', 'inv_result_tongjijieguo')?></a></li>
            <li role="presentation"><a href="#survey_stat_detail" aria-controls="survey_stat_detail" role="tab" data-toggle="tab"><?=Yii::t('frontend', 'inv_result_nimingwenjuanxiangqing')?></a></li>
          </ul>
          <div class="tab-content">
            <div role="tabpanel" class="tab-pane active" id="survey_stat">
              <div class=" panel-default scoreList">
                <div class="panel-body">
                  <div class="col-md-8 col-md-offset-2">
                    <h3><?=$results['title'] ?></h3>
                    <p><?=Yii::t('frontend', 'inv_result_yiyou_special_1_start')?><strong class="strong"><?=$results['sub_result_arr_num'] ?></strong><?=Yii::t('frontend', 'inv_result_rencanyu_special_1_end')?>
                  <? if($is_course!="yes"){ ?>   <?=Yii::t('frontend', 'inv_result_youxiaoqi')?>：<?=$results['start_at'] ?> - <?=$results['end_at'] ?>
                  <?} ?>   </p>
                    <hr>
                    
                   <? $num=0;  ?>         
                   <? foreach ($results['question'] as $ques): ?> 
                             
                     <? if ($ques['question_type']=='0'||$ques['question_type']=='1'){ ?>  
                      <? $num=$num+1; ?>          	
                     	<div class="question_results">
                     	<div class="row">
	                     	<div class="col-md-12 col-sm-12">
	                     	<h4><?=$num ?>.<?=$ques['question_title'] ?></h4>
	                     	</div>
                     	</div>
                     	                        <? foreach ($ques['options'] as $opt): ?>
                     	                      <div class="row">
                     	                        <div class="col-md-12 col-sm-12">
                     	                          <div class="form-group form-group-sm">
                     	                            <label class="col-sm-12 control-label"><?=$opt['option_title'] ?></label>
                     	                            <div class="col-sm-12">
                     	                              <div class="col-sm-8 voteBack"><span class="voteValue" style="width:<?=$opt['submit_num_rate'] ?>%;"></span></div>
                     	                              <div class="col-sm-4 voteNum"><?=$opt['submit_num'] ?>(<?=$opt['submit_num_rate'] ?>%)</div>
                     	                            </div>
                     	                          </div>
                     	                        </div>
                     	                      </div>
                     	                       <? endforeach; ?>
                     	  </div>	
                    <? }else if($ques['question_type']=='2'){ ?>
                    <? $num=$num+1; ?>     
                <div class="question_results">
                      <div class="row">
                        <div class="col-md-12 col-sm-12">
                          <h4><?=$num ?>.<?=$ques['question_title'] ?></h4>
                        </div>
                       
                      </div>
                       <div class="row">
	                        <div class="col-md-12 col-sm-12">
	                        <p><?=$ques['question_description'] ?></p>
	                        </div>
                        </div>
                       <? foreach ($ques['options'] as $opt): ?>
                      <div class="row">
                        <div class="col-md-12 col-sm-12">
                          <p><?=$opt['option_result'] ?></p>
                          <h6><?=$opt['user_id1'] ?> <?=Yii::t('frontend', 'inv_result_fabiaoyu')?><?=$opt['created_at'] ?></h6>
                        </div>
                      </div>
                      <? endforeach; ?>
                   
                      <div class="moreDetails hide">
                        <? foreach ($ques['options2'] as $opt): ?>
                        <div class="row">
                          <div class="col-md-12 col-sm-12">
                            <p><?=$opt['option_result'] ?></p>
                            <h6><?=$opt['user_id1'] ?> <?=Yii::t('frontend', 'inv_result_fabiaoyu')?><?=$opt['created_at'] ?></h6>
                          </div>
                        </div>
                         <? endforeach; ?>
                       
                      </div>
                      <? if(sizeof($ques['options2'])>0){?>
                      <a href="###" class="moreDetailsBtn pull-right"><?=Yii::t('frontend', 'inv_result_zhankai')?></a>
                      <?} ?>
                     
                    </div>
                   
                           <? } ?>
                           
                    <? endforeach; ?>
                    
                    
                  </div>
                </div>
              </div>
            </div>
            <div role="tabpanel" class="tab-pane" id="survey_stat_detail">
              <div class=" panel-default scoreList">
                <div class="panel-body">
                  <div class="actionBar">
                    <div class="form-group pull-left">
                      <select id="rel_users_id" class="form-control" name="CourseService[course_type]">
                        <? if($results['all_user_count']!=0){?>
                       <option value="all"><?=Yii::t('frontend', 'inv_result_quanbuxueyuan')?>(<?=$results['all_user_count'] ?>)</option>
                        <option value="no"><?=Yii::t('frontend', 'complete_status_nostart')?>(<?=$results['no_submit_user'] ?>)</option>
                        <?} ?>
                        <option value="yes" selected="selected"><?=Yii::t('frontend', 'complete_status_done')?>(<?=$results['sub_result_arr_num'] ?>)</option>
                      </select>
                    </div>
                  </div>
                  <div id="list"></div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <input type="hidden" id="investigation_id" value="<?=$results['id'] ?>"/>
  <input type="hidden" id="course_id" value="<?=$id ?>"/>
 <!-- 查看 -->
 <div class="ui modal" id="view_survey_show" >
 </div>
  
  <script type="text/javascript">
  $('.moreDetailsBtn').bind('click', function() {
    var thisBtn = $(this)

    if (thisBtn.text() == "<?=Yii::t('frontend', 'inv_result_zhankai')?>") {
      thisBtn.prev().removeClass('hide')
      thisBtn.text("<?=Yii::t('frontend', 'inv_result_shouqi')?>")
    } else {
      thisBtn.prev().addClass('hide')
      thisBtn.text("<?=Yii::t('frontend', 'inv_result_zhankai')?>")
    }
  })
  
  
  
     $(function(){
          

    	 
     });

      function  view_survey_show(id){
        var investigation_id= $("#investigation_id").val();
        <? if($is_course!="yes"){ ?>
		var url="<?=Url::toRoute(['investigation-result/st-survey',])?>"+"?investigation_id="+investigation_id+"&user_id="+id;
		<?}else{ ?>  
		var course_id=$("#course_id").val();
		var url="<?=Url::toRoute(['investigation-result/st-course-survey',])?>"+"?investigation_id="+investigation_id+"&user_id="+id+"&&course_id="+course_id;
		<?}?> 
		FmodalLoadData("view_survey_show",url);
	  }


  	 function FmodalLoadData(target, url)
	 {
		 if(url){
	  	   $('#'+target).empty();
	         $('#'+target).load(url, function (){
	         		 app.alertWide("#"+target,{
	         			afterHide: function (){ 
	         				$('#'+target).empty();
	         				
	             	    }
	     		    });
	             });
	        
	     }
	 }


   <? if($is_course!="yes"){ ?>  
  	var list_url = "<?=Url::toRoute(['investigation-result/s-nm-list'])?>"+"?iid=<?=$iid ?>"+"&rel_users_type=yes&all_user_count_is_0=<?=$results['all_user_count_is_0'] ?>";
   <?} else{?>	
    var list_url = "<?=Url::toRoute(['investigation-result/s-nm-list'])?>"+"?iid=<?=$iid ?>&&course_id=<?=$id ?>"+"&rel_users_type=yes&all_user_count_is_0=<?=$results['all_user_count_is_0'] ?>";
   <?}?>	

    $(document).ready(function () {
        app.genCalendar();   
        load(list_url, 'list', true);


        $("#rel_users_id").change(function(){
      	  

      	  var rel_users_id_val=$("#rel_users_id").val();

      	  <? if($is_course!="yes"){ ?>  
      	  list_url = "<?=Url::toRoute(['investigation-result/s-nm-list'])?>"+"?iid=<?=$iid ?>"+"&rel_users_type="+rel_users_id_val+"&all_user_count_is_0=<?=$results['all_user_count_is_0'] ?>";
      	  <?} else{?>
      	  list_url = "<?=Url::toRoute(['investigation-result/s-nm-list'])?>"+"?iid=<?=$iid ?>&&course_id=<?=$id ?>"+"&rel_users_type="+rel_users_id_val+"&all_user_count_is_0=<?=$results['all_user_count_is_0'] ?>";
      	  <?}?>		

      	  load(list_url, 'list', true);
    	  });
    });


    function load(ajaxUrl, container, is_bind) {
        $("#" + container).empty();
        if (is_bind) {
            $("#list_loading").removeClass('hide');
            ajaxGet(ajaxUrl, container, bind);
        }
        else {
            ajaxGet(ajaxUrl, container, null);
        }
    }
    function bind(target, data) {
        $("#list_loading").addClass('hide');
        $("#" + target).html(data);
        $("#" + target + ' .pagination a').bind('click', function () {
            var url = $(this).attr('href');
            load(url, target, true);
            return false;
        });
    }
  
  </script>
