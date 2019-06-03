<?php
/**
 * User: GROOT (pzyme@outlook.com)
 * Date: 2016/5/4
 * Time: 14:09
 */

namespace common\services\api;

use common\models\framework\FwUser;
use common\models\social\SoCollect;
use common\services\framework\PointRuleService;

use common\traits\ResponseTrait;
use common\traits\ValidatorTrait;
use common\traits\ParserTrait;
use common\traits\HelperTrait;

class UserService extends FwUser{
    use ResponseTrait,ValidatorTrait,ParserTrait,HelperTrait;
    
    public $systemKey;
    protected $user;
    public function __construct($system_key, FwUser $user,array $config = [])
    {
        $this->systemKey = $system_key;
        $this->user = $user;
        parent::__construct($config);
    }
    
   

    /**
     * 是否收藏
     * @param $user_id
     * @param $object_id
     * @param $type
     * @return bool
     */
    public function isMineFav($user_id,$object_id,$type) {
        $res = SoCollect::findOne(['object_id' => $object_id, 'user_id' => $user_id, 'type' => $type], false);
        return !empty($res);
    }

    /**
     * 添加收藏
     * @param $user_id
     * @param $object_id
     * @param $type
     * @return bool
     */
    public function addFav($user_id,$object_id,$type) {
        $collect = new SoCollect();
        return $collect->addCollect($user_id, $object_id,$type == "1" ? SoCollect::TYPE_QUESTION:SoCollect::TYPE_COURSE);
    }

    /**
     * 取消收藏
     * @param $user_id
     * @param $object_id
     * @param $type
     * @return bool
     */
    public function removeFav($user_id,$object_id,$type) {
        return SoCollect::deleteAll(['object_id' => $object_id, 'user_id' => $user_id, 'type' => $type]) > 0;
    }

    /**
     * 增加积分
     * @param $action
     * @param null $user_id
     * @param null $company_id
     * @param string $resource_id
     * @return array
     */
    public function increaseIntegral($action,$user_id = null,$company_id = null,$resource_id = '') {
        $pointRuleService = new PointRuleService();
        return $pointRuleService->checkActionForPoint(
            $this->_isset($this->user,'company_id',$company_id),
            $this->_isset($this->user,'kid',$user_id),
            $action,
            $this->systemKey,
            $resource_id
        );
    }
}