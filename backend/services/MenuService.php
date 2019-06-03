<?php
/**
 * Created by PhpStorm.
 * User: TangMingQiang
 * Date: 3/26/15
 * Time: 5:01 PM
 */

namespace backend\services;

use common\models\framework\FwPermission;
use common\models\framework\FwRole;
use common\models\framework\FwRolePermission;
use common\models\framework\FwUser;
use common\models\treemanager\FwTreeNode;
use common\viewmodels\framework\Menu;
use common\base\BaseActiveRecord;
use common\eLearningLMS;
use common\services\framework\RbacService;
use Yii;
use yii\helpers\ArrayHelper;

class MenuService extends BaseActiveRecord{

    /**
     * 根据系统标志获取后台菜单
     * @param $system_flag
     * @return string
     */
    public static function getBackendMenu($system_flag)
    {

        if (Yii::$app->session->has("BackendMenu")) {
            return Yii::$app->session->get("BackendMenu");
        }
        else {
            $menuModel = new Menu();

            $menuStartTag = $menuModel->getMenuStartTag();

            if (!Yii::$app->user->getIsGuest()) {
                $menuNode = self::getBackendMenuNode($system_flag);
            }

            $menuCollapseButton = $menuModel->GetCollapseButton();

            $menuEndTag = $menuModel->getMenuEndTag();

            $result = $menuStartTag . $menuNode . $menuCollapseButton . $menuEndTag;

            Yii::$app->session->set("BackendMenu", $result);

            return $result;
        }

    }

    /**
     * 根据菜单子节点
     * @param $system_flag
     * @param null $parentNodeId
     * @param null $currentNodeData
     * @param int $menuLevel
     * @return string
     */
    private static function getBackendMenuNode($system_flag, $parentNodeId = null, $currentNodeData = null, $menuLevel = 1)
    {
        if ($currentNodeData == null)
            $currentNodeData = self::getBackendSubMenuData($system_flag,$parentNodeId);

        if (isset($currentNodeData) && count($currentNodeData) > 0) {
            $menuStr = "";

            foreach ($currentNodeData as $menu) {


                $menuModel = new Menu();

                $menuNodeStartTag = $menuModel->getMenuNodeStartTag();
                $menuNodeEndTag = $menuModel->getMenuNodeEndTag();

                $subParentNodeId = $menu->tree_node_id;

                $subNodeData = self::getBackendSubMenuData($system_flag,$subParentNodeId);

                if (count($subNodeData) == 0)
                {
                    $noSubNode = true;
                }
                else
                {
                    $noSubNode = false;
                }

                if ($menu->is_display == Menu::DISPLAY_FLAG_YES) {
                    $menuNodeHtml = $menuModel->GetNodeButton($noSubNode, $menu);
                    $menuLevel = $menuLevel + 1;
                }
                else
                {
                    $menuNodeHtml = "";
                }

                if ($noSubNode) {
                    //无子节点
                    $subMenuHtml = "";
                } else {
                    //有子节点
                    if ($menuLevel == 1)
                    {
                        $subMenuNodeStartTag = "";
                        $subMenuNodeEndTag = "";
                    }
                    else if ($menuLevel == 2){
                        $subMenuNodeStartTag = $menuModel->getSecondMenuNodeStartTag();
                        $subMenuNodeEndTag = $menuModel->getSubMenuNodeEndTag();
                    }
                    else
                    {
                        $subMenuNodeStartTag = $menuModel->getThirdMenuNodeStartTag();
                        $subMenuNodeEndTag = $menuModel->getSubMenuNodeEndTag();
                    }


                    $subMenuHtml = $subMenuNodeStartTag . self::getBackendMenuNode($system_flag, $parentNodeId, $subNodeData, $menuLevel) . $subMenuNodeEndTag;
                }

                $eachMenStr = $menuNodeStartTag . $menuNodeHtml . $subMenuHtml . $menuNodeEndTag;

                $menuStr = $menuStr . $eachMenStr;
            }
        }
        else
        {
            $menuStr = "";
        }

        return $menuStr;
    }

