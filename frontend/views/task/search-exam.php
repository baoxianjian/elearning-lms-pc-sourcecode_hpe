<?php
use components\widgets\TLinkPager;
use yii\helpers\Html;
use yii\helpers\Url;
?>
<ul>
	<? if ($data):?>
		<? foreach ($data as $row): ?>
			<li>
				<a id="<?= $row['kid'] ?>" href="javascript:void(0);" onclick="selectExam(this,'<?= $row['kid'] ?>','<?= Html::encode(addslashes($row['title'])) ?>')" class="btn btn-default btn-xs"><?=Yii::t('frontend', 'select')?></a>
				<h5>[<?= Yii::t('frontend', 'exam') ?>]<?= Html::encode($row['title']) ?></h5>
			</li>
		<? endforeach; ?>
	<? else:?>
		<li style="display: block;width: 100%;text-align: center">
			<?= Yii::t('frontend', 'unmeet_result') ?>
		</li>
	<?endif;?>
</ul>
<nav>
	<?php
	echo TLinkPager::widget([
		'id' => 'page2',
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
	function selectExam(obj, id, title) {
		if ($(obj).hasClass('btn-success')) {
			return false;
		}

		$(obj).addClass('btn-success').html('<?=Yii::t('frontend', 'select_yes')?>').attr("disabled", "disabled");
		temp_select_exam_arr.push(id);
		temp_select_exam_id_arr.push(id);
		var temp = title;
		if (GetLength(title) > 32) {
			temp = cutstr(title, 32);
		}
		var exam_tmp = [
			{
				kid: id,
				title: title,
				str_title: temp
			}];
		addTaskArrays(exam_tmp);
	}
</script>