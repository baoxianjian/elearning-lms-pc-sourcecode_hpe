<?php
use yii\helpers\Html;
use components\widgets\TLinkPager;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use components\widgets\TBreadcrumbs;


$this->params['breadcrumbs'][] = ['label'=>Yii::t('common','resource_management'),'url'=>['/resource/index']];
$this->params['breadcrumbs'][] = Yii::t('common','learning_report');
$this->params['breadcrumbs'][] = '';
?>
<style type="text/css">
  label{font-weight: 600;}
</style>
<?= html::jsFile('/static/frontend/js/underscore-min.js') ?>
<?= html::jsFile('/static/frontend/js/Chart.js') ?>
<div class="container">
    <div class="row">
 <?= TBreadcrumbs::widget([
            'tag' => 'ol',
            'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
        ]) ?>
      <div class="col-md-12 col-sm-12">
        <div class="courseInfo">
          <ul class="nav nav-tabs hotNews" role="tablist" id="myTab">
           <? for($i=0 ;$i<$count;$i++){ $r=$result[$i] ?>
            <li role="presentation"  <?if($i==0){?> class="active" <? } ?>><a href="#<?=$r['menu_code'] ?>" class="loadStaticsTable" id="<?=$r['menu_code'] ?>_id" aria-controls="<?=$r['menu_code'] ?>" role="tab" data-toggle="tab"><?=$r['menu_name'] ?></a></li>
           <? } ?>
          </ul>
          <div class="tab-content">
            <? for($i=0 ;$i<$count;$i++){ $r=$result[$i] ?>
            <div role="tabpanel" class="tab-pane <?if($i==0){?> active <? } ?>" id="<?=$r['menu_code'] ?>"></div>
            <? } ?>
            
          </div>
        </div>
      </div>
    </div>
  </div>
  
  <style>
<!--
.-query-list{
    width: 215px;
    float: left;
    display: inline-block;
}

-->
</style>
  
   <script type="text/javascript">

     function FmodalLoadData1(target, url)
	 {
	       if(url){
	           
	           $('#'+target).empty();
	           $('#'+target).load(url ,function (){
					
	               });
	       }
	  }
 


    $(function(){
//
   <? for($i=0 ;$i<$count;$i++){ $r=$result[$i] ?>
      <?if($i==0){?>
	   FmodalLoadData1("<?=$r['menu_code'] ?>","<?=Url::toRoute([$r["action_url"]])?>");
	  <? } ?>

	  $("#<?=$r['menu_code'] ?>_id").click(function(){

			tab_empty();
			FmodalLoadData1("<?=$r['menu_code'] ?>","<?=Url::toRoute([$r["action_url"]])?>");
	  });
	  
   <? } ?>


//
        });

function tab_empty(){	
	 $("#statistical_analysis").empty();
	 $("#activity_degree").empty();
	 $("#course_report").empty();
	 $("#course_study_condition_day").empty();
	 $("#course_user_score").empty();
	 $("#study_condition_day").empty();
	 $("#user_study_condition_gather").empty();
}
       
    </script>
    
    