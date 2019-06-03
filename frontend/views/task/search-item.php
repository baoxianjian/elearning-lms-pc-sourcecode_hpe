<?php
use common\models\learning\LnCourse;
use components\widgets\TLinkPager;
use yii\helpers\Html;

?>
<table class="table table-hover table-center">
	<tbody>
	<tr>
		<td width="15%"><?= Yii::t('common', 'action') ?></td>
		<td width="45%"><?= Yii::t('common', 'course_name') ?></td>
		<td width="40%"><?= Yii::t('frontend', 'audience') ?></td>
	</tr>
	<? if ($data):?>
		<? foreach ($data as $row): ?>
			<tr>
				<td><a id="<?= $row['kid'] ?>" href="javascript:void(0);" onclick="selectCourse(this,'<?= $row['kid'] ?>','<?= Html::encode(addslashes($row['course_name'])) ?>','<?= $row->course_type ?>','<?= $row['audienceName'] ? '2' : '1' ?>')" class="btn btn-default btn-xs" style="white-space:normal "><?=Yii::t('frontend', 'select')?></a></td>
				<td><label class="preview" title="<?= Html::encode($row['course_name'])?>" >[<?= $row->course_type === LnCourse::COURSE_TYPE_ONLINE ? Yii::t('common', 'online') : Yii::t('common', 'face-to-face') ?>]<?= Html::encode($row['course_name'])?></label></td>
				<td><?= $row['audienceName'] ? Html::encode($row['audienceName']) : '-' ?></td>
			</tr>
		<? endforeach;?>
	<? else:?>
		<tr>
			<td colspan="3"><?= Yii::t('frontend', 'unmeet_result') ?></td>
		</tr>
	<?endif;?>
	</tbody>
</table>
<nav style="float:right; margin:0 10px;">
	<?php
	echo TLinkPager::widget([
		'id' => $page_id,
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
	function selectCourse(obj, id, name, type, flag) {
		if ($(obj).hasClass('btn-success')) {
			return false;
		}
		if (auFlag === '0') {
			auFlag = flag;
		}
		else if (auFlag !== flag) {
			app.showMsg("<?= Yii::t('frontend', 'task_item_audience_and_other_not_push')?>");
			return false;
		}
		else if (auFlag === '2') {
			app.showMsg("<?= Yii::t('frontend', 'task_item_audience_course_single_push')?>");
			return false;
		}

		$(obj).addClass('btn-success').html('<?=Yii::t('frontend', 'select_yes')?>').attr("disabled", "disabled");
		temp_select_course_arr.push(id);
		temp_select_course_id_arr.push(id);
		var temp = name;
		if (GetLength(name) > 32) {
			temp = cutstr(name, 32);
		}
		var type_name='';
		if (type === '<?=LnCourse::COURSE_TYPE_ONLINE?>') {
			type_name = '<?=Yii::t('common', 'online')?>';
		}
		else {
			type_name = '<?=Yii::t('common', 'face-to-face')?>';
		}

		var cous_tmp = [
			{
				kid: id,
				course_name: name,
				str_name: temp,
				course_type: type_name
			}];
		addTaskArrays(cous_tmp);
	}
</script>