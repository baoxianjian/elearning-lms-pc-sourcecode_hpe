<?php
/**
 * Created by PhpStorm.
 * User: t62539
 * Date: 12/26/2015
 * Time: 9:21 PM
 */

namespace common\helpers;


use common\models\framework\FwCompany;
use common\models\framework\FwDictionary;
use common\services\framework\CompanyService;
use common\services\framework\DictionaryService;
use common\services\framework\DomainService;
use common\services\framework\UserService;
use common\base\BaseActiveRecord;
use common\crpty\CryptErrorCode;
use common\crpty\MessageCrypt;
use Exception;
use Yii;

class TBaseHelper
{
    private static $encodingKey = "hpe-online-lms"; //密钥
    private static $systemVersion = "5.0"; //当前系统版本号

    public static $devEnvironmentSites = ['127.0.0.1', 'localhost', 'develop.elearning.com', 'develop', 'test.elearning.com', 'test'];

    /**
     * 优先取个人配置的语言信息(如果已经登陆的话)
     * 其次取企业的默认语言(如果已登陆的话取个人对应的企业,否则取二级域名对应的企业)
     * 最后取系统参数字典中配置的默认语言
     * 如果都没有设置,则默认中文
     */
    public static function getLanguage()
    {
        try {
            $language = null;

            //如果有lang参数，优先使用
            if (!empty(Yii::$app->request->getQueryParam("lang"))) {
                $language = Yii::$app->request->getQueryParam("lang");
            }

            if (!Yii::$app->user->getIsGuest()) {

                $currentUserId = strval(Yii::$app->user->getId());
                $sessionKey = "Language_" . $currentUserId;
                if (Yii::$app->session->has($sessionKey)) {
                    $language = Yii::$app->session->get($sessionKey);
                } else {
                    if (empty($language)) {
                        $language = Yii::$app->user->identity->language;
                    }
                    if (empty($language)) {
                        $companyId = Yii::$app->user->identity->company_id;

                        $companyModel = FwCompany::findOne($companyId);

                        if (!empty($companyModel)) {
                            $language = $companyModel->language;
                        }
                    }
                }
            } else {
                if (empty($language)) {
                    $cacheKey = "Common_Language";

                    $language = BaseActiveRecord::loadFromCache($cacheKey, true, $hasCache);

                    if (empty($language) && !$hasCache) {

                        $hostUrl = Yii::$app->request->getHostInfo();
                        $hostName = str_replace(['http:', 'https:', '/'], ['', '', ''], $hostUrl);
                        $position = strpos($hostName, ":");
                        if ($position == false || $position == 0) {
                            //不包含端口号，不做处理
                        } else {
                            $hostName = substr($hostName, 0, $position);
                        }
                        $companyModel = null;

                        $secondLevelDomainOpen = false;//二级域名功能启用开关
                        $isTopLevelDomain = false;//是否主站域名

                        if (isset(Yii::$app->params['main_site_url'])) {
                            $main_site_url = Yii::$app->params['main_site_url'];
                        }

                        if (isset(Yii::$app->params['cachedSites'])) {
                            $cachedSites = Yii::$app->params['cachedSites'];
                        }

                        if (isset($main_site_url) && !empty($main_site_url)) {
                            if (is_array($main_site_url)) {//主站域名配置如果是数组
                                $isTopLevelDomain = in_array($hostName, $main_site_url);
                            } else if ($main_site_url == $hostName) {
                                $isTopLevelDomain = true;
                            }
                        } else {
                            //如果没有设置main_site_url，任何站点都是主站
                            $isTopLevelDomain = true;
                        }

                        if (!$isTopLevelDomain && isset($cachedSites) && count($cachedSites) > 0) {
                            $secondLevelDomainOpen = in_array($hostName, $cachedSites);
                        } else {
                            //如果没有设置cachedSites，任何站点都是主站
                            $isTopLevelDomain = true;
                        }

                        if (!$isTopLevelDomain) {
                            if ($secondLevelDomainOpen) {
                                $companyModel = FwCompany::findOne(['second_level_domain' => $hostName, 'status' => FwCompany::STATUS_FLAG_NORMAL]);

                                if (!empty($companyModel)) {
                                    $cacheKey = "Company_Language_" . $companyModel->kid;
                                    $language = $companyModel->language;
                                }
                            } else {
//                    $this->redirect('error');
                                //如果没找到配置的域名，则认为是主站点
                            }
                        }
                    }

                }
            }

            if (empty($language)) {
                $service = new DictionaryService();
                $language = $service->getDictionaryValueByCode("system", "default_language");
            }

            if (empty($language)) {
                $language = "zh-CN";
            }


            if (!Yii::$app->user->getIsGuest()) {

                Yii::$app->session->set($sessionKey, $language);
            } else {
                if (!$hasCache) {
                    BaseActiveRecord::saveToCache($cacheKey, $language);
                }
            }
        } catch (Exception $e) {
            $errMsg = $e->getMessage();
        }

        return $language;
    }


