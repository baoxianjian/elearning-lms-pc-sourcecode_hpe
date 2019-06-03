  <?php
/**
 * User: zhanglei
 * Date: 2015/8/12
 * Time: 13:02
 */
use components\widgets\TBreadcrumbs;
use yii\helpers\Url;
use common\helpers\TTimeHelper;
use common\helpers\TStringHelper;
use yii\helpers\Html;
use components\widgets\TLinkPager;
?>
<!-- 全部签到记录的弹出窗口 -->
<div class="header">
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    <h4 class="modal-title" id="myModalLabel"><?= Yii::t('frontend', 'signin_all_data') ?></h4>
</div>
<div class="content">
    <div class="panel-body">
        <div class="actionBar">
          <form class="form-inline pull-left">
            <div class="form-group">
              <div class="form-group field-courseservice-course_type ">
                <select id="courseservice-course_type" class="form-control" name="CourseService[course_type]">
                  <option value="" <?php if(!$param['signstatus']){echo 'selected';}?>><?= Yii::t('frontend', 'signin_all') ?></option>
                  <option value="1" <?php if($param['signstatus']==1){echo 'selected';}?>><?= Yii::t('frontend', 'signined') ?></option>
                  <option value="2" <?php if($param['signstatus']==2){echo 'selected';}?>><?= Yii::t('frontend', 'signined_not') ?></option>
                </select>
              </div>
            </div>
          </form>
          <form class="form-inline pull-left">
            <div class="form-group">
              <div class="form-group field-courseservice-course_type ">
                <select id="courseservice-course_date" class="form-control" name="CourseService[course_type]">
                <?php if((time()<$param['open_start_time'])||(time()>$param['open_end_time'])){?>
                    <option value="<?php echo strtotime(TTimeHelper::getCurrentDayStart()) ;?>"  selected><?= Yii::t('frontend', 'today') ?></option>
                <?php }?>
                  <?php  while ($param['open_start_time']<$param['open_end_time']){?>
                    <option value="<?php echo $param['open_start_time'];?>" <?php if($param['time']==$param['open_start_time']){echo 'selected' ;}?>><?php echo date('m月d日',$param['open_start_time']); ?></option>
                    <?php $param['open_start_time']+= 24*60*60 ;}?>
                </select>
              </div>
            </div>
          </form>
          <a class="btn btn-sm pull-right" href="<?=Yii::$app->urlManager->createUrl(['teacher/detail-sign-down'])?>?id=<?=$id?>" target="_bank"><?= Yii::t('frontend', 'export_all_records') ?></a>
        </div>
        <table class="table table-bordered table-hover table_teacher">
          <tbody>
            <tr>
              <td><?= Yii::t('common', 'real_name') ?></td>
              <td><?= Yii::t('frontend', 'department') ?></td>
              <td><?php echo date('m月d日',$param['time']);?></td>
            </tr>
           <?php if($students): foreach ($students as $s):?>
            <tr>
              <td><?php echo $s['real_name'] ;?></td>
              <td><?
                      $dictionaryService = new \common\services\framework\DictionaryService();
                      if(isset($s['location'])): echo $dictionaryService->getDictionaryNameByCode("location",$s['location']); endif;?>
                      <?=$s['orgnization_name']?>
                  </td>
              <td><?php if($s['sign_time']){echo date('H:i',$s['sign_time']);}else {echo Yii::t('frontend', 'signined_not');}?></td>
            </tr>
         <?php endforeach; endif;?>

          </tbody>
        </table>
        <div class="col-md-12">
              <nav id="detailSignPaget">
                     <?php
                      echo TLinkPager::widget([
                       'id' => 'page',
                       'pagination' => $pages,
                       'displayPageSizeSelect'=>false
                      ]);
                    ?>
              </nav>
          </div>
    </div>
</div>
   
 
  <script>
   $("document").ready(function (){
	var recurl = '<?=Yii::$app->urlManager->createUrl(['teacher/detail-sign-record'])?>?id=<?=$id?>';

 		 $('#courseservice-course_type').bind('change', function() {
 			 updateRecord() ;
	   	})
	   	 $('#courseservice-course_date').bind('change', function() {
 			 updateRecord() ;
	   	})
	   	function updateRecord(){
		   var signstatus = $('#courseservice-course_type').val();
		   var time = $('#courseservice-course_date').val();
		   url =  recurl + "&signstatus="+signstatus+"&signtime="+time
		   ajaxGet(url, "allSignIn");
		 }
   });
   
    $(function(){
        $("#detailSignPaget .pagination").on('click', 'a', function(e){
            e.preventDefault();
            ajaxGet($(this).attr('href'), "allSignIn");
        });
  
    });

    
</script>
                