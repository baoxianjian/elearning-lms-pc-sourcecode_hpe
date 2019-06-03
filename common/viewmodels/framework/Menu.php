<?php
namespace common\viewmodels\framework;

use Yii;
use yii\base\Model;
use yii\helpers\Url;


/**
 * This is the viewmodel class for Menu.
 *
 * @property string $kid
 * @property string $tree_node_id
 * @property int $tree_level
 * @property string $parent_node_id
 * @property string $action_url
 * @property string action_class
 * @property string $action_parameter
 * @property string $action_type
 * @property string $action_tip
 * @property string $action_target
 * @property string $description
 * @property string $system_flag
 * @property string $is_display
 *
 */
class Menu extends Model
{
    public $tree_node_code;
    public $tree_node_name;
    public $tree_level;
    public $kid;
    public $tree_node_id;
    public $parent_node_id;
    public $system_flag;
    public $action_url;
    public $action_class;
    public $action_type;
    public $action_tip;
    public $action_parameter;
    public $action_target;
    public $description;
    public $is_display;
    public $i18n_flag;

    const ACTION_TYPE_ACTION = "1";
    const ACTION_TYPE_URL = "0";

    const DISPLAY_FLAG_NO = "0";
    const DISPLAY_FLAG_YES = "1";


    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['tree_node_code', 'tree_node_name', 'parent_node_id', 'kid',
                'tree_node_id', 'system_flag', 'action_target','action_url',
                'action_parameter','action_target','action_class','action_type','description','is_display','i18n_flag'], 'string'],
            [['tree_level'], 'integer'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'tree_node_code' => Yii::t('common', 'tree_node_code'),
            'tree_node_name' => Yii::t('common', 'tree_node_name'),
            'tree_level' => Yii::t('common', 'tree_level'),
            'kid' => Yii::t('common', 'permission_id'),
            'tree_node_id' => Yii::t('common', 'tree_node_id'),
            'parent_node_id' => Yii::t('common', 'parent_node_id'),
            'action_url' => Yii::t('common', 'action_url'),
            'action_class' => Yii::t('common', 'action_class'),
            'action_target' => Yii::t('common', 'action_target'),
            'action_parameter' => Yii::t('common', 'action_parameter'),
            'action_type' => Yii::t('common', 'action_type'),
            'description' => Yii::t('common', 'description'),
            'system_flag' => Yii::t('common', 'system_flag'),
            'is_display' => Yii::t('common', 'is_display'),
            'i18n_flag' => Yii::t('common', 'i18n_flag'),
        ];
    }


    public function GetCollapseButton()
    {
        $menuTitle = Yii::t('common', 'menu_title');
        $menuCollapse = Yii::t('common', 'menu_collapse');
        $collapseButtonHtml = "<a id='closeIt' href='javascript:;' title='". $menuTitle . "'>".$menuCollapse."</a>";
        return $collapseButtonHtml;
    }

    public function GetNodeButton($noSubNode = false,$menu)
    {
        if (isset($menu->tree_node_name) && $menu->tree_node_name != "") {
            if (!empty($menu->i18n_flag)) {
                $menu->tree_node_name = Yii::t('data', $menu->i18n_flag);
            }

            $classTag = "";
            if (isset($menu->action_class) && $menu->action_class != "") {
                $classTag = "<i class='fa " . $menu->action_class . " fa-fw'></i> ";
            }


            $endSpanTag = "";
            if (!$noSubNode) {
                $endSpanTag = "<span class='fa arrow'></span>";
            }

            if (isset($menu->action_tip) && $menu->action_tip != "") {
                $titleTip = "title='" . $menu->action_tip . "'";
            }
            else {
                $titleTip = "title='" . $menu->tree_node_name . "'";
            }

            $targetTag = "";
            if (isset($menu->action_target) && $menu->action_target != "") {
                $targetTag = "target='" . $menu->action_target . "'";
            }


            if (isset($menu->action_url) && $menu->action_url != "" && $menu->action_url != "#") {

                if ($menu->action_type == self::ACTION_TYPE_URL) {
                    $url = $menu->action_url;
                } else {
                    if ($menu->action_parameter != "") {
                        $urlArray = [];
                        array_push($urlArray,$menu->action_url);
                        $parameter = json_decode($menu->action_parameter,true);




                        if (isset($parameter) && $parameter != null) {
                            $urlArray = array_merge($urlArray, $parameter);
                        }
                        $url = Url::toRoute($urlArray);
                    } else {
                        $url = Url::toRoute([$menu->action_url]);
                    }
                }
//                javascript:LoadContentPage();
                return  "<a href='" . $url ."' " . $targetTag . " " . $titleTip . ">" . $classTag . $menu->tree_node_name . $endSpanTag . "</a>";
            } else {
                return "<a href='#" . $menu->tree_node_code . "' " . $targetTag . " " . $titleTip . ">" . $classTag . $menu->tree_node_name . $endSpanTag . "</a>";
            }
        }
        else {
            return "";
        }
    }

    public function getSecondMenuNodeStartTag()
    {
        return "<ul class='nav nav-second-level'>";
    }

    public function getThirdMenuNodeStartTag()
    {
        return "<ul class='nav nav-third-level'>";
    }


    public function getSubMenuNodeEndTag()
    {
        return "</ul>";
    }

    public function getMenuNodeStartTag()
    {
        return "<li>";
    }


    public function getMenuNodeEndTag()
    {
        return "</li>";
    }


    public function getMenuStartTag()
    {
        $sidebarDiv = "<div class='navbar-default sidebar' role='navigation'>";
        $navbarDiv = "<div class='sidebar-nav navbar-collapse'>";
        $sidMenuUl = "<ul class='nav' id='side-menu'>";

        return $sidebarDiv . $navbarDiv . $sidMenuUl;
    }


    public function getMenuEndTag()
    {
        $sidebarDiv = "</div>";
        $navbarDiv = "</div>";
        $sidMenuUl = "</ul>";

        return $sidMenuUl . $navbarDiv . $sidebarDiv;
    }

}