    /**
     * 优先取个人配置的默认主题(如果已经登陆的话)
     * 其次取企业的默认主题(如果已登陆的话取个人对应的企业,否则取二级域名对应的企业)
     * 最后取系统参数字典中配置的默认主题
     * 如果都没有设置,则不启用主题
     */
    public static function getTheme()
    {
        try {
            $theme = null;
            if (!Yii::$app->user->getIsGuest()) {
                $currentUserId = strval(Yii::$app->user->getId());
                $sessionKey = "Theme_" . $currentUserId;
                if (Yii::$app->session->has($sessionKey)) {
                    $theme = Yii::$app->session->get($sessionKey);
                } else {
                    $theme = Yii::$app->user->identity->theme;
                    if (empty($theme)) {
                        $companyId = Yii::$app->user->identity->company_id;

                        $companyModel = FwCompany::findOne($companyId);

                        if (!empty($companyModel)) {
                            $theme = $companyModel->theme;
                        }
                    }
                }
            } else {
                $hostUrl = Yii::$app->request->getHostInfo();
                $hostName = str_replace(['http:', 'https:', '/'], ['', '', ''], $hostUrl);
                $position = strpos($hostName, ":");
                if ($position == false || $position == 0) {
                    //不包含端口号，不做处理
                } else {
                    $hostName = substr($hostName, 0, $position);
                }

                $cacheKey = "Common_Theme_" . $hostName;

                $theme = BaseActiveRecord::loadFromCache($cacheKey, true, $hasCache);
                if (empty($theme) && !$hasCache) {
                    $companyModel = null;

                    $secondLevelDomainOpen = false;//二级域名功能启用开关
                    $isTopLevelDomain = false;//是否主站域名

                    if (isset(Yii::$app->params['main_site_url'])) {
                        $main_site_url = Yii::$app->params['main_site_url'];
                    }

                    if (isset(Yii::$app->params['cachedSites'])) {
                        $cachedSites = Yii::$app->params['cachedSites'];
                    }

                    if (isset($main_site_url) && !empty($main_site_url)) {
                        if (is_array($main_site_url)) {//主站域名配置如果是数组
                            $isTopLevelDomain = in_array($hostName, $main_site_url);
                        } else if ($main_site_url == $hostName) {
                            $isTopLevelDomain = true;
                        }
                    } else {
                        //如果没有设置main_site_url，任何站点都是主站
                        $isTopLevelDomain = true;
                    }

                    if (!$isTopLevelDomain && isset($cachedSites) && count($cachedSites) > 0) {
                        $secondLevelDomainOpen = in_array($hostName, $cachedSites);
                    } else {
                        //如果没有设置cachedSites，任何站点都是主站
                        $isTopLevelDomain = true;
                    }

                    if (!$isTopLevelDomain) {
                        if ($secondLevelDomainOpen) {
                            $companyModel = FwCompany::findOne(['second_level_domain' => $hostName, 'status' => FwCompany::STATUS_FLAG_NORMAL]);

                            if (!empty($companyModel)) {
//                                $cacheKey = "Company_Theme_" . $companyModel->kid;
                                $theme = $companyModel->theme;
                            }
                        } else {
//                    $this->redirect('error');、
                            //如果没找到配置的域名，则认为是主站点
                        }
                    }
                }
            }

            if (empty($theme)) {
                $service = new DictionaryService();
                $theme = $service->getDictionaryValueByCode("system", "default_theme");
            }
            if (!Yii::$app->user->getIsGuest()) {
                Yii::$app->session->set($sessionKey, $theme);
            } else {
                if (!$hasCache) {
                    BaseActiveRecord::saveToCache($cacheKey, $theme);
                }
            }
        } catch (Exception $e) {
            $errMsg = $e->getMessage();
        }

        return $theme;
    }

