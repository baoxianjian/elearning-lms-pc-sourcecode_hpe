<?php
/**
 * Created by PhpStorm.
 * User: TangMingQiang
 * Date: 11/29/15
 * Time: 8:08 PM
 */

namespace common\services\framework;


use common\models\framework\FwCompanyMenu;
use Yii;
use yii\helpers\Url;

class CompanyMenuService extends FwCompanyMenu
{

    /**
     * 根据菜单类型获取企业个性化菜单
     * @param $companyId
     * @param $menuType
     * @return array
     */
    public function getCompanyMenuByType($companyId, $menuType)
    {
        if (!empty($companyId)) {
            $sessionKey = "CompanyMenu_" . $companyId . "_" . $menuType;
            if (Yii::$app->session->has($sessionKey)) {
                return Yii::$app->session->get($sessionKey);
            } else {
                $tableName = FwCompanyMenu::realTableName();


                $sql = "select a.* from " . $tableName . " a, (select cm.menu_code,min(cm.share_flag) as sp_share_flag from "
                    . $tableName . " cm where cm.is_deleted='0' and cm.menu_type = '" . $menuType . "' and (cm.company_id = '" . $companyId . "' or cm.company_id is null) "
                    . "group by cm.menu_code) b "
                    . "where a.menu_code = b.menu_code and a.share_flag = b.sp_share_flag and a.is_deleted='0' and a.status='1' "
                    . "and a.menu_type = '" . $menuType . "' and (a.company_id = '" . $companyId . "' or a.company_id is null) order by a.sequence_number asc";

                $model = new FwCompanyMenu();
                $result = $model->findBySql($sql)->all();
//        $result = eLearningLMS::queryAll($sql);


                if (!empty($result) && count($result) > 0) {
                    foreach ($result as $single) {
                        $i18nFlag = $single->i18n_flag;
                        if (!empty($i18nFlag)) {
                            $single->menu_name = Yii::t('data', $i18nFlag);
                        }
                    }
                }

                Yii::$app->session->set($sessionKey, $result);

                return $result;
            }
        }
        else {
            return null;
        }
    }

    /**
     * 清除企业菜单缓存
     */
    public function clearCompanyMenuSession()
    {
        $companyId = Yii::$app->user->identity->company_id;

        $menuType = "portal";
        $sessionKey = "CompanyMenu_" . $companyId . "_" . $menuType;
        if (Yii::$app->session->has($sessionKey)) {
             Yii::$app->session->remove($sessionKey);
        }


        $menuType = "report";
        $sessionKey = "CompanyMenu_" . $companyId . "_" . $menuType;
        if (Yii::$app->session->has($sessionKey)) {
             Yii::$app->session->remove($sessionKey);
        }

        $menuType = "tool-box";
        $sessionKey = "CompanyMenu_" . $companyId . "_" . $menuType;
        if (Yii::$app->session->has($sessionKey)) {
            Yii::$app->session->remove($sessionKey);
        }


        $menuType = "portal-menu";
        $sessionKey = "CompanyMenu_" . $companyId . "_" . $menuType;
        if (Yii::$app->session->has($sessionKey)) {
            Yii::$app->session->remove($sessionKey);
        }

        $sessionKey = "CompanyPortalMenu_" . $companyId;
        if (Yii::$app->session->has($sessionKey)) {
            Yii::$app->session->remove($sessionKey);
        }
    }

    /**
     * 获取企业个性化门户菜单
     * @param $companyId
     * @return mixed|string
     */
    public function getCompanyPortalMenu($companyId)
    {
        if (!empty($companyId)) {
            $sessionKey = "CompanyPortalMenu_" . $companyId;
            if (Yii::$app->session->has($sessionKey)) {
                return Yii::$app->session->get($sessionKey);
            } else {
                $menuUrl = "";

                $portalMenu = $this->getCompanyMenuByType($companyId, "portal");

                if (!empty($portalMenu)) {
                    $menuUrl .= "<div class='container fullLength'>";
                    $menuUrl .= "<ul class='pageMenu'>";

                    foreach ($portalMenu as $menu) {
                        $str = "";
                        $actionClass = $menu->action_class;
                        $actionIcon = $menu->action_icon;
                        $actionTip = $menu->action_tip;
                        $actionName = $menu->menu_name;
                        $actionTarget = $menu->action_target;
                        $actionUrl = $menu->action_url;
                        $actionType = $menu->action_type;
                        $actionParameter = $menu->action_parameter;


                        if (!empty($actionClass)) {
                            $str .= "<li style='" . $actionClass . "'>";
                        } else {
                            $str .= "<li>";
                        }

                        if (!empty($actionIcon)) {
                            $str .= $actionIcon;
                        }


                        if (!empty($actionTip)) {
                            $titleTip = "title='" . $this->$actionTip . "' ";
                        } else {
                            $titleTip = "title='" . $actionName . "' ";
                        }

                        if (!empty($actionTarget)) {
                            $targetTag = "target='" . $actionTarget . "' ";
                        } else {
                            $targetTag = "";
                        }


                        if (!empty($actionUrl)) {
                            if ($actionType == FwCompanyMenu::ACTION_TYPE_URL) {
                                $menuSubUrl = $actionUrl;
                            } else {
                                if (!empty($actionParameter)) {
                                    $urlArray = [];
                                    array_push($urlArray, $actionUrl);
                                    $parameter = json_decode($actionParameter, true);


                                    if (isset($parameter) && $parameter != null) {
                                        $urlArray = array_merge($urlArray, $parameter);
                                    }
                                    $menuSubUrl = Url::toRoute($urlArray);
                                } else {
                                    $menuSubUrl = Url::toRoute([$actionUrl]);
                                }
                            }

                            $str .= "<a " . $titleTip . $targetTag . "href='" . $menuSubUrl . "'>" . $actionName . "</a>";
                        } else {
                            $str .= "<a " . $titleTip . $targetTag . "href='#'>" . $actionName . "</a>";
                        }


                        $str .= "</li>";
                        $menuUrl .= $str;
                    }


                    $menuUrl .= "</ul>";
                    $menuUrl .= "</div>";
                }

                Yii::$app->session->set($sessionKey, $menuUrl);

                return $menuUrl;
            }
        }
        else {
            return null;
        }
    }
}