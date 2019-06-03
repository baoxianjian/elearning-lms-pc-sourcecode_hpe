<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/3/31
 * Time: 11:04
 */

?>
<div id="jsTree_tree" class="demo jstree jstree-1 jstree-default"></div>
<script>
    var allTreeNodeId = '<?=join(',', $allTreeNodeId)?>';
    $(function(){
        var jstree = $('#jsTree_tree');
        jstree.bind("loaded.jstree",function(e,data){
            jstree.jstree("open_all");
        }).jstree({'core' : {
            'multiple' : false,
            'data' : [
                <?php
                if (!empty($result)){
                ?>
                { "id" : "-1", "parent" : "#", "text" : "<?=$TreeTypeName?>" },
                <?php
                foreach ($result as $v){
                ?>
                { "id" : "<?=$v['kid']?>", "parent" : "<?=$v['parent_node_id']=='#'?'-1':$v['parent_node_id']?>", "text" : "<?=$v['tree_node_name']?>" },
                <?php
                }
                }else{
                ?>
                { "id" : "-1", "parent" : "#", "text" : "<?=$TreeTypeName?>"}
                <?php
                }
                ?>
            ]
        }});
        jstree.on("changed.jstree", function (e, data) {
            console.log(data.selected);
            var treeNodeId = data.selected;
            if (treeNodeId == '-1'){
                return ;
            }
            $("#tree").val(treeNodeId);
            getOrgnizationUserList();
        });
    })
</script>