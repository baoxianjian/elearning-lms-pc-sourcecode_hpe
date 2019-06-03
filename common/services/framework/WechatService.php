<?php
/**
 * Created by PhpStorm.
 * User: tangming
 * Date: 12/15/2015
 * Time: 1:56 PM
 */

namespace common\services\framework;


use common\base\BaseService;
use common\models\framework\FwCompany;
use common\models\framework\FwCompanyWechat;
use common\models\framework\FwExternalSystem;
use common\models\framework\FwExternalSystemValue;
use common\models\framework\FwServiceLog;
use common\models\framework\FwUser;
use common\models\framework\FwUserWechatAccount;
use common\models\framework\FwWechatQrscene;
use common\viewmodels\wechat\Token;
use common\crpty\MessageCrypt;
use common\crpty\WechatErrorCode;
use common\crpty\WechatMsgCrypt;
use common\helpers\TURLHelper;
use DOMDocument;
use Yii;
use yii\base\Exception;
use yii\helpers\Html;
use yii\helpers\Url;

class WechatService
{
    const SYSTEM_CODE = "wechat";
    const ROBOT_CODE = "robot-service";

    const METHOD_POST = "POST";
    const METHOD_GET = "GET";

    const QR_CODE_TEMP = "QR_SCENE";//临时
    const QR_CODE_LIMIT = "QR_LIMIT_SCENE";//永久
    const QR_CODE_LIMIT_STR = "QR_LIMIT_STR_SCENE";//永久的字符串参数值


    const QR_SCENE_ACTION_BIND_USER = "bind_user"; //绑定账号

    const LOG_SERVICE_CODE = "wechat-log";

    const WECHAT_TOKEN = "eLearning";
    const WECHAT_WEB_AUTH_URL = 'https://open.weixin.qq.com/connect/oauth2/authorize';
    const WECHAT_WEB_TOKEN_URL = 'https://api.weixin.qq.com/sns/oauth2/access_token';
    const WECHAT_WEB_AUTH_USER_INFO = 'https://api.weixin.qq.com/sns/userinfo';

    public $systemId;
    protected $apiAddress;
    protected $systemStatus;
    protected $externalSystemModel;

    protected $logServiceId;

    protected $robotApiAddress;
    protected $robotApiKey;

    public function __construct()
    {
        $externalSystemService = new ExternalSystemService();
        $this->externalSystemModel = $externalSystemService->getExternalSystemInfoByExternalSystemCode(self::SYSTEM_CODE);
        $this->systemStatus = $this->externalSystemModel->status;
        $this->apiAddress = $this->externalSystemModel->api_address;
        $this->systemId = $this->externalSystemModel->kid;

        $commonServiceSerivce = new ServiceService();
        $this->logServiceId = $commonServiceSerivce->getServiceIdByServiceCode(self::LOG_SERVICE_CODE);


        $robotModel = $externalSystemService->getExternalSystemInfoByExternalSystemCode(self::ROBOT_CODE);
        $this->robotApiAddress = $robotModel->api_address;
        $this->robotApiKey = $robotModel->system_key;
    }


    /**
     * 获取错误消息
     * @param $errorCode
     * @return null|string
     */
    private function getErrorMessage($errorCode) {
        $errorMessage = null;
        switch ($errorCode) {
            case "-1": $errorMessage = "系统繁忙，此时请开发者稍候再试";break;
            case "0": $errorMessage = "请求成功";break;
            case "40001": $errorMessage = "获取access_token时AppSecret错误，或者access_token无效。请开发者认真比对AppSecret的正确性，或查看是否正在为恰当的公众号调用接口";break;
            case "40002": $errorMessage = "不合法的凭证类型";break;
            case "40003": $errorMessage = "不合法的OpenID，请开发者确认OpenID（该用户）是否已关注公众号，或是否是其他公众号的OpenID";break;
            case "40004": $errorMessage = "不合法的媒体文件类型";break;
            case "40005": $errorMessage = "不合法的文件类型";break;
            case "40006": $errorMessage = "不合法的文件大小";break;
            case "40007": $errorMessage = "不合法的媒体文件id";break;
            case "40008": $errorMessage = "不合法的消息类型";break;
            case "40009": $errorMessage = "不合法的图片文件大小";break;
            case "40010": $errorMessage = "不合法的语音文件大小";break;
            case "40011": $errorMessage = "不合法的视频文件大小";break;
            case "40012": $errorMessage = "不合法的缩略图文件大小";break;
            case "40013": $errorMessage = "不合法的AppID，请开发者检查AppID的正确性，避免异常字符，注意大小写";break;
            case "40014": $errorMessage = "不合法的access_token，请开发者认真比对access_token的有效性（如是否过期），或查看是否正在为恰当的公众号调用接口";break;
            case "40015": $errorMessage = "不合法的菜单类型";break;
            case "40016": $errorMessage = "不合法的按钮个数";break;
            case "40017": $errorMessage = "不合法的按钮个数";break;
            case "40018": $errorMessage = "不合法的按钮名字长度";break;
            case "40019": $errorMessage = "不合法的按钮KEY长度";break;
            case "40020": $errorMessage = "不合法的按钮URL长度";break;
            case "40021": $errorMessage = "不合法的菜单版本号";break;
            case "40022": $errorMessage = "不合法的子菜单级数";break;
            case "40023": $errorMessage = "不合法的子菜单按钮个数";break;
            case "40024": $errorMessage = "不合法的子菜单按钮类型";break;
            case "40025": $errorMessage = "不合法的子菜单按钮名字长度";break;
            case "40026": $errorMessage = "不合法的子菜单按钮KEY长度";break;
            case "40027": $errorMessage = "不合法的子菜单按钮URL长度";break;
            case "40028": $errorMessage = "不合法的自定义菜单使用用户";break;
            case "40029": $errorMessage = "不合法的oauth_code";break;
            case "40030": $errorMessage = "不合法的refresh_token";break;
            case "40031": $errorMessage = "不合法的openid列表";break;
            case "40032": $errorMessage = "不合法的openid列表长度";break;
            case "40033": $errorMessage = "不合法的请求字符，不能包含\uxxxx格式的字符";break;
            case "40035": $errorMessage = "不合法的参数";break;
            case "40038": $errorMessage = "不合法的请求格式";break;
            case "40039": $errorMessage = "不合法的URL长度";break;
            case "40050": $errorMessage = "不合法的分组id";break;
            case "40051": $errorMessage = "分组名字不合法";break;
            case "40117": $errorMessage = "分组名字不合法";break;
            case "40118": $errorMessage = "media_id大小不合法";break;
            case "40119": $errorMessage = "button类型错误";break;
            case "40120": $errorMessage = "button类型错误";break;
            case "40121": $errorMessage = "不合法的media_id类型";break;
            case "40130": $errorMessage = "至少需要2个OpenId";break;
            case "40132": $errorMessage = "微信号不合法";break;
            case "40137": $errorMessage = "不支持的图片格式";break;
            case "41001": $errorMessage = "缺少access_token参数";break;
            case "41002": $errorMessage = "缺少appid参数";break;
            case "41003": $errorMessage = "缺少refresh_token参数";break;
            case "41004": $errorMessage = "缺少secret参数";break;
            case "41005": $errorMessage = "缺少多媒体文件数据";break;
            case "41006": $errorMessage = "缺少media_id参数";break;
            case "41007": $errorMessage = "缺少子菜单数据";break;
            case "41008": $errorMessage = "缺少oauth code";break;
            case "41009": $errorMessage = "缺少openid";break;
            case "42001": $errorMessage = "access_token超时，请检查access_token的有效期，请参考基础支持-获取access_token中，对access_token的详细机制说明";break;
            case "42002": $errorMessage = "refresh_token超时";break;
            case "42003": $errorMessage = "oauth_code超时";break;
            case "43001": $errorMessage = "需要GET请求";break;
            case "43002": $errorMessage = "需要POST请求";break;
            case "43003": $errorMessage = "需要HTTPS请求";break;
            case "43004": $errorMessage = "需要接收者关注";break;
            case "43005": $errorMessage = "需要好友关系";break;
            case "44001": $errorMessage = "多媒体文件为空";break;
            case "44002": $errorMessage = "POST的数据包为空";break;
            case "44003": $errorMessage = "图文消息内容为空";break;
            case "44004": $errorMessage = "文本消息内容为空 ";break;
            case "45001": $errorMessage = "多媒体文件大小超过限制";break;
            case "45002": $errorMessage = "消息内容超过限制";break;
            case "45003": $errorMessage = "标题字段超过限制";break;
            case "45004": $errorMessage = "描述字段超过限制";break;
            case "45005": $errorMessage = "链接字段超过限制";break;
            case "45006": $errorMessage = "图片链接字段超过限制";break;
            case "45007": $errorMessage = "语音播放时间超过限制";break;
            case "45008": $errorMessage = "图文消息超过限制";break;
            case "45009": $errorMessage = "接口调用超过限制";break;
            case "45010": $errorMessage = "创建菜单个数超过限制";break;
            case "45015": $errorMessage = "回复时间超过限制";break;
            case "45016": $errorMessage = "系统分组，不允许修改";break;
            case "45017": $errorMessage = "分组名字过长";break;
            case "45018": $errorMessage = "分组数量超过上限";break;
            case "46001": $errorMessage = "不存在媒体数据";break;
            case "46002": $errorMessage = "不存在的菜单版本";break;
            case "46003": $errorMessage = "不存在的菜单数据";break;
            case "46004": $errorMessage = "不存在的用户";break;
            case "47001": $errorMessage = "解析JSON/XML内容错误";break;
            case "48001": $errorMessage = "api功能未授权，请确认公众号已获得该接口，可以在公众平台官网-开发者中心页中查看接口权限";break;
            case "50001": $errorMessage = "用户未授权该api";break;
            case "50002": $errorMessage = "用户受限，可能是违规后接口被封禁";break;
            case "61451": $errorMessage = "参数错误(invalid parameter)";break;
            case "61452": $errorMessage = "无效客服账号(invalid kf_account)";break;
            case "61453": $errorMessage = "客服帐号已存在(kf_account exsited)";break;
            case "61454": $errorMessage = "客服帐号名长度超过限制(仅允许10个英文字符，不包括@及@后的公众号的微信号)(invalid kf_acount length)";break;
            case "61455": $errorMessage = "客服帐号名包含非法字符(仅允许英文+数字)(illegal character in kf_account)";break;
            case "61456": $errorMessage = "客服帐号个数超过限制(10个客服账号)(kf_account count exceeded)";break;
            case "61457": $errorMessage = "无效头像文件类型(invalid file type)";break;
            case "61450": $errorMessage = "系统错误(system error)";break;
            case "61500": $errorMessage = "日期格式错误";break;
            case "61501": $errorMessage = "日期范围错误";break;
            case "9001001": $errorMessage = "POST数据参数不合法";break;
            case "9001002": $errorMessage = "远端服务不可用";break;
            case "9001003": $errorMessage = "Ticket不合法";break;
            case "9001004": $errorMessage = "获取摇周边用户信息失败";break;
            case "9001005": $errorMessage = "获取商户信息失败";break;
            case "9001006": $errorMessage = "获取OpenID失败";break;
            case "9001007": $errorMessage = "上传文件缺失";break;
            case "9001008": $errorMessage = "上传素材的文件类型不合法";break;
            case "9001009": $errorMessage = "上传素材的文件尺寸不合法";break;
            case "9001010": $errorMessage = "上传失败";break;
            case "9001020": $errorMessage = "帐号不合法";break;
            case "9001021": $errorMessage = "已有设备激活率低于50%，不能新增设备";break;
            case "9001022": $errorMessage = "设备申请数不合法，必须为大于0的数字";break;
            case "9001023": $errorMessage = "已存在审核中的设备ID申请";break;
            case "9001024": $errorMessage = "一次查询设备ID数量不能超过50";break;
            case "9001025": $errorMessage = "设备ID不合法";break;
            case "9001026": $errorMessage = "页面ID不合法";break;
            case "9001027": $errorMessage = "页面参数不合法";break;
            case "9001028": $errorMessage = "一次删除页面ID数量不能超过10";break;
            case "9001029": $errorMessage = "页面已应用在设备中，请先解除应用关系再删除";break;
            case "9001030": $errorMessage = "一次查询页面ID数量不能超过50";break;
            case "9001031": $errorMessage = "时间区间不合法";break;
            case "9001032": $errorMessage = "保存设备与页面的绑定关系参数错误";break;
            case "9001033": $errorMessage = "门店ID不合法";break;
            case "9001034": $errorMessage = "设备备注信息过长";break;
            case "9001035": $errorMessage = "设备申请参数不合法";break;
            case "9001036": $errorMessage = "查询起始值begin不合法";break;
        }

        return $errorMessage;
    }

