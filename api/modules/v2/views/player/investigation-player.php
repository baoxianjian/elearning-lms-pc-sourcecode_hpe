<?php
/**
 * Created by PhpStorm.
 * User: tangming
 * Date: 9/9/2015
 * Time: 12:54 PM
 */
use yii\helpers\Url;

?>
  <div id="investigation_content_list">

  </div>
   <script type="text/javascript">

         $(function(){
//
   		      var url= "<?=Url::toRoute(['investigation/course-play-investigation',])?>"+"?modResId=<?=$modResId?>&courseRegId=<?=$courseRegId?>&courseId=<?=$courseId?>&courseCompleteProcessId=<?=$courseCompleteProcessId?>&courseCompleteFinalId=<?=$courseCompleteFinalId?>";
console.log(url);
        	 $("#investigation_content_list").load(url);

        	 //
        });


  </script>            