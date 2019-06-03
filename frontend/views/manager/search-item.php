<?php
use components\widgets\TLinkPager;
use yii\helpers\Html;
use yii\helpers\Url;
?>
<ul>
	<? foreach ($data as $row): ?>
		<li>
			<a id="<?= $row['kid'] ?>" href="javascript:void(0);" onclick="selectCourse(this,'<?= $row['kid'] ?>','<?= Html::encode(addslashes($row['course_name'])) ?>')" class="btn btn-default btn-xs"><?=Yii::t('frontend', 'select')?></a>
			<h5>[<?= Yii::t('common', 'course') ?>]<?= Html::encode($row['course_name']) ?></h5>
		</li>
	<? endforeach; ?>
</ul>
<nav>
	<?php
	echo TLinkPager::widget([
		'id' => $page_id,
		'pagination' => $pages,
		'maxButtonCount'=>4,
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
	function selectCourse(obj, id, name) {
		if ($(obj).hasClass('btn-success')) {
			return false;
		}

		$(obj).addClass('btn-success').html('<?=Yii::t('frontend', 'select_yes')?>').attr("disabled", "disabled");
		temp_select_course_arr.push(id);
		temp_select_course_id_arr.push(id);
		var temp = name;
		if (GetLength(name) > 32) {
			temp = cutstr(name, 32);
		}
		var cous_tmp = [
			{
				kid: id,
				course_name: name,
				str_name: temp
			}];
		addTaskArrays(cous_tmp);
	}
</script>