    /**
     * 根据菜单子节点数据
     * @param $system_flag
     * @param null $parentNodeId
     * @return array
     */
    private static function getBackendSubMenuData($system_flag,$parentNodeId = null)
    {
//        $permissionTableName = self::calculateTableName(FwPermission::tableName());
//        $treeNodeTableName = self::calculateTableName(FwTreeNode::tableName());
//        $rolePermissionTableName = self::calculateTableName(FwRolePermission::tableName());

        $userId = Yii::$app->user->getId();

//        $userModel = FwUser::findOne($userId);

        $rbacService = new RbacService();
        $selected_keys = $rbacService->getRoleListIncludeSpecialByUserId($userId);

        $kids = "";
        if ($selected_keys != null) {
            foreach ($selected_keys as $key) {
                if (!empty($key)) {
                    $kids = $kids . "'" . $key . "',";
                }
            }
        }

        $kids = rtrim($kids,",");

        if ($kids == "")
            $results = null;
        else {
            $permissionWhereModel = new FwRolePermission();
            $queryPermissionWhereSql = $permissionWhereModel->find(false)
                ->andFilterWhere(['=', 'status', FwPermission::STATUS_FLAG_NORMAL])
                ->andWhere(BaseActiveRecord::getQuoteColumnName("role_id") . " in (" . $kids . ")")
                ->select("permission_id")
                ->createCommand()
                ->getRawSql();

            $permissionWhere = FwPermission::tableName() . "." . self::getQuoteColumnName("kid") . " in (" . $queryPermissionWhereSql . ") ";

            if (isset($parentNodeId) && $parentNodeId != null)
                $parentWhere = FwTreeNode::tableName() . "." . self::getQuoteColumnName("parent_node_id") . " = '" . $parentNodeId . "' ";
            else

                $parentWhere = FwTreeNode::tableName() . "." . self::getQuoteColumnName("parent_node_id") . " is null ";

//        $sql = "select treeNode.tree_node_code,treeNode.tree_node_name,treeNode.parent_node_id,treeNode.tree_level,"
//            . "permission.* from " .$permissionTableName." permission "
//            . "inner join " .$treeNodeTableName." treeNode on permission.tree_node_id = treeNode.kid "
//            . "and permission.is_deleted = '0' and treeNode.is_deleted = '0' "
//            . "and treeNode.status = '1' and permission.system_flag='".$system_flag."' "
//            . "and permission.permission_type = '1' "
//            . "and " . $parentWhere
//            . "and " . $permissionWhere
//            . "order by treeNode.sequence_number asc";
//        $results = eLearningLMS::queryAll($sql);
//
            $permissionModel = new FwPermission();
            $query = $permissionModel->find(false)
                ->innerJoin(FwTreeNode::tableName(),
                    FwPermission::tableName() . "." . self::getQuoteColumnName("tree_node_id") . " = " . FwTreeNode::tableName() . "." . self::getQuoteColumnName("kid") .
                    " and " . FwTreeNode::tableName() . "." . self::getQuoteColumnName("is_deleted") . " = '" . FwTreeNode::DELETE_FLAG_NO . "'")
                ->andFilterWhere(['=', FwTreeNode::realTableName() . ".status", FwTreeNode::STATUS_FLAG_NORMAL])
                ->andFilterWhere(['=', 'system_flag', $system_flag])
                ->andFilterWhere(['=', 'permission_type', FwPermission::PERMISSION_TYPE_MENU])
                ->andWhere($parentWhere)
                ->andWhere($permissionWhere)
                ->addOrderBy(['sequence_number' => SORT_ASC])
                ->select([
                    FwPermission::tableName() . '.*',
                    'tree_node_code' => "tree_node_code",
                    'tree_node_name' => "tree_node_name",
                    'parent_node_id' => "parent_node_id",
                    'tree_level' => "tree_level",
                ]);

            $results = $query->all();

        }
        return $results;
    }

}