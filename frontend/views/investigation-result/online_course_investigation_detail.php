<?php


use yii\helpers\Html;
use components\widgets\TLinkPager;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use components\widgets\TBreadcrumbs;


	$this->params['breadcrumbs'][] = ['label'=>Yii::t('common','resource_management'),'url'=>['/resource/index']];
	$this->params['breadcrumbs'][] = ['label'=>Yii::t('frontend', 'inv_result_zaixiankechengguanli'),'url'=>['/resource/course/manage']];
	
	$this->params['breadcrumbs'][] = Yii::t('frontend', 'inv_result_kechengdiaochaliebiaochakan');
	$this->params['breadcrumbs'][] = '';

?>


 <div class="container">
    <div class="row">
        <?= TBreadcrumbs::widget([
            'tag' => 'ol',
            'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
        ]) ?>
      <div class="col-md-12 col-sm-12">
        <div class="courseInfo">
          <ul class="nav nav-tabs hotNews" role="tablist" id="myTab">
            <li role="presentation" class="active"><a href="#resource_score" role="tab" data-toggle="tab" class="loadActiveTable"><?=Yii::t('frontend', 'inv_result_kechengdiaochaliebiaochakan')?></a></li>
          </ul>
          <div class="tab-content">
            
            <div role="tabpanel" class="tab-pane active" id="resource_score">
              <div class=" panel-default scoreList">
                <div class="panel-body">
                  <table class="table table-bordered table-hover table-striped table-center" style="margin-top:20px;">
                    <tbody>
                      <tr>
                        <td><?=Yii::t('frontend', 'inv_result_diaochadanyuan')?></td>
                        <td><?=Yii::t('frontend', 'inv_result_zhuangtai')?></td>
                        <td><?=Yii::t('frontend', 'inv_result_fabushijian')?></td>
                        <td><?=Yii::t('frontend', 'exam_choose_caozuo')?></td>
                      </tr>
                     <? foreach ($results as $info): ?>
                      <tr>
                        <td align="left"><?=$info['title'] ?></td>
                        <td><?=Yii::t('frontend', 'inv_result_yifabu')?></td>
                        <td><?=$info['created_at']?></td>
                        <td><a href="###" onclick="online_course_show('<?=$info['kid'] ?>','<?=$info['investigation_type'] ?>')"><?=Yii::t('frontend', 'exam_xiangqing')?></a></td>
                      </tr>
                     <? endforeach; ?>
                     
                    </tbody>
                  </table>
                  
                  
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  
  <input type="hidden" id="course_id" value="<?=$course_id ?>"/>
  
    <script type="text/javascript">

    function online_course_show(kid,investigation_type){
            var course_id=$("#course_id").val();
			//0：问卷；1：投票',
    		if(investigation_type=="0"){
    			 window.location = "<?=Yii::$app->urlManager->createUrl(['investigation-result/course-survey-manage-result-survey'])?>"+"?id="+kid+"&&course_id="+course_id;
        	}else{
        		 window.location = "<?=Yii::$app->urlManager->createUrl(['investigation-result/course-survey-manage-result-vote'])?>"+"?id="+kid+"&&course_id="+course_id;
            }
   
     }

    </script>