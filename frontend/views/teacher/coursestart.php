<?php
/**
 * User: zhanglei
 * Date: 2015/8/17
 * Time: 13:02
 */
use common\models\learning\LnCourse;
use yii\helpers\Url;
use common\helpers\TTimeHelper;
use common\helpers\TStringHelper;
use yii\helpers\Html;
use components\widgets\TLinkPager;

?>

<div class="panel-default scoreList">
	<div class="panel-default scoreList pathBlock offlineCourse">
		<div role="tab" id="headingOne">
			<ul class="panel-collapse collapse in" role="tabpanel" aria-labelledby="headingOne" id="collapseExample">
				<?php
				if (!empty($course)) :
					foreach($course as $t):
				?>
					<li class="pathStep"><span class="step ">【<?=$t->getCourseTypeText()?>】<?=$t->course_name ?><?if($t->open_start_time): ?>【<?=date('Y-m-d', $t->open_start_time) ?>】<?endif;?></span>
					<span class="stepTime pull-right">
						<? if($t->course_type === LnCourse::COURSE_TYPE_FACETOFACE): ?>
							<a href="<?=Yii::$app->urlManager->createUrl(['teacher/edit','id'=>$t->kid])?>" style="margin-right: 10px;"><?=Yii::t('frontend', 'editor_text')?></a>
						<? endif; ?>
						<a href="<?=Yii::$app->urlManager->createUrl(['teacher/detail','id'=>$t->kid])?>"><?=Yii::t('common', 'art_datail')?></a>
					</span>
						<div class="pathTask">
							<table>
								<tr>
									<td class="col"><span><strong><?=Yii::t('frontend', 'date_text')?>:</strong> <? if($t->open_start_time): ?><?=date('Y-m-d', $t->open_start_time) ?><? elseif($t->start_time): ?><?=TTimeHelper::toDate($t->start_time)?><? endif; ?></span></td>
									<td class="col"><span><strong><?=Yii::t('common', 'course_default_credit')?>:</strong> <?=$t->default_credit?></span></td>
									<td class="col"><span><strong><?=Yii::t('common', 'class_hour')?>:</strong> <?=$t->course_period ?></span></td>
								</tr>
								<tr>
									<td class="col"><span><strong><?=Yii::t('common', 'course_language')?>:</strong> <?=$t->getDictionaryText('course_language',$t->course_language)?></span></td>
									<td class="col"><span><strong><?=Yii::t('frontend', 'enroll')?>:</strong> <?=$t->register_number ?><?= Yii::t('frontend', 'people') ?></span></td>
									<td class="col"><span><strong><?=Yii::t('common', 'lecturer')?>:</strong><? if($teacher=$t->getLnCourseTeacher($t->kid)):?><?if($teacher): foreach ($teacher as $te): echo $te['teacher_name']." " ; endforeach; endif;?><?endif; ?></span></td>
								</tr>
								<tr>
									<td colspan="3" class="attach"><span><strong><?=Yii::t('frontend', 'question_content')?>:</strong>
											<?=TStringHelper::subStr($t->course_desc_nohtml,70,'utf-8',0,'...') ?>
										</span>
									</td>
								</tr>
							</table>
						</div>
						<div class="labelArea">
							<? if($tags =$t->getLnCourseTag($t->kid)):?>
								<? foreach ($tags as $tag): ?>
									<span class="label label-info"><?=$tag['tag_value'] ?></span>
								<? endforeach; endif;?>
						</div></li>
				<?endforeach; else :?>
					<div class="centerBtnArea noData " style="float:none">
						<i class="glyphicon glyphicon-calendar"></i>
						<p><?=Yii::t('common', 'no_data')?></p>
					</div>
				<?endif; ?>
			</ul>
			<nav>
				<?php
				if (!empty($page)) {
					echo TLinkPager::widget([
						'id' => 'page2',
						'pagination' => $page,
						'displayPageSizeSelect' => false
					]);
				}
				?>
			</nav>
		</div>
	</div>
</div>
<script>
	$(function(){
		$("#courseIntro .pagination").on('click', 'a', function(e){
			e.preventDefault();
			ajaxGet($(this).attr('href'), "courseIntro");
		});
		$("#courseIntro .preview").on('click', function(e){
			e.preventDefault();
			preView('previewModal', $(this).attr('href'));
		});

	});
</script>