    public static function getCpuData($speed = 0.5)
    {

        if (false === ($prevVal = @file("/proc/stat"))) return false;
        $prevVal = implode($prevVal, PHP_EOL);
        $prevArr = explode(' ', trim($prevVal));
        $prevTotal = $prevArr[2] + $prevArr[3] + $prevArr[4] + $prevArr[5];
        $prevIdle = $prevArr[5];
        usleep($speed * 1000000);
        $val = @file("/proc/stat");
        $val = implode($val, PHP_EOL);
        $arr = explode(' ', trim($val));
        $total = $arr[2] + $arr[3] + $arr[4] + $arr[5];
        $idle = $arr[5];
        $intervalTotal = intval($total - $prevTotal);
        return round(100 * (($intervalTotal - ($idle - $prevIdle)) / $intervalTotal),2);
    }

    /**
     * Cpu相关信息
     * @return array|bool
     */
    public static function getCpuInfoData()
    {
        if (false === ($str = @file("/proc/cpuinfo"))) return false;
        $str = implode("", $str);
        @preg_match_all("/model\s+name\s{0,}\:+\s{0,}([\w\s\)\(\@.-]+)([\r\n]+)/s", $str, $model);
        @preg_match_all("/cpu\s+MHz\s{0,}\:+\s{0,}([\d\.]+)[\r\n]+/", $str, $mhz);
        @preg_match_all("/cache\s+size\s{0,}\:+\s{0,}([\d\.]+\s{0,}[A-Z]+[\r\n]+)/", $str, $cache);
        @preg_match_all("/bogomips\s{0,}\:+\s{0,}([\d\.]+)[\r\n]+/", $str, $bogomips);
        $res = array();
        if (false !== is_array($model[1])) {
            $res['cpu']['num'] = sizeof($model[1]);
            if ($res['cpu']['num'] == 1)
                $x1 = '';
            else
                $x1 = ' &times;' . $res['cpu']['num'];
            $res['cpu']['mhz'] = $mhz[1][0];
            $mhz[1][0] = ' <br />   频率:' . $mhz[1][0];
            $res['cpu']['cache'] = $cache[1][0];
            $cache[1][0] = ' <br />   二级缓存:' . $cache[1][0];
            $res['cpu']['bogomips'] = $bogomips[1][0];
            $bogomips[1][0] = ' <br />  Bogomips:' . $bogomips[1][0];
            $res['cpu']['model'][] = $model[1][0] . $mhz[1][0] . $cache[1][0] . $bogomips[1][0] . $x1;
            if (false !== is_array($res['cpu']['model'])) $res['cpu']['model'] = implode("<br />", $res['cpu']['model']);
            if (false !== is_array($res['cpu']['mhz'])) $res['cpu']['mhz'] = implode("<br />", $res['cpu']['mhz']);
            if (false !== is_array($res['cpu']['cache'])) $res['cpu']['cache'] = implode("<br />", $res['cpu']['cache']);
            if (false !== is_array($res['cpu']['bogomips'])) $res['cpu']['bogomips'] = implode("<br />", $res['cpu']['bogomips']);
        }
        return $res;
    }

