<?php
use yii\helpers\Html;
use yii\widgets\Breadcrumbs;
use components\widgets\TLinkPager;
use yii\helpers\Url;

?>

<ul>
   <? foreach ($data as $row): ?>
	<li>
	<a href="##" onclick="save(['<?= $row['kid'] ?>','<?= $row['course_name'] ?>','111'],this)" class="btn btn-default btn-xs"><?=Yii::t('frontend', 'select')?></a>
	<h5>[<?= Yii::t('common', 'course') ?>]<?= $row['course_name'] ?></h5>
	<!-- 
	<p>完成要求: <?= $row['course_desc'] ?></p>
	 -->
	</li>
  <? endforeach; ?>
</ul>
<nav>
    <?php
      echo TLinkPager::widget([
       'id' => $page_id,
       'pagination' => $pages,
      ]);
   ?>
   
</nav>
 


<script type="text/javascript">
    $(document).ready(function () {
		
    });
    function save(arrs,node){
			$(node).attr("disabled","disabled");
			$.get("<?=Url::toRoute(['message/selected',])?>"+"?mission_id="+uuid+"&select_id="+arrs[0],function(data){
				  loadTab("<?=Url::toRoute(['message/get-course',])?>"+"?uuid="+uuid+"&page=<?=$pageNo ?>", 'courseList');
				  var cous_tmp = [
						           {
						                   kid:arrs[0],
						                   course_name:arrs[1],
						                   course_desc:arrs[2]
						           }]
				   addTaskArrays(cous_tmp); 
			});  
			
    }
</script>