<?php
/**
 * Created by PhpStorm.
 * User: tangming
 * Date: 7/10/2015
 * Time: 5:53 PM
 */
use yii\helpers\Html;
use yii\helpers\Url;
use common\crpty\AES;
use common\models\learning\LnFiles;

$time = time();
$str = base64_encode($file_id . '|||' . $time);
$aes = new AES();
$hash = $aes->encrypt($str);
?>
<?= Html::hiddenInput("currentModResId",$modResId,['id'=>'currentModResId'])?>
<?= Html::hiddenInput("currentCoursewareId",$coursewareId,['id'=>'currentCoursewareId'])?>
<?= Html::hiddenInput("currentCourseId",$courseId,['id'=>'currentCourseId'])?>
<?= Html::hiddenInput("currentComponentCode",$componentCode,['id'=>'currentComponentCode'])?>

<!--<script src="/static/frontend/js/jquery.media.js"></script>-->
<!--                学习课程达到45分钟以上为完成-->
	<?php 
	$file_path=null;
	if($supportEncryptPdfVer!=null && intval($supportEncryptPdfVer)<21){
		$file = LnFiles::findOne($file_id);
		$file_path = $file->file_path;
		if (!file_exists($_SERVER['DOCUMENT_ROOT'] .$file_path)) { //检查文件是否存在
			echo Yii::t('common','file_not_found');
			$file_path=null;
			die;
		}
	}
	?>
    <iframe src="/components/pdfplayer/web/viewer.html?file=<?= urlencode( $file_path!=null?$file_path:Url::toRoute( ['player/pdf-view','system_key'=>$system_key,'access_token'=>$access_token, 'id'=>$file_id, 'hash'=>$hash[1]] ) ) ?>" width="100%" height="100%" style="position: fixed"></iframe>
