<?php
use components\widgets\TLinkPager;
use yii\helpers\Url;
?>
<ul>
   <? foreach ($data as $row): ?>
	<li>
		<a id="<?= $row['kid'] ?>" href="javascript:void(0);" onclick="selectSurvey(this,'<?= $row['kid'] ?>','<?= Html::encode(addslashes($row['title'])) ?>')" class="btn btn-default btn-xs"><?=Yii::t('frontend', 'select')?></a>
		<h5>[<?= Yii::t('common', 'investigation') ?>]<?= Html::encode($row['title']) ?></h5>
	</li>
  <? endforeach; ?>
</ul>
<nav>
	<?php
	echo TLinkPager::widget([
		'id' => 'page3',
		'pagination' => $pages,
		'maxButtonCount'=>6,
	]);
	?>
</nav>
<script type="text/javascript">
	$(document).ready(
		function () {
			$(".task_id").each(function () {
				$("#" + $(this).val()).addClass('btn-success').html('<?=Yii::t('frontend', 'select_yes')?>').attr("disabled", "disabled");
			});
		});
	function selectSurvey(obj, id, title) {
		if ($(obj).hasClass('btn-success')) {
			return false;
		}

		$(obj).addClass('btn-success').html('<?=Yii::t('frontend', 'select_yes')?>').attr("disabled", "disabled");
		temp_select_survey_arr.push(id);
		temp_select_survey_id_arr.push(id);
		var temp = title;
		if (GetLength(title) > 32) {
			temp = cutstr(title, 32);
		}
		var survey_tmp = [
			{
				kid: id,
				title: title,
				str_title: temp
			}];
		addTaskArrays(survey_tmp);
	}
</script>