    /**
     * 微信服务是否运行中
     * @return bool
     */
    public function isRunning()
    {
        return $this->systemStatus == FwExternalSystem::STATUS_FLAG_NORMAL ? true : false;
    }

    /**
     * 根据openid找帐号
     * @param string $companyId 企业ID
     * @param string $openId OpenID
     * @return mixed|null|FwUserWechatAccount
     */
    public function findWechatAccountByOpenId($companyId, $openId)
    {
        if (empty($openId) || empty($companyId))
            return null;

        $model = new FwUserWechatAccount();
        $result = $model->findOne([
            'company_id' => $companyId,
            'open_id' => $openId
        ]);

        return $result;
    }


    /**
     * 根据unionid找帐号
     * @param string $companyId 企业ID
     * @param string $unionId UnionID
     * @return mixed|null|FwUserWechatAccount
     */
    public function findWechatAccountByUnionId($companyId, $unionId)
    {
        if (empty($unionId) || empty($companyId))
            return null;

        $model = new FwUserWechatAccount();
        $result = $model->findOne([
            'company_id' => $companyId,
            'union_id' => $unionId
        ]);

        return $result;
    }


    /**
     * 获取用户的微信帐号
     * @param string $userId 用户ID
     * @return FwUserWechatAccount
     */
    public function getWechatAccount($userId,$openId = null,$multi = true)
    {
        if (empty($userId))
            return null;

        $condition = [
            'user_id' => $userId
        ];
        if(!empty($openId)) {
            $condition['open_id'] = $openId;
        }
        $model = new FwUserWechatAccount();

        if($multi) {
            return $model->findAll($condition);
        }
        return $model->findOne($condition,false);
    }


    /**
     * 解除绑定微信帐号
     * @param string $userId 用户ID
     * @param string $companyId 企业ID
     * @param string $openId 微信openID
     * @return bool 成功与否
     */
    public function unBindWechatAccount($userId, $companyId, $openId = null)
    {
        if (empty($userId))
            return false;

        $condition['user_id'] = $userId;
        $condition['company_id'] = $companyId;
        if(!empty($openId)) {
            $condition['open_id'] = $openId;
        }
        $affectRows = FwUserWechatAccount::deleteAll($condition);
        return $affectRows > 0;
    }


    /**
     * 绑定微信帐号
     * @param string $companyId 企业ID
     * @param string $userId 用户ID
     * @param string $openId OpenID
     * @param string $errMessage 错误消息
     * @param array  $withUserInfo 微信用户信息
     * @param bool   $multi 用户=>微信 (hasMany)
     * @return bool 成功与否
     */
    public function bindWechatAccount($companyId, $userId, $openId, &$errMessage,$withUserInfo = null,$multi = false)
    {
        if (empty($userId) || empty($openId))
            return false;

        if($withUserInfo !== null && is_array($withUserInfo)) {
            $result = $withUserInfo;
            $ret = true;
        } else {
            $ret = $this->getUserInfo($companyId, $openId, $result, $errMessage);
        }
        
        if ($ret) {
            $model = $this->getWechatAccount($userId,$multi ? $openId : null,false);

            if (empty($model)) {
                //如果不存在微信帐号，则直接新建
                $model = new FwUserWechatAccount();
                $model->company_id = $companyId;
                $model->user_id = $userId;
            }

            $model->subscribe = strval($result["subscribe"]);
            $model->open_id = $result["openid"];
            $model->nick_name = $result["nickname"];

            //值为1时是男性，值为2时是女性，值为0时是未知
            $sex = strval($result["sex"]);
            if ($sex == "1")
                $model->sex = "男";
            else if ($sex == "1")
                $model->sex = "女";
            else
                $model->sex = "其他";
            $model->city = $result["city"];
            $model->country = $result["country"];
            $model->province = $result["province"];
            $model->language = $result["language"];
            $model->headimg_url = $result["headimgurl"];
            $model->subscribe_time = $result["subscribe_time"];
            $model->union_id = $result["unionid"];
            $model->remark = $result["remark"];
            $model->group_id = strval($result["groupid"]);

            if ($model->save()) {
                return true;
            }
            else {
                return false;
            }
        }
        else {
            return false;
        }
    }

    /**
     * 停用帐号
     * @param $model FwExternalSystemValue
     */
    private function stopWechatAccount($model) {
        if (!empty($model)) {
            if ($model->delete())
                return true;
            else {
                return false;
            }
        }
        else {
            return true;
        }
    }

    /**
     * 获取企业正在使用的微信公众号信息
     * @param string $companyId 企业ID
     * @return mixed|FwCompanyWechat
     */
    public function getCompanyActiveWechat($companyId)
    {
        $model = new FwCompanyWechat();
        $result = $model->find(false)
            ->andFilterWhere(['=','company_id',$companyId])
            ->andFilterWhere(['=','status', FwCompanyWechat::STATUS_FLAG_NORMAL])
            ->one();

        return $result;
    }


    /**
     * 根据AppId获取企业正在使用的微信公众号信息
     * @param string $appId
     * @return array|null|\yii\db\ActiveRecord
     */
    public function getCompanyActiveWechatByAppId($appId)
    {
        $model = new FwCompanyWechat();
        $result = $model->find(false)
            ->andFilterWhere(['=','app_id',$appId])
            ->andFilterWhere(['=','status', FwCompanyWechat::STATUS_FLAG_NORMAL])
            ->one();

        return $result;
    }

    /**
     * 获取access_token
     * access_token是公众号的全局唯一票据，公众号调用各接口时都需使用access_token。
     * 开发者需要进行妥善保存。access_token的存储至少要保留512个字符空间。
     * access_token的有效期目前为2个小时，需定时刷新，重复获取将导致上次获取的access_token失效。
     * @param string $companyId 企业ID
     * @param string $result Token微信令牌包
     * @param string $errMessage 错误消息
     * @param bool $withCache 是否启用Cache
     * @return bool 成功与否
     */
    public function getAccessToken($companyId, &$result, &$errMessage, $withCache = true)
    {
        if (!$this->isRunning()) {
            $errMessage = Yii::t('common', 'wechat_service_stop');
            return false;
        }

        $sessionKey = "WechatAccessToken-" . $companyId;

        if ($withCache && Yii::$app->cache->exists($sessionKey)) {
            $wechatTokenModel = Yii::$app->cache->get($sessionKey);
        }

        if (!empty($wechatTokenModel) && $wechatTokenModel->endAt > time()) {
            $wechatModel = FwCompanyWechat::findOne($wechatTokenModel->wechatId);
            if (!empty($wechatModel) && $wechatModel->status == FwCompanyWechat::STATUS_FLAG_NORMAL) {
                $result = $wechatTokenModel;
                return true;
            }
            else {
                $errMessage = Yii::t('common', 'company_no_active_wechat');
                return false;
            }
        }
        else {
            $wechatModel = $this->getCompanyActiveWechat($companyId);
            if (empty($wechatModel) || empty($wechatModel->app_id) || empty($wechatModel->app_secret)) {
                $errMessage = Yii::t('common', 'company_no_active_wechat');
                return false;
            }

            $appid = trim($wechatModel->app_id);
            $appsecret = trim($wechatModel->app_secret);
            $url = $this->apiAddress . "cgi-bin/token?grant_type=client_credential&appid=" . $appid . "&secret=" . $appsecret;



            if ($this->getDataFromWechat($url, self::METHOD_GET, null, true, $jsonResult, $errMessage)) {
                $jsonInfo = json_decode($jsonResult, true);

                $wechatTokenModel = new Token();
                $wechatTokenModel->accessToken = $jsonInfo["access_token"];
                $wechatTokenModel->expiresIn = $jsonInfo["expires_in"];
                $wechatTokenModel->startAt = time();
                if (!empty($wechatTokenModel->expiresIn) && is_integer($wechatTokenModel->expiresIn)) {
                    $wechatTokenModel->endAt = $wechatTokenModel->startAt + $wechatTokenModel->expiresIn;
                }
                $wechatTokenModel->companyId = $companyId;
                $wechatTokenModel->userId = $companyId;
                $wechatTokenModel->wechatId = $wechatModel->kid;

                $result = $wechatTokenModel;

                if ($withCache) {
                    Yii::$app->cache->set($sessionKey, $result);
                }

                return true;
            } else {
                return false;
            }
        }
    }


    /**
     * 如果公众号基于安全等考虑，需要获知微信服务器的IP地址列表，以便进行相关限制，可以通过该接口获得微信服务器IP地址列表。
     * @param string $companyId 企业ID
     * @param string $result 微信服务器的IP地址列表
     * @param string $errMessage 错误消息
     * @param bool $withCache 是否启用Cache
     * @return bool 成功与否
     */
    public function getCallBackIP($companyId, &$result, &$errMessage, $withCache = true)
    {
        if ($this->getAccessToken($companyId, $wechatTokenModel, $errMessage, $withCache)){

            $url = $this->apiAddress . "cgi-bin/getcallbackip?access_token=" . trim($wechatTokenModel->accessToken);

            if ($this->getDataFromWechat($url, self::METHOD_GET, null, true, $jsonResult, $errMessage)) {
                $jsonInfo = json_decode($jsonResult, true);
                $result = $jsonInfo["ip_list"];
                return true;
            } else {
                return false;
            }
        }
        else {
            return false;
        }
    }