    /**
     * 内存相关数据
     * 单位为B
     * @return array|bool
     */
    public static function getMenData()
    {
        if (false === ($str = @file("/proc/meminfo"))) return false;
        $str = implode("", $str);
        preg_match_all("/MemTotal\s{0,}\:+\s{0,}([\d\.]+).+?MemFree\s{0,}\:+\s{0,}([\d\.]+).+?Cached\s{0,}\:+\s{0,}([\d\.]+).+?SwapTotal\s{0,}\:+\s{0,}([\d\.]+).+?SwapFree\s{0,}\:+\s{0,}([\d\.]+)/s", $str, $buf);
        preg_match_all("/Buffers\s{0,}\:+\s{0,}([\d\.]+)/s", $str, $buffers);
        $res = array();
        $res['memTotal'] = round($buf[1][0] / 1024, 2);
        $res['memFree'] = round($buf[2][0] / 1024, 2);
        $res['memBuffers'] = round($buffers[1][0] / 1024, 2);
        $res['memCached'] = round($buf[3][0] / 1024, 2);
        $res['memUsed'] = $res['memTotal'] - $res['memFree'];
        $res['memPercent'] = (floatval($res['memTotal']) != 0) ? round($res['memUsed'] / $res['memTotal'] * 100, 2) : 0;

        $res['memRealUsed'] = $res['memTotal'] - $res['memFree'] - $res['memCached'] - $res['memBuffers']; //真实内存使用
        $res['memRealFree'] = $res['memTotal'] - $res['memRealUsed']; //真实空闲
        $res['memRealPercent'] = (floatval($res['memTotal']) != 0) ? round($res['memRealUsed'] / $res['memTotal'] * 100, 2) : 0; //真实内存使用率

        $res['memCachedPercent'] = (floatval($res['memCached']) != 0) ? round($res['memCached'] / $res['memTotal'] * 100, 2) : 0; //Cached内存使用率

        $res['swapTotal'] = round($buf[4][0] / 1024, 2);
        $res['swapFree'] = round($buf[5][0] / 1024, 2);
        $res['swapUsed'] = round($res['swapTotal'] - $res['swapFree'], 2);
        $res['swapPercent'] = (floatval($res['swapTotal']) != 0) ? round($res['swapUsed'] / $res['swapTotal'] * 100, 2) : 0;


        //判断内存如果小于1G，就显示M，否则显示G单位

        if ($res['memTotal'] < 1024) {
            $memTotal = $res['memTotal'] . " M";
            $mt = $res['memTotal'] . " M";
            $mu = $res['memUsed'] . " M";
            $mf = $res['memFree'] . " M";
            $mc = $res['memCached'] . " M"; //cache化内存
            $mb = $res['memBuffers'] . " M"; //缓冲
            $st = $res['swapTotal'] . " M";
            $su = $res['swapUsed'] . " M";
            $sf = $res['swapFree'] . " M";
            $swapPercent = $res['swapPercent'];
            $memRealUsed = $res['memRealUsed'] . " M"; //真实内存使用
            $memRealFree = $res['memRealFree'] . " M"; //真实内存空闲
            $memRealPercent = $res['memRealPercent']; //真实内存使用比率
            $memPercent = $res['memPercent']; //内存总使用率
            $memCachedPercent = $res['memCachedPercent']; //cache内存使用率
        } else {
            $memTotal = round($res['memTotal'] / 1024, 3) . " G";
            $mt = round($res['memTotal'] / 1024, 3) . " G";
            $mu = round($res['memUsed'] / 1024, 3) . " G";
            $mf = round($res['memFree'] / 1024, 3) . " G";
            $mc = round($res['memCached'] / 1024, 3) . " G";
            $mb = round($res['memBuffers'] / 1024, 3) . " G";
            $st = round($res['swapTotal'] / 1024, 3) . " G";
            $su = round($res['swapUsed'] / 1024, 3) . " G";
            $sf = round($res['swapFree'] / 1024, 3) . " G";
            $swapPercent = $res['swapPercent'];
            $memRealUsed = round($res['memRealUsed'] / 1024, 3) . " G"; //真实内存使用
            $memRealFree = round($res['memRealFree'] / 1024, 3) . " G"; //真实内存空闲
            $memRealPercent = $res['memRealPercent']; //真实内存使用比率
            $memPercent = $res['memPercent']; //内存总使用率
            $memCachedPercent = $res['memCachedPercent']; //cache内存使用率
        }

        $res['u_memTotal'] = $memTotal;
        $res['u_memTotal'] = $mt;
        $res['u_memUsed'] = $mu;
        $res['u_memFree'] = $mf;
        $res['u_memCached'] = $mc; //cache化内存
        $res['u_memBuffers'] = $mb; //缓冲
        $res['u_swapTotal'] = $st;
        $res['u_swapUsed'] = $su;
        $res['u_swapFree'] = $sf;
        $res['u_swapPercent'] = $swapPercent;
        $res['u_memRealUsed'] = $memRealUsed; //真实内存使用
        $res['u_memRealFree'] = $memRealFree; //真实内存空闲
        $res['u_memRealPercent'] = $memRealPercent; //真实内存使用比率
        $res['u_memPercent'] = $memPercent; //内存总使用率
        $res['u_memCachedPercent'] = $memCachedPercent; //cache内存使用率
        return $res;
    }


