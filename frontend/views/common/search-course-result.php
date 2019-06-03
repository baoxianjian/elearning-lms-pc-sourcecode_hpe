<?php
use yii\helpers\Html;
use yii\widgets\Breadcrumbs;
use components\widgets\TLinkPager;
use yii\helpers\Url;

?>

<ul>
	<? foreach ($data as $row): ?>
		<li>
			<a id="<?= $row['kid'] ?>" href="javascript:void(0);" onclick="selectCourse(this,'<?= $row['kid'] ?>','<?= Html::encode(addslashes($row['course_name'])) ?>')" class="btn btn-default btn-xs"><?= Yii::t('frontend', 'select') ?></a>
			<h5>[<?= Yii::t('common', 'course') ?>]<?= Html::encode($row['course_name']) ?></h5>
		</li>
	<? endforeach; ?>
</ul>
<nav id="selectCoursePage">
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
				$("#" + $(this).val()).addClass('btn-success').html('<?= Yii::t('frontend', 'select_yes') ?>').attr("disabled", "disabled");
			});
		});
	function selectCourse(obj, id, name) {
		if ($(obj).hasClass('btn-success')) {
			return false;
		}

		$(obj).addClass('btn-success').html('<?= Yii::t('frontend', 'select_yes') ?>').attr("disabled", "disabled");
		temp_select_course_arr.push(id);
		temp_select_course_id_arr.push(id);
		var temp=name;
		if(GetLength(name)>32)
		{
			temp=cutstr(name,32);
		}
		var cous_tmp = [
			{
				kid: id,
				course_name: name,
				str_name: temp
			}];
		addTaskArrays(cous_tmp);
	}

    $(function(){
        $("#selectCoursePage .pagination").on('click', 'a', function(e){
            e.preventDefault();
            ajaxGet($(this).attr('href'), "courseList");
        });
       
    });

	function GetLength(str) {
		var realLength = 0, len = str.length, charCode = -1;
		for (var i = 0; i < len; i++) {
			charCode = str.charCodeAt(i);
			if (charCode >= 0 && charCode <= 128) realLength += 1;
			else realLength += 2;
		}
		return realLength;
	};

	/**
	 * js截取字符串，中英文都能用
	 * @param str：需要截取的字符串
	 * @param len: 需要截取的长度
	 */
	function cutstr(str, len) {
		var str_length = 0;
		var str_len = 0;
		str_cut = new String();
		str_len = str.length;
		for (var i = 0; i < str_len; i++) {
			a = str.charAt(i);
			str_length++;
			if (escape(a).length > 4) {
				//中文字符的长度经编码之后大于4
				str_length++;
			}
			str_cut = str_cut.concat(a);
			if (str_length >= len) {
				str_cut = str_cut.concat("...");
				return str_cut;
			}
		}
		//如果给定字符串小于指定长度，则返回源字符串；
		if (str_length < len) {
			return str;
		}
	}
</script>