    /**
     * 将一条长链接转成短链接。
     * 主要使用场景： 开发者用于生成二维码的原链接（商品、支付二维码等）太长导致扫码速度和成功率下降，
     * 将原长链接通过此接口转成短链接再生成二维码将大大提升扫码速度和成功率。
     * @param string $companyId 企业ID
     * @param string $longUrl 长链接
     * @param string $result 短链接
     * @param string $errMessage 错误消息
     * @param bool $withCache 是否启用Cache
     * @return bool 成功与否
     */
    public function getShorturl($companyId, $longUrl, &$result, &$errMessage, $withCache = true)
    {
        if ($this->getAccessToken($companyId, $wechatTokenModel, $errMessage, $withCache)) {

            $url = $this->apiAddress . "cgi-bin/shorturl?access_token=" . trim($wechatTokenModel->accessToken);

            $postDataArr = [
                'action' => 'long2short',
                'long_url' => $longUrl,
            ];

            if ($this->getDataFromWechat($url, self::METHOD_POST, $postDataArr, true, $jsonResult, $errMessage)) {
                $jsonInfo = json_decode($jsonResult, true);
                $result = $jsonInfo["short_url"];
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }


    /**
     * 生成带参数的二维码
     * 为了满足用户渠道推广分析的需要，公众平台提供了生成带参数二维码的接口。使用该接口可以获得多个带不同场景值的二维码，用户扫描后，公众号可以接收到事件推送。
     * @param string $companyId 企业ID
     * @param string $actionScene 二维码类型
     * @param int $expireSeconds 有效时间
     * @param string $qrsceneAction 场景操作类型
     * @param string $qrsceneValue 场景值
     * @param string $result Ticket
     * @param string $errMessage 错误消息
     * @param bool $withCache 是否启用Cache
     * @return bool 成功与否
     */
    public function createQRCode($companyId, $actionScene = self::QR_CODE_TEMP, $expireSeconds = 0, $qrsceneAction, $qrsceneValue, &$result, &$errMessage, $withCache = true)
    {
        if ($this->getAccessToken($companyId, $wechatTokenModel, $errMessage, $withCache)) {

            $wechatQrsceneService = new WechatQrsceneService();

            $sceneId = $wechatQrsceneService->GetQrsceneIdByAction($qrsceneAction);

            $startAt = time();
            $endAt = null;

            $url = $this->apiAddress . "cgi-bin/qrcode/create?access_token=" . trim($wechatTokenModel->accessToken);

            if ($actionScene == self::QR_CODE_TEMP) {
                if (empty($expireSeconds) || $expireSeconds > 604800)
                    $expireSeconds = 604800;

                $endAt = $startAt + $expireSeconds;
                $postDataArr = [
                    'expire_seconds' => $expireSeconds,
                    'action_name' => $actionScene,
                    'action_info' => [
                        'scene' => [
                            'scene_id' => $sceneId
                        ]
                    ],
                ];
            }
            else if ($actionScene == self::QR_CODE_LIMIT){
                $postDataArr = [
                    'action_name' => $actionScene,
                    'action_info' => [
                        'scene' => [
                            'scene_id' => $sceneId
                        ]
                    ],
                ];
            }
            else {
                $postDataArr = [
                    'action_name' => $actionScene,
                    'action_info' => [
                        'scene' => [
                            'scene_str' => strval($sceneId)
                        ]
                    ],
                ];
            }



            if ($this->getDataFromWechat($url, self::METHOD_POST, $postDataArr, true, $jsonResult, $errMessage)) {
                $jsonInfo = json_decode($jsonResult, true);
                $result = $jsonInfo["ticket"];

                $model = new FwWechatQrscene();

                $model->company_id = $companyId;
                $model->qrscene_type = $actionScene;
                $model->start_at = $startAt;
                $model->end_at = $endAt;
                $model->qrscene_action = $qrsceneAction;
                $model->qrscene_id = strval($sceneId);
                $model->qrscene_value = $qrsceneValue;
                $model->ticket = $result;

                $model->save();

                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * 生成二维码图片地址
     * @param string $ticket Ticket
     * @return string 二维码图片地址
     */
    public function getQRCodeSrcUrlByTicket($ticket) {
        return "https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=" . $ticket;
    }

    /**
     * 获取关注者列表（需要认证）
     * 公众号可通过本接口来获取帐号的关注者列表，关注者列表由一串OpenID（加密后的微信号，每个用户对每个公众号的OpenID是唯一的）组成。
     * 一次拉取调用最多拉取10000个关注者的OpenID，可以通过多次拉取的方式来满足需求。
     * @param string $companyId 企业ID
     * @param string $nextOpenId 第一个拉取的OPENID，不填默认从头开始拉取
     * @param string $result 关注者列表
     * total：关注该公众账号的总用户数
     * count：拉取的OPENID个数，最大值为10000
     * data：列表数据，OPENID的列表
     * next_openid：拉取列表的后一个用户的OPENID
     * @param string $errMessage 错误消息
     * @param bool $withCache 是否启用Cache
     * @return bool 成功与否
     */
    public function getUserList($companyId, $nextOpenId, &$result, &$errMessage, $withCache = true)
    {
        if ($this->getAccessToken($companyId, $wechatTokenModel, $errMessage, $withCache)) {
            $wechatModel = FwCompanyWechat::findOne($wechatTokenModel->wechatId);
            if ($wechatModel->is_authenticated == FwCompanyWechat::NO) {
                $errMessage = Yii::t('common', 'wechat_no_authenticated');
                return false;
            } else {
                $url = $this->apiAddress . "cgi-bin/user/get?access_token=" . trim($wechatTokenModel->accessToken) . "&next_openid=" . $nextOpenId;

                if ($this->getDataFromWechat($url, self::METHOD_GET, null, true, $jsonResult, $errMessage)) {
                    $result = json_decode($jsonResult, true);
                    return true;
                } else {
                    return false;
                }
            }
        } else {
            return false;
        }
    }

    /**
     * 获取用户基本信息（需要认证）
     * 在关注者与公众号产生消息交互后，公众号可获得关注者的OpenID（加密后的微信号，每个用户对每个公众号的OpenID是唯一的。
     * 对于不同公众号，同一用户的openid不同）。
     * 公众号可通过本接口来根据OpenID获取用户基本信息，包括昵称、头像、性别、所在城市、语言和关注时间。
     * @param string $companyId 企业ID
     * @param string $openId OpenID
     * @param string $result 微信用户
     * @param string $errMessage 错误消息
     * @param bool $withCache 是否启用Cache
     * @return bool 成功与否
     */
    public function getUserInfo($companyId, $openId, &$result, &$errMessage, $withCache = true)
    {
        if ($this->getAccessToken($companyId, $wechatTokenModel, $errMessage, $withCache)) {
            $wechatModel = FwCompanyWechat::findOne($wechatTokenModel->wechatId);
            if ($wechatModel->is_authenticated == FwCompanyWechat::NO) {
                $errMessage = Yii::t('common', 'wechat_no_authenticated');
                return false;
            } else {
                $url = $this->apiAddress . "cgi-bin/user/info?access_token=" . trim($wechatTokenModel->accessToken) . "&openid=" . $openId . "&lang=zh_CN";

                if ($this->getDataFromWechat($url, self::METHOD_GET, null, true, $jsonResult, $errMessage)) {
                    $jsonInfo = json_decode($jsonResult, true);
                    $result = $jsonInfo;
                    return true;
                } else {
                    return false;
                }
            }
        } else {
            return false;
        }
    }


    /**
     * 批量获取用户基本信息（需要认证）
     * 开发者可通过该接口来批量获取用户基本信息。最多支持一次拉取100条。
     * @param string $companyId 企业ID
     * @param array $postDataArr OpenId数组，全都放在user_list节点下
     * @param array $result User[]微信用户数组
     * @param string $errMessage 错误消息
     * @param bool $withCache 是否启用Cache
     * @return bool 成功与否
     */
    public function batchGetUserInfo($companyId, $postDataArr, &$result, &$errMessage, $withCache = true)
    {
        if ($this->getAccessToken($companyId, $wechatTokenModel, $errMessage, $withCache)) {
            $wechatModel = FwCompanyWechat::findOne($wechatTokenModel->wechatId);
            if ($wechatModel->is_authenticated == FwCompanyWechat::NO) {
                $errMessage = Yii::t('common', 'wechat_no_authenticated');
                return false;
            } else {
                $url = $this->apiAddress . "cgi-bin/user/info/batchget?access_token=" . trim($wechatTokenModel->accessToken);

                if ($this->getDataFromWechat($url, self::METHOD_POST, $postDataArr, true, $jsonResult, $errMessage)) {
                    $jsonInfo = json_decode($jsonResult, true);
                    $result = $jsonInfo;
                    return true;
                } else {
                    return false;
                }
            }
        } else {
            return false;
        }
    }

    /**
     * 上传图文消息内的图片获取URL【订阅号与服务号认证后均可用】
     * 这个接口上传的图片，只可嵌入在消息内容中
     * @param string $companyId 企业ID
     * @param string $filePath 图片绝对路径
     * @param string $result 图片Url
     * @param string $errMessage 错误消息
     * @param bool $withCache 是否启用Cache
     * @return bool 成功与否
     */
    public function uploadImageForMessage($companyId, $filePath, &$result, &$errMessage, $withCache = true)
    {
        if ($this->getAccessToken($companyId, $wechatTokenModel, $errMessage, $withCache)) {
            $wechatModel = FwCompanyWechat::findOne($wechatTokenModel->wechatId);
            if ($wechatModel->is_authenticated == FwCompanyWechat::NO) {
                $errMessage = Yii::t('common', 'wechat_no_authenticated');
                return false;
            } else {
                $url = $this->apiAddress . "cgi-bin/media/uploadimg?access_token=" . trim($wechatTokenModel->accessToken);

                $postDataArr = [
                    'media' => "@" . $filePath
                ];

                if ($this->getDataFromWechat($url, self::METHOD_POST, $postDataArr, false, $jsonResult, $errMessage)) {
                    $jsonInfo = json_decode($jsonResult, true);
                    $result = $jsonInfo["url"];
                    return true;
                } else {
                    return false;
                }
            }
        } else {
            return false;
        }
    }

    /**
     * 新增临时素材（需要认证）
     * 上传的临时多媒体文件有格式和大小限制，如下：
     *    图片（image）: 1M，支持JPG格式
     *    语音（voice）：2M，播放长度不超过60s，支持AMR\MP3格式
     *    视频（video）：10MB，支持MP4格式
     *    缩略图（thumb）：64KB，支持JPG格式
     *    媒体文件在后台保存时间为3天，即3天后media_id失效。
     * @param string $companyId 企业ID
     * @param string $filePath 素材绝对路径
     * @param string $type 素材类型
     * @param string $result MediaId
     * @param string $errMessage 错误消息
     * @param bool $withCache 是否启用Cache
     * @return bool 成功与否
     */
    public function uploadTempMedia($companyId, $filePath, $type, &$result, &$errMessage, $withCache = true)
    {
        if ($this->getAccessToken($companyId, $wechatTokenModel, $errMessage, $withCache)) {
            $wechatModel = FwCompanyWechat::findOne($wechatTokenModel->wechatId);
            if ($wechatModel->is_authenticated == FwCompanyWechat::NO) {
                $errMessage = Yii::t('common', 'wechat_no_authenticated');
                return false;
            } else {
                $url = $this->apiAddress . "cgi-bin/media/upload?access_token=" . trim($wechatTokenModel->accessToken) . "&type=" . $type;

                if ($type == "video") {
                    //如果要获取的素材是视频文件时，不能使用https协议，必须更换成http协议
                    $url = str_replace('https', 'http', $url);
                }

                $postDataArr = [
                    'media' => "@" . $filePath
                ];

                if ($this->getDataFromWechat($url, self::METHOD_POST, $postDataArr, false, $jsonResult, $errMessage)) {
                    $jsonInfo = json_decode($jsonResult, true);
                    $result = $jsonInfo["media_id"];
                    return true;
                } else {
                    return false;
                }
            }
        } else {
            return false;
        }
    }

    /**
     * 新增其他类型永久素材（需要认证）
     * 永久素材的数量是有上限的，请谨慎新增。图文消息素材和图片素材的上限为5000，其他类型为1000
     * 素材的格式大小等要求与公众平台官网一致。具体是，图片大小不超过2M，支持bmp/png/jpeg/jpg/gif格式，语音大小不超过5M，长度不超过60秒，支持mp3/wma/wav/amr格式
     *
     * @param string $companyId 企业ID
     * @param string $filePath 素材绝对路径
     * @param string $type 媒体文件类型，分别有图片（image）、语音（voice）、视频（video）和缩略图（thumb）
     * @param array $videoInfo 视频文件数组，包含title视频素材的标题和introduction视频素材的描述
     * @param array $result 结果数组，包含media_id和url
     * @param string $errMessage 错误消息
     * @param bool $withCache 是否启用Cache
     * @return bool 成功与否
     */
    public function uploadForeverMedia($companyId, $filePath, $type, $videoInfo = null, &$result, &$errMessage, $withCache = true)
    {
        if ($this->getAccessToken($companyId, $wechatTokenModel, $errMessage, $withCache)) {
            $wechatModel = FwCompanyWechat::findOne($wechatTokenModel->wechatId);
            if ($wechatModel->is_authenticated == FwCompanyWechat::NO) {
                $errMessage = Yii::t('common', 'wechat_no_authenticated');
                return false;
            } else {
                $url = $this->apiAddress . "cgi-bin/material/add_material?access_token=" . trim($wechatTokenModel->accessToken) . "&type=" . $type;

                if ($type == "video") {
                    //如果要获取的素材是视频文件时，不能使用https协议，必须更换成http协议
                    $url = str_replace('https', 'http', $url);

                    //在上传视频素材时需要POST另一个表单，id为description，包含素材的描述信息，内容格式为JSON
                    $postDataArr = [
                        'media' => "@" . $filePath,
                        'description' => $videoInfo
                    ];
                } else {
                    $postDataArr = [
                        'media' => "@" . $filePath
                    ];
                }

                if ($this->getDataFromWechat($url, self::METHOD_POST, $postDataArr, false, $jsonResult, $errMessage)) {
                    $result = json_decode($jsonResult, true);
                    return true;
                } else {
                    return false;
                }
            }
        } else {
            return false;
        }
    }

    /**
     * 获取临时素材（需要认证）
     * 公众号可以使用本接口获取临时素材（即下载临时的多媒体文件）。请注意，视频文件不支持https下载，调用该接口需http协议。
     * @param string $companyId 企业ID
     * @param string $mediaId MeidaID
     * @param string $result MediaId
     * @param string $errMessage 错误消息
     * @param bool $withCache 是否启用Cache
     * @return bool 成功与否
     */
    public function getTempMedia($companyId, $mediaId, &$result, &$errMessage, $withCache = true)
    {
        if ($this->getAccessToken($companyId, $wechatTokenModel, $errMessage, $withCache)) {
            $wechatModel = FwCompanyWechat::findOne($wechatTokenModel->wechatId);
            if ($wechatModel->is_authenticated == FwCompanyWechat::NO) {
                $errMessage = Yii::t('common', 'wechat_no_authenticated');
                return false;
            } else {
                $url = $this->apiAddress . "cgi-bin/media/get?access_token=" . trim($wechatTokenModel->accessToken) . "&media_id=" . $mediaId;

                //如果要获取的素材是视频文件时，不能使用https协议，必须更换成http协议
//            $url = str_replace('https','http',$url);

                if ($this->downloadWechatFile($url, $result, $errMessage)) {
                    return true;
                } else {
                    return false;
                }
            }
        } else {
            return false;
        }
    }

    /**
     * 上传临时图文消息素材【订阅号与服务号认证后均可用】
     * @param string $companyId 企业ID
     * @param array $articles
     * {
     *    "articles": [ 图文消息，一个图文消息支持1到10条图文
     *      {
     *          "thumb_media_id":"qI6_Ze_6PtV7svjolgs-rN6stStuHIjs9_DidOHaj0Q-mwvBelOXCFZiq2OsIU-p",图文消息的封面图片素材id（必须是永久mediaID）
     *          "author":"xxx",图文消息的作者
     *          "title":"Happy Day",图文消息的标题
     *          "content_source_url":"www.qq.com",在图文消息页面点击“阅读原文”后的页面
     *          "content":"content",图文消息页面的内容，支持HTML标签。具备微信支付权限的公众号，可以使用a标签，其他公众号不能使用
     *          "digest":"digest",图文消息的描述
     *          "show_cover_pic":"1",是否显示封面，0为false，即不显示，1为true，即显示
     *      },
     *      {
     *          "thumb_media_id":"qI6_Ze_6PtV7svjolgs-rN6stStuHIjs9_DidOHaj0Q-mwvBelOXCFZiq2OsIU-p",
     *          "author":"xxx",
     *          "title":"Happy Day",
     *          "content_source_url":"www.qq.com",
     *          "content":"content",
     *          "digest":"digest",
     *          "show_cover_pic":"0"
     *      }
     *      ]
     *  }
     * @param string $result 结果 MediaId
     * @param string $errMessage 错误消息
     * @param bool $withCache 是否启用Cache
     * @return bool 成功与否
     */
    public function uploadTempNews($companyId, $articles, &$result, &$errMessage, $withCache = true)
    {
        if ($this->getAccessToken($companyId, $wechatTokenModel, $errMessage, $withCache)) {
            $wechatModel = FwCompanyWechat::findOne($wechatTokenModel->wechatId);
            if ($wechatModel->is_authenticated == FwCompanyWechat::NO) {
                $errMessage = Yii::t('common', 'wechat_no_authenticated');
                return false;
            } else {
                $url = $this->apiAddress . "cgi-bin/media/uploadnews?access_token=" . trim($wechatTokenModel->accessToken);

                $postDataArr = $articles;

                if ($this->getDataFromWechat($url, self::METHOD_POST, $postDataArr, true, $jsonResult, $errMessage)) {
                    $jsonInfo = json_decode($jsonResult, true);
                    $result = $jsonInfo["media_id"];
                    return true;
                } else {
                    return false;
                }
            }
        } else {
            return false;
        }
    }

    /**
     * 新增永久图文素材（需要认证）
     * @param string $companyId 企业ID
     * @param array $articles
     * {
     *    "articles": [ 图文消息，一个图文消息支持1到10条图文
     *      {
     *          "thumb_media_id":"qI6_Ze_6PtV7svjolgs-rN6stStuHIjs9_DidOHaj0Q-mwvBelOXCFZiq2OsIU-p",图文消息的封面图片素材id（必须是永久mediaID）
     *          "author":"xxx",图文消息的作者
     *          "title":"Happy Day",图文消息的标题
     *          "content_source_url":"www.qq.com",在图文消息页面点击“阅读原文”后的页面
     *          "content":"content",图文消息的具体内容，支持HTML标签，必须少于2万字符，小于1M，且此处会去除JS
     *          "digest":"digest",图文消息的描述
     *          "show_cover_pic":"1",是否显示封面，0为false，即不显示，1为true，即显示
     *      },
     *      {
     *          "thumb_media_id":"qI6_Ze_6PtV7svjolgs-rN6stStuHIjs9_DidOHaj0Q-mwvBelOXCFZiq2OsIU-p",
     *          "author":"xxx",
     *          "title":"Happy Day",
     *          "content_source_url":"www.qq.com",
     *          "content":"content",
     *          "digest":"digest",
     *          "show_cover_pic":"0"
     *      }
     *      ]
     *  }
     * @param string $result 结果MediaId
     * @param string $errMessage 错误消息
     * @param bool $withCache 是否启用Cache
     * @return bool 成功与否
     */
    public function uploadForeverNews($companyId, $articles, &$result, &$errMessage, $withCache = true)
    {
        if ($this->getAccessToken($companyId, $wechatTokenModel, $errMessage, $withCache)) {
            $wechatModel = FwCompanyWechat::findOne($wechatTokenModel->wechatId);
            if ($wechatModel->is_authenticated == FwCompanyWechat::NO) {
                $errMessage = Yii::t('common', 'wechat_no_authenticated');
                return false;
            } else {
                $url = $this->apiAddress . "cgi-bin/media/add_news?access_token=" . trim($wechatTokenModel->accessToken);

                $postDataArr = $articles;

                if ($this->getDataFromWechat($url, self::METHOD_POST, $postDataArr, true, $jsonResult, $errMessage)) {
                    $jsonInfo = json_decode($jsonResult, true);
                    $result = $jsonInfo["media_id"];
                    return true;
                } else {
                    return false;
                }
            }
        } else {
            return false;
        }
    }

    /**
     * 获取永久素材（需要认证）
     * 公众号可以使用本接口获取临时素材（即下载临时的多媒体文件）。请注意，视频文件不支持https下载，调用该接口需http协议。
     * @param string $companyId 企业ID
     * @param string $mediaId MeidaID
     * @param array $result
     * 如果请求的素材为图文消息
     *  {
     *        "news_item":
     *      [
     *      {
     *          "title":TITLE,
     *          "thumb_media_id"::THUMB_MEDIA_ID,
     *          "show_cover_pic":SHOW_COVER_PIC(0/1),
     *          "author":AUTHOR,
     *          "digest":DIGEST,
     *          "content":CONTENT,
     *          "url":URL,
     *          "content_source_url":CONTENT_SOURCE_URL
     *      },
     *      //多图文消息有多篇文章
     *      ]
     * }
     * 如果返回的是视频消息素材，则内容如下：
     * {
     *      "title":TITLE,
     *      "description":DESCRIPTION,
     *      "down_url":DOWN_URL,
     * }
     * 其他类型的素材消息，则响应的直接为素材的内容，开发者可以自行保存为文件。
     * @param string $errMessage 错误消息
     * @param bool $withCache 是否启用Cache
     * @return bool 成功与否
     */
    public function getForeverMedia($companyId, $mediaId, &$result, &$errMessage, $withCache = true)
    {
        if ($this->getAccessToken($companyId, $wechatTokenModel, $errMessage, $withCache)) {
            $wechatModel = FwCompanyWechat::findOne($wechatTokenModel->wechatId);
            if ($wechatModel->is_authenticated == FwCompanyWechat::NO) {
                $errMessage = Yii::t('common', 'wechat_no_authenticated');
                return false;
            } else {
                $url = $this->apiAddress . "cgi-bin/material/get_material?access_token=" . trim($wechatTokenModel->accessToken);

                $postDataArr = [
                    'media_id' => $mediaId
                ];

                if ($this->getDataFromWechat($url, self::METHOD_POST, $postDataArr, true, $jsonResult, $errMessage)) {
                    $result = json_decode($jsonResult, true);
                    //其他类型的素材消息，则响应的直接为素材的内容，开发者可以自行保存为文件。（需要根据$type修改）
                    return true;
                } else {
                    return false;
                }
            }
        } else {
            return false;
        }
    }

    /**
     * 删除永久素材（需要认证）
     * @param string $companyId 企业ID
     * @param string $mediaId MeidaID
     * @param bool $result 成功与否
     * @param string $errMessage 错误消息
     * @param bool $withCache 是否启用Cache
     * @return bool 成功与否
     */
    public function deleteForeverMedia($companyId, $mediaId, &$result, &$errMessage, $withCache = true)
    {
        if ($this->getAccessToken($companyId, $wechatTokenModel, $errMessage, $withCache)) {
            $wechatModel = FwCompanyWechat::findOne($wechatTokenModel->wechatId);
            if ($wechatModel->is_authenticated == FwCompanyWechat::NO) {
                $errMessage = Yii::t('common', 'wechat_no_authenticated');
                return false;
            } else {
                $url = $this->apiAddress . "cgi-bin/material/del_material?access_token=" . trim($wechatTokenModel->accessToken);

                $postDataArr = [
                    'media_id' => $mediaId
                ];

                if ($this->getDataFromWechat($url, self::METHOD_POST, $postDataArr, true, $jsonResult, $errMessage)) {
                    $result = true;
                    return true;
                } else {
                    return false;
                }
            }
        } else {
            return false;
        }
    }

    /**
     * 对永久图文素材进行修改（需要认证）
     * @param string $companyId 企业ID
     * @param array $articles
     * {
     *      "media_id":MEDIA_ID,要修改的图文消息的id
     *       "index":INDEX,要更新的文章在图文消息中的位置（多图文消息时，此字段才有意义），第一篇为0
     *       "articles": {
     *      "title": TITLE,标题
     *       "thumb_media_id": THUMB_MEDIA_ID,图文消息的封面图片素材id（必须是永久mediaID）
     *       "author": AUTHOR,作者
     *       "digest": DIGEST,图文消息的摘要，仅有单图文消息才有摘要，多图文此处为空
     *       "show_cover_pic": SHOW_COVER_PIC(0 / 1),是否显示封面，0为false，即不显示，1为true，即显示
     *       "content": CONTENT,图文消息的具体内容，支持HTML标签，必须少于2万字符，小于1M，且此处会去除JS
     *       "content_source_url": CONTENT_SOURCE_URL,图文消息的原文地址，即点击“阅读原文”后的URL
     *       }
     * }
     * @param bool $result 成功与否
     * @param string $errMessage 错误消息
     * @param bool $withCache 是否启用Cache
     * @return bool 成功与否
     */
    public function updateForeverNews($companyId, $articles, &$result, &$errMessage, $withCache = true)
    {
        if ($this->getAccessToken($companyId, $wechatTokenModel, $errMessage, $withCache)) {
            $wechatModel = FwCompanyWechat::findOne($wechatTokenModel->wechatId);
            if ($wechatModel->is_authenticated == FwCompanyWechat::NO) {
                $errMessage = Yii::t('common', 'wechat_no_authenticated');
                return false;
            } else {
                $url = $this->apiAddress . "cgi-bin/media/update_news?access_token=" . trim($wechatTokenModel->accessToken);

                $postDataArr = $articles;

                if ($this->getDataFromWechat($url, self::METHOD_POST, $postDataArr, true, $jsonResult, $errMessage)) {
                    $result = true;
                    return true;
                } else {
                    return false;
                }
            }
        } else {
            return false;
        }
    }

    /**
     * 获取永久素材的总数（需要认证）
     * @param string $companyId 企业ID
     * @param array $result
     * {
     *      "voice_count":COUNT,语音总数量
     *      "video_count":COUNT,视频总数量
     *      "image_count":COUNT,图片总数量
     *      "news_count":COUNT,图文总数量
     * }
     * @param string $errMessage 错误消息
     * @param bool $withCache 是否启用Cache
     * @return bool 成功与否
     */
    public function getMaterialCount($companyId, &$result, &$errMessage, $withCache = true)
    {
        if ($this->getAccessToken($companyId, $wechatTokenModel, $errMessage, $withCache)) {
            $wechatModel = FwCompanyWechat::findOne($wechatTokenModel->wechatId);
            if ($wechatModel->is_authenticated == FwCompanyWechat::NO) {
                $errMessage = Yii::t('common', 'wechat_no_authenticated');
                return false;
            } else {
                $url = $this->apiAddress . "cgi-bin/material/get_materialcount?access_token=" . trim($wechatTokenModel->accessToken);

                if ($this->getDataFromWechat($url, self::METHOD_GET, null, true, $jsonResult, $errMessage)) {
                    $result = json_decode($jsonResult, true);
                    return true;
                } else {
                    return false;
                }
            }
        } else {
            return false;
        }
    }


    /**
     * 获取永久素材的列表（需要认证）
     * @param string $companyId 企业ID
     * @param string $type 素材的类型，图片（image）、视频（video）、语音 （voice）、图文（news）
     * @param int $offset 从全部素材的该偏移位置开始返回，0表示从第一个素材 返回
     * @param int $count 返回素材的数量，取值在1到20之间
     * @param array $result 永久素材的列表
     * @param string $errMessage 错误消息
     * @param bool $withCache 是否启用Cache
     * @return bool 成功与否
     */
    public function batchGetMaterialList($companyId, $type, $offset, $count, &$result, &$errMessage, $withCache = true)
    {
        if ($this->getAccessToken($companyId, $wechatTokenModel, $errMessage, $withCache)) {
            $wechatModel = FwCompanyWechat::findOne($wechatTokenModel->wechatId);
            if ($wechatModel->is_authenticated == FwCompanyWechat::NO) {
                $errMessage = Yii::t('common', 'wechat_no_authenticated');
                return false;
            } else {
                $url = $this->apiAddress . "cgi-bin/material/batchget_material?access_token=" . trim($wechatTokenModel->accessToken);

                $postDataArr = [
                    "type" => $type,//素材的类型，图片（image）、视频（video）、语音 （voice）、图文（news）
                    "offset" => $offset,
                    "count" => $count
                ];

                if ($this->getDataFromWechat($url, self::METHOD_POST, $postDataArr, true, $jsonResult, $errMessage)) {
                    $result = json_decode($jsonResult, true);
                    return true;
                } else {
                    return false;
                }
            }
        } else {
            return false;
        }
    }

    /**
     * 根据分组进行群发【订阅号与服务号认证后均可用】
     * @param string $companyId 企业ID
     * @param string $content text类型直接是文本内容，wxcard类型是card_id，其他类型是media_id
     * @param string $groupId 群组Id
     * @param string $type 支持text，mpnews，voice，image，mpvideo，wxcard
     * @param string $result 消息发送任务的ID
     * @param string $errMessage 错误消息
     * @param bool $withCache 是否启用Cache
     * @return bool 成功与否
     */
    public function massSendToAllMessage($companyId, $content, $groupId = "",$type = "text", &$result, &$errMessage, $withCache = true)
    {
        if ($this->getAccessToken($companyId, $wechatTokenModel, $errMessage, $withCache)) {
            $wechatModel = FwCompanyWechat::findOne($wechatTokenModel->wechatId);
            if ($wechatModel->is_authenticated == FwCompanyWechat::NO) {
                $errMessage = Yii::t('common', 'wechat_no_authenticated');
                return false;
            } else {
                $url = $this->apiAddress . "cgi-bin/message/mass/sendall?access_token=" . trim($wechatTokenModel->accessToken);

                $isToAll = false;
                if (empty($groupId)) {
                    $isToAll = true;
                }

                if ($type == "text") {
                    $content = urlencode(htmlspecialchars($content));
                    $postDataArr = [
                        'filter' => [
                            'is_to_all' => $isToAll,
                            'group_id' => $groupId
                        ],
                        'text' => [
                            'content' => $content
                        ],
                        'msgtype' => $type
                    ];
                } else if ($type == "mpnews" || $type == "voice" || $type == "image" || $type == "mpvideo" || $type == "music") {
                    $postDataArr = [
                        'filter' => [
                            'is_to_all' => $isToAll,
                            'group_id' => $groupId
                        ],
                        $type => [
                            'media_id' => $content
                        ],
                        'msgtype' => $type
                    ];
                } else if ($type == "wxcard") {
                    $postDataArr = [
                        'filter' => [
                            'is_to_all' => $isToAll,
                            'group_id' => $groupId
                        ],
                        $type => [
                            'card_id' => $content
                        ],
                        'msgtype' => $type
                    ];
                }

                if ($this->getDataFromWechat($url, self::METHOD_POST, $postDataArr, true, $jsonResult, $errMessage)) {
                    $jsonInfo = json_decode($jsonResult, true);
                    $result = $jsonInfo["msg_id"]; //消息发送任务的ID
                    //消息的数据ID，该字段只有在群发图文消息时，才会出现。
                    //可以用于在图文分析数据接口中，获取到对应的图文消息的数据，是图文分析数据接口中的msgid字段中的前半部分，详见图文分析数据接口中的msgid字段的介绍。
                    return true;
                } else {
                    return false;
                }
            }
        } else {
            return false;
        }
    }

    /**
     * 根据OpenID列表群发【订阅号不可用，服务号认证后可用】
     * @param string $companyId 企业ID
     * @param string $content text类型直接是文本内容，wxcard类型是card_id，其他类型是media_id
     * @param array $toUserList OpenId数组
     * @param string $type 支持text，mpnews，voice，image，mpvideo，wxcard
     * @param string $result 消息发送任务的ID
     * @param string $errMessage 错误消息
     * @param bool $withCache 是否启用Cache
     * @return bool 成功与否
     */
    public function massSendToOpenIdMessage($companyId, $content, $toUserList = [], $type = "text", &$result, &$errMessage, $withCache = true)
    {
        if ($this->getAccessToken($companyId, $wechatTokenModel, $errMessage, $withCache)) {
            $wechatModel = FwCompanyWechat::findOne($wechatTokenModel->wechatId);
            if ($wechatModel->is_authenticated == FwCompanyWechat::NO) {
                $errMessage = Yii::t('common', 'wechat_no_authenticated');
                return false;
            } else {
                $url = $this->apiAddress . "cgi-bin/message/mass/send?access_token=" . trim($wechatTokenModel->accessToken);

                if ($type == "text") {
                    $content = urlencode(htmlspecialchars($content));
                    $postDataArr = [
                        'touser' => $toUserList,
                        'text' => [
                            'content' => $content
                        ],
                        'msgtype' => $type
                    ];
                } else if ($type == "mpnews" || $type == "voice" || $type == "image" || $type == "mpvideo" || $type == "music") {
                    $postDataArr = [
                        'touser' => $toUserList,
                        $type => [
                            'media_id' => $content
                        ],
                        'msgtype' => $type
                    ];
                } else if ($type == "wxcard") {
                    $postDataArr = [
                        'touser' => $toUserList,
                        $type => [
                            'card_id' => $content
                        ],
                        'msgtype' => $type
                    ];
                }

                if ($this->getDataFromWechat($url, self::METHOD_POST, $postDataArr, true, $jsonResult, $errMessage)) {
                    $jsonInfo = json_decode($jsonResult, true);
                    $result = $jsonInfo["msg_id"]; //消息发送任务的ID
                    //消息的数据ID，该字段只有在群发图文消息时，才会出现。
                    //可以用于在图文分析数据接口中，获取到对应的图文消息的数据，是图文分析数据接口中的msgid字段中的前半部分，详见图文分析数据接口中的msgid字段的介绍。
                    return true;
                } else {
                    return false;
                }
            }
        } else {
            return false;
        }
    }


    /**
     * 给单人发送客服消息（需要认证）
     * 当用户主动发消息给公众号的时候（包括发送信息、点击自定义菜单、订阅事件、扫描二维码事件、支付成功事件、用户维权），微信将会把消息数据推送给开发者，
     * 开发者在一段时间内（目前修改为48小时）可以调用客服消息接口，通过POST一个JSON数据包来发送消息给普通用户，在48小时内不限制发送次数。
     * 此接口主要用于客服等有人工消息处理环节的功能，方便开发者为用户提供更加优质的服务。
     * @param string $companyId 企业ID
     * @param string $content text类型直接是文本内容，wxcard类型是card_id，其他类型是media_id
     * @param string $toUserId OpenId
     * @param string $type 支持text，mpnews，voice，image，mpvideo，wxcard
     * @param string $result 消息发送任务的ID
     * @param string $errMessage 错误消息
     * @param bool $withCache 是否启用Cache
     * @return bool 成功与否
     */
    public function sendToOpenIdMessage($companyId, $content, $toUserId, $type = "text", &$result, &$errMessage, $withCache = true)
    {
        if ($this->getAccessToken($companyId, $wechatTokenModel, $errMessage, $withCache)) {
            $wechatModel = FwCompanyWechat::findOne($wechatTokenModel->wechatId);
            if ($wechatModel->is_authenticated == FwCompanyWechat::NO) {
                $errMessage = Yii::t('common', 'wechat_no_authenticated');
                return false;
            } else {

                $url = $this->apiAddress . "cgi-bin/message/custom/send?access_token=" . trim($wechatTokenModel->accessToken);

                if ($type == "text") {
                    $content = urlencode(htmlspecialchars($content));
                    $postDataArr = [
                        'touser' => $toUserId,
                        'text' => [
                            'content' => $content
                        ],
                        'msgtype' => $type
                    ];
                } else if ($type == "mpnews" || $type == "voice" || $type == "image" || $type == "mpvideo" || $type == "music") {
                    $postDataArr = [
                        'touser' => $toUserId,
                        $type => [
                            'media_id' => $content
                        ],
                        'msgtype' => $type
                    ];
                } else if ($type == "wxcard") {
                    $postDataArr = [
                        'touser' => $toUserId,
                        $type => [
                            'card_id' => $content
                        ],
                        'msgtype' => $type
                    ];
                }

                if ($this->getDataFromWechat($url, self::METHOD_POST, $postDataArr, true, $jsonResult, $errMessage)) {
                    $result = true;
                    return true;
                } else {
                    return false;
                }
            }
        } else {
            return false;
        }
    }

    /**
     * 删除群发【订阅号与服务号认证后均可用】
     * 群发只有在刚发出的半小时内可以删除，发出半小时之后将无法被删除。
     * @param string $companyId 企业ID
     * @param string $msgId 发送出去的消息ID
     * @param bool $result 成功与否
     * @param string $errMessage 错误消息
     * @param bool $withCache 是否启用Cache
     * @return bool 成功与否
     */
    public function deleteMessage($companyId, $msgId, &$result, &$errMessage, $withCache = true)
    {
        if ($this->getAccessToken($companyId, $wechatTokenModel, $errMessage, $withCache)) {
            $wechatModel = FwCompanyWechat::findOne($wechatTokenModel->wechatId);
            if ($wechatModel->is_authenticated == FwCompanyWechat::NO) {
                $errMessage = Yii::t('common', 'wechat_no_authenticated');
                return false;
            } else {
                $url = $this->apiAddress . "cgi-bin/message/mass/delete?access_token=" . trim($wechatTokenModel->accessToken);

                $postDataArr = [
                    'msg_id' => $msgId,
                ];

                if ($this->getDataFromWechat($url, self::METHOD_POST, $postDataArr, true, $jsonResult, $errMessage)) {
                    $result = true;
                    return true;
                } else {
                    return false;
                }
            }
        } else {
            return false;
        }
    }


    /**
     * 查询群发消息发送状态【订阅号与服务号认证后均可用】
     * @param string $companyId 企业ID
     * @param string $msgId 发送出去的消息ID
     * @param bool $result 成功与否
     * @param string $errMessage 错误消息
     * @param bool $withCache 是否启用Cache
     * @return bool 成功与否
     */
    public function getMessage($companyId, $msgId, &$result, &$errMessage, $withCache = true)
    {
        if ($this->getAccessToken($companyId, $wechatTokenModel, $errMessage, $withCache)) {
            $wechatModel = FwCompanyWechat::findOne($wechatTokenModel->wechatId);
            if ($wechatModel->is_authenticated == FwCompanyWechat::NO) {
                $errMessage = Yii::t('common', 'wechat_no_authenticated');
                return false;
            } else {
                $url = $this->apiAddress . "cgi-bin/message/mass/get?access_token=" . trim($wechatTokenModel->accessToken);

                $postDataArr = [
                    'msg_id' => $msgId,
                ];

                if ($this->getDataFromWechat($url, self::METHOD_POST, $postDataArr, true, $jsonResult, $errMessage)) {
                    $jsonInfo = json_decode($jsonResult, true);
                    $msgStatus = $jsonInfo["msg_status"]; //消息发送任务的ID
                    if ($msgStatus == "SEND_SUCCESS") {
                        $result = true;
                    } else {
                        $result = false;
                    }
                    return true;
                } else {
                    return false;
                }
            }
        } else {
            return false;
        }
    }


    /**
     * 预览接口【订阅号与服务号认证后均可用】
     * 开发者可通过该接口发送消息给指定用户，在手机端查看消息的样式和排版。
     * 为了满足第三方平台开发者的需求，在保留对openID预览能力的同时，增加了对指定微信号发送预览的能力，但该能力每日调用次数有限制（100次），请勿滥用。
     * @param string $companyId 企业ID
     * @param string $content 发送文本消息时文本的内容 或 用于群发的消息的media_id
     * @param string $toOpenId 接收消息用户对应该公众号的openid，该字段也可以改为towxname，以实现对微信号的预览
     * @param string $type 群发的消息类型，图文消息为mpnews，文本消息为text，语音为voice，音乐为music，图片为image，视频为video，卡券为wxcard
     * @param bool $result 成功与否
     * @param string $errMessage 错误消息
     * @param bool $withCache 是否启用Cache
     * @return bool 成功与否
     */
    public function previewMessage($companyId, $content, $toOpenId, $isOpenId = true, $type = "text",   &$result, &$errMessage, $withCache = true)
    {
        if ($this->getAccessToken($companyId, $wechatTokenModel, $errMessage, $withCache)) {
            $wechatModel = FwCompanyWechat::findOne($wechatTokenModel->wechatId);
            if ($wechatModel->is_authenticated == FwCompanyWechat::NO) {
                $errMessage = Yii::t('common', 'wechat_no_authenticated');
                return false;
            } else {
                $url = $this->apiAddress . "cgi-bin/message/mass/preview?access_token=" . trim($wechatTokenModel->accessToken);

                $str = "touser";
                if (!$isOpenId) {
                    $str = "towxname";
                }


                if ($type == "text") {
                    $content = urlencode(htmlspecialchars($content));
                    $postDataArr = [
                        $str => $toOpenId,
                        'text' => [
                            'content' => $content
                        ],
                        'msgtype' => $type
                    ];
                } else if ($type == "mpnews" || $type == "voice" || $type == "image" || $type == "mpvideo" || $type == "music") {
                    $postDataArr = [
                        $str => $toOpenId,
                        $type => [
                            'media_id' => $content
                        ],
                        'msgtype' => $type
                    ];
                } else if ($type == "wxcard") {
                    $postDataArr = [
                        $str => $toOpenId,
                        $type => [
                            'card_id' => $content
                        ],
                        'msgtype' => $type
                    ];
                }

                if ($this->getDataFromWechat($url, self::METHOD_POST, $postDataArr, true, $jsonResult, $errMessage)) {
                    $jsonInfo = json_decode($jsonResult, true);
                    $result = $jsonInfo["msg_id"]; //消息发送任务的ID
                    return true;
                } else {
                    return false;
                }
            }
        } else {
            return false;
        }
    }

    /**
     * 设置所属行业（需要认证）
     * 设置行业可在MP中完成，每月可修改行业1次，账号仅可使用所属行业中相关的模板，为方便第三方开发者，提供通过接口调用的方式来修改账号所属行业
     * @param string $companyId 企业ID
     * @param string $industryId1 行业1ID
     * @param string $industryId2 行业1ID
     * @param bool $result 成功与否
     * @param string $errMessage 错误消息
     * @param bool $withCache 是否启用Cache
     * @return bool 成功与否
     */
    public function setTemplateIndustry($companyId, $industryId1, $industryId2, &$result, &$errMessage, $withCache = true)
    {
        if ($this->getAccessToken($companyId, $wechatTokenModel, $errMessage, $withCache)) {
            $wechatModel = FwCompanyWechat::findOne($wechatTokenModel->wechatId);
            if ($wechatModel->is_authenticated == FwCompanyWechat::NO) {
                $errMessage = Yii::t('common', 'wechat_no_authenticated');
                return false;
            } else {
                $url = $this->apiAddress . "cgi-bin/template/api_set_industry?access_token=" . trim($wechatTokenModel->accessToken);

                $postDataArr = [
                    'industry_id1' => $industryId1,
                    'industry_id2' => $industryId2,
                ];

                if ($this->getDataFromWechat($url, self::METHOD_POST, $postDataArr, true, $jsonResult, $errMessage)) {
                    $result = true;
                    return true;
                } else {
                    return false;
                }
            }
        } else {
            return false;
        }
    }


    /**
     * 获得模板ID（需要认证）
     * 从行业模板库选择模板到账号后台，获得模板ID的过程可在MP中完成。
     * @param string $companyId 企业ID
     * @param string $templateIdShort 模板库中模板的编号，有“TM**”和“OPENTMTM**”等形式
     * @param string $result 模板ID
     * @param string $errMessage 错误消息
     * @param bool $withCache 是否启用Cache
     * @return bool 成功与否
     */
    public function getTemplateIdByIdShort($companyId, $templateIdShort, &$result, &$errMessage, $withCache = true)
    {
        if ($this->getAccessToken($companyId, $wechatTokenModel, $errMessage, $withCache)) {
            $wechatModel = FwCompanyWechat::findOne($wechatTokenModel->wechatId);
            if ($wechatModel->is_authenticated == FwCompanyWechat::NO) {
                $errMessage = Yii::t('common', 'wechat_no_authenticated');
                return false;
            } else {
                $url = $this->apiAddress . "cgi-bin/template/api_add_template?access_token=" . trim($wechatTokenModel->accessToken);

                $postDataArr = [
                    'template_id_short' => $templateIdShort,
                ];

                if ($this->getDataFromWechat($url, self::METHOD_POST, $postDataArr, true, $jsonResult, $errMessage)) {
                    $jsonInfo = json_decode($jsonResult, true);
                    $result = $jsonInfo["template_id"]; //模板ID
                    return true;
                } else {
                    return false;
                }
            }
        } else {
            return false;
        }
    }


    /**
     * 根据模板发送消息（需要认证）
     * @param string $companyId 企业ID
     * @param string $toUserId 接收模板消息的用户的OpenID
     * @param string $templateId 模板ID
     * @param string $templateUrl 跳转url
     * @param array $data 模板数据
     * @param string $result 消息ID
     * @param string $errMessage 错误消息
     * @param bool $withCache 是否启用Cache
     * @return bool 成功与否
     */
    public function sendMessageByTemplate($companyId, $toUserId, $templateId, $templateUrl, $data, &$result, &$errMessage, $withCache = true)
    {
        if ($this->getAccessToken($companyId, $wechatTokenModel, $errMessage, $withCache)) {
            $wechatModel = FwCompanyWechat::findOne($wechatTokenModel->wechatId);
            if ($wechatModel->is_authenticated == FwCompanyWechat::NO) {
                $errMessage = Yii::t('common', 'wechat_no_authenticated');
                return false;
            } else {
                $url = $this->apiAddress . "cgi-bin/message/template/send?access_token=" . trim($wechatTokenModel->accessToken);

                $postDataArr = [
                    'touser' => $toUserId,
                    'template_id' => $templateId,
                    "url" => $templateUrl,
                    "data" => $data,
                ];

                if ($this->getDataFromWechat($url, self::METHOD_POST, $postDataArr, true, $jsonResult, $errMessage)) {
                    $jsonInfo = json_decode($jsonResult, true);
                    $result = $jsonInfo["msgid"]; //消息ID
                    return true;
                } else {
                    return false;
                }
            }
        } else {
            return false;
        }
    }

    /**
     * 获取统一登录的OpenId
     * @param string $companyId 企业ID
     * @param string $code 登录代码
     * @param string $result OpenId
     * @return bool 成功与否
     */
    public function  getLoginOpenId($companyId, $code, &$result, &$errMessage) {
        $wechatModel = $this->getCompanyActiveWechat($companyId);
        if (!empty($wechatModel) && !empty($wechatModel->app_id) && !empty($wechatModel->app_secret)) {
            $url = $this->apiAddress . "sns/oauth2/access_token?appid=" . $wechatModel->app_id .
                "&secret=" . $wechatModel->app_secret . "&code=" . $code . "&grant_type=authorization_code";

            if ($this->getDataFromWechat($url, self::METHOD_GET, null, true, $jsonResult, $errMessage)) {
                $jsonInfo = json_decode($jsonResult, true);
                $result = $jsonInfo["openid"]; //消息ID
                return true;
            } else {
                return false;
            }
        }
        else {
            return false;
        }

    }
    /**
     * 向微信服务器发送请求并获取数据
     * @param string $url 接口地址
     * @param string $method 调用方法
     * @param null|array $postDataArr Post数据数组
     * @param bool $isEncodePostData 是否对数据进行编码
     * @param string $jsonResult Json结果
     * @param string $errMessage 错误消息
     * @return bool 成功与否
     */
    private function getDataFromWechat($url, $method = self::METHOD_GET, $postDataArr = null,$isEncodePostData = true, &$jsonResult, &$errMessage)
    {
        $logInfo = "请求地址：" . $url . ";";
        $commonServiceSerivce = new ServiceService();

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        if ($method == self::METHOD_POST) {
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_POST, 1);
            if (!empty($postDataArr) && count($postDataArr) > 0) {
                $postJosnData = urldecode(json_encode($postDataArr));
                $logInfo .= "参数：" . $postJosnData . ";";
                if ($isEncodePostData) {
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $postJosnData);
                } else {
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $postDataArr);
                }
            }
        }
        $jsonResult = curl_exec($ch);
        curl_close($ch);

        $logInfo .= "结果：" . $jsonResult;
        if (!empty($jsonResult)) {
            $jsonInfo = json_decode($jsonResult, true);
            if (isset($jsonInfo["errcode"]) && $jsonInfo["errcode"] != 0) {
                $errMessage = $this->getErrorMessage($jsonInfo["errcode"]);
                if (empty($errMessage)) {
                    $errMessage = $this->getErrorMessage($jsonInfo["errmsg"]);
                }
                $commonServiceSerivce->recordServiceLog($this->logServiceId, FwServiceLog::ACTION_STATUS_ERROR, $logInfo);
                return false;
            } else {
                $commonServiceSerivce->recordServiceLog($this->logServiceId, FwServiceLog::ACTION_STATUS_NORMAL, $logInfo);
                return true;
            }
        } else {
            $errMessage = Yii::t('common', 'wechat_service_error');
            $commonServiceSerivce->recordServiceLog($this->logServiceId, FwServiceLog::ACTION_STATUS_ERROR, $logInfo);
            return false;
        }
    }

    /**
     * 获取机器人答复
     * @param $info
     * @param $jsonResult
     * @return bool
     */
    public function getDataFromRobot($info, &$jsonResult, &$errMessage = null)
    {
        if (!empty($info)) {
            $url = $this->robotApiAddress . "?key=" . $this->robotApiKey . "&info=" . $info;
            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

            $jsonResult = curl_exec($ch);
            curl_close($ch);

            if (!empty($jsonResult)) {
                $jsonInfo = json_decode($jsonResult, true);
                if (isset($jsonInfo["code"]) && $jsonInfo["code"] == 100000 && isset($jsonInfo["text"])) {
                    $jsonResult = $jsonInfo["text"];
                    return true;
                } else {
                    $errMessage = "机器人出错了";
                    return false;
                }
            } else {
                return false;
            }
        }
        else {
            return false;
        }
    }


    /**
     * 下载微信文件
     * @param string $url Url路径
     * @param string $result 结果
     * @param string $errMessage 错误消息
     * @return bool 成功与否
     */
    private function downloadWechatFile($url, &$result, &$errMessage) {
        $logInfo = "请求地址：" . $url . ";";
        $commonServiceSerivce = new ServiceService();

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_NOBODY, 0);//只取body头
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $jsonResult = curl_exec($ch);
        $httpInfo = curl_getinfo($ch);
        curl_close($ch);

        $result = array_merge(
            ['header' => $httpInfo],
            ['body' => $jsonResult]
        );


        if (!empty($jsonResult)) {
            $jsonInfo = json_decode($jsonResult, true);
            if (isset($jsonInfo["errcode"]) && $jsonInfo["errcode"] != 0) {
                $logInfo .= "结果：失败";
                $errMessage = $this->getErrorMessage($jsonInfo["errcode"]);
                if (empty($errMessage)) {
                    $errMessage = $this->getErrorMessage($jsonInfo["errmsg"]);
                }
                $commonServiceSerivce->recordServiceLog($this->logServiceId, FwServiceLog::ACTION_STATUS_ERROR, $logInfo);
                return false;
            } else {
                $logInfo .= "结果：成功";
                $commonServiceSerivce->recordServiceLog($this->logServiceId, FwServiceLog::ACTION_STATUS_NORMAL, $logInfo);
                return true;
            }
        } else {
            $logInfo .= "结果：失败";
            $errMessage = Yii::t('common', 'wechat_service_error');
            $commonServiceSerivce->recordServiceLog($this->logServiceId, FwServiceLog::ACTION_STATUS_ERROR, $logInfo);
            return false;
        }
    }


    /**
     * 保存文件
     * @param string $fileName 文件保存绝对路径
     * @param string $fileContent 文件内容
     */
    public function saveWechatFile($fileName, $fileContent) {
        $localFile = fopen($fileName, 'w');
        if (false !== $localFile) {
            if (false !== fwrite($localFile, $fileContent)) {
                fclose($localFile);
            }
        }
    }


    /**
     * 消息回复处理
     * @param string $companyId 企业ID
     */
    public function responseMsg($companyId)
    {
        $wechatModel = $this->getCompanyActiveWechat($companyId);
        $commonServiceSerivce = new ServiceService();
        if (!empty($wechatModel) && !empty($wechatModel->action_token)) {
            //get post data, May be due to the different environments
            $postData = $GLOBALS["HTTP_RAW_POST_DATA"];

            $resultStr = "";
            //extract post data
            if (!empty($postData)) {
                /* libxml_disable_entity_loader is to prevent XML eXternal Entity Injection,
                   the best way is to check the validity of xml by yourself */
                libxml_disable_entity_loader(true);

                $decryptMsg = $this->deryptMsg($wechatModel, $postData);
                if (!empty($decryptMsg)) {
                    $postObj = simplexml_load_string($decryptMsg, 'SimpleXMLElement', LIBXML_NOCDATA);

                    if (!empty($postObj)) {
//                        $fromUsername = $postObj->FromUserName;
//                        $toUsername = $postObj->ToUserName;
//                        $keyword = trim($postObj->Content);
//                        $time = time();
                        $msgType = $postObj->MsgType;

                        switch ($msgType) {
                            case "text":
                                $resultStr = $this->messageHandleText($wechatModel, $postObj);
                                break;
                            case "event":
                                $resultStr = $this->messageHandleEvent($wechatModel, $postObj);
                                break;
                            case "image":
                                $resultStr = "服务号暂时不接收图片消息";
                                break;
                            case "voice":
                                $resultStr = "服务号暂时不接收语音消息";
                                break;
                            case "video":
                                $resultStr = "服务号暂时不接收视频消息";
                                break;
                            case "shortvideo":
                                $resultStr = "服务号暂时不接收小视频消息";
                                break;
                            case "location":
                                $resultStr = "服务号暂时不接收地理位置消息";
                                break;
                            case "link":
                                $resultStr = "服务号暂时不接收链接消息";
                                break;
                            default:
                                $resultStr = "Unknow msg type: " . $msgType;
                                break;
                        }
                    }
                }
            }

            $wechatUrl = Yii::$app->urlManager->createAbsoluteUrl(["service/wechat-service","companyId"=>$companyId]);
            $logInfo = "请求地址：" . $wechatUrl . ";";
            $logInfo .= "参数：" . json_encode(Yii::$app->request->getQueryParams()) . ";";
            $logInfo .= "postData：" . $postData . ";";
            $logInfo .= "结果：" . $resultStr . ";";

            $commonServiceSerivce->recordServiceLog($this->logServiceId, FwServiceLog::ACTION_STATUS_NORMAL, $logInfo);
            echo $resultStr;
        } else {
            $errMessage = Yii::t('common', 'company_no_active_wechat');
            $commonServiceSerivce->recordServiceLog($this->logServiceId, FwServiceLog::ACTION_STATUS_ERROR, $errMessage);
            exit;
        }
    }


    /**
     * 检查签名
     * @param string $companyId 企业Id
     * @return bool 成功与否
     */
    public function checkSignature($companyId)
    {
        $wechatModel = $this->getCompanyActiveWechat($companyId);

        if (!empty($wechatModel) && !empty($wechatModel->action_token)) {
            $signature = Yii::$app->request->getQueryParam("signature");
            $timeStamp = Yii::$app->request->getQueryParam("timestamp");
            $nonce = Yii::$app->request->getQueryParam("nonce");

            $wechatUrl = Yii::$app->urlManager->createAbsoluteUrl(["service/wechat-service","companyId"=>$companyId]);
            $logInfo = "请求地址：" . $wechatUrl . ";";
            $logInfo .= "参数：" . json_encode(Yii::$app->request->getQueryParams()) . ";";
            $commonServiceSerivce = new ServiceService();
            $commonServiceSerivce->recordServiceLog($this->logServiceId, FwServiceLog::ACTION_STATUS_NORMAL, $logInfo);

            $token = trim($wechatModel->action_token);
            $tmpArr = array($token, $timeStamp, $nonce);
            // use SORT_STRING rule
            sort($tmpArr, SORT_STRING);
            $tmpStr = implode($tmpArr);
            $tmpStr = sha1($tmpStr);

            if ($tmpStr == $signature) {
                return true;
            } else {
                return false;
            }
        }
        else {
            return false;
        }
    }

    /**
     * 对消息进行必要的解密处理（取决于微信参数开关）
     * @param $wechatModel FwCompanyWechat
     * @param string $encryptMsg 加密消息
     * @return string 明文消息
     */
    private function deryptMsg($wechatModel, $encryptMsg) {
        if ($wechatModel->security_mode == FwCompanyWechat::SECURITY_MODE_ENCRYPT) {
            $encodingAesKey = trim($wechatModel->encoding_aes_key);
            $appId = trim($wechatModel->app_id);
            $token = trim($wechatModel->action_token);

            $msgSignature = Yii::$app->request->getQueryParam("msg_signature");
            $timeStamp = Yii::$app->request->getQueryParam("timestamp");
            $nonce = Yii::$app->request->getQueryParam("nonce");


            $decryptMsg = "";
            $pc = new WechatMsgCrypt();
            $pc->WechatMsgCrypt($token, $encodingAesKey, $appId);
            $errCode = $pc->decryptMsg($msgSignature, $timeStamp, $nonce, $encryptMsg, $decryptMsg);

            $commonServiceSerivce = new ServiceService();
            if ($errCode == WechatErrorCode::OK) {
//                $commonServiceSerivce->recordServiceLog($this->logServiceId, FwServiceLog::ACTION_STATUS_NORMAL, "解密后: " . $decryptMsg);
            } else {
                $commonServiceSerivce->recordServiceLog($this->logServiceId, FwServiceLog::ACTION_STATUS_ERROR, "解密失败：" . WechatErrorCode::GetCryptErrorMessage($errCode));
            }
        }
        else {
            $decryptMsg = $encryptMsg;
        }

        return $decryptMsg;
    }

    /**
     * 对消息进行必要的加密处理（取决于微信参数开关）
     * @param $wechatModel FwCompanyWechat
     * @param string $decryptMsg 解密消息
     * @return string 加密消息
     */
    private function encryptMsg($wechatModel, $decryptMsg) {
        if ($wechatModel->security_mode == FwCompanyWechat::SECURITY_MODE_ENCRYPT) {
            $encodingAesKey = trim($wechatModel->encoding_aes_key);
            $appId = trim($wechatModel->app_id);
            $token = trim($wechatModel->action_token);

            $pc = new WechatMsgCrypt();
            $pc->WechatMsgCrypt($token, $encodingAesKey, $appId);

            $timeStamp = Yii::$app->request->getQueryParam("timestamp");
            $nonce = Yii::$app->request->getQueryParam("nonce");

            $encryptMsg = '';
            $errCode = $pc->encryptMsg($decryptMsg, $timeStamp, $nonce, $encryptMsg);

            $commonServiceSerivce = new ServiceService();
            if ($errCode == WechatErrorCode::OK) {
//                    $commonServiceSerivce->recordServiceLog($this->logServiceId, FwServiceLog::ACTION_STATUS_NORMAL, "加密后: " . $encryptMsg);
            } else {
                $commonServiceSerivce->recordServiceLog($this->logServiceId, FwServiceLog::ACTION_STATUS_NORMAL, "加密失败: " . WechatErrorCode::GetCryptErrorMessage($errCode));
            }
        }
        else {
            $encryptMsg = $decryptMsg;
        }

        return $encryptMsg;
    }

    /**
     * 处理文本消息
     * @param $wechatModel FwCompanyWechat
     * @param $postObj
     * @return string
     */
    private function messageHandleText($wechatModel, $postObj)
    {
        $fromUsername = $postObj->FromUserName;
        $toUsername = $postObj->ToUserName;
        $content = trim($postObj->Content);
        $time = time();

        $textTpl = "<xml>
							<ToUserName><![CDATA[%s]]></ToUserName>
							<FromUserName><![CDATA[%s]]></FromUserName>
							<CreateTime>%s</CreateTime>
							<MsgType><![CDATA[text]]></MsgType>
							<Content><![CDATA[%s]]></Content>
							<FuncFlag>0</FuncFlag>
							</xml>";

        if(!empty($content))
        {
//            $contentStr = "欢迎来到【" . trim($wechatModel->mp_name) ."】";
            //暂时所有问题都由机器人回答
            if (!$this->getDataFromRobot($content, $contentStr)) {
                $contentStr = "Input something...";
            }
        }else{
            $contentStr = "Input something...";
        }

        $replyMsg = sprintf($textTpl, $fromUsername, $toUsername, $time, $contentStr);
        $resultStr = $this->encryptMsg($wechatModel, $replyMsg);

        return $resultStr;
    }


    /**
     * 处理事件消息
     * @param $wechatModel FwCompanyWechat
     * @param $postObj
     * @return string
     */
    private function messageHandleEvent($wechatModel, $postObj)
    {
        $fromUsername = $postObj->FromUserName;
        $toUsername = $postObj->ToUserName;
        $content = trim($postObj->Content);
        $event = $postObj->Event;
        $time = time();

        $textTpl = "<xml>
                    <ToUserName><![CDATA[%s]]></ToUserName>
                    <FromUserName><![CDATA[%s]]></FromUserName>
                    <CreateTime>%s</CreateTime>
                    <MsgType><![CDATA[text]]></MsgType>
                    <Content><![CDATA[%s]]></Content>
                    <FuncFlag>0</FuncFlag>
                    </xml>";
        $wechatQrsceneService = new WechatQrsceneService();
        if ($event == "subscribe") {
            if (isset($postObj->EventKey) && !empty($postObj->EventKey)) {
                //扫描带参数二维码事件
                $eventKey = $postObj->EventKey; //事件KEY值，qrscene_为前缀，后面为二维码的参数值
                $ticket = $postObj->Ticket; //二维码的ticket，可用来换取二维码图片

                $qrSceneId = str_replace("qrscene_","",$eventKey);

                $companyId = $wechatModel->company_id;
                $qrSceneModel = $wechatQrsceneService->GetQrsceneByQrSceneId($companyId,$qrSceneId,$ticket);
                if (!empty($qrSceneModel)) {
                    $userId = $qrSceneModel->qrscene_value;
                    $openId = $fromUsername;
                    if ($this->bindWechatAccount($companyId, $userId, $openId,$errMessage)) {
                        $userModel = FwUser::findOne($userId);

                        $contentStr = "感谢您关注【" . trim($wechatModel->mp_name) . "】，系统已为您绑定账户：" . $userModel->user_name;
                    }
                    else {
                        $contentStr = "感谢您关注【" . trim($wechatModel->mp_name) . "】";
                    }
//                    else {
//                        $contentStr = "感谢您关注【" . trim($wechatModel->mp_name) . "】，companyId:".$companyId."，userId:".$userId."，openId:".$openId."，qrSceneId:".$qrSceneId."，errMessage:".$errMessage;
//                    }
                }
                else {
                    $contentStr = "感谢您关注【" . trim($wechatModel->mp_name) . "】";
                }
            }
            else {
                //单纯关注事件
                $contentStr = "感谢您关注【" . trim($wechatModel->mp_name) . "】";
            }
        } else if ($event == "unsubscribe") {
            $contentStr = "感谢您曾经对【" . trim($wechatModel->mp_name) . "】的关注，欢迎再来";
        } else if ($event == "SCAN") {
            //用户已关注时的事件推送
            if (isset($postObj->EventKey)) {
                //扫描带参数二维码事件
                $eventKey = $postObj->EventKey; //事件KEY值，是一个32位无符号整数，即创建二维码时的二维码scene_id
                $ticket = $postObj->Ticket; //二维码的ticket，可用来换取二维码图片

                $qrSceneId = strval($eventKey);
                $companyId = $wechatModel->company_id;
                $qrSceneModel = $wechatQrsceneService->GetQrsceneByQrSceneId($companyId,$qrSceneId,$ticket);

//                $contentStr = "已扫描，companyId:".$companyId;
                if (!empty($qrSceneModel)) {
                    $userId = $qrSceneModel->qrscene_value;
                    $openId = $fromUsername;
//                    $contentStr = "已扫描，companyId:".$companyId."，userId:".$userId."，openId:".$openId."，qrSceneId:".$qrSceneId;
                    if ($this->bindWechatAccount($companyId, $userId, $openId, $errMessage)) {
                        $userModel = FwUser::findOne($userId);
                        $contentStr = "感谢您关注【" . trim($wechatModel->mp_name) . "】，系统已为您绑定账户：" . $userModel->user_name;
                    }
                    else {
                        $contentStr = "感谢您关注【" . trim($wechatModel->mp_name) . "】，绑定账户失败：" . $errMessage;
                    }
//                    else {
//                        $contentStr = "已扫描，companyId:".$companyId."，userId:".$userId."，openId:".$openId."，qrSceneId:".$qrSceneId."，errMessage:".$errMessage;
//                    }
                }
            }
        } else if ($event == "LOCATION") {
            //上报地理位置事件
            $latitude = $postObj->Latitude; //地理位置纬度
            $longitude = $postObj->Longitude; //地理位置经度
            $precision = $postObj->Precision; //地理位置精度

            $contentStr = "LOCATION:" . $latitude . "," . $longitude . "," . $precision;
        } else if ($event == "CLICK") {
            //点击菜单拉取消息时的事件推送
            //用户点击自定义菜单后，微信会把点击事件推送给开发者，请注意，点击菜单弹出子菜单，不会产生上报。

            $eventKey = $postObj->EventKey; //事件KEY值，与自定义菜单接口中KEY值对应
            $contentStr = "CLICK:" . $eventKey;
        }else if ($event == "VIEW") {
            //点击菜单跳转链接时的事件推送
            //用户点击自定义菜单后，微信会把点击事件推送给开发者，请注意，点击菜单弹出子菜单，不会产生上报。

            $eventKey = $postObj->EventKey; //事件KEY值，设置的跳转URL
            $contentStr = "VIEW:" . $eventKey;
        }else if ($event == "MASSSENDJOBFINISH") {
            //事件推送群发结果
            //由于群发任务提交后，群发任务可能在一定时间后才完成，因此，群发接口调用时，仅会给出群发任务是否提交成功的提示，
            //若群发任务提交成功，则在群发任务结束时，会向开发者在公众平台填写的开发者URL（callback URL）推送事件。
            $eventKey = $postObj->EventKey; //事件KEY值，设置的跳转URL
            $contentStr = "MASSSENDJOBFINISH:" . $eventKey;
        }else {
            $contentStr = "Unknow Event: " . $event;
        }


        if (!empty($contentStr)) {
            $replyMsg = sprintf($textTpl, $fromUsername, $toUsername, $time, $contentStr);

            $resultStr = $this->encryptMsg($wechatModel, $replyMsg);

            return $resultStr;
        }
    }

    /**
     * 生成网页授权的跳转链接
     * @param $app_id
     * @param $redirect
     * @param string $scope
     * @param string $state
     * @return string
     */
    public function getWebAuthUrl($app_id,$redirect,$scope = 'snsapi_userinfo',$state = 'T') {
        $param = [
            'appid' => $app_id,
            'redirect_uri' => $redirect,
            'response_type' => 'code',
            'scope' => $scope,
            'state' => $state
        ];
        return self::WECHAT_WEB_AUTH_URL.'?'.http_build_query($param).'#wechat_redirect';
    }

    /**
     * 获取网页授权access token
     * @param $app_id
     * @param $app_secret
     * @param $code
     * @return null
     */
    public function getWebAuthAccessToken($app_id,$app_secret,$code) {
        $param = [
            'appid' => $app_id,
            'secret' => $app_secret,
            'code' => $code,
            'grant_type' => 'authorization_code'
        ];

        $url = self::WECHAT_WEB_TOKEN_URL.'?'.http_build_query($param);
        $result = $error = null;
        $this->getDataFromWechat($url,self::METHOD_GET,null,true,$result,$error);
        return $result;
    }

    /**
     * 网页授权获取用户信息
     * @param $access_token
     * @param $openid
     * @param string $lang
     * @return array|bool
     */
    public function getWebAuthUserInfo($access_token,$openid,$lang = 'zh_CN') {
        $param = [
            'access_token' => $access_token,
            'openid' => $openid,
            'lang' => $lang
        ];
        $url = self::WECHAT_WEB_AUTH_USER_INFO . '?' .http_build_query($param);
        $result = $error = null;
        $ret = $this->getDataFromWechat($url,self::METHOD_GET,null,true,$result,$error);
        if($ret) {
            $res = json_decode($result,true);
            if(is_array($res) && isset($res['errcode'])) {
                return false;
            }
            $res['subscribe'] = 0;
            $res['subscribe_time'] = time();
            $res['remark'] = 'web auth';
            $res['groupid'] = 0;
            $res['language'] = $lang;
            unset($res['privilege']);
            return $res;
        } else {
            return false;
        }
    }
}