    /**
     * 硬盘情况
     * @return array
     */
    public static function getDiskData()
    {
        $iTotal = round(@disk_total_space(".") / (1024 * 1024 * 1024), 3); //总
        $iUsableness = round(@disk_free_space(".") / (1024 * 1024 * 1024), 3); //可用
        $iImpropriate = $iTotal - $iUsableness; //已用
        $iPercent = (floatval($iTotal) != 0) ? round($iImpropriate / $iTotal * 100, 2) : 0;
        $sDesc = '';
        if($iPercent>85){
            $sDesc ="磁盘空间不多了，建议近期清理一下日志";
        }
        if($iPercent>95){
            $sDesc ="磁盘空间快满了，可能会影响审计系统正常使用，建议立即清理日志";
        }

        return array(
            'iTotal' => $iTotal,
            'iUsableness' => $iUsableness,
            'iImpropriate' => $iImpropriate,
            'iPercent' => $iPercent,
            'sDesc' => $sDesc,
        );
    }

    /**
     * 网接相关流量信息
     */
    public static function getInterfaceData()
    {

        //mii-tool
        $strs = @file("/proc/net/dev");

        for ($i = 2; $i < count($strs); $i++) {
            preg_match_all("/([^\s]+):[\s]{0,}(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)/", $strs[$i], $info);
            $NetOutSpeed[$i] = $info[10][0]; //入网实时
            $NetInputSpeed[$i] = $info[2][0]; //出网实时
            $NetInput[$i] = self::formatsize($info[2][0]); //入网流量
            $NetOut[$i] = self::formatsize($info[10][0]); //出网流量
        }
    }

    /**
     * 单位自动转换
     * @param $size
     * @return string
     */
    public static function formatSize($size)
    {
        $danwei = array(' B ', ' K ', ' M ', ' G ', ' T ');
        $allsize = array();
        $i = 0;

        for ($i = 0; $i < 5; $i++) {
            if (floor($size / pow(1024, $i)) == 0) {
                break;
            }
        }
        $allsize1 = array();
        for ($l = $i - 1; $l >= 0; $l--) {
            $allsize1[$l] = floor($size / pow(1024, $l));
            if (isset($allsize1[$l + 1])) {

                $allsize[$l] = $allsize1[$l] - $allsize1[$l + 1] * 1024;
            }
        }

        $len = count($allsize);
        $fsize = "";
        for ($j = $len - 1; $j >= 0; $j--) {
            $fsize = $fsize . $allsize[$j] . $danwei[$j];
        }
        return $fsize;
    }

    /**
     * 获取Mac地址
     * @return null|string
     */
    public static function getMacAddress()
    {
        $osType = strtolower(PHP_OS);
        $returnArray = null;// 返回带有MAC地址的字串数组
        $macAddress = null;
        if (strlen($osType) >= 3) {
            $osShort = substr($osType, 0, 3);
        } else {
            $osShort = $osType;
        }
        if ($osShort == "win") {
            $returnArray = self::getMacAddressForWindows();
        } else {
            $returnArray = self::getMacAddressForLinux();
        }
        if (!empty($returnArray) && count($returnArray) > 0) {
            $tempArray = array();

            foreach ($returnArray as $value) {
                if (preg_match("/[0-9a-f][0-9a-f][:-]" . "[0-9a-f][0-9a-f][:-]" . "[0-9a-f][0-9a-f][:-]" . "[0-9a-f][0-9a-f][:-]" . "[0-9a-f][0-9a-f][:-]" . "[0-9a-f][0-9a-f]/i", $value, $tempArray)) {
                    $macAddress = $tempArray[0];
                    break;
                }
            }

            unset($tempArray);
        }

        if (!empty($macAddress)) {
            $macAddress = strtoupper(str_replace(":", "-", $macAddress));
        } else {
//            //先显示操作系统名,以便查找问题
//            $macAddress = $osType;
        }

        return $macAddress;
    }

    private static function getMacAddressForWindows()
    {
        $returnArray = null;
        try {
            @exec("ipconfig /all", $returnArray);

            if (!$returnArray) {
                $ipconfig = $_SERVER["WINDIR"] . "\system32\ipconfig.exe";
                if (is_file($ipconfig))
                    @exec($ipconfig . " /all", $returnArray);
                else
                    @exec($_SERVER["WINDIR"] . "\system\ipconfig.exe /all", $returnArray);
            }
        } catch (Exception $ex) {
            $ex->getMessage();
        }

        return $returnArray;
    }


