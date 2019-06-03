<?php
/**
 * Created by adophper.
 * User: Administrator
 * Date: 2015/7/7
 * Time: 14:39
 */
?>
<div class="nameCard">
    <div class="boxBody">
        <h3><?= Yii::t('frontend', 'page_lesson_hot_all_type') ?></h3>
<!--        <ul class="hotCourse">-->
<!--            <li>-->
<!--                <input id="allCatalog" type="checkbox" value="0" checked>全部分类<span class="pull-right">--><?//= $count ?><!--</span>-->
<!--            </li>-->
<!--            --><?// $i = 1; ?>
<!--            --><?// foreach ($catalog as $key=>$p): ?>
<!--                <li>-->
<!--                    <input id="class_--><?//= $i ?><!--" type="checkbox" class="category_id" value="--><?//= $p['kid'] ?><!--" data-type="p">--><?//= $p['category_name'] ?><!--<span class="pull-right">--><?//=$p['count']?><!--</span>-->
<!--                </li>-->
<!--                --><?// $i++; ?>
<!--            --><?// endforeach; ?>
<!--        </ul>-->
        <? echo $category; ?>
    </div>
</div>
<script>
    $(function () {
        $("#allCatalog").on('click', function () {
            if ($(this).is(':checked') == true) {
                checkIds = '0';
                $(".category_id").attr('checked', false);
                loadInfo(url + '&type=' + course_type + "&order=" + order, 'list_panel', true);
            }
        });
        $(".category_id").on('click', function () {
            var checked = $(this).prop('checked');
            var code = $(this).attr('data-path');
            var parent = $(this).attr('data-parent');
            if (!checked) {
                var temp=$("input[data-path='" + parent + "']");
                temp.prop('checked', checked);
                $("input[data-path='" + temp.attr('data-parent') + "']").prop('checked', checked);
            }
            else {
                var temp=$("input[data-path='" + parent + "']");
                var not = $("input[data-parent='" + parent + "']:not(:checked)").length;
                if (not === 0) {
                    $("input[data-path='" + parent + "']").prop('checked', checked);
                }
                var not = $("input[data-parent='" + temp.attr('data-parent') + "']:not(:checked)").length;
                if (not === 0) {
                    $("input[data-path='" + temp.attr('data-parent') + "']").prop('checked', checked);
                }
            }

            $("input[data-path^='" + code + "']").prop('checked', checked);

            var check_length = $(".category_id:checked").length;
            if (check_length == 0) {
                $("#allCatalog").prop('checked', true);
                loadInfo(url + '&type=' + course_type + "&order=" + order, 'list_panel', true);
            } else {
                $("#allCatalog").attr('checked', false);
                checkIds = $(".category_id:checked").map(function () {
                    return this.value;
                }).get().join(',');
                loadInfo(url + "&ids=" + checkIds + '&type=' + course_type + "&order=" + order, 'list_panel', true);
            }
        });
    });
</script>