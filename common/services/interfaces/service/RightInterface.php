<?php
/**
 * Created by PhpStorm.
 * User: TangMingQiang
 * Date: 2/27/16
 * Time: 3:42 PM
 */

namespace common\services\interfaces\service;


use common\services\framework\CompanyMenuService;
use common\services\framework\RbacService;
use common\services\framework\UserCompanyService;
use common\services\framework\UserDomainService;
use common\services\framework\UserOrgnizationService;
use common\services\framework\UserService;
use common\base\BaseActiveRecord;

class RightInterface
{
    private $userService;
    private $userCompanyService;
    private $userDomainService;
    private $userOrgnizationService;
    private $rbacService;
    private $companyMenuService;

    /**
     * @return CompanyMenuService
     */
    public function getCompanyMenuService()
    {
        if (!isset($this->companyMenuService)) {
            $this->companyMenuService = new CompanyMenuService();
        }
        return $this->companyMenuService;
    }

    /**
     * @return RbacService
     */
    public function getRbacService()
    {
        if (!isset($this->rbacService)) {
            $this->rbacService = new RbacService();
        }
        return $this->rbacService;
    }

    /**
     * @return UserService
     */
    public function getUserService()
    {
        if (!isset($this->userService)) {
            $this->userService = new UserService();
        }

        return $this->userService;
    }

    /**
     * @return UserCompanyService
     */
    public function getUserCompanyService()
    {
        if (!isset($this->userCompanyService)) {
            $this->userCompanyService = new UserCompanyService();
        }
        return $this->userCompanyService;
    }

    /**
     * @return UserDomainService
     */
    public function getUserDomainService()
    {
        if (!isset($this->userDomainService)) {
            $this->userDomainService = new UserDomainService();
        }
        return $this->userDomainService;
    }

    /**
     * @return UserOrgnizationService
     */
    public function getUserOrgnizationService()
    {
        if (!isset($this->userOrgnizationService)) {
            $this->userOrgnizationService = new UserOrgnizationService();
        }
        return $this->userOrgnizationService;
    }


    /**
     * 取用户所能管理的企业
     * @param $userId
     * @param $status
     * @param bool $withSession
     * @return array|\yii\db\ActiveRecord[]
     */
    public function getManagedCompanyListByUserId($userId, $status = BaseActiveRecord::STATUS_FLAG_NORMAL, $withSession = true){
        return $this->getUserCompanyService()->getManagedListByUserId($userId, $status, $withSession);
    }


    /**
     * 取用户所能查询的企业
     * @param $userId
     * @param $status
     * @param bool $withSession
     * @return array|\yii\db\ActiveRecord[]
     */
    public function getSearchCompanyListByUserId($userId, $status = BaseActiveRecord::STATUS_FLAG_NORMAL, $withSession = true){
        return $this->getUserCompanyService()->getSearchListByUserId($userId, $status, $withSession);
    }

    /**
     * 取用户所能管理的域
     * @param $userId
     * @param string $status
     * @param bool $withSession
     * @return array|\yii\db\ActiveRecord[]
     */
    public function getManagedDomainListByUserId($userId, $status = BaseActiveRecord::STATUS_FLAG_NORMAL, $withSession = true){
        return $this->getUserDomainService()->getManagedListByUserId($userId, $status, $withSession);
    }

    /**
     * 取用户所能查询的域
     * @param $userId
     * @param string $status
     * @param bool $withSession
     * @return mixed
     */
    public function getSearchDomainListByUserId($userId, $status = BaseActiveRecord::STATUS_FLAG_NORMAL, $withSession = true){
        return $this->getUserDomainService()->getSearchListByUserId($userId, $status, $withSession);
    }


    /**
     * 取用户所能管理的组织部门
     * @param $userId
     * @param string $status
     * @param bool $withSession
     * @return mixed
     */
    public function getManagedOrgnizationListByUserId($userId, $status = BaseActiveRecord::STATUS_FLAG_NORMAL, $withSession = true){
        return $this->getUserOrgnizationService()->getManagedListByUserId($userId, $status, $withSession);
    }

    /**
     * 取用户所能查询的组织部门
     * @param $userId
     * @param string $status
     * @param bool $withSession
     * @return mixed
     */
    public function getSearchOrgnizationListByUserId($userId, $status = BaseActiveRecord::STATUS_FLAG_NORMAL, $withSession = true){
        return $this->getUserOrgnizationService()->getSearchListByUserId($userId, $status, $withSession);
    }


    /**
     * 取用户的角色信息
     * @param $userId
     * @param bool $withSession
     * @return array|\common\models\framework\FwUserRole[]
     */
    public function getRoleListByUserId($userId, $withSession = true){
        return $this->getUserService()->getRoleListByUserId($userId, $withSession);
    }