    private static function getMacAddressForLinux(){
        $returnArray = null;
        try {
            @exec("ifconfig -a", $returnArray);
        }
        catch (Exception $ex) {
            $ex->getMessage();
        }
        return $returnArray;
    }



    /**
     * 获取机器码
     * @param $result //机器码
     * @param $errorMessage //错误消息
     * @return bool 成功与否
     */
    public static function getMachineCode($macAddress,&$result,&$errorMessage)
    {
        if (empty($macAddress)) {
            return false;
        }
        
        $companyService = new CompanyService();
        $companyCount = $companyService->getCompanyCount(true);

        $domainService = new DomainService();
        $domainCount = $domainService->getDomainCount(true);

        $userService = new UserService();
        $userCount = $userService->getUserCount(null, true, true);
        //机器码格式：Mac地址|总企业数|总域数|总人员数
        $machineCode = $macAddress . "|" . strval($companyCount) . "|" . strval($domainCount) . "|" . strval($userCount);
        $pc = new MessageCrypt();
        $pc->MessageCrypt(self::$encodingKey);
        $encryptMode = MessageCrypt::ENCRYPT_MODE_AES;
        $resultCode = $pc->encryptMsg($encryptMode, $machineCode, $result);
        if ($resultCode == CryptErrorCode::OK) {
            return true;
        } else {
            $errorMessage = CryptErrorCode::getCryptErrorMessage($resultCode);
            return false;
        }
    }

    /**
     * 检查注册码是否有效
     * @param $license //注册码
     * @param $errorMessage //错误消息
     * @return bool 是否有效
     */
    public static function checkLicense($license, &$errorMessage)
    {
        $cacheKey = "LMS_CORE_INFO_CHECK";

        $currentTime = time();

        if ($license == "Xw4/PxEP4n3FBbC2S8rWrgX0JTx2FO60x2Rai56TZfNlKtyK2qSbUeTQy/APnooKJceKPite5hVRcA956DDktljRIXT8rm+WGtdxYUyyHx8NiJh3BQP3TDPtc9vBqHKTL2QlqX4sfceVxZS+vLMCYzuSP2jXKv3p+cE5WULMnxs=" ||
            $license == "Xw4/PxEP4n3FBbC2S8rWrqlBUG5iKh1HoT7DFT0klR2AY/HyhCWtsVXqZ/dwPc7CwZvBORupBmvh4SdRkN5g89WGBQ/Cdrv7eT3R5GgJtg35hdGUldIJBdzrv5Bx4rNqMapRKgNF6lGSpCbRbVT/0GPrRXxKkFQlfLGLdxzkavk=")
        {
            $lastTime = strtotime('2016-12-31 23:59:59');

            if ($lastTime < $currentTime) {
                $errorMessage = Yii::t('common', "validate_time_exceed");
                return false;
            }
        }

        $checked = BaseActiveRecord::loadFromCache($cacheKey, true, $hasCache);

        if ($checked && !$hasCache) {
            return true;
        }
        else {
            $errorMessage = null;
            $macAddress = self::getMacAddress();
            $errMsg = Yii::t('system', 'frontend_name') . Yii::t('common', 'license_error');
            $isSuccess = true;
            if (empty($macAddress)) {
                return $isSuccess;//没网卡不做验证
            } else {
                if (empty($license)) {
                    $isSuccess = false;
                } else {
                    $pc = new MessageCrypt();
                    $pc->MessageCrypt(self::$encodingKey);
                    $encryptMode = MessageCrypt::ENCRYPT_MODE_AES;
                    $resultCode = $pc->decryptMsg($encryptMode, $license, $result);

                    if ($resultCode == CryptErrorCode::OK) {
                        //格式为:注册码ID|注册码版本|授权单位名|授权用户名|授权时间|授权MAC地址|授权版本号|总企业数|总域数|总人数|到期日|用户标识
                        $licenseArray = explode("|", $result);

                        try {

                            if (empty($licenseArray) || count($licenseArray) != 12) {
                                $isSuccess = false;
                            }

                            if ($isSuccess) {

                                $licenseId = $licenseArray[0];
                                $licenseVersion = $licenseArray[1];
                                $authCompany = $licenseArray[2];
                                $authUser = $licenseArray[3];
                                $authTime = $licenseArray[4];
                                $authMac = $licenseArray[5];
                                $authVersion = $licenseArray[6];
                                $authTotalCompany = $licenseArray[7];
                                $authTotalDomain = $licenseArray[8];
                                $authTotalUser = $licenseArray[9];
                                $authValidateTime = $licenseArray[10];
                                $authUserDetailInfo = $licenseArray[11];
                            }

                            if ($isSuccess && $authMac != "unlimited" && $authMac != $macAddress) {
                                $isSuccess = false;
                            }

                            if ($isSuccess && !empty($authVersion) && $authVersion != "unlimited") {
                                if (floor(self::$systemVersion) > floatval($authVersion)) {
                                    $errorMessage = Yii::t('common', "version_exceed_{number}", ["number" => $authVersion . ".X"]);
                                    $isSuccess = false;
                                }
                            }

                            if ($isSuccess && !empty($authTotalCompany) && $authTotalCompany != "0") {
                                $companyService = new CompanyService();
                                $companyCount = $companyService->getCompanyCount(true);

                                if ($companyCount > $authTotalCompany) {
                                    $errorMessage = Yii::t('common', "company_exceed_{number}", ["number" => $authTotalCompany]);
                                    $isSuccess = false;
                                }
                            }

                            if ($isSuccess && !empty($authTotalDomain) && $authTotalDomain != "0") {
                                $domainService = new DomainService();
                                $domainCount = $domainService->getDomainCount(true);

                                if ($domainCount > $authTotalDomain) {
                                    $errorMessage = Yii::t('common', "domain_exceed_{number}", ["number" => $authTotalDomain]);
                                    $isSuccess = false;
                                }
                            }

                            if ($isSuccess && !empty($authTotalUser) && $authTotalUser != "0") {
                                $userService = new UserService();
                                $userCount = $userService->getUserCount(null, true, true);

                                if ($userCount > $authTotalUser) {
                                    $errorMessage = Yii::t('common', "user_exceed_{number}", ["number" => $authTotalUser]);
                                    $isSuccess = false;
                                }
                            }

                            if ($isSuccess && !empty($authValidateTime)) {
                                $endTime = strtotime($authValidateTime . ' 23:59:59');

                                if ($endTime < $currentTime) {
                                    $errorMessage = Yii::t('common', "validate_time_exceed");
                                    $isSuccess = false;
                                }
                            }

                        } catch (Exception $ex) {
                            $isSuccess = false;
                        }
                    } else {
                        $isSuccess = false;
                    }
                }

                if (!$isSuccess && empty($errorMessage)) {
                    if (self::getMachineCode($macAddress, $machineCode, $errorMessage)) {
                        $errorMessage = $errMsg .
                            Yii::t('common', 'machine_code_info_{code}', ['code' => $machineCode]);

                    } else {
                        $errorMessage = $errMsg;
                    }
                }

            }

            if ($isSuccess) {
                BaseActiveRecord::saveToCache($cacheKey, true);
            }

            return $isSuccess;
        }
    }

    /**
     * 获得用户首页地址
     * @param string $returnType 返回类型：url、action
     * @return string
     */
    public static function getHomePage($returnType = 'url')
    {
        $companyId = Yii::$app->user->identity->company_id;
        $defaultPortal = FwCompany::USER_PORTAL;
        if (!empty($companyId)) {
            $companyModel = FwCompany::findOne($companyId);
            $defaultPortal = $companyModel->default_portal;
        }

        if ($defaultPortal == FwCompany::COMPANY_PORTAL) {
            $page = 'site/index';
        } else {
//                    Yii::getLogger()->log("start Login 3", Logger::LEVEL_ERROR);
            $page = 'student/index';
        }

        if ($returnType === 'action') {
            return $page;
        }

        $url = TURLHelper::createUrl($page);
        return $url;
    }

    /**
     * 是否使用MongoDB
     * @return bool
     */
    public static function isUseMongoDB(){
        $dictionaryService = new DictionaryService();
        $isUseMongoDB = $dictionaryService->getDictionaryValueByCode("system","is_use_mongodb");
        if ($isUseMongoDB == null) {
            return false;
        }
        else {
            if ($isUseMongoDB == FwDictionary::YES) {
                return true;
            }
            else {
                return false;
            }
        }
    }
}