    /**
     * 取用户的角色信息（文本字符串）
     * @param $userId
     * @param bool $withSession
     * @return array|\common\models\framework\FwUserRole[]
     */
    public function getRoleListStringByUserId($userId, $withSession = true){
        return $this->getUserService()->getRoleListStringByUserId($userId, $withSession);
    }

    /**
     * 取用户的岗位信息
     * @param $userId
     * @param bool $withSession
     * @return array|\common\models\framework\FwUserPosition[]
     */
    public function getPositionListByUserId($userId, $withSession = true){
        return $this->getUserService()->getPositionListByUserId($userId, $withSession);
    }


    /**
     * 取用户的岗位信息（文本字符串）
     * @param $userId
     * @param bool $withSession
     * @return string
     */
    public function getPositionListStringByUserId($userId, $withSession = true){
        return $this->getUserService()->getPositionListStringByUserId($userId, $withSession);
    }

    /**
     * 取用户的企业信息（文本字符串）
     * @param $userId
     * @param bool $withSession
     * @return string
     */
    public function getCompanyStringByUserId($userId, $withSession = true){
        return $this->getUserService()->getCompanyStringByUserId($userId, $withSession);
    }


    /**
     * 取用户的组织部门信息（文本字符串）
     * @param $userId
     * @param bool $withSession
     * @return string
     */
    public function getOrgnizationStringByUserId($userId, $withSession = true){
        return $this->getUserService()->getOrgnizationStringByUserId($userId, $withSession);
    }

    /**
     * 取用户的域信息（文本字符串）
     * @param $userId
     * @param bool $withSession
     * @return string
     */
    public function getDomainStringByUserId($userId, $withSession = true){
        return $this->getUserService()->getDomainStringByUserId($userId, $withSession);
    }

    /**
     * 取用户的经理信息
     * @param $userId
     * @param bool $withSession
     * @return array|\yii\db\ActiveRecord[]
     */
    public function getReportingManagerByUserId($userId, $withSession = true){
        return $this->getUserService()->getReportingManagerByUserId($userId, $withSession);
    }

    /**
     * 取用户的审批人信息,返回审批人的user_id
     * @param $userId
     * @param bool $withSession
     * @return array|\yii\db\ActiveRecord[]
     */
    public function getApproverByUserId($userId, $withSession = true){
        return $this->getUserService()->getApproverByUserId($userId, $withSession);
    }

    /**
     * 取用户的经理信息（文本字符串）
     * @param $userId
     * @param bool $withSession
     * @return string
     */
    public function getReportingManagerStringByUserId($userId, $withSession = true){
        return $this->getUserService()->getReportingManagerStringByUserId($userId, $withSession);
    }

    /**
     * 取用户的下属
     * @param $userId
     * @param bool $withSession
     * @return array|\yii\db\ActiveRecord[]
     */
    public function getDirectReporterByUserId($userId, $withSession = true){
        return $this->getUserService()->getDirectReporterByUserId($userId, $withSession);
    }

    /**
     * 取用户的下属（文本字符串）
     * @param $userId
     * @param bool $withSession
     * @return string
     */
    public function getDirectReporterStringByUserId($userId, $withSession = true){
        return $this->getUserService()->getDirectReporterStringByUserId($userId, $withSession);
    }


    /**
     * 判断是否能访问指定的Action
     * @param $userId
     * @param $systemFlag
     * @param $actionUrl
     * @param null $actionParameter
     * @param bool $withSession
     * @param bool $withCache
     * @return bool
     */
    public function canAction($userId, $systemFlag, $actionUrl, $actionParameter = null, $withSession = true, $withCache = true){
        return $this->getRbacService()->canAction($userId, $systemFlag, $actionUrl, $actionParameter, $withSession, $withCache);
    }

    /**
     * 判断是否能访问指定的权限代码
     * @param $userId
     * @param $systemFlag
     * @param $permissionCode
     * @param bool $withSession
     * @param bool $withCache
     * @return bool
     */
    public function canPermisionCode($userId, $systemFlag, $permissionCode, $withSession = true, $withCache = true){
        return $this->getRbacService()->canPermisionCode($userId, $systemFlag, $permissionCode, $withSession, $withCache);
    }


    /**
     * 判断是否能访问指定的Url
     * @param $userId
     * @param $systemFlag
     * @param $url
     * @param bool $withSession
     * @param bool $withCache
     * @return bool
     */
    public function canUrl($userId, $systemFlag, $url, $withSession = true, $withCache = true){
        return $this->getRbacService()->canUrl($userId, $systemFlag, $url, $withSession, $withCache);
    }

    /**
     * 根据菜单类型获取企业个性化菜单
     * @param $companyId
     * @param $menuType
     * @return array
     */
    public function getCompanyMenuByType($companyId, $menuType){
        return $this->getCompanyMenuService()->getCompanyMenuByType($companyId, $menuType);
    }
}