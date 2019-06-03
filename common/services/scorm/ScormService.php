<?php
/**
 * Created by PhpStorm.
 * User: TangMingQiang
 * Date: 5/15/15
 * Time: 10:54 AM
 */

namespace common\services\scorm;

use common\models\framework\FwUser;
use common\models\learning\LnCourseComplete;
use common\models\learning\LnCoursewareScorm;
use common\models\learning\LnCoursewareScormRelate;
use common\models\learning\LnScormAiccSession;
use common\models\learning\LnScormScoes;
use common\models\learning\LnScormScoesData;
use common\models\learning\LnScormSeqMapinfo;
use common\models\learning\LnScormSeqObjective;
use common\models\learning\LnScormSeqRollru;
use common\models\learning\LnScormSeqRollrucond;
use common\models\learning\LnScormSeqRulecond;
use common\models\learning\LnScormSeqRuleconds;
use common\models\learning\LnModRes;
use common\services\learning\CourseCompleteService;
use common\services\learning\ResourceCompleteService;
use common\base\BaseActiveRecord;
use common\helpers\TXmlHelper;
use stdClass;
use yii;
use yii\helpers\Html;

class ScormService extends LnCoursewareScorm
{
    const SCO_ALL = "0";
    const SCO_DATA = "1";
    const SCO_ONLY = "2";

    const SCORM_12 = "1";
    const SCORM_13 = "2";
    const SCORM_AICC = "3";

    const GRADE_TYPE_NONE = "0";
    const GRADE_TYPE_VALUE = "1";
    const GRADE_TYPE_SCALE = "2";
    const GRADE_TYPE_TEXT = "3";

    const AICC_HACP_TIMEOUT = 200;

    /**
     * 判断组件是否Scorm类型的
     * @param $componentCode
     * @return bool
     */
    function isScormComponent($componentCode) {
        if ($componentCode == "scorm" || $componentCode == "aicc") {
            return true;
        }
        else {
            return false;
        }
    }

    /**
     * Check the hacp_session for whether it is valid.
     * @param $sessionId
     * @return null|LnScormAiccSession
     */
    function scorm_aicc_confirm_hacp_session($sessionId) {
        $allowAiccHACP = true;
        if (!$allowAiccHACP) {
            return null;
        }

        $time = time() - ($this::AICC_HACP_TIMEOUT * 60);
        $scormAICCSessionService = new ScormAICCSessionService();
        $sessionData = $scormAICCSessionService->getScormAICCSessionBySessionId($sessionId,$time);

        if (!empty($sessionData)) { // Update timemodified as this is still an active session - resets the timeout.
            $sessionData->save();
        }
        return $sessionData;
    }

    function scorm_add_time($a, $b) {
        $aes = explode(':', $a);
        $bes = explode(':', $b);
        $aseconds = explode('.', $aes[2]);
        $bseconds = explode('.', $bes[2]);
        $change = 0;

        $acents = 0;  // Cents.
        if (count($aseconds) > 1) {
            $acents = $aseconds[1];
        }
        $bcents = 0;
        if (count($bseconds) > 1) {
            $bcents = $bseconds[1];
        }
        $cents = $acents + $bcents;
        $change = floor($cents / 100);
        $cents = $cents - ($change * 100);
        if (floor($cents) < 10) {
            $cents = '0'. $cents;
        }

        $secs = $aseconds[0] + $bseconds[0] + $change;  // Seconds.
        $change = floor($secs / 60);
        $secs = $secs - ($change * 60);
        if (floor($secs) < 10) {
            $secs = '0'. $secs;
        }

        $mins = $aes[1] + $bes[1] + $change;   // Minutes.
        $change = floor($mins / 60);
        $mins = $mins - ($change * 60);
        if ($mins < 10) {
            $mins = '0' .  $mins;
        }

        $hours = $aes[0] + $bes[0] + $change;  // Hours.
        if ($hours < 10) {
            $hours = '0' . $hours;
        }

        if ($cents != '0') {
            return $hours . ":" . $mins . ":" . $secs . '.' . $cents;
        } else {
            return $hours . ":" . $mins . ":" . $secs;
        }
    }

    /**
     * 获取Aicc hacp交互时的Session信息
     * @param $courseRegId
     * @param $courseId
     * @param $coursewareId
     * @param $modResId
     * @param $scormId
     * @param $scormScoId
     * @param $modId
     * @param $userId
     * @param $attempt
     * @return null|string
     */
    function scorm_aicc_get_hacp_session($courseRegId, $courseId, $coursewareId, $modResId, $scormId, $scormScoId, $modId, $userId, $attempt) {
        $allowAiccHACP = true;
        if (!$allowAiccHACP) {
            return null;
        }
        $hacpSession = $this->random_string(20);
        $model = new LnScormAiccSession();
        $model->scorm_sco_id = $scormScoId;
        $model->scorm_id = $scormId;
        $model->course_reg_id = $courseRegId;
        $model->course_id = $courseId;
        $model->courseware_id = $coursewareId;
//        $model->courseware_id = $coursewareId;
        $model->mod_id = $modId;
        $model->mod_res_id = $modResId;
        $model->attempt = $attempt;
        $model->user_id = $userId;
        $model->hacp_session = $hacpSession;

        if ($model->save()){
            return $hacpSession;
        }
        else {
            return null;
        }
    }

    /**
     * 获取指定位数的随机码
     * @param int $length
     * @return string
     */
    function random_string($length=15) {
        $pool  = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $pool .= 'abcdefghijklmnopqrstuvwxyz';
        $pool .= '0123456789';
        $poollen = strlen($pool);
        $string = '';
        for ($i = 0; $i < $length; $i++) {
            $string .= substr($pool, (mt_rand()%($poollen)), 1);
        }
        return $string;
    }

    /**
     * 根据课件ID获取Scorm
     * @param $coursewareId
     * @return LnCoursewareScorm
     */
    function getScormByCoursewareId($coursewareId,$withCache=true) {
        $cacheKey = "Scorm_CoursewareId_" . $coursewareId;

        $result = self::loadFromCache($cacheKey, $withCache,$hasCache);

        if (empty($result) && !$hasCache) {
            $scormRelateModel = new LnCoursewareScormRelate();
            $scormRelateResult = $scormRelateModel->find(false)
                ->andFilterWhere(['=','courseware_id',$coursewareId])
                ->one();

            if (!empty($scormRelateResult)) {
                $result = LnCoursewareScorm::findOne($scormRelateResult->scorm_id);

                self::saveToCache($cacheKey, $result);
            }
        }

        return $result;
    }

    /**
     * 从scorm文件中获取RESOURCES节点信息
     * @param $blocks
     * @return array
     */
    function scorm_get_resources($blocks) {
        $resources = array();
        foreach ($blocks as $block) {
            if ($block['name'] == 'RESOURCES' && isset($block['children'])) {
                foreach ($block['children'] as $resource) {
                    if ($resource['name'] == 'RESOURCE') {
                        $resources[$this->addslashes_js($resource['attrs']['IDENTIFIER'])] = $resource['attrs'];
                    }
                }
            }
        }
        return $resources;
    }


    /**
     * 根据xml文件构建scorm对象
     * @param $blocks
     * @param $scoes
     * @return null
     */
    function scorm_get_manifest($blocks, $scoes) {

        static $parents = array();
        static $resources;

        static $manifest;
        static $organization;

        $manifestresourcesnotfound = array();
        if (count($blocks) > 0) {
            foreach ($blocks as $block) {
                switch ($block['name']) {
                    case 'METADATA': //METADATA节点，主要包括：schema，schemaversion和location信息
                        if (isset($block['children'])) {
                            foreach ($block['children'] as $metadata) {
                                if ($metadata['name'] == 'SCHEMAVERSION') {
                                    if (empty($scoes->version)) {
                                        $isversionset = (preg_match("/^(1\.2)$|^(CAM )?(1\.3)$/", $metadata['tagData'], $matches));
                                        if (isset($metadata['tagData']) && $isversionset) {
                                            $scoes->version = 'SCORM_'.$matches[count($matches) - 1];
                                        } else {
                                            $isversionset = (preg_match("/^2004 (3rd|4th) Edition$/", $metadata['tagData'], $matches));
                                            if (isset($metadata['tagData']) && $isversionset) {
                                                $scoes->version = 'SCORM_1.3';
                                            } else {
                                                $scoes->version = 'SCORM_1.2';
                                            }
                                        }
                                    }
                                }
                            }
                        }
                        break;
                    case 'MANIFEST':
                        $manifest = $block['attrs']['IDENTIFIER'];
                        $organization = '';
                        $resources = array();
                        $resources = $this->scorm_get_resources($block['children']);
                        $scoes = $this->scorm_get_manifest($block['children'], $scoes);
                        if (empty($scoes->elements) || count($scoes->elements) <= 0) {
                            foreach ($resources as $item => $resource) {
                                if (!empty($resource['HREF'])) {
                                    $sco = new stdClass();
                                    $sco->identifier = $item;
                                    $sco->title = $item;
                                    $sco->parent = '/';
                                    $sco->launch = $resource['HREF'];
                                    $sco->scorm_type = $resource['ADLCP:SCORMTYPE'];
                                    $scoes->elements[$manifest][$organization][$item] = $sco;
                                }
                            }
                        }
                        break;
                    case 'ORGANIZATIONS':
                        if (!isset($scoes->defaultorg) && isset($block['attrs']['DEFAULT'])) {
                            $scoes->defaultorg = $block['attrs']['DEFAULT'];
                        }
                        if (!empty($block['children'])) {
                            $scoes = $this->scorm_get_manifest($block['children'], $scoes);
                        }
                        break;
                    case 'ORGANIZATION':
                        $identifier = $block['attrs']['IDENTIFIER'];
                        $organization = '';
                        $scoes->elements[$manifest][$organization][$identifier] = new stdClass();
                        $scoes->elements[$manifest][$organization][$identifier]->identifier = $identifier;
                        $scoes->elements[$manifest][$organization][$identifier]->parent = '/';
                        $scoes->elements[$manifest][$organization][$identifier]->launch = '';
                        $scoes->elements[$manifest][$organization][$identifier]->scorm_type = '';

                        $parents = array();
                        $parent = new stdClass();
                        $parent->identifier = $identifier;
                        $parent->organization = $organization;
                        array_push($parents, $parent);
                        $organization = $identifier;

                        if (!empty($block['children'])) {
                            $scoes = $this->scorm_get_manifest($block['children'], $scoes);
                        }

                        array_pop($parents);
                        break;
                    case 'ITEM':
                        $parent = array_pop($parents);
                        array_push($parents, $parent);

                        $identifier = $block['attrs']['IDENTIFIER'];
                        $scoes->elements[$manifest][$organization][$identifier] = new stdClass();
                        $scoes->elements[$manifest][$organization][$identifier]->identifier = $identifier;
                        $scoes->elements[$manifest][$organization][$identifier]->parent = $parent->identifier;
                        if (!isset($block['attrs']['ISVISIBLE'])) {
                            $block['attrs']['ISVISIBLE'] = 'true';
                        }
                        $scoes->elements[$manifest][$organization][$identifier]->isvisible = $block['attrs']['ISVISIBLE'];
                        if (!isset($block['attrs']['PARAMETERS'])) {
                            $block['attrs']['PARAMETERS'] = '';
                        }
                        $scoes->elements[$manifest][$organization][$identifier]->parameters = $block['attrs']['PARAMETERS'];
                        if (!isset($block['attrs']['IDENTIFIERREF'])) {
                            $scoes->elements[$manifest][$organization][$identifier]->launch = '';
                            $scoes->elements[$manifest][$organization][$identifier]->scorm_type = 'asset';
                        } else {
                            $idref = $block['attrs']['IDENTIFIERREF'];
                            $base = '';
                            if (isset($resources[$idref]['XML:BASE'])) {
                                $base = $resources[$idref]['XML:BASE'];
                            }
                            if (!isset($resources[$idref])) {
                                $manifestresourcesnotfound[] = $idref;
                                $scoes->elements[$manifest][$organization][$identifier]->launch = '';
                            } else {
                                $scoes->elements[$manifest][$organization][$identifier]->launch = $base.$resources[$idref]['HREF'];
                                if (empty($resources[$idref]['ADLCP:SCORMTYPE'])) {
                                    $resources[$idref]['ADLCP:SCORMTYPE'] = 'asset';
                                }
                                $scoes->elements[$manifest][$organization][$identifier]->scorm_type = $resources[$idref]['ADLCP:SCORMTYPE'];
                            }
                        }

                        $parent = new stdClass();
                        $parent->identifier = $identifier;
                        $parent->organization = $organization;
                        array_push($parents, $parent);

                        if (!empty($block['children'])) {
                            $scoes = $this->scorm_get_manifest($block['children'], $scoes);
                        }

                        array_pop($parents);
                        break;
                    case 'TITLE':
                        $parent = array_pop($parents);
                        array_push($parents, $parent);
                        if (!isset($block['tagData'])) {
                            $block['tagData'] = '';
                        }
                        $scoes->elements[$manifest][$parent->organization][$parent->identifier]->title = $block['tagData'];
                        break;
                    case 'ADLCP:PREREQUISITES'://学习的前提条件
                        if ($block['attrs']['TYPE'] == 'aicc_script') {//用来识别ADLCP:PREREQUISITES的type是否为aicc_script
                            $parent = array_pop($parents);
                            array_push($parents, $parent);
                            if (!isset($block['tagData'])) {
                                $block['tagData'] = '';
                            }
                            $scoes->elements[$manifest][$parent->organization][$parent->identifier]->prerequisites = $block['tagData'];
                        }
                        break;
                    case 'ADLCP:MAXTIMEALLOWED'://允许尝试的最大时间，值如：00:30:00
                        $parent = array_pop($parents);
                        array_push($parents, $parent);
                        if (!isset($block['tagData'])) {
                            $block['tagData'] = '';
                        }
                        $scoes->elements[$manifest][$parent->organization][$parent->identifier]->maxtimeallowed = $block['tagData'];
                        break;
                    case 'ADLCP:TIMELIMITACTION'://当超过最大时间后的动作；值有：exit,message; exit,no message; continue,message; continue,nomessage;
                        $parent = array_pop($parents);
                        array_push($parents, $parent);
                        if (!isset($block['tagData'])) {
                            $block['tagData'] = '';
                        }
                        $scoes->elements[$manifest][$parent->organization][$parent->identifier]->timelimitaction = $block['tagData'];
                        break;
                    case 'ADLCP:DATAFROMLMS'://启动scorm sco后可显示的内容，值：小于255位的字符串
                        $parent = array_pop($parents);
                        array_push($parents, $parent);
                        if (!isset($block['tagData'])) {
                            $block['tagData'] = '';
                        }
                        $scoes->elements[$manifest][$parent->organization][$parent->identifier]->datafromlms = $block['tagData'];
                        break;
                    case 'ADLCP:MASTERYSCORE'://掌握分数；0-100
                        $parent = array_pop($parents);
                        array_push($parents, $parent);
                        if (!isset($block['tagData'])) {
                            $block['tagData'] = '';
                        }
                        $scoes->elements[$manifest][$parent->organization][$parent->identifier]->masteryscore = $block['tagData'];
                        break;
                    case 'ADLCP:COMPLETIONTHRESHOLD':
                        //完成与否的判断门槛，scorm 2004以上版本才有,值：0.0000 to 1.0000， <adlcp:completionThreshold completedByMeasure = “true” minProgressMeasure= “0.75” />
                        $parent = array_pop($parents);
                        array_push($parents, $parent);
                        if (!isset($block['attrs']['MINPROGRESSMEASURE'])) {
                            $block['attrs']['MINPROGRESSMEASURE'] = '1.0';
                        }
                        $scoes->elements[$manifest][$parent->organization][$parent->identifier]->threshold = $block['attrs']['MINPROGRESSMEASURE'];
                        break;
                    case 'ADLNAV:PRESENTATION':
                        //scorm 2004以上版本才有，值如下：
                        //<adlnav:presentation>
                        //<adlnav:navigationInterface>
                        //<adlnav:hideLMSUI>continue</adlnav:hideLMSUI>
                        //<adlnav:hideLMSUI>previous</adlnav:hideLMSUI>
                        //</adlnav:navigationInterface>
                        //</adlnav:presentation>
                        $parent = array_pop($parents);
                        array_push($parents, $parent);
                        if (!empty($block['children'])) {
                            foreach ($block['children'] as $adlnav) {
                                if ($adlnav['name'] == 'ADLNAV:NAVIGATIONINTERFACE') {
                                    foreach ($adlnav['children'] as $adlnavinterface) {
                                        if ($adlnavinterface['name'] == 'ADLNAV:HIDELMSUI') {
                                            if ($adlnavinterface['tagData'] == 'continue') {
                                                $scoes->elements[$manifest][$parent->organization][$parent->identifier]->hidecontinue = 1;
                                            }
                                            if ($adlnavinterface['tagData'] == 'previous') {
                                                $scoes->elements[$manifest][$parent->organization][$parent->identifier]->hideprevious = 1;
                                            }
                                            if ($adlnavinterface['tagData'] == 'exit') {
                                                $scoes->elements[$manifest][$parent->organization][$parent->identifier]->hideexit = 1;
                                            }
                                            if ($adlnavinterface['tagData'] == 'exitAll') {
                                                $scoes->elements[$manifest][$parent->organization][$parent->identifier]->hideexitall = 1;
                                            }
                                            if ($adlnavinterface['tagData'] == 'abandon') {
                                                $scoes->elements[$manifest][$parent->organization][$parent->identifier]->hideabandon = 1;
                                            }
                                            if ($adlnavinterface['tagData'] == 'abandonAll') {
                                                $scoes->elements[$manifest][$parent->organization][$parent->identifier]->hideabandonall = 1;
                                            }
                                            if ($adlnavinterface['tagData'] == 'suspendAll') {
                                                $scoes->elements[$manifest][$parent->organization][$parent->identifier]->hidesuspendall = 1;
                                            }
                                        }
                                    }
                                }
                            }
                        }
                        break;
                    case 'IMSSS:SEQUENCING':
                        //scorm 2004以上版本才有，<imsss:controlMode choice="false" choiceExit="false" flow="true" forwardOnly = "true"/>
                        $parent = array_pop($parents);
                        array_push($parents, $parent);
                        if (!empty($block['children'])) {
                            foreach ($block['children'] as $sequencing) {
                                if ($sequencing['name'] == 'IMSSS:CONTROLMODE') {
                                    if (isset($sequencing['attrs']['CHOICE'])) {
                                        $scoes->elements[$manifest][$parent->organization][$parent->identifier]->choice =
                                            $sequencing['attrs']['CHOICE'] == 'true' ? 1 : 0;
                                    }
                                    if (isset($sequencing['attrs']['CHOICEEXIT'])) {
                                        $scoes->elements[$manifest][$parent->organization][$parent->identifier]->choiceexit =
                                            $sequencing['attrs']['CHOICEEXIT'] == 'true' ? 1 : 0;
                                    }
                                    if (isset($sequencing['attrs']['FLOW'])) {
                                        $scoes->elements[$manifest][$parent->organization][$parent->identifier]->flow =
                                            $sequencing['attrs']['FLOW'] == 'true' ? 1 : 0;
                                    }
                                    if (isset($sequencing['attrs']['FORWARDONLY'])) {
                                        $scoes->elements[$manifest][$parent->organization][$parent->identifier]->forwardonly =
                                            $sequencing['attrs']['FORWARDONLY'] == 'true' ? 1 : 0;
                                    }
                                    if (isset($sequencing['attrs']['USECURRENTATTEMPTOBJECTINFO'])) {
                                        $scoes->elements[$manifest][$parent->organization][$parent->identifier]->usecurrentattemptobjectinfo =
                                            $sequencing['attrs']['USECURRENTATTEMPTOBJECTINFO'] == 'true' ? 1 : 0;
                                    }
                                    if (isset($sequencing['attrs']['USECURRENTATTEMPTPROGRESSINFO'])) {
                                        $scoes->elements[$manifest][$parent->organization][$parent->identifier]->usecurrentattemptprogressinfo =
                                            $sequencing['attrs']['USECURRENTATTEMPTPROGRESSINFO'] == 'true' ? 1 : 0;
                                    }
                                }
                                if ($sequencing['name'] == 'IMSSS:DELIVERYCONTROLS') {
                                    if (isset($sequencing['attrs']['TRACKED'])) {
                                        $scoes->elements[$manifest][$parent->organization][$parent->identifier]->tracked =
                                            $sequencing['attrs']['TRACKED'] == 'true' ? 1 : 0;
                                    }
                                    if (isset($sequencing['attrs']['COMPLETIONSETBYCONTENT'])) {
                                        $scoes->elements[$manifest][$parent->organization][$parent->identifier]->completionsetbycontent =
                                            $sequencing['attrs']['COMPLETIONSETBYCONTENT'] == 'true' ? 1 : 0;
                                    }
                                    if (isset($sequencing['attrs']['OBJECTIVESETBYCONTENT'])) {
                                        $scoes->elements[$manifest][$parent->organization][$parent->identifier]->objectivesetbycontent =
                                            $sequencing['attrs']['OBJECTIVESETBYCONTENT'] == 'true' ? 1 : 0;
                                    }
                                }
                                if ($sequencing['name'] == 'ADLSEQ:CONSTRAINEDCHOICECONSIDERATIONS') {
                                    if (isset($sequencing['attrs']['CONSTRAINCHOICE'])) {
                                        $scoes->elements[$manifest][$parent->organization][$parent->identifier]->constrainChoice =
                                            $sequencing['attrs']['CONSTRAINCHOICE'] == 'true' ? 1 : 0;
                                    }
                                    if (isset($sequencing['attrs']['PREVENTACTIVATION'])) {
                                        $scoes->elements[$manifest][$parent->organization][$parent->identifier]->preventactivation =
                                            $sequencing['attrs']['PREVENTACTIVATION'] == 'true' ? 1 : 0;
                                    }
                                }
                                if ($sequencing['name'] == 'IMSSS:OBJECTIVES') {
                                    $objectives = array();
                                    foreach ($sequencing['children'] as $objective) {
                                        $objectivedata = new stdClass();
                                        $objectivedata->primaryobj = 0;
                                        switch ($objective['name']) {
                                            case 'IMSSS:PRIMARYOBJECTIVE':
                                                $objectivedata->primaryobj = 1;
                                                break;//原文漏了break
                                            case 'IMSSS:OBJECTIVE':
                                                $objectivedata->satisfiedbymeasure = 0;
                                                if (isset($objective['attrs']['SATISFIEDBYMEASURE'])) {
                                                    $objectivedata->satisfiedbymeasure =
                                                        $objective['attrs']['SATISFIEDBYMEASURE'] == 'true' ? 1 : 0;
                                                }
                                                $objectivedata->objectiveid = '';
                                                if (isset($objective['attrs']['OBJECTIVEID'])) {
                                                    $objectivedata->objectiveid = $objective['attrs']['OBJECTIVEID'];
                                                }
                                                $objectivedata->minnormalizedmeasure = 1.0;
                                                if (!empty($objective['children'])) {
                                                    $mapinfos = array();
                                                    foreach ($objective['children'] as $objectiveparam) {
                                                        if ($objectiveparam['name'] == 'IMSSS:MINNORMALIZEDMEASURE') {
                                                            if (isset($objectiveparam['tagData'])) {
                                                                $objectivedata->minnormalizedmeasure = $objectiveparam['tagData'];
                                                            } else {
                                                                $objectivedata->minnormalizedmeasure = 0;
                                                            }
                                                        }
                                                        if ($objectiveparam['name'] == 'IMSSS:MAPINFO') {
                                                            $mapinfo = new stdClass();
                                                            $mapinfo->targetobjectiveid = '';
                                                            if (isset($objectiveparam['attrs']['TARGETOBJECTIVEID'])) {
                                                                $mapinfo->targetobjectiveid =
                                                                    $objectiveparam['attrs']['TARGETOBJECTIVEID'];
                                                            }
                                                            $mapinfo->readsatisfiedstatus = 1;
                                                            if (isset($objectiveparam['attrs']['READSATISFIEDSTATUS'])) {
                                                                $mapinfo->readsatisfiedstatus =
                                                                    $objectiveparam['attrs']['READSATISFIEDSTATUS'] == 'true' ? 1 : 0;
                                                            }
                                                            $mapinfo->writesatisfiedstatus = 0;
                                                            if (isset($objectiveparam['attrs']['WRITESATISFIEDSTATUS'])) {
                                                                $mapinfo->writesatisfiedstatus =
                                                                    $objectiveparam['attrs']['WRITESATISFIEDSTATUS'] == 'true' ? 1 : 0;
                                                            }
                                                            $mapinfo->readnormalizemeasure = 1;
                                                            if (isset($objectiveparam['attrs']['READNORMALIZEDMEASURE'])) {
                                                                $mapinfo->readnormalizemeasure =
                                                                    $objectiveparam['attrs']['READNORMALIZEDMEASURE'] == 'true' ? 1 : 0;
                                                            }
                                                            $mapinfo->writenormalizemeasure = 0;
                                                            if (isset($objectiveparam['attrs']['WRITENORMALIZEDMEASURE'])) {
                                                                $mapinfo->writenormalizemeasure =
                                                                    $objectiveparam['attrs']['WRITENORMALIZEDMEASURE'] == 'true' ? 1 : 0;
                                                            }
                                                            array_push($mapinfos, $mapinfo);
                                                        }
                                                    }
                                                    if (!empty($mapinfos)) {
                                                        $objectivedata->mapinfos = $mapinfos;//把原文$objectivesdata改成了$objectivedata
                                                    }
                                                }
                                                break;
                                        }
                                        array_push($objectives, $objectivedata);
                                    }
                                    $scoes->elements[$manifest][$parent->organization][$parent->identifier]->objectives = $objectives;
                                }
                                if ($sequencing['name'] == 'IMSSS:LIMITCONDITIONS') {
                                    if (isset($sequencing['attrs']['ATTEMPTLIMIT'])) {
                                        $scoes->elements[$manifest][$parent->organization][$parent->identifier]->attemptLimit =
                                            $sequencing['attrs']['ATTEMPTLIMIT'];
                                    }
                                    if (isset($sequencing['attrs']['ATTEMPTABSOLUTEDURATIONLIMIT'])) {
                                        $scoes->elements[$manifest][$parent->organization][$parent->identifier]->attemptAbsoluteDurationLimit =
                                            $sequencing['attrs']['ATTEMPTABSOLUTEDURATIONLIMIT'];
                                    }
                                }
                                if ($sequencing['name'] == 'IMSSS:ROLLUPRULES') {
                                    if (isset($sequencing['attrs']['ROLLUPOBJECTIVESATISFIED'])) {
                                        $scoes->elements[$manifest][$parent->organization][$parent->identifier]->rollupobjectivesatisfied =
                                            $sequencing['attrs']['ROLLUPOBJECTIVESATISFIED'] == 'true' ? 1 : 0;
                                    }
                                    if (isset($sequencing['attrs']['ROLLUPPROGRESSCOMPLETION'])) {
                                        $scoes->elements[$manifest][$parent->organization][$parent->identifier]->rollupprogresscompletion =
                                            $sequencing['attrs']['ROLLUPPROGRESSCOMPLETION'] == 'true' ? 1 : 0;
                                    }
                                    if (isset($sequencing['attrs']['OBJECTIVEMEASUREWEIGHT'])) {
                                        $scoes->elements[$manifest][$parent->organization][$parent->identifier]->objectivemeasureweight =
                                            $sequencing['attrs']['OBJECTIVEMEASUREWEIGHT'];
                                    }

                                    if (!empty($sequencing['children'])) {
                                        $rolluprules = array();
                                        foreach ($sequencing['children'] as $sequencingrolluprule) {
                                            if ($sequencingrolluprule['name'] == 'IMSSS:ROLLUPRULE' ) {
                                                $rolluprule = new stdClass();
                                                $rolluprule->childactivityset = 'all';
                                                if (isset($sequencingrolluprule['attrs']['CHILDACTIVITYSET'])) {
                                                    $rolluprule->childactivityset = $sequencingrolluprule['attrs']['CHILDACTIVITYSET'];
                                                }
                                                $rolluprule->minimumcount = 0;
                                                if (isset($sequencingrolluprule['attrs']['MINIMUMCOUNT'])) {
                                                    $rolluprule->minimumcount = $sequencingrolluprule['attrs']['MINIMUMCOUNT'];
                                                }
                                                $rolluprule->minimumpercent = 0.0000;
                                                if (isset($sequencingrolluprule['attrs']['MINIMUMPERCENT'])) {
                                                    $rolluprule->minimumpercent = $sequencingrolluprule['attrs']['MINIMUMPERCENT'];
                                                }
                                                if (!empty($sequencingrolluprule['children'])) {
                                                    foreach ($sequencingrolluprule['children'] as $rolluproleconditions) {
                                                        if ($rolluproleconditions['name'] == 'IMSSS:ROLLUPCONDITIONS') {
                                                            $conditions = array();
                                                            $rolluprule->conditioncombination = 'all';
                                                            if (isset($rolluproleconditions['attrs']['CONDITIONCOMBINATION'])) {
                                                                $rolluprule->CONDITIONCOMBINATION = $rolluproleconditions['attrs']['CONDITIONCOMBINATION'];
                                                            }
                                                            foreach ($rolluproleconditions['children'] as $rolluprulecondition) {
                                                                if ($rolluprulecondition['name'] == 'IMSSS:ROLLUPCONDITION') {
                                                                    $condition = new stdClass();
                                                                    if (isset($rolluprulecondition['attrs']['CONDITION'])) {
                                                                        $condition->cond = $rolluprulecondition['attrs']['CONDITION'];
                                                                    }
                                                                    $condition->operator = 'noOp';
                                                                    if (isset($rolluprulecondition['attrs']['OPERATOR'])) {
                                                                        $condition->operator = $rolluprulecondition['attrs']['OPERATOR'];
                                                                    }
                                                                    array_push($conditions, $condition);
                                                                }
                                                            }
                                                            $rolluprule->conditions = $conditions;
                                                        }
                                                        if ($rolluproleconditions['name'] == 'IMSSS:ROLLUPACTION') {
                                                            $rolluprule->rollupruleaction = $rolluproleconditions['attrs']['ACTION'];
                                                        }
                                                    }
                                                }
                                                array_push($rolluprules, $rolluprule);
                                            }
                                        }
                                        $scoes->elements[$manifest][$parent->organization][$parent->identifier]->rolluprules = $rolluprules;
                                    }
                                }

                                if ($sequencing['name'] == 'IMSSS:SEQUENCINGRULES') {
                                    if (!empty($sequencing['children'])) {
                                        $sequencingrules = array();
                                        foreach ($sequencing['children'] as $conditionrules) {
                                            $conditiontype = -1;
                                            switch($conditionrules['name']) {
                                                case 'IMSSS:PRECONDITIONRULE':
                                                    $conditiontype = 0;
                                                    break;
                                                case 'IMSSS:POSTCONDITIONRULE':
                                                    $conditiontype = 1;
                                                    break;
                                                case 'IMSSS:EXITCONDITIONRULE':
                                                    $conditiontype = 2;
                                                    break;
                                            }
                                            if (!empty($conditionrules['children'])) {
                                                $sequencingrule = new stdClass();
                                                foreach ($conditionrules['children'] as $conditionrule) {
                                                    if ($conditionrule['name'] == 'IMSSS:RULECONDITIONS') {
                                                        $ruleconditions = array();
                                                        $sequencingrule->conditioncombination = 'all';
                                                        if (isset($conditionrule['attrs']['CONDITIONCOMBINATION'])) {
                                                            $sequencingrule->conditioncombination = $conditionrule['attrs']['CONDITIONCOMBINATION'];
                                                        }
                                                        foreach ($conditionrule['children'] as $rulecondition) {
                                                            if ($rulecondition['name'] == 'IMSSS:RULECONDITION') {
                                                                $condition = new stdClass();
                                                                if (isset($rulecondition['attrs']['CONDITION'])) {
                                                                    $condition->cond = $rulecondition['attrs']['CONDITION'];
                                                                }
                                                                $condition->operator = 'noOp';
                                                                if (isset($rulecondition['attrs']['OPERATOR'])) {
                                                                    $condition->operator = $rulecondition['attrs']['OPERATOR'];
                                                                }
                                                                $condition->measurethreshold = 0.0000;
                                                                if (isset($rulecondition['attrs']['MEASURETHRESHOLD'])) {
                                                                    $condition->measurethreshold = $rulecondition['attrs']['MEASURETHRESHOLD'];
                                                                }
                                                                $condition->referencedobjective = '';
                                                                if (isset($rulecondition['attrs']['REFERENCEDOBJECTIVE'])) {
                                                                    $condition->referencedobjective = $rulecondition['attrs']['REFERENCEDOBJECTIVE'];
                                                                }
                                                                array_push($ruleconditions, $condition);
                                                            }
                                                        }
                                                        $sequencingrule->ruleconditions = $ruleconditions;
                                                    }
                                                    if ($conditionrule['name'] == 'IMSSS:RULEACTION') {
                                                        $sequencingrule->action = $conditionrule['attrs']['ACTION'];
                                                    }
                                                    $sequencingrule->type = $conditiontype;
                                                }
                                                array_push($sequencingrules, $sequencingrule);
                                            }
                                        }
                                        $scoes->elements[$manifest][$parent->organization][$parent->identifier]->sequencingrules = $sequencingrules;
                                    }
                                }
                            }
                        }
                        break;
                }
            }
        }
        if (!empty($manifestresourcesnotfound)) {
            // Throw warning to user to let them know manifest contains references to resources that don't appear to exist.
            $scoes = null;//如果$scoes为空，则这个scorm文件有问题
        }
        return $scoes;
    }


    /**
     * 解析scorm课件
     * Sets up SCORM 1.2/2004 packages using the manifest file.
     * Called whenever SCORM changes
     * @param object $scorm instance - fields are updated and changes saved into database
     * @param string $manifest - path to manifest file or stored_file.
     * @return bool
     */
    public function scorm_parse_scorm($scorm, $manifestInfo) {
        // Load manifest into string.
        $xmltext = $manifestInfo;//原文支持文件路径和文件对象2种类型，现在改为直接传入内容值

        $defaultorgid = 0;
        $firstinorg = 0;

        $pattern = '/&(?!\w{2,6};)/';
        $replacement = '&amp;';
        $xmltext = preg_replace($pattern, $replacement, $xmltext);

        $objxml = new TXmlHelper();
        $manifests = $objxml->parse($xmltext);
        $scoes = new stdClass();
        $scoes->version = '';
        $scoes = $this->scorm_get_manifest($manifests, $scoes);
        $newscoes = array();
        $sequenceNumber = 0;

        $scormScoesService = new ScormScoesService();
        if (count($scoes->elements) > 0) {

            //根据scorm_id获取所有scorm_scoes信息，此处要改
            $scormScoesDataService = new ScormScoesDataService();

            $scormScoesTrackService = new ScormScoesTrackService();
            $scormSeqMapinfoService = new ScormSeqMapinfoService();
            $scormSeqObjectiveService = new ScormSeqObjectiveService();


            $scormSeqRulecondsService = new ScormSeqRulecondsService();
            $scormSeqRulecondService = new ScormSeqRulecondService();

            $scormSeqRollupruleService = new ScormSeqRollupruleService();
            $scormSeqRolluprulecondService = new ScormSeqRolluprulecondService();

            $oldItemCount = $scormScoesDataService->getScormScoesDataCountByScormId($scorm->kid);

            //判断是否存在历史数据，如果存在，需要先清空
            if ($oldItemCount != 0) {

                $scormScoesTrackService->deleteScormScoesTrackByScormId($scorm->kid);
                $scormScoesDataService->deleteScormScoesDataByScormId($scorm->kid);

                $scormSeqMapinfoService->DeleteScormSeqMapinfoByScormId($scorm->kid);
                $scormSeqObjectiveService->DeleteScormSeqObjectiveByScormId($scorm->kid);

                $scormSeqRulecondService->DeleteScormSeqRulecondByScormId($scorm->kid);
                $scormSeqRulecondsService->DeleteScormSeqRulecondsByScormId($scorm->kid);


                $scormSeqRolluprulecondService->DeleteScormSeqRolluprulecondByScormId($scorm->kid);
                $scormSeqRollupruleService->DeleteScormSeqRollupruleByScormId($scorm->kid);

                $scormScoesService->deleteScormScoesByScormId($scorm->kid);
            }

            foreach ($scoes->elements as $manifest => $organizations) {
                foreach ($organizations as $organization => $items) {
                    foreach ($items as $identifier => $item) {
                        $sequenceNumber++;
                        // This new db mngt will support all SCORM future extensions.
                        $newitem = new LnScormScoes();
                        $newitem->scorm_id = $scorm->kid;
                        $newitem->manifest = $manifest;
                        $newitem->organization = $organization;
                        $newitem->sequence_number = $sequenceNumber;

                        $standarddatas = array('parent', 'identifier', 'launch', 'scorm_type', 'title');
                        foreach ($standarddatas as $standarddata) {
                            if (isset($item->$standarddata)) {
                                $newitem->$standarddata = $item->$standarddata;
                            } else {
                                $newitem->$standarddata = '';
                            }
                        }

                        if (!empty($defaultorgid) && !empty($scoes->defaultorg) && empty($firstinorg) &&
                            $newitem->parent == $scoes->defaultorg) {

                            $firstinorg = $sequenceNumber;
                        }


                        if ($newitem->title == null)
                            $newitem->title = "";

                        if ($newitem->scorm_type == null)
                            $newitem->scorm_type = "";

                        // Insert the new SCO, and retain the link between the old and new for later adjustment.
                        $newitem->needReturnKey = true;
                        if ($newitem->save()) {
                            $scoId = $newitem->kid;
                        }
                        // Save this sco in memory so we can use it later.
                        $newscoes[$scoId] = $newitem;

                        if ($optionaldatas = $this->scorm_optionals_data($item, $standarddatas)) {
                            $data = new LnScormScoesData();
                            $data->scorm_sco_id = $scoId;
                            $data->scorm_id = $scorm->kid;
                            foreach ($optionaldatas as $optionaldata) {
                                if (isset($item->$optionaldata)) {
                                    $data->name = str_replace("masteryscore", "mastery_score",$optionaldata) ;//对于 有些课件出现masteryscore的情况，统一转换成mastery_score
                                    $data->value = strval($item->$optionaldata);
                                    $data->save();
                                }
                            }
                        }

                        if (isset($item->sequencingrules)) {
                            foreach ($item->sequencingrules as $sequencingrule) {
                                $rule = new LnScormSeqRuleconds();
                                $rule->scorm_sco_id = $scoId;
                                $rule->scorm_id = $scorm->kid;
                                $rule->rule_type = strval($sequencingrule->type);
                                $rule->condition_combination = $sequencingrule->conditioncombination;
                                $rule->action = $sequencingrule->action;
                                $rule->needReturnKey = true;
                                if ($rule->save()) {
                                    $ruleid = $rule->kid;

                                    if (isset($sequencingrule->ruleconditions)) {
                                        $ruleCondList = [];
                                        foreach ($sequencingrule->ruleconditions as $rulecondition) {
                                            $rulecond = new LnScormSeqRulecond();
                                            $rulecond->scorm_sco_id = $scoId;
                                            $rulecond->scorm_id = $scorm->kid;;
                                            $rulecond->rule_conds_id = $ruleid;
                                            $rulecond->referenced_objective = $rulecondition->referencedobjective;
                                            $rulecond->measure_threshold = $rulecondition->measurethreshold;
                                            $rulecond->operator = $rulecondition->operator;
                                            $rulecond->cond = $rulecondition->cond;

                                            array_push($ruleCondList, $rulecond);
                                        }

                                        BaseActiveRecord::batchInsertSqlArray($ruleCondList);
                                    }
                                }
                            }
                        }

                        if (isset($item->rolluprules)) {
                            foreach ($item->rolluprules as $rolluprule) {
                                $rollup = new LnScormSeqRollru();
                                $rollup->scorm_sco_id = $scoId;
                                $rollup->scorm_id = $scorm->kid;;
                                $rollup->child_activity_set = $rolluprule->childactivityset;
                                $rollup->minimum_count = $rolluprule->minimumcount;
                                $rollup->minimum_percent = $rolluprule->minimumpercent;
                                $rollup->action = $rolluprule->rollupruleaction;
                                $rollup->condition_combination = $rolluprule->conditioncombination;
                                $rollup->needReturnKey = true;
                                if ($rollup->save()) {
                                    $rollupruleid = $rollup->kid;
                                    if (isset($rolluprule->conditions)) {
                                        $rollupRuleCondList = [];

                                        foreach ($rolluprule->conditions as $condition) {
                                            $cond = new LnScormSeqRollrucond();
                                            $cond->scorm_sco_id = $scoId;
                                            $cond->scorm_id = $scorm->kid;;
                                            $cond->rollup_rule_id = $rollupruleid;
                                            $cond->operator = $condition->operator;
                                            $cond->cond = $condition->cond;

                                            array_push($rollupRuleCondList, $cond);
                                        }

                                        BaseActiveRecord::batchInsertSqlArray($rollupRuleCondList);
                                    }
                                }
                            }
                        }

                        if (isset($item->objectives)) {
                            foreach ($item->objectives as $objective) {
                                $obj = new LnScormSeqObjective();
                                $obj->scorm_sco_id = $scoId;
                                $obj->scorm_id = $scorm->kid;;
                                $obj->primary_obj = strval($objective->primaryobj);
                                $obj->satisfied_by_measure = strval($objective->satisfiedbymeasure);
                                $obj->objective_id = strval($objective->objectiveid);
                                $obj->min_normalized_measure = trim($objective->minnormalizedmeasure);
                                $obj->needReturnKey = true;
                                if ($obj->save()) {
                                    $objectiveid = $obj->kid;
                                    if (isset($objective->mapinfos)) {
                                        $seqMapList = [];
                                        foreach ($objective->mapinfos as $objmapinfo) {
                                            $mapinfo = new LnScormSeqMapinfo();
                                            $mapinfo->scorm_sco_id = $scoId;
                                            $mapinfo->scorm_id = $scorm->kid;;
                                            $mapinfo->objective_id = $objectiveid;
                                            $mapinfo->target_objective_id = $objmapinfo->targetobjectiveid;
                                            $mapinfo->read_satisfied_status = strval($objmapinfo->readsatisfiedstatus);
                                            $mapinfo->write_satisfied_status = strval($objmapinfo->writesatisfiedstatus);
                                            $mapinfo->read_normalized_measure = $objmapinfo->readnormalizedmeasure;
                                            $mapinfo->write_normalized_measure = $objmapinfo->writenormalizedmeasure;
                                            array_push($seqMapList, $mapinfo);
                                        }

                                        BaseActiveRecord::batchInsertSqlArray($seqMapList);
                                    }
                                }
                            }
                        }
                        if (empty($defaultorgid) && ((empty($scoes->defaultorg)) || ($scoes->defaultorg == $identifier))) {
                            $defaultorgid = $scoId;
                        }
                    }
                }
            }

            if (empty($scoes->version)) {
                $scoes->version = 'SCORM_1.2';
            }
            $scorm->scorm_version = $scoes->version;
        }
        $scorm->launch_scorm_sco_id = null;
        // Check launch sco is valid.
        if (!empty($defaultorgid) && isset($newscoes[$defaultorgid]) && !empty($newscoes[$defaultorgid]->launch)) {
            // Launch param is valid - do nothing.
            $scorm->launch_scorm_sco_id = $defaultorgid;
        } else if (!empty($defaultorgid) && isset($newscoes[$defaultorgid]) && empty($newscoes[$defaultorgid]->launch)) {

            $scoes = $scormScoesService->getFirstLaunchableItem($scorm->kid, $firstinorg);

            if (!empty($scoes)) {
                $sco = $scoes[0];//reset($scoes); // We only care about the first record - the above query only returns one.
                $scorm->launch_scorm_sco_id = $sco->kid;
            }
        }
        if (empty($scorm->launch)) {

            // No valid Launch is specified - find the first launchable sco instead.
            $scoes = $scormScoesService->getFirstLaunchableItem($scorm->kid, null);
            if (!empty($scoes)) {
                $sco =  $scoes[0];//reset($scoes); // We only care about the first record - the above query only returns one.
                $scorm->launch_scorm_sco_id = $sco->kid;
            }
        }

        $scorm->save();
        return true;
    }

    /**
     * 解析aicc课件
     * @param $scorm
     * @param $aiccPath
     * @return int|null
     */
    function scorm_parse_aicc($scorm, $aiccPath) {
        $version = 'AICC';
        $ids = array();
        $courses = array();
        $extaiccfiles = array('crs','des','au','cst','ort','pre','cmp');
        $sequenceNumber = 0;
        $pkgdir = Yii::$app->basePath.'/..' . $aiccPath ;


        if ($handle = opendir($pkgdir)) {
            while (($file = readdir($handle)) !== false) {
                if ($file[0] != '.') {
                    $ext = substr($file,strrpos($file,'.'));
                    $extension = strtolower(substr($ext,1));
                    if (in_array($extension,$extaiccfiles)) {
                        $id = strtolower(basename($file,$ext));
                        if (!isset($ids[$id])) {
                            $ids[$id] = new stdClass();
                        }
                        $ids[$id]->$extension = $file;
                    }
                }
            }
            closedir($handle);
        }

        foreach ($ids as $courseid => $id) {
            if (isset($id->crs)) {
                if (is_file($pkgdir.'/'.$id->crs)) {
                    $rows = file($pkgdir.'/'.$id->crs);
                    foreach ($rows as $row) {
                        if (preg_match("/^(.+)=(.+)$/",$row,$matches)) {
                            if (!isset($courses[$courseid])){
                                $courses[$courseid] = new stdClass();
                            }
                            switch (strtolower(trim($matches[1]))) {
                                case 'course_id':
                                    $courses[$courseid]->manifest = trim($matches[2]);
                                    $courses[$courseid]->id = trim($matches[2]);
                                    break;
                                case 'course_title':
                                    $courses[$courseid]->title = trim($matches[2]);
                                    break;
                                case 'version':
                                    $courses[$courseid]->version = 'AICC_'.trim($matches[2]);
                                    break;
                            }
                        }
                    }
                }
            }
            if (isset($id->des)) {
                $rows = file($pkgdir.'/'.$id->des);
                $columns = $this->scorm_get_aicc_columns($rows[0]);
                $regexp = $this->scorm_forge_cols_regexp($columns->columns);
                for ($i=1;$i<count($rows);$i++) {
                    if (preg_match($regexp,$rows[$i],$matches)) {
                        for ($j=0;$j<count($columns->columns);$j++) {
                            $column = $columns->columns[$j];
                            if (!isset($courses[$courseid]->elements[substr(trim($matches[$columns->mastercol+1]),1,-1)])){
                                $courses[$courseid]->elements[substr(trim($matches[$columns->mastercol+1]),1,-1)] = new stdClass();
                            }
                            $courses[$courseid]->elements[substr(trim($matches[$columns->mastercol+1]),1,-1)]->$column = substr(trim($matches[$j+1]),1,-1);
                        }
                    }
                }
            }
            if (isset($id->au)) {
                $rows = file($pkgdir.'/'.$id->au);
                $columns = $this->scorm_get_aicc_columns($rows[0]);
                $regexp = $this->scorm_forge_cols_regexp($columns->columns);
                for ($i=1;$i<count($rows);$i++) {
                    if (preg_match($regexp,$rows[$i],$matches)) {
                        for ($j=0;$j<count($columns->columns);$j++) {
                            $column = $columns->columns[$j];
                            if (!isset($courses[$courseid]->elements[substr(trim($matches[$columns->mastercol+1]),1,-1)])){
                                $courses[$courseid]->elements[substr(trim($matches[$columns->mastercol+1]),1,-1)] = new stdClass();
                            }
                            $courses[$courseid]->elements[substr(trim($matches[$columns->mastercol+1]),1,-1)]->$column = substr(trim($matches[$j+1]),1,-1);
                        }
                    }
                }
            }
            if (isset($id->cst)) {
                $rows = file($pkgdir.'/'.$id->cst);
                $columns = $this->scorm_get_aicc_columns($rows[0],'block');
                $regexp = $this->scorm_forge_cols_regexp($columns->columns,'(.+)?,');
                for ($i=1;$i<count($rows);$i++) {
                    if (preg_match($regexp,$rows[$i],$matches)) {
                        for ($j=0;$j<count($columns->columns);$j++) {
                            if ($j != $columns->mastercol) {
                                if (!isset($courses[$courseid]->elements[substr(trim($matches[$j+1]),1,-1)])){
                                    $courses[$courseid]->elements[substr(trim($matches[$j+1]),1,-1)] = new stdClass();
                                }
                                $courses[$courseid]->elements[substr(trim($matches[$j+1]),1,-1)]->parent = substr(trim($matches[$columns->mastercol+1]),1,-1);
                            }
                        }
                    }
                }
            }
            if (isset($id->ort)) {
                $rows = file($pkgdir.'/'.$id->ort);
                $columns = $this->scorm_get_aicc_columns($rows[0],'course_element');
                $regexp = $this->scorm_forge_cols_regexp($columns->columns,'(.+)?,');
                for ($i=1;$i<count($rows);$i++) {
                    if (preg_match($regexp,$rows[$i],$matches)) {
                        for ($j=0;$j<count($matches)-1;$j++) {
                            if ($j != $columns->mastercol) {
                                if (!isset($courses[$courseid]->elements[substr(trim($matches[$j+1]),1,-1)])){
                                    $courses[$courseid]->elements[substr(trim($matches[$j+1]),1,-1)] = new stdClass();
                                }
                                $courses[$courseid]->elements[substr(trim($matches[$j+1]),1,-1)]->parent = substr(trim($matches[$columns->mastercol+1]),1,-1);
                            }
                        }
                    }
                }
            }
            if (isset($id->pre)) {
                $rows = file($pkgdir.'/'.$id->pre);
                $columns = $this->scorm_get_aicc_columns($rows[0],'structure_element');
                $regexp = $this->scorm_forge_cols_regexp($columns->columns,'(.+),');
                for ($i=1;$i<count($rows);$i++) {
                    if (preg_match($regexp,$rows[$i],$matches)) {
                        if (!isset($courses[$courseid]->elements[$columns->mastercol+1])){
                            $courses[$courseid]->elements[$columns->mastercol+1] = new stdClass();
                        }
                        $courses[$courseid]->elements[$columns->mastercol+1]->prerequisites = substr(trim($matches[1-$columns->mastercol+1]),1,-1);
                    }
                }
            }
            if (isset($id->cmp)) {
                //$rows = file($pkgdir.'/'.$id->cmp);
            }
        }
        //根据scorm_id获取所有scorm_scoes信息，此处要改
        $scormScoesDataService = new ScormScoesDataService();
        $scormScoesTrackService = new ScormScoesTrackService();
        $scormScoesService = new ScormScoesService();

        $oldItemCount = $scormScoesDataService->getScormScoesDataCountByScormId($scorm->kid);

        if ($oldItemCount != 0) {
            $scormScoesTrackService->deleteScormScoesTrackByScormId($scorm->kid);
            $scormScoesDataService->deleteScormScoesDataByScormId($scorm->kid);
            $scormScoesService->deleteScormScoesByScormId($scorm->kid);
        }

        $launchId = null;
        if (isset($courses)) {
            foreach ($courses as $course) {

                if (isset($course->elements) && count($course->elements) > 0) {
                    $sequenceNumber++;
                    $newitem = new LnScormScoes();
                    $newitem->scorm_id = $scorm->kid;
                    $newitem->title = $course->title;
                    $newitem->manifest = $course->manifest;
                    $newitem->organization = "";
                    $newitem->parent = "/";
                    $newitem->identifier = $course->id;
                    $newitem->launch = "";
                    $newitem->scorm_type = "";
                    $newitem->sequence_number = $sequenceNumber;

                    $newitem->needReturnKey = true;
                    if ($newitem->save()) {
                        $scoRootId = $newitem->kid;
                        $scoRootIdentifier = $newitem->identifier;
                    }

                    foreach ($course->elements as $element) {
                        $sequenceNumber++;
                        $newitem = new LnScormScoes();
                        $newitem->scorm_id = $scorm->kid;
                        $newitem->manifest = $course->manifest;
                        $newitem->sequence_number = $sequenceNumber;
                        $newitem->identifier = $element->system_id;
                        $newitem->organization = $course->id;
                        if (isset($element->title))
                            $newitem->title = $element->title;
                        else
                            $newitem->title = $course->title;

                        if (!isset($element->parent) || strtolower($element->parent) == 'root') {
                            $newitem->parent = $scoRootIdentifier;
                        } else {
                            $newitem->parent = $element->parent;
                        }

                        if (isset($element->file_name)) {
                            $newitem->launch = $element->file_name;
                            $newitem->scorm_type = 'sco';
                        }
                        else {
                            $newitem->launch = '';
                            $newitem->scorm_type = '';
                        }
                        $newitem->needReturnKey = true;
                        if ($newitem->save()) {
                            $scoId = $newitem->kid;

                            if (empty($launchId) && $newitem->scorm_type == 'sco') {
                                $launchId = $scoId;
                            }
                        } else {
                            $scoId = null;
                        }

                        if (!empty($scoId)) {
                            if (isset($element->web_launch)) {
                                $scodata = new LnScormScoesData();
                                $scodata->scorm_sco_id = $scoId;
                                $scodata->scorm_id = $scorm->kid;
                                $scodata->name = 'parameters';
                                $scodata->value = $element->web_launch;

                                $scodata->save();
                            }
                            if (isset($element->prerequisites)) {
                                $scodata = new LnScormScoesData();
                                $scodata->scorm_sco_id = $scoId;
                                $scodata->scorm_id = $scorm->kid;
                                $scodata->name = 'prerequisites';
                                $scodata->value = $element->prerequisites;

                                $scodata->save();
                            }
                            if (isset($element->max_time_allowed)) {
                                $scodata = new LnScormScoesData();
                                $scodata->scorm_sco_id = $scoId;
                                $scodata->scorm_id = $scorm->kid;
                                $scodata->name = 'max_time_allowed';
                                $scodata->value = $element->max_time_allowed;

                                $scodata->save();
                            }
                            if (isset($element->time_limit_action)) {
                                $scodata = new LnScormScoesData();
                                $scodata->scorm_sco_id = $scoId;
                                $scodata->scorm_id = $scorm->kid;
                                $scodata->name = 'time_limit_action';
                                $scodata->value = $element->time_limit_action;

                                $scodata->save();
                            }
                            if (isset($element->mastery_score)) {
                                $scodata = new LnScormScoesData();
                                $scodata->scorm_sco_id = $scoId;
                                $scodata->scorm_id = $scorm->kid;
                                $scodata->name = 'mastery_score';
                                $scodata->value = $element->mastery_score;

                                $scodata->save();
                            }
                            if (isset($element->core_vendor)) {
                                $scodata = new LnScormScoesData();
                                $scodata->scorm_sco_id = $scoId;
                                $scodata->scorm_id = $scorm->kid;
                                $scodata->name = 'datafromlms';
                                $scodata->value = str_replace('<cr>', "\r\n", $element->core_vendor);
//                                $scodata->value = eregi_replace('<cr>', "\r\n", $element->core_vendor);

                                $scodata->save();
                            }
                        }
                    }
                }
            }


            $scorm->launch_scorm_sco_id = $launchId;
            $scorm->scorm_version = $version;
            $scorm->save();
            return true;
        }
        else {
            return false;
        }


    }

    /**
     * Take the header row of an AICC definition file
     * and returns sequence of columns and a pointer to
     * the sco identifier column.
     *
     * @param string $row AICC header row
     * @param string $masterName AICC sco identifier column
     * @return mixed
     */
    private function scorm_get_aicc_columns($row,$masterName='system_id') {
        $tok = strtok(strtolower($row),"\",\n\r");
        $result = new stdClass();
        $result->columns = array();
        $i=0;
        while ($tok) {
            if ($tok != "") {
                $result->columns[] = $tok;
                if ($tok == $masterName) {
                    $result->mastercol = $i;
                }
                $i++;
            }
            $tok = strtok("\",\n\r");
        }
        return $result;
    }

    /**
     * Given a colums array return a string containing the regular
     * expression to match the columns in a text row.
     *
     * @param array $column The header columns
     * @param string $remodule The regular expression module for a single column
     * @return string
     */
    private function scorm_forge_cols_regexp($columns,$remodule='(".*")?,') {
        $regexp = '/^';
        foreach ($columns as $column) {
            $regexp .= $remodule;
        }
        $regexp = substr($regexp,0,-1) . '/';
        return $regexp;
    }

    /**
     * 筛选出可选的参数数据，用以记录到scoes_data表
     * @param $item
     * @param $standarddata
     * @return array
     */
    function scorm_optionals_data($item, $standarddata) {
        $result = array();
        $sequencingdata = array('sequencingrules', 'rolluprules', 'objectives');
        foreach ($item as $element => $value) {
            if (! in_array($element, $standarddata)) {
                if (! in_array($element, $sequencingdata)) {
                    $result[] = $element;
                }
            }
        }
        return $result;
    }

    /**
     * 判断sco是叶子节点
     * @param $sco
     * @return bool
     */
    private function scorm_is_leaf($sco) {
        $service = new ScormScoesService();
        $result = $service->getScormScoesByParent($sco->scorm_id, $sco->identifier);
        if ($result != null && count($result) > 0) {
            return false;
        }
        return true;
    }



    /**
     * 返回sco相关所有数据
     * Returns an object containing all datas relative to the given sco ID
     *
     * @param integer $kid The sco ID
     * @return mixed (false if sco id does not exists)
     */
    public function scorm_get_sco($kid, $what = self::SCO_ALL) {
        $service = new ScormScoesDataService();
        $sco = LnScormScoes::findOne($kid)->toArray();

        if ($sco != null) {
            $sco = ($what == self::SCO_DATA) ? new stdClass() : $sco;
            $scodatas = $service->getScormScoesDataByScormScoId($kid);

            if (($what != self::SCO_ONLY) && ($scodatas != null)) {
                foreach ($scodatas as $scodata) {
                    if ($what == self::SCO_DATA)
                    {
                        $sco->{($scodata->name)} = $scodata->value;
                        //$sco[$scodata->name] = $scodata->value;
                    }
                    else {
                        $sco[$scodata->name] = $scodata->value;
                    }
                }
            } else if (($what != self::SCO_ONLY) && ($scodatas = null)) {
                $sco->parameters = '';
            }
            return $sco;
        } else {
            return false;
        }
    }

    /**
     * 获取sco父节点
     * @param $sco
     * @return null
     */
    function scorm_get_parent($sco) {

        if ($sco->parent != '/') {
            $service = new ScormScoesService();
            $result = $service->getScormScoesByIdentifier($sco->scorm_id, $sco->parent);
            if ($result != null) {
                return $this->scorm_get_sco($result->kid);
            }
        }
        return null;
    }

    /**
     * 获取sco子节点
     * @param $sco
     * @return null
     */
    private function scorm_get_children($sco) {
        $service = new ScormScoesService();
        $children = $service->getScormScoesByParent($sco->scorm_id, $sco->identifier);
        if ($children != null) {
            return $children;
        }
        return null;
    }

    /**
     * 获取sco同级子节点
     * @param $sco
     * @return null
     */
    function scorm_get_siblings($sco) {
        $service = new ScormScoesService();
        $siblings = $service->getScormSiblingScoesByParent($sco->kid, $sco->scorm_id, $sco->parent);
        if ($siblings != null) {
            return $siblings;
        }
        return null;
    }

    /**
     * 获取sco有效子节点
     * @param $sco
     * @return array|bool
     */
    private function scorm_get_available_children($sco) {
        $service = new ScormScoesService();
        $res = $service->getScormScoesByParent($sco->scorm_id, $sco->identifier);

        if (!$res || $res == null) {
            return false;
        } else {
            foreach ($res as $sco) {
                $result[] = $sco;
            }
            return $result;
        }
    }

    /**
     * 递归获取sco所有下级有效子节点
     * @param array $descend
     * @param $sco
     * @return array
     */
    private function scorm_get_available_descendent($descend = array(), $sco) {
        if ($sco == null) {
            return $descend;
        } else {
            $avchildren = $this->scorm_get_available_children($sco);
            foreach ($avchildren as $avchild) {
                array_push($descend, $avchild);
            }
            foreach ($avchildren as $avchild) {
                $this->scorm_get_available_descendent($descend, $avchild);
            }
        }
    }


    /**
     * 获取sco所有上级父节点
     * Get an array that contains all the parent scos for this sco.
     * @param $sco
     * @return array
     */
    function scorm_get_ancestors($sco) {
        $ancestors = array();
        $continue = true;
        while ($continue) {
            $ancestor = $this->scorm_get_parent($sco);
            if (!empty($ancestor) && $ancestor->kid !== $sco->kid) {
                $sco = $ancestor;
                $ancestors[] = $ancestor;
                if ($sco->parent == '/') {
                    $continue = false;
                }
            } else {
                $continue = false;
            }
        }
        return $ancestors;
    }

    /**
     * 获取当前节点及所有子节点
     * @param array $preorder
     * @param null $sco
     * @return array
     */
    private function scorm_get_preorder(&$preorder = array(), $sco = null) {
        if ($sco != null) {
            array_push($preorder, $sco);
            if ($children = $this->scorm_get_children($sco)) {
                foreach ($children as $child) {
                    $this->scorm_get_preorder($preorder, $child);
                }
            }
        }
        return $preorder;
    }

    /**
     * 获取除跟节点的父节点
     * @param $ancestors
     * @param $sco
     * @return mixed
     */
    function scorm_find_common_ancestor($ancestors, $sco) {
        $pos = $this->scorm_array_search('identifier', $sco->parent, $ancestors);
        if ($sco->parent != '/') {
            if ($pos === false) {
                return $this->scorm_find_common_ancestor($ancestors, $this->scorm_get_parent($sco));
            }
        }
        return $pos;
    }


    /**
     * 在数组中查询scorm信息
     * @param $item
     * @param $needle
     * @param $haystacks
     * @param bool $strict
     * @return bool|int|string
     */
    function scorm_array_search($item, $needle, $haystacks, $strict = false) {
        if (!empty($haystacks)) {
            foreach ($haystacks as $key => $element) {
                if ($strict) {
                    if ($element->{$item} === $needle) {
                        return $key;
                    }
                } else {
                    if ($element->{$item} == $needle) {
                        return $key;
                    }
                }
            }
        }
        return false;
    }

    /**
     * Does proper javascript quoting.
     *
     * Do not use addslashes anymore, because it does not work when magic_quotes_sybase is enabled.
     *
     * @param mixed $var String, Array, or Object to add slashes to
     * @return mixed quoted result
     */
    function addslashes_js($var) {
        if (is_string($var)) {
            $var = str_replace('\\', '\\\\', $var);
            $var = str_replace(array('\'', '"', "\n", "\r", "\0"), array('\\\'', '\\"', '\\n', '\\r', '\\0'), $var);
            $var = str_replace('</', '<\/', $var);   // XHTML compliance.
        } else if (is_array($var)) {
            $var = array_map('addslashes_js', $var);
        } else if (is_object($var)) {
            $a = get_object_vars($var);
            foreach ($a as $key => $value) {
                $a[$key] = $this->addslashes_js($value);
            }
            $var = (object)$a;
        }
        return $var;
    }

    /**
     * scorm版本号
     * 规则：日期+sprintf("%03d", course_version);
     * @param $coursewareId
     * @return string
     */
    public function getScormVersion($coursewareId)
    {
        if (empty($coursewareId)) return date('Ymd') . '001';
//        $lncourse = new LnCoursewareScorm();
        $result = $this->getScormByCoursewareId($coursewareId);
        if (!empty($result)) {
            $course_version = $result->scorm_version;
            if (substr($course_version, 0, 8) == date('Ymd')) {
                $last_version = substr($course_version, -3);
                return date('Ymd') . sprintf("%03d", intval($last_version) + 1);
            } else {
                return date('Ymd') . '001';
            }
        }
        else {
            return null;
        }
    }

    /**
     * 课件版本检查
     * Returns the SCORM version used.
     * @param string $scormversion comes from $scorm->version
     * @param string $version one of the defined vars SCORM_12, SCORM_13, SCORM_AICC (or empty)
     * @return Scorm version.
     */
    private function scorm_version_check($scormversion, $version='') {
        $scormversion = trim(strtolower($scormversion));
        if (empty($version) || $version == self::SCORM_12) {
            if ($scormversion == 'scorm_12' || $scormversion == 'scorm_1.2') {
                return self::SCORM_12;
            }
            if (!empty($version)) {
                return false;
            }
        }
        if (empty($version) || $version == self::SCORM_13) {
            if ($scormversion == 'scorm_13' || $scormversion == 'scorm_1.3') {
                return self::SCORM_13;
            }
            if (!empty($version)) {
                return false;
            }
        }
        if (empty($version) || $version == self::SCORM_AICC) {
            if (strpos($scormversion, 'aicc')) {
                return self::SCORM_AICC;
            }
            if (!empty($version)) {
                return false;
            }
        }
        return false;
    }

    /**
     * 返回最后尝试次数，如果没有访问过，则返回1
     * @param $courseRegId
     * @return int
     */
    public function scorm_get_last_attempt($courseRegId) {
        $courseComplete = new CourseCompleteService();
        return $courseComplete->getLastAttempt($courseRegId);
    }

    /**
     * 转换成绩格式
     * helper function to return a formatted list of interactions for reports.
     *
     * @param array $trackdata the records from scorm_scoes_track table
     * @return object formatted list of interactions
     */
    private function scorm_format_interactions($trackdata) {
        $usertrack = new stdClass();

        // Defined in order to unify scorm1.2 and scorm2004.
        $usertrack->score_raw = '';
        $usertrack->status = '';
        $usertrack->total_time = '00:00:00';
        $usertrack->session_time = '00:00:00';
//        $usertrack->timemodified = 0;

        foreach ($trackdata as $key=>$track) {
            $element = $key;
            $usertrack->{$element} = $track->value;
            switch ($element) {
                case 'cmi.core.lesson_status':
                case 'cmi.completion_status':
                    if ($track->value == 'not attempted') {
                        $track->value = 'notattempted';
                    }
                    $usertrack->status = $track->value;
                    break;
                case 'cmi.core.score.raw':
                case 'cmi.score.raw':
                    $usertrack->score_raw = (float) sprintf('%2.2f', $track->value);
                    break;
                case 'cmi.core.session_time':
                case 'cmi.session_time':
                    $usertrack->session_time = $track->value;
                    break;
                case 'cmi.core.total_time':
                case 'cmi.total_time':
                    $usertrack->total_time = $track->value;
                    break;
                case 'cmi.core.score.min':
                case 'cmi.score.min':
                    $usertrack->min_score = $track->value;
                    break;
                case 'cmi.core.score.max':
                case 'cmi.score.max':
                    $usertrack->max_score = $track->value;
                    break;
            }
//            if (isset($track->timemodified) && ($track->timemodified > $usertrack->timemodified)) {
//                $usertrack->timemodified = $track->timemodified;
//            }
        }
/*
        if (!empty($usertrack->score_raw ) && !empty($usertrack->max_score) && $usertrack->max_score != "0" && $usertrack->max_score != "100") {
            $usertrack->score_raw = $usertrack->score_raw * 100 / intval($usertrack->max_score) ; //sco的转换，默认都是转换百分制，以后如果要配置，就这个100
        }
*/

        return $usertrack;
    }

    /**
     * 根据当前尝试获取相关成绩数据
     * @param $modResId
     * @param $scoId
     * @param $userId
     * @param string $attempt
     * @return bool
     */
    public function scorm_get_tracks($courseRegId,$modResId, $scoId, $userId, $attempt, $withSession = false) {
        if (!empty($courseRegId)) {
            // Gets all tracks of specified sco and user.
            $scormScoesTrackService = new ScormScoesTrackService();

            if (empty($attempt)) {
                $attempt = $this->scorm_get_last_attempt($courseRegId);
            }

            $trackdata = $scormScoesTrackService->getScoesTrackResultByAttempt($courseRegId, $modResId, $scoId, $attempt, $withSession);

            if ($trackdata != null) {
                $usertrack = $this->scorm_format_interactions($trackdata);
                $usertrack->user_id = $userId;
                $usertrack->scorm_sco_id = $scoId;
                $usertrack->course_reg_id = $courseRegId;
                $usertrack->mod_res_id = $modResId;

                return $usertrack;
            } else {
                return false;
            }
        }
        else {
            return false;
        }
    }

    /**
     * 获取父节点scoes
     * Get the parent scoes!
     * @param $result
     * @param $currentorg
     * @return array
     */
    private function scorm_get_toc_get_parent_child(&$result, $currentorg) {
        $final = array();
        $level = 0;
        // Organization is always the root, prevparent.
        if (!empty($currentorg)) {
            $prevparent = $currentorg;
        } else {
            $prevparent = '/';
        }

        foreach ($result as $sco) {
            if ($sco->parent == '/') {
                $final[$level][$sco->identifier] = $sco;
                $prevparent = $sco->identifier;
                unset($result[$sco->kid]);
            } else {
                if ($sco->parent == $prevparent) {
                    //唐明强：对于某些Scorm可能存在identifier重复的问题，则强制更名
                    if (isset($final[$level][$sco->identifier])) {
                        $sco->identifier = $sco->identifier . "_NEW_" . strval($sco->sequence_number);
                    }
                    $final[$level][$sco->identifier] = $sco;
                    $prevparent = $sco->identifier;
                    unset($result[$sco->kid]);
                } else {
                    if (!empty($final[$level])) {
                        $found = false;
                        foreach ($final[$level] as $fin) {
                            if ($sco->parent == $fin->identifier) {
                                $found = true;
                            }
                        }

                        if ($found) {
                            $final[$level][$sco->identifier] = $sco;
                            unset($result[$sco->kid]);
                            $found = false;
                        } else {
                            $level++;
                            $final[$level][$sco->identifier] = $sco;
                            unset($result[$sco->kid]);
                        }
                    }
                }
            }
        }

        for ($i = 0; $i <= $level; $i++) {
            $prevparent = '';
            foreach ($final[$i] as $ident => $sco) {
                if (empty($prevparent)) {
                    $prevparent = $ident;
                }
                if (!isset($final[$i][$prevparent]->children)) {
                    $final[$i][$prevparent]->children = array();
                }
                if ($sco->parent == $prevparent) {
                    $final[$i][$prevparent]->children[] = $sco;
                    $prevparent = $ident;
                } else {
                    $parent = false;
                    foreach ($final[$i] as $identifier => $scoobj) {
                        if ($identifier == $sco->parent) {
                            $parent = $identifier;
                        }
                    }

                    if ($parent !== false) {
                        $final[$i][$parent]->children[] = $sco;
                    }
                }
            }
        }

        $results = array();
        for ($i = 0; $i <= $level; $i++) {
            $keys = array_keys($final[$i]);
            $results[] = $final[$i][$keys[0]];
        }

        return $results;
    }

    /**
     * This is really a little language parser for AICC_SCRIPT
     * evaluates the expression and returns a boolean answer
     * see 2.3.2.5.1. Sequencing/Navigation Today  - from the SCORM 1.2 spec (CAM).
     *
     * @param string $prerequisites the aicc_script prerequisites expression
     * @param array  $usertracks the tracked user data of each SCO visited
     * @return boolean
     */
    private function scorm_eval_prerequisites($prerequisites, $usertracks) {

        // This is really a little language parser - AICC_SCRIPT is the reference
        // see 2.3.2.5.1. Sequencing/Navigation Today  - from the SCORM 1.2 spec.
        $element = '';
        $stack = array();
        $statuses = array(
            'passed' => 'passed',
            'completed' => 'completed',
            'failed' => 'failed',
            'incomplete' => 'incomplete',
            'browsed' => 'browsed',
            'not attempted' => 'notattempted',
            'p' => 'passed',
            'c' => 'completed',
            'f' => 'failed',
            'i' => 'incomplete',
            'b' => 'browsed',
            'n' => 'notattempted'
        );
        $i = 0;

        // Expand the amp entities.
        $prerequisites = preg_replace('/&amp;/', '&', $prerequisites);
        // Find all my parsable tokens.
        $prerequisites = preg_replace('/(&|\||\(|\)|\~)/', '\t$1\t', $prerequisites);
        // Expand operators.
        $prerequisites = preg_replace('/&/', '&&', $prerequisites);
        $prerequisites = preg_replace('/\|/', '||', $prerequisites);
        // Now - grab all the tokens.
        $elements = explode('\t', trim($prerequisites));

        // Process each token to build an expression to be evaluated.
        $stack = array();
        foreach ($elements as $element) {
            $element = trim($element);
            if (empty($element)) {
                continue;
            }
            if (!preg_match('/^(&&|\|\||\(|\))$/', $element)) {
                // Create each individual expression.
                // Search for ~ = <> X*{} .

                // Sets like 3*{S34, S36, S37, S39}.
                if (preg_match('/^(\d+)\*\{(.+)\}$/', $element, $matches)) {
                    $repeat = $matches[1];
                    $set = explode(',', $matches[2]);
                    $count = 0;
                    foreach ($set as $setelement) {
                        if (isset($usertracks[$setelement]) &&
                            ($usertracks[$setelement]->status == 'completed' || $usertracks[$setelement]->status == 'passed')) {
                            $count++;
                        }
                    }
                    if ($count >= $repeat) {
                        $element = 'true';
                    } else {
                        $element = 'false';
                    }
                } else if ($element == '~') {
                    // Not maps ~.
                    $element = '!';
                } else if (preg_match('/^(.+)(\=|\<\>)(.+)$/', $element, $matches)) {
                    // Other symbols = | <> .
                    $element = trim($matches[1]);
                    if (isset($usertracks[$element])) {
                        $value = trim(preg_replace('/(\'|\")/', '', $matches[3]));
                        if (isset($statuses[$value])) {
                            $value = $statuses[$value];
                        }
                        if ($matches[2] == '<>') {
                            $oper = '!=';
                        } else {
                            $oper = '==';
                        }
                        $element = '(\''.$usertracks[$element]->status.'\' '.$oper.' \''.$value.'\')';
                    } else {
                        $element = 'false';
                    }
                } else {
                    // Everything else must be an element defined like S45 ...
                    if (isset($usertracks[$element]) &&
                        ($usertracks[$element]->status == 'completed' || $usertracks[$element]->status == 'passed')) {
                        $element = 'true';
                    } else {
                        $element = 'false';
                    }
                }

            }
            $stack[] = ' '.$element.' ';
        }
        return eval('return '.implode($stack).';');
    }

    /**
     * 获取课件的单元信息
     * Returns an object (array) containing all the scoes data related to the given sco ID
     *
     * @param integer $id The scorm ID
     * @param integer $organisation an organisation ID - defaults to false if not required
     * @return mixed (false if there are no scoes or an array)
     */
    private function scorm_get_scoes($scormId, $organization=null) {
        $scormScoesService = new ScormScoesService();
        $scoes = $scormScoesService->getScormScoesByScormId($scormId,$organization);

        $scoesArray = $this->scorm_scoes_convert_to_array($scoes);
        return $scoesArray;
    }


    /**
     * 把scoes对象转换成数组
     * @param $scoes
     * @return array
     */
    private function scorm_scoes_convert_to_array($scoes)
    {
        $scoesArray = array();
        foreach($scoes as $item) {
            $sco = new stdClass();
            $sco->kid = $item->kid;
            $sco->scorm_id = $item->scorm_id;
            $sco->title = $item->title;
            $sco->manifest = $item->manifest;
            $sco->organization = $item->organization;
            $sco->parent = $item->parent;
            $sco->identifier = $item->identifier;
            $sco->launch = $item->launch;
            $sco->scorm_type = $item->scorm_type;
            $sco->sequence_number = $item->sequence_number;
            $scoesArray[] = $sco;
        }
        return $scoesArray;
    }

    /**
     * 获取状态对应的图标Html
     * @param $statusIconType
     */
    public function getStatusIconHtmlByType($statusIconType) {
        $strStatus = 'scorm_status_'.$statusIconType;
        if ($statusIconType == "completed")
        {
            $statusIconHtml = "<i class='glyphicon glyphicon-check' title=".Yii::t('common',$strStatus)."></i>";
        }
        else if ($statusIconType == 'incomplete')
        {
            $statusIconHtml = "<i class='glyphicon glyphicon-edit' title=".Yii::t('common',$strStatus)."></i>";
        }
        else if ($statusIconType == 'failed')
        {
            $statusIconHtml = "<i class='glyphicon glyphicon-remove-circle' title=".Yii::t('common',$strStatus)."></i>";
        }
        else if ($statusIconType == 'suspend')
        {
            $statusIconHtml = "<i class='glyphicon glyphicon-ban-circle' title=".Yii::t('common',$strStatus)."></i>";
        }
        else if ($statusIconType == 'browsed')
        {
            $statusIconHtml = "<i class='glyphicon glyphicon-share-alt' title=".Yii::t('common',$strStatus)."></i>";
        }
        else if ($statusIconType == 'notattempted')
        {
            $statusIconHtml = "<i class='glyphicon glyphicon-unchecked' title=".Yii::t('common',$strStatus)."></i>";
        }
        else if ($statusIconType == 'file')
        {
            $statusIconHtml = "<i class='glyphicon glyphicon-save-file' title=".Yii::t('common','scorm_status_assetlaunched')."></i>";
        }
        else if ($statusIconType == 'unchecked')
        {
            $statusIconHtml = "<i class='glyphicon glyphicon-unchecked' title=".Yii::t('common','scorm_status_notattempted')."></i>";
        }

        return $statusIconHtml;
    }
    /**
     * 获取目录对象
     * @param $userId
     * @param $scormId
     * @param string $currentorg
     * @param string $scoId
     * @param string $mode
     * @param string $attempt
     * @param bool|false $play
     * @param null $organizationsco
     * @return array
     */
    public function scorm_get_toc_object($courseRegId,$modResId,$userId, $scorm, $currentorg="", $scoId="", $mode="normal", $attempt="",
                                         $play=false, $organizationsco=null, $coursewareId=null, $withSession = false) {
        $scormId = $scorm->kid;
        // Always pass the mode even if empty as that is what is done elsewhere and the urls have to match.
//        $modestr = '&mode=';
//        if ($mode != 'normal') {
//            $modestr = '&mode='.$mode;
//        }
        if (empty($attempt) && !empty($courseRegId)) {
            $attempt = $this->scorm_get_last_attempt($courseRegId);
        }

        $result = array();
        $incomplete = false;

        if (!empty($organizationsco)) {
            $result[0] = $organizationsco;
            $result[0]->isvisible = true;
            $result[0]->statusIconType = '';
//            $result[0]->url = '';
        }


        $scoes = $this->scorm_get_scoes($scormId, $currentorg);

        if ($scoes != null) {
            // Retrieve user tracking data for each learning object.
            $usertracks = array();
            foreach ($scoes as $sco) {
                if (!empty($sco->launch)) {
                    if (!empty($courseRegId) && $mode == "normal" && $usertrack = $this->scorm_get_tracks($courseRegId,$modResId,$sco->kid, $userId, $attempt, $withSession)) {
                        if ($usertrack->status == '') {
                            $usertrack->status = 'notattempted';
                        }
                        $usertracks[$sco->identifier] = $usertrack;
                    }
                }
            }
            $allowVisit = true;
            foreach ($scoes as $sco) {
//                $statusicon = "";
                if (!isset($sco->isvisible)) {
                    $sco->isvisible = true;
                }

                if (empty($sco->title)) {
                    $sco->title = $sco->identifier;
                }

                if ($this->scorm_version_check($scorm->scorm_version, self::SCORM_13)) {
                    $sco->prereq = true;
                } else {
                    $sco->prereq = empty($sco->prerequisites) || $this->scorm_eval_prerequisites($sco->prerequisites, $usertracks);
                }

                if ($sco->isvisible) {
                    if (!empty($sco->launch)) {
                        if (empty($scoId) && ($mode != 'normal')) {
                            $scoId = $sco->kid;
                        }

                        if (isset($usertracks[$sco->identifier])) {
                            $usertrack = $usertracks[$sco->identifier];
//                            $strstatus = 'scorm_status_'.$usertrack->status;

                            if ($sco->scorm_type == 'sco') {
                                $trackStatus = $usertrack->status;
                                if ($trackStatus == "completed" || $trackStatus == "passed")
                                {
                                    $statusIconType = "completed";
//                                    $statusicon = "<i class='glyphicon glyphicon-check' title=".Yii::t('common',$strstatus)."></i>";
                                }
                                else if ($trackStatus == 'incomplete')
                                {
                                    $statusIconType = "incomplete";
//                                    $statusicon = "<i class='glyphicon glyphicon-edit' title=".Yii::t('common',$strstatus)."></i>";
                                }
                                else if ($trackStatus == 'failed')
                                {
                                    $statusIconType = "failed";
//                                    $statusicon = "<i class='glyphicon glyphicon-remove-circle' title=".Yii::t('common',$strstatus)."></i>";
                                }
                                else if ($trackStatus == 'suspend')
                                {
                                    $statusIconType = "suspend";
//                                    $statusicon = "<i class='glyphicon glyphicon-ban-circle' title=".Yii::t('common',$strstatus)."></i>";
                                }
                                else if ($trackStatus == 'browsed')
                                {
                                    $statusIconType = "browsed";
//                                    $statusicon = "<i class='glyphicon glyphicon-share-alt' title=".Yii::t('common',$strstatus)."></i>";
                                }
                                else if ($trackStatus == 'notattempted')
                                {
                                    $statusIconType = "notattempted";
//                                    $statusicon = "<i class='glyphicon glyphicon-unchecked' title=".Yii::t('common','scorm_status_notattempted')."></i>";
                                }
//                                else
//                                {
//                                    $statusicon = Html::img("/static/frontend/images/courseware/".$usertrack->status.".gif",
//                                        ['title'=>Yii::t('common',$strstatus),'alt'=>Yii::t('common',$strstatus)]);
//                                }

//                                $statusicon = HTML::img("/static/frontend/images/courseware/".$trackStatus.".gif",
//                                    ['title'=>Yii::t('common',$strstatus),'alt'=>Yii::t('common',$strstatus)]);
                            } else {
                                //附件
                                $statusIconType = "file";
//                                $statusicon = "<i class='glyphicon glyphicon-save-file' title=".Yii::t('common','scorm_status_assetlaunched')."></i>";
                            }

                            if (($usertrack->status == 'notattempted') ||
                                ($usertrack->status == 'incomplete') ||
                                ($usertrack->status == 'browsed')) {
                                $incomplete = true;
                                if ($play && empty($scoId)) {
                                    $scoId = $sco->kid;
                                }
                            }


                            $exitvar = 'cmi.core.exit';

                            if ($this->scorm_version_check($scorm->scorm_version, self::SCORM_13)) {
                                $exitvar = 'cmi.exit';
                            }

                            if ($incomplete && isset($usertrack->{$exitvar}) && ($usertrack->{$exitvar} == 'suspend')) {
                                //suspend被中止
                                $statusIconType = "suspend";
//                                $statusicon = "<i class='glyphicon glyphicon-ban-circle' title=".Yii::t('common','scorm_status_suspended')."></i>";

//                                $statusicon = HTML::img("/static/frontend/images/courseware/suspend.gif",
//                                    ['title'=>Yii::t('common','scorm_status_suspended'),'alt'=>Yii::t('common','scorm_status_suspended')]);
                            }

                        } else {
                            if ($play && empty($scoId)) {
                                $scoId = $sco->kid;
                            }

                            $incomplete = true;

                            if ($sco->scorm_type == 'sco') {
                                //未学习
                                $statusIconType = "unchecked";
//                                $statusicon = "<i class='glyphicon glyphicon-unchecked' title=".Yii::t('common','scorm_status_notattempted')."></i>";

//                                $statusicon = HTML::img("/static/frontend/images/courseware/notattempted.gif",
//                                    ['title'=>Yii::t('common','scorm_status_notattempted'),'alt'=>Yii::t('common','scorm_status_notattempted')]);
                            } else {
                                //附件
                                $statusIconType = "file";
//                                $statusicon = "<i class='glyphicon glyphicon-save-file' title=".Yii::t('common','scorm_status_asset')."></i>";
                            }
                        }
                    }
                    else
                    {
                        if ($sco->scorm_type == 'asset')
                            $statusIconType = "asset";
//                            $statusicon = "<i class='glyphicon glyphicon-folder-close' title=".Yii::t('common','scorm_status_folder')."></i>";
                    }
                }

                if (empty($statusIconType)) {
                    //未学习
                    $sco->statusIconType = "unchecked";
                    $incomplete = true;
//                    $sco->statusicon = "<i class='glyphicon glyphicon-unchecked' title=".Yii::t('common','scorm_status_notattempted')."></i>";

//                    $statusicon = HTML::img("/static/frontend/images/courseware/notattempted.gif",
//                        ['title'=>Yii::t('common','scorm_status_notattempted'),'alt'=>Yii::t('common','scorm_status_notattempted')]);
                } else {
                    $sco->statusIconType = $statusIconType;
//                    $sco->statusicon = $statusicon;
                }

                if (!empty($modResId)) {
                    $sco->componentCode = "scorm";
                    $sco->modResId = $modResId;
//                    $componentCode = LnComponent::findOne(LnModRes::findOne($modResId)->component_id)->component_code;
//                    $sco->url = "javascript:reloadplayer('" . $componentCode . "','" . $modResId . "','" . $sco->kid . "');";// 'scormId='.$scormId.'&scoId='.$sco->kid.'&currentOrg='.$currentorg.$modestr.'&attempt='.$attempt;
                }
//                else
//                {
//                    $sco->url = "javascript:reloadplayer('scorm','" . $coursewareId . "','" . $sco->kid . "');";// 'scormId='.$scormId.'&scoId='.$sco->kid.'&currentOrg='.$currentorg.$modestr.'&attempt='.$attempt;
//                }
                $sco->incomplete = $incomplete;

                //如果当前节点是未访问的，那么下一个节点暂时不允许访问
                if ($allowVisit)
                {
                    //2016/1/15:发现moodle实际是不控制是否能学下一单元的。
//                    if ($incomplete && $mode == "normal") {
//                        $allowVisit = false;
//                    }
                }
                else
                {
                    $sco->url = "";
                }

                if (!in_array($sco->kid, array_keys($result))) {
                    $result[$sco->kid] = $sco;
                }
            }
        }

        // Get the parent scoes!
        $result = $this->scorm_get_toc_get_parent_child($result, $currentorg);

        // Be safe, prevent warnings from showing up while returning array.
        if (!isset($scoId)) {
            $scoId = '';
        }

        return array('scoes' => $result, 'usertracks' => $usertracks, 'scoId' => $scoId);
    }

    /**
     * 获取目录信息
     * @param $userId
     * @param $modResId
     * @param $scormId
     * @param $cmid
     * @param string $currentOrg
     * @param string $scoId
     * @param string $mode
     * @param string $attempt
     * @param bool|false $play
     * @return stdClass
     */
    public function scorm_get_toc($courseRegId, $userId, $modResId, $scorm, $currentOrg='', $scoId='', $mode='normal',
                                  $attempt="", $play=false, $currentScoId, $coursewareId, $withSession=false) {
        $scormId = $scorm->kid;
        $scormScoesService = new ScormScoesService();

        if (empty($attempt) && !empty($courseRegId)) {
            $attempt = $this->scorm_get_last_attempt($courseRegId);
        }

        $result = new stdClass();
        $organizationsco = null;



        if (!empty($currentorg)) {
            $organizationsco = $scormScoesService->getScormScoesByIdentifier($scormId,$currentorg);
            if (!empty($organizationsco->title)) {
                if ($play) {
                    $result->toctitle = $organizationsco->title;
                }
            }
        }

        $scoes = $this->scorm_get_toc_object($courseRegId, $modResId, $userId, $scorm, $currentOrg, $scoId, $mode, $attempt, $play, $organizationsco,$coursewareId,$withSession);

        $treeview = $this->scorm_format_toc_for_treeview($courseRegId, $modResId, $userId, $scorm, $scoes['scoes'][0]->children, $scoes['usertracks'],
            $currentOrg, $attempt, $play, $organizationsco, false,$currentScoId);

        $result->toc = $treeview->toc;

        if (!empty($scoes['scoId'])) {
            $scoId = $scoes['scoId'];
        }

        if (empty($scoId)) {
            // If this is a normal package with an org sco and child scos get the first child.
            if (!empty($scoes['scoes'][0]->children)) {
                $result->sco = $scoes['scoes'][0]->children[0];
            } else { // This package only has one sco - it may be a simple external AICC package.
                $result->sco = $scoes['scoes'][0];
            }

        } else {
            $result->sco = $this->scorm_get_sco($scoId);
        }

        $result->prerequisites = $treeview->prerequisites;
        $result->incomplete = $treeview->incomplete;
        $result->attemptleft = $treeview->attemptleft;

        return $result;
    }


    /**
     * 获取目录树结构
     * @param $courseRegId
     * @param $modResId
     * @param $userId
     * @param $scormId
     * @param $scoes
     * @param $usertracks
     * @param string $currentorg
     * @param string $attempt
     * @param bool|false $play
     * @param null $organizationsco
     * @param bool|false $children
     * @return stdClass
     */
    private function scorm_format_toc_for_treeview($courseRegId,$modResId,$userId, $scorm, $scoes, $usertracks, $currentorg='',
                                                   $attempt='', $play=false, $organizationsco=null, $children=false, $currentScoId) {
        $result = new stdClass();
        $result->prerequisites = true;
        $result->incomplete = true;
        $result->toc = '';

        if (!$children) {
            if (!empty($courseRegId)) {
                $attemptsMade = $this->scorm_get_last_attempt($courseRegId);
                $result->attemptleft = $scorm->max_attempt == 0 ? 1 : $scorm->max_attempt - $attemptsMade;
            }
            else{
                $result->attemptleft = 1;
            }
        }

        if (!$children) {
            $result->toc = "<ul>";
//            $result->toc = "<ul class='panel-collapse collapse in' role='tabpanel' aria-labelledby='headingOne' id='collapseExample'>";
//            $result->toc .= "<h4>".Yii::t('common','scorm_course_unit')."</h4>";

            if (!$play && !empty($organizationsco)) {
                $result->toc .= "<li>" .$organizationsco->title."</li>";
            }
        }

        $prevsco = '';
        if (!empty($scoes)) {
            foreach ($scoes as $sco) {
                $scoId = $sco->kid;
                $componentCode = $sco->componentCode;
                $sco->statusIcon = $this->getStatusIconHtmlByType($sco->statusIconType);
                $sco->url =  "javascript:reloadplayer('" . $componentCode . "','" . $modResId . "','" . $scoId . "');";

                if ($scoId == $currentScoId) {
                    $result->toc .= "<li><span class='taskName bg'>";
                }
                else
                {
                    $result->toc .= "<li><span class='taskName'>";
                }

                $sco->isvisible = true;

                if ($sco->isvisible) {
                    $score = '';

                    if (isset($usertracks[$sco->identifier])) {
                        $viewscore = false;
                        if (isset($usertracks[$sco->identifier]->score_raw) && $viewscore) {
                            if ($usertracks[$sco->identifier]->score_raw != '') {
                                $score = '&nbsp;(score:'.$usertracks[$sco->identifier]->score_raw.')';
                            }
                        }
                    }

                    if (!empty($sco->prereq)) {
                        if ($sco->kid == $scoId) {
                            $result->prerequisites = true;
                        }

                        if (!empty($prevsco) && $this->scorm_version_check($scorm->scorm_version, self::SCORM_13) && !empty($prevsco->hidecontinue)) {
                            $result->toc .= Html::tag('span', $sco->statusIcon.'&nbsp;'.$sco->title);
                        } else {
                            if (!empty($sco->launch)) {
                                if (!empty($sco->url)) {
                                    $result->toc .= Html::a( $sco->statusIcon.'&nbsp;'.$sco->title.$score,$sco->url,
                                        ['data-scoid' => $sco->kid, 'title' => $sco->title]);
                                }
                                else
                                {
                                    $result->toc .= $sco->statusIcon.'&nbsp;'.$sco->title;
                                }
                            } else {
                                $result->toc .= $sco->statusIcon.'&nbsp;'.$sco->title;
                            }
                        }

                    } else {
                        $result->toc .= $sco->statusIcon.'&nbsp;'.$sco->title;
                    }

                } else {
                    $result->toc .= "&nbsp;".$sco->title;
                }

                if (!empty($sco->children)) {
                    $result->toc .= "</span>";//先结束标题

                    $result->toc .= "<ul>";//开始子节点
                    $childresult = $this->scorm_format_toc_for_treeview($courseRegId,$modResId,$userId, $scorm, $sco->children, $usertracks,
                        $currentorg, $attempt, $play, $organizationsco, true,$currentScoId);
                    $result->toc .= $childresult->toc;
                    $result->toc .=  "</ul>";

                    $result->toc .= "</li>";//结束节点
                } else {
                    $result->toc .= "</span></li>";//先结束标题和节点
                }
                $prevsco = $sco;
            }
            $result->incomplete = $sco->incomplete;
        }

        if (!$children) {
            $result->toc .= "</ul>";
        }

        return $result;
    }


    /**
     * 更新成绩
     * Update grades in central gradebook
     *
     * @category grade
     * @param object $scorm
     * @param int $userid specific user only, 0 mean all
     * @param bool $nullifnone
     */
    function scorm_update_grades($courseRegId,$courseCompleteProcessId,$courseCompleteFinalId,$modResId, $scorm, $userId, $attempt,
                                 $allowRepeat = false, $isMaster = false, $withSession=false,$systemKey=null,&$courseComplete =false,&$getCetification=false,&$courseId=null,&$certificationId=null)
    {
        if ($grades = $this->scorm_get_user_grades($courseRegId, $modResId, $scorm, $userId, $attempt, $withSession)) {


//            $completeGrade = $grades[$userId]->rawgrade;
            $completeScore = $grades[$userId]->rawgrade;

//            if ($completeScore != null) {
                $resourceCompleteService = new ResourceCompleteService();

                $scormScoesService = new ScormScoesService();
                $scormScoesTrackService = new ScormScoesTrackService();
                $scoes = $scormScoesService->getScormScoesByScormId($scorm->kid, null, "sco");
                $allScoPassed = false;
                //如何知道一个课件是否已经全部完成？课件中可能包含很多单元
                $passCount = 0;
                $allCount = count($scoes);
                foreach ($scoes as $sco) {
//                    $counted = false;
                    $scormScoId = $sco->kid;

                    $scormScoesDataService = new ScormScoesDataService();
                    $masteryScoreModel = $scormScoesDataService->getScormScoesDataByName($scormScoId, "mastery_score", $withSession);
                    //如果有设置mastery_score，则表示成绩一定要达到合格线，否则只要完成，就算通过
                    if (!empty($masteryScoreModel) && !empty($masteryScoreModel->value) && ($completeScore == null || $completeScore < $masteryScoreModel->value)) {
                        //未合格
                    } else {
                        $result = $scormScoesTrackService->checkIsScormScoesCompletedByAttempt($courseRegId, $modResId, $scormScoId, $attempt, $scorm, $withSession);
                        if ($result) {
//                            $counted = true;
                            $passCount = $passCount + 1;
                        }
                    }
                }

                if ($allCount == $passCount) {
                    $allScoPassed = true;
                }
//
//            if ($allPass) {
                //不管是否合格，都记录成绩
                if (!$allScoPassed && $completeScore == null) {
                    //如果scorm并没有完成，且没有传入成绩，则不记录
                } else {
//                    Yii::getLogger()->log("pc courseCompleteProcessId:" . $courseCompleteProcessId, Logger::LEVEL_ERROR);
//                    Yii::getLogger()->log("pc courseRegId:" . $courseRegId, Logger::LEVEL_ERROR);
//                    Yii::getLogger()->log("pc modResId:" . $modResId, Logger::LEVEL_ERROR);
//                    Yii::getLogger()->log("pc completeScore:" . $completeScore, Logger::LEVEL_ERROR);
//                    Yii::getLogger()->log("pc allowRepeat:" . ($allowRepeat? "true" : "false"), Logger::LEVEL_ERROR);
//                    Yii::getLogger()->log("pc allScoPassed:" . ($allScoPassed ? "true" : "false"), Logger::LEVEL_ERROR);
//                    Yii::getLogger()->log("pc isMaster:" . ($isMaster? "true" : "false"), Logger::LEVEL_ERROR);
                    $resourceCompleteService->addResCompleteDoneInfo($courseCompleteProcessId, $courseRegId, $modResId, LnCourseComplete::COMPLETE_TYPE_PROCESS, $completeScore, null, $allowRepeat, $systemKey, $allScoPassed,$isMaster);
                    $resourceCompleteService->addResCompleteDoneInfo($courseCompleteFinalId, $courseRegId, $modResId, LnCourseComplete::COMPLETE_TYPE_FINAL, $completeScore, null, $allowRepeat, $systemKey, $allScoPassed,$isMaster,$courseComplete,$getCetification,$courseId,$certificationId);
                }
//            }
//            }
        }
    }

//    /**
//     * Update/create grade item for given scorm
//     *
//     * @category grade
//     * @uses GRADE_TYPE_VALUE
//     * @uses GRADE_TYPE_NONE
//     * @param object $scorm object with extra cmidnumber
//     * @param mixed $grades optional array/object of grade(s); 'reset' means reset grades in gradebook
//     * @return object grade_item
//     */
//    function scorm_grade_item_update($courseRegId,$modResId,$scorm, $grades=null) {
//        $coursewareModel = LnCourseware::findOne($scorm->courseware_id);
//        $params = array('itemname' => $coursewareModel->courseware_name);
//        if (isset($scorm->scorm_id)) {
//            $params['scorm_id'] = $scorm->kid;
//        }
//
//        if (isset($courseRegId)) {
//            $params['course_reg_id'] = $courseRegId;
//        }
//
//        if (isset($modResId)) {
//            $params['mod_res_id'] = $modResId;
//        }
//
//        $modResModel = LnModRes::findOne($modResId);
//
//        if ($modResModel->score_strategy == LnModRes::SCORE_STRATEGY_OBJECTS) {
//            $scormScoesService = new ScormScoesService();
//            $maxgrade = $scormScoesService->GetScormLaunchableItemCount($scorm->kid);
//            if ($maxgrade) {
//                $params['gradetype'] = self::GRADE_TYPE_VALUE;
//                $params['grademax']  = $maxgrade;
//                $params['grademin']  = 0;
//            } else {
//                $params['gradetype'] = self::GRADE_TYPE_NONE;
//            }
//        } else {
//            $params['gradetype'] = self::GRADE_TYPE_VALUE;
//            $params['grademax']  = $scorm->total_score;
//            $params['grademin']  = 0;
//        }
//
//        if ($grades === 'reset') {
//            $params['reset'] = true;
//            $grades = null;
//        }
//
//    }



    /**
     * 计算指定用户的成绩
     * Return grade for given user or all users.
     *
     * @global stdClass
     * @global object
     * @param int $scormid id of scorm
     * @param int $userid optional user id, 0 means all users
     * @return array array of grades, false if none
     */
    function scorm_get_user_grades($courseRegId,$modResId,$scorm, $userId, $attempt, $withSession=false)
    {
        $grades = array();
        if (empty($courseRegId)) {
            return false;
        } else {
            $grades[$userId] = new stdClass();
            $grades[$userId]->kid = $userId;
            $grades[$userId]->userid = $userId;
            $grades[$userId]->rawgrade = $this->scorm_grade_user($courseRegId, $modResId, $scorm, $userId, $attempt, $withSession);
        }

        return $grades;
    }

    /**
     * 计算用户成绩
     * @param $courseRegId
     * @param $modResId
     * @param $scorm
     * @param $userId
     * @return float|int
     */
    function scorm_grade_user($courseRegId, $modResId, $scorm,  $userId, $lastAttempt, $withSession=false) {

        // Ensure we dont grade user beyond $scorm->maxattempt settings.
        if (empty($lastAttempt)) {
            $lastAttempt = $this->scorm_get_last_attempt($courseRegId);
        }

        if ($scorm->max_attempt != 0 && intval($lastAttempt) >= $scorm->max_attempt) {
            $lastAttempt = strval($scorm->max_attempt);
        }

        $modResModel = LnModRes::findOne($modResId);

        switch ($modResModel->attempt_strategy) {
            case LnModRes::ATTEMPT_STRATEGY_FIRST:
                return  $this->scorm_grade_user_attempt($courseRegId, $modResId, $scorm, $userId, "1",$withSession);
                break;
            case LnModRes::ATTEMPT_STRATEGY_LAST:
                return  $this->scorm_grade_user_attempt($courseRegId, $modResId, $scorm, $userId, $lastAttempt,$withSession);
                break;
            case LnModRes::ATTEMPT_STRATEGY_HIGHEST:
                $maxscore = null;
                for ($attempt = 1; $attempt <= $lastAttempt; $attempt++) {
                    $attemptScore =  $this->scorm_grade_user_attempt($courseRegId, $modResId, $userId, $attempt,$withSession);
                    if ($attemptScore != null) {
                        if ($maxscore == null) {
                            $maxscore = 0;
                        }
                        $maxscore = $attemptScore > $maxscore ? $attemptScore : $maxscore;
                    }
                }
                return $maxscore;

                break;
            case LnModRes::ATTEMPT_STRATEGY_AVERAGE:
//                $attemptCount =  $this->scorm_get_attempt_count($courseRegId, $modResId, $userId, $scorm, false, true);
//                $attempt = $this->scorm_get_last_attempt($courseRegId);
                $sumScore = null;
                $avgScore = null;
                $attemptCount = 0;
                for ($attempt = 1; $attempt <= $lastAttempt; $attempt++) {
                    $attemptScore =  $this->scorm_grade_user_attempt($courseRegId, $modResId, $scorm, $userId, $attempt,$withSession);
                    if ($attemptScore != null) {
                        if ($sumScore == null) {
                            $sumScore = 0;
                        }
                        $sumScore += $attemptScore;
                        $attemptCount +=1 ;
                    }
                }

                if ($sumScore != null) {
                    $avgScore = round($sumScore / $attemptCount);
                }
                return $avgScore;
                break;
        }
    }

    /**
     * 获取用户指定尝试的成绩
     * @param $courseRegId
     * @param $modResId
     * @param $scorm
     * @param $userId
     * @param int $attempt
     * @return float|int|null
     */
    function scorm_grade_user_attempt($courseRegId, $modResId, $scorm, $userId, $attempt="1", $withSession=false)
    {
        $scormId = $scorm->kid;
        $attemptScore = new stdClass();
        $attemptScore->scoes = 0; //已完成的单元数
        $attemptScore->values = 0; //有成绩的单元数
        $attemptScore->max = null; //最高成绩，没有成绩，默认为null
        $attemptScore->sum = null; //成绩总合，没有成绩，默认为null

        $scormScoesService = new ScormScoesService();

        $scoes = $scormScoesService->getScormScoesByScormId($scormId);

        if (empty($scoes)) {
            return null;
        }

        $totalScoesCount = count($scoes); //总单元数

        $modResModel = LnModRes::findOne($modResId);

        foreach ($scoes as $sco) {

            if (!empty($courseRegId) && $usertrack = $this->scorm_get_tracks($courseRegId, $modResId, $sco->kid, $userId, $attempt, $withSession)) {
                if (($usertrack->status == 'completed') || ($usertrack->status == 'passed')) {
                    $attemptScore->scoes++;
                }
                
                
                if (isset($usertrack->score_raw) && $usertrack->score_raw != null && $usertrack->score_raw != "") {
                    $attemptScore->values++;
                    
                    if ($attemptScore->sum == null) {
                        $attemptScore->sum = 0;
                    }
                    $attemptScore->sum += $usertrack->score_raw;

                    if ($attemptScore->max == null) {
                        $attemptScore->max = 0;
                    }
                    $attemptScore->max = ($usertrack->score_raw > $attemptScore->max) ? $usertrack->score_raw : $attemptScore->max;
//                    if (isset($userdata->timemodified) && ($userdata->timemodified > $attemptScore->lastmodify)) {
//                        $attemptScore->lastmodify = $userdata->timemodified;
//                    } else {
//                        $attemptScore->lastmodify = 0;
//                    }
                }
            }
        }

        $score = null;
        switch ($modResModel->score_strategy) {
            case LnModRes::SCORE_STRATEGY_HIGHEST:
                $score = $attemptScore->max;
                break;
            case LnModRes::SCORE_STRATEGY_AVERAGE:
                if ($attemptScore->values > 0 && $attemptScore->sum != null) {
                    $score = $attemptScore->sum / $attemptScore->values;
                }
                break;
            case LnModRes::SCORE_STRATEGY_SUM:
                $score = $attemptScore->sum;
                break;
            case LnModRes::SCORE_STRATEGY_OBJECTS:
                if ($totalScoesCount > 0) {
                    $score = $attemptScore->scoes / $totalScoesCount * 100; //通过个数/总个数*100
                }
                break;
            default:
                $score = $attemptScore->max;   // Remote Learner GRADEHIGHEST is default.
        }

        return $score;
    }



    /**
     * Sets up $userdata array and default values for SCORM 1.2 .
     *
     * @param stdClass $userdata an empty stdClass variable that should be set up with user values
     * @param object $scorm package record
     * @param string $scoid SCO Id
     * @param string $attempt attempt number for the user
     * @param string $mode scorm display mode type
     * @return array The default values that should be used for SCORM 1.2 package
     */
    public function get_scorm_default(&$userdata, $courseRegId,$modResId, $scoId,  $attempt, $mode, $withSession = false) {

        $userId = Yii::$app->user->getId();
        $user = FwUser::findOne($userId);

        $userdata->student_id = $userId;
        $userdata->student_name = $user->real_name;// $USER->lastname .', '. $USER->firstname;

        if (!empty($courseRegId) && $usertrack = $this->scorm_get_tracks($courseRegId,$modResId,$scoId, $userId, $attempt,$withSession)) {
            foreach ($usertrack as $key => $value) {
                $userdata->$key = $value;
            }
        } else {
            $userdata->status = '';
            $userdata->score_raw = '';
        }

        if ($scodatas = $this->scorm_get_sco($scoId, self::SCO_DATA)) {
            foreach ($scodatas as $key => $value) {
                $userdata->$key = $value;
            }
        } else {
            //print_error('cannotfindsco', 'scorm');
        }
        if (!$sco = $this->scorm_get_sco($scoId)) {
            //print_error('cannotfindsco', 'scorm');
        }

        if (isset($userdata->status)) {
            if ($userdata->status == '') {
                $userdata->entry = 'ab-initio';
            } else {
                if (isset($userdata->{'cmi.core.exit'}) && ($userdata->{'cmi.core.exit'} == 'suspend')) {
                    $userdata->entry = 'resume';
                } else {
                    $userdata->entry = '';
                }
            }
        }

        $userdata->mode = 'normal';
        if (!empty($mode)) {
            $userdata->mode = $mode;
        }
        if ($userdata->mode == 'normal') {
            $userdata->credit = 'credit';
        } else {
            $userdata->credit = 'no-credit';
        }

        $def = array();
        $def['cmi.core.student_id'] = $userdata->student_id;
        $def['cmi.core.student_name'] = $userdata->student_name;
        $def['cmi.core.credit'] = $userdata->credit;
        $def['cmi.core.entry'] = $userdata->entry;
        $def['cmi.core.lesson_mode'] = $userdata->mode;
        $def['cmi.launch_data'] = $this->scorm_isset($userdata, 'datafromlms');
        $def['cmi.student_data.mastery_score'] = $this->scorm_isset($userdata, 'masteryscore');
        $def['cmi.student_data.max_time_allowed'] = $this->scorm_isset($userdata, 'maxtimeallowed');
        $def['cmi.student_data.time_limit_action'] = $this->scorm_isset($userdata, 'timelimitaction');
        $def['cmi.core.total_time'] = $this->scorm_isset($userdata, 'cmi.core.total_time', '00:00:00');

        // Now handle standard userdata items.
        $def['cmi.core.lesson_location'] = $this->scorm_isset($userdata, 'cmi.core.lesson_location');
        $def['cmi.core.lesson_status'] = $this->scorm_isset($userdata, 'cmi.core.lesson_status');
        $def['cmi.core.score.raw'] = $this->scorm_isset($userdata, 'cmi.core.score.raw');
        $def['cmi.core.score.max'] = $this->scorm_isset($userdata, 'cmi.core.score.max');
        $def['cmi.core.score.min'] = $this->scorm_isset($userdata, 'cmi.core.score.min');
        $def['cmi.core.exit'] = $this->scorm_isset($userdata, 'cmi.core.exit');
        $def['cmi.suspend_data'] = $this->scorm_isset($userdata, 'cmi.suspend_data');
        $def['cmi.comments'] = $this->scorm_isset($userdata, 'cmi.comments');
        $def['cmi.student_preference.language'] = $this->scorm_isset($userdata, 'cmi.student_preference.language');
        $def['cmi.student_preference.audio'] = $this->scorm_isset($userdata, 'cmi.student_preference.audio', '0');
        $def['cmi.student_preference.speed'] = $this->scorm_isset($userdata, 'cmi.student_preference.speed', '0');
        $def['cmi.student_preference.text'] = $this->scorm_isset($userdata, 'cmi.student_preference.text', '0');
        return $def;
    }

    /**
     * Check for a parameter in userdata and return it if it's set
     * or return the value from $ifempty if its empty
     *
     * @param stdClass $userdata Contains user's data
     * @param string $param parameter that should be checked
     * @param string $ifempty value to be replaced with if $param is not set
     * @return string value from $userdata->$param if its not empty, or $ifempty
     */
    private function scorm_isset($userdata, $param, $ifempty = '') {
        if (isset($userdata->$param)) {
            return $userdata->$param;
        } else {
            return $ifempty;
        }
    }

    /**
     * Build up the JavaScript representation of an array element
     *
     * @param string $a left array element
     * @param string $b right array element
     * @return comparator - 0,1,-1
     */
    function scorm_element_cmp($a, $b) {
        preg_match('/.*?(\d+)\./', $a, $matches);
        $left = intval($matches[1]);
        preg_match('/.?(\d+)\./', $b, $matches);
        $right = intval($matches[1]);
        if ($left < $right) {
            return -1; // Smaller.
        } else if ($left > $right) {
            return 1;  // Bigger.
        } else {
            // Look for a second level qualifier eg cmi.interactions_0.correct_responses_0.pattern.
            if (preg_match('/.*?(\d+)\.(.*?)\.(\d+)\./', $a, $matches)) {
                $leftterm = intval($matches[2]);
                $left = intval($matches[3]);
                if (preg_match('/.*?(\d+)\.(.*?)\.(\d+)\./', $b, $matches)) {
                    $rightterm = intval($matches[2]);
                    $right = intval($matches[3]);
                    if ($leftterm < $rightterm) {
                        return -1; // Smaller.
                    } else if ($leftterm > $rightterm) {
                        return 1;  // Bigger.
                    } else {
                        if ($left < $right) {
                            return -1; // Smaller.
                        } else if ($left > $right) {
                            return 1;  // Bigger.
                        }
                    }
                }
            }
            // Fall back for no second level matches or second level matches are equal.
            return 0;  // Equal to.
        }
    }

    /**
     * Build up the JavaScript representation of an array element
     *
     * @param string $sversion SCORM API version
     * @param array $userdata User track data
     * @param string $elementname Name of array element to get values for
     * @param array $children list of sub elements of this array element that also need instantiating
     * @return Javascript array elements
     */
    public function scorm_reconstitute_array_element($sversion, $userdata, $elementname, $children) {
        // Reconstitute comments_from_learner and comments_from_lms.
        $current = '';
        $currentsubelement = '';
        $currentsub = '';
        $count = 0;
        $countsub = 0;
        $scormseperator = '_';
        $return = '';
        if ($this->scorm_version_check($sversion, self::SCORM_13)) { // Scorm 1.3 elements use a . instead of an _ .
            $scormseperator = '.';
        }
        // Filter out the ones we want.
        $elementlist = array();
        foreach ($userdata as $element => $value) {
            if (substr($element, 0, strlen($elementname)) == $elementname) {
                $elementlist[$element] = $value;
            }
        }

        // Sort elements in .n array order.
        if (!empty($elementlist))
            uksort($elementlist, array($this, "scorm_element_cmp") );


        // Generate JavaScript.
        foreach ($elementlist as $element => $value) {
            if ($this->scorm_version_check($sversion, self::SCORM_13)) {
                $element = preg_replace('/\.(\d+)\./', ".N\$1.", $element);
                preg_match('/\.(N\d+)\./', $element, $matches);
            } else {
                $element = preg_replace('/\.(\d+)\./', "_\$1.", $element);
                preg_match('/\_(\d+)\./', $element, $matches);
            }
            if (count($matches) > 0 && $current != $matches[1]) {
                if ($countsub > 0) {
                    $return .= '    '.$elementname.$scormseperator.$current.'.'.$currentsubelement.'._count = '.$countsub.";\n";
                }
                $current = $matches[1];
                $count++;
                $currentsubelement = '';
                $currentsub = '';
                $countsub = 0;
                $end = strpos($element, $matches[1]) + strlen($matches[1]);
                $subelement = substr($element, 0, $end);
                $return .= '    '.$subelement." = new Object();\n";
                // Now add the children.
                if (!empty($children)) {
                    foreach ($children as $child) {
                        $return .= '    ' . $subelement . "." . $child . " = new Object();\n";
                        $return .= '    ' . $subelement . "." . $child . "._children = " . $child . "_children;\n";
                    }
                }
            }

            // Now - flesh out the second level elements if there are any.
            if ($this->scorm_version_check($sversion, self::SCORM_13)) {
                $element = preg_replace('/(.*?\.N\d+\..*?)\.(\d+)\./', "\$1.N\$2.", $element);
                preg_match('/.*?\.N\d+\.(.*?)\.(N\d+)\./', $element, $matches);
            } else {
                $element = preg_replace('/(.*?\_\d+\..*?)\.(\d+)\./', "\$1_\$2.", $element);
                preg_match('/.*?\_\d+\.(.*?)\_(\d+)\./', $element, $matches);
            }

            // Check the sub element type.
            if (count($matches) > 0 && $currentsubelement != $matches[1]) {
                if ($countsub > 0) {
                    $return .= '    '.$elementname.$scormseperator.$current.'.'.$currentsubelement.'._count = '.$countsub.";\n";
                }
                $currentsubelement = $matches[1];
                $currentsub = '';
                $countsub = 0;
                $end = strpos($element, $matches[1]) + strlen($matches[1]);
                $subelement = substr($element, 0, $end);
                $return .= '    '.$subelement." = new Object();\n";
            }

            // Now check the subelement subscript.
            if (count($matches) > 0 && $currentsub != $matches[2]) {
                $currentsub = $matches[2];
                $countsub++;
                $end = strrpos($element, $matches[2]) + strlen($matches[2]);
                $subelement = substr($element, 0, $end);
                $return .= '    '.$subelement." = new Object();\n";
            }

            $return .= '    '.$element.' = \''.$value."';\n";
        }
        if ($countsub > 0) {
            $return .= '    '.$elementname.$scormseperator.$current.'.'.$currentsubelement.'._count = '.$countsub.";\n";
        }
        if ($count > 0) {
            $return .= '    '.$elementname.'._count = '.$count.";\n";
        }
        return $return;
    }


    public function scorm_seq_overall($courseRegId,$courseCompleteProcessId,$courseCompleteFinalId,$modResId,$scorm,$scoId, $userId, $action, $attempt) {

        $seq = $this->scorm_seq_navigation($courseRegId,$modResId,$scorm,$scoId, $userId, $action, $attempt);
        if ($seq->navigation) {
            if ($seq->termination != null) {
                $seq = $this->scorm_seq_termination($courseRegId,$courseCompleteProcessId,$courseCompleteFinalId,$modResId,$scorm,$scoId, $userId, $seq, $attempt);
            }
            if ($seq->sequencing != null) {
                $seq = $this->scorm_seq_sequencing($courseRegId,$courseCompleteProcessId,$courseCompleteFinalId,$modResId,$scorm,$scoId, $userId, $seq, $attempt);
                if ($seq->sequencing == 'exit') { // Return the control to the LTS.
                    return 'true';
                }
            }
            if ($seq->delivery != null) {
                $seq = $this->scorm_sequencing_delivery($courseRegId,$modResId,$userId, $seq,$attempt);
                $seq = $this->scorm_content_delivery_environment($courseRegId,$courseCompleteProcessId,$courseCompleteFinalId,$modResId,$scorm,$seq, $userId,$attempt);
            }
        }
//        if ($seq->exception != null) {
//            $seq = $this->scorm_sequencing_exception($seq);
//        }
        return 'true';
    }

    // Delivery Request Process.
    private function scorm_sequencing_delivery($courseRegId,$modResId,$userId, $seq,$attempt) {

        if (!$this->scorm_is_leaf($seq->delivery)) {
            $seq->deliveryvalid = false;
            $seq->exception = 'DB.1.1-1';
            return $seq;
        }
        $ancestors = $this->scorm_get_ancestors($seq->delivery);
        $arrpath = array_reverse($ancestors);
        array_push ($arrpath, $seq->delivery); // Path from the root to the target.

        if (empty($arrpath)) {
            $seq->deliveryvalid = false;
            $seq->exception = 'DB.1.1-2';
            return $seq;
        }

        foreach ($arrpath as $activity) {
            if ($this->scorm_check_activity($courseRegId,$modResId,$activity, $userId,$attempt)) {
                $seq->deliveryvalid = false;
                $seq->exception = 'DB.1.1-3';
                return $seq;
            }
        }

        $seq->deliveryvalid = true;
        return $seq;

    }

    private function scorm_clear_suspended_activity($courseRegId,$courseCompleteProcessId,$courseCompleteFinalId,$modResId,$scorm,$userId, $sco, $seq, $attempt) {
        $scormScoesTrackService = new ScormScoesTrackService();


        $currentact = $seq->currentactivity;
        $scoId = $sco->kid;
        $track = $scormScoesTrackService->getScormScoesTrackElementInfoByAttempt($courseRegId,$modResId,$scoId,'suspendedactivity',$attempt);
        if ($track != null) {
            $ancestors = $this->scorm_get_ancestors($sco);
            $commonpos = $this->scorm_find_common_ancestor($ancestors, $currentact);
            if ($commonpos !== false) {
                if ($activitypath = array_slice($ancestors, 0, $commonpos)) {
                    if (!empty($activitypath)) {

                        foreach ($activitypath as $activity) {
                            if ($this->scorm_is_leaf($activity)) {
                                $this->scorm_seq_set($courseRegId,$courseCompleteProcessId,$courseCompleteFinalId,$modResId,$scorm,'suspended', $scoId, $userId,$attempt,false);
                            } else {
                                $children = $this->scorm_get_children($activity);
                                $bool = false;
                                foreach ($children as $child) {
                                    if ($this->scorm_seq_is($courseRegId,$modResId,'suspended', $child->kid, $userId,$attempt)) {
                                        $bool = true;
                                    }
                                }
                                if (!$bool) {
                                    $this->scorm_seq_set($courseRegId,$courseCompleteProcessId,$courseCompleteFinalId,$modResId,$scorm,'suspended', $activity->kid, $userId,$attempt,false);
                                }
                            }
                        }
                    }
                }
            }
            $this->scorm_seq_set($courseRegId,$courseCompleteProcessId,$courseCompleteFinalId,$modResId,$scorm,'suspendedactivity', $scoId, $userId,$attempt,false);
        }
    }

    private function scorm_content_delivery_environment($courseRegId,$courseCompleteProcessId,$courseCompleteFinalId,$modResId,$scorm,$seq, $userId,$attempt) {

        $act = $seq->currentactivity;
        $scoId = $act->kid;
        if ($this->scorm_seq_is( $courseRegId,$modResId,'active', $scoId, $userId,$attempt)) {
            $seq->exception = 'DB.2-1';
            return $seq;
        }
        $scormScoesTrackService = new ScormScoesTrackService();
        $track = $scormScoesTrackService->getScormScoesTrackElementInfoByAttempt($courseRegId,$modResId,$scoId,'suspendedactivity',$attempt);
        if ($track != null) {
//            $seq =
            $this->scorm_clear_suspended_activity($courseRegId,$courseCompleteProcessId,$courseCompleteFinalId,$modResId,$scorm,$userId, $seq->delivery, $seq, $attempt);
        }

        $this->scorm_terminate_descendent_attempts($courseRegId,$courseCompleteProcessId,$courseCompleteFinalId,$modResId,$scorm,$seq->delivery, $userId, $seq,$attempt);
        $ancestors = $this->scorm_get_ancestors($seq->delivery);
        $arrpath = array_reverse($ancestors);
        array_push ($arrpath, $seq->delivery);
        foreach ($arrpath as $activity) {
            $activityId = $activity->kid;
            if (!$this->scorm_seq_is($courseRegId,$modResId,'active', $activityId, $userId,$attempt)) {
                if (!isset($activity->tracked) || ($activity->tracked == 1)) {
                    if (!$this->scorm_seq_is($courseRegId,$modResId,'suspended', $activityId, $attempt)) {
                        $r = $scormScoesTrackService->getScormScoesTrackElementInfoByAttempt($courseRegId,$modResId,$scoId,'activityattemptcount',$attempt);
                        $r->value = ($r->value) + 1;
                        $r->save();
                        if ($r->value == 1) {
                            $this->scorm_seq_set($courseRegId,$courseCompleteProcessId,$courseCompleteFinalId,$modResId,$scorm,'activityprogressstatus', $activityId, $userId,$attempt);
                        }
                        $scormScoesTrackService->insertTrackData($courseRegId,$courseCompleteProcessId,$courseCompleteFinalId,$modResId,$scorm,$activityId, $userId, 'objectiveprogressstatus',$attempt, 'false');
                        $scormScoesTrackService->insertTrackData($courseRegId,$courseCompleteProcessId,$courseCompleteFinalId,$modResId,$scorm,$activityId, $userId, 'objectivesatisfiedstatus',$attempt, 'false');
                        $scormScoesTrackService->insertTrackData($courseRegId,$courseCompleteProcessId,$courseCompleteFinalId,$modResId,$scorm,$activityId, $userId, 'objectivemeasurestatus', $attempt,'false');
                        $scormScoesTrackService->insertTrackData($courseRegId,$courseCompleteProcessId,$courseCompleteFinalId,$modResId,$scorm,$activityId, $userId, 'objectivenormalizedmeasure',$attempt, '0.0');

                        $scormScoesTrackService->insertTrackData($courseRegId,$courseCompleteProcessId,$courseCompleteFinalId,$modResId,$scorm,$activityId, $userId, 'attemptprogressstatus',$attempt, 'false');
                        $scormScoesTrackService->insertTrackData($courseRegId,$courseCompleteProcessId,$courseCompleteFinalId,$modResId,$scorm,$activityId, $userId, 'attemptcompletionstatus',$attempt, 'false');
                        $scormScoesTrackService->insertTrackData($courseRegId,$courseCompleteProcessId,$courseCompleteFinalId,$modResId,$scorm,$activityId, $userId, 'attemptabsoluteduration',$attempt, '0.0');
                        $scormScoesTrackService->insertTrackData($courseRegId,$courseCompleteProcessId,$courseCompleteFinalId,$modResId,$scorm,$activityId, $userId, 'attemptexperiencedduration',$attempt, '0.0');
                        $scormScoesTrackService->insertTrackData($courseRegId,$courseCompleteProcessId,$courseCompleteFinalId,$modResId,$scorm,$activityId, $userId, 'attemptcompletionamount',$attempt, '0.0');
                    }
                }
                $this->scorm_seq_set($courseRegId,$courseCompleteProcessId,$courseCompleteFinalId,$modResId,$scorm,'active', $activityId, $userId,$attempt);
            }
        }
        $seq->delivery = $seq->currentactivity;
        $this->scorm_seq_set($courseRegId,$courseCompleteProcessId,$courseCompleteFinalId,$modResId,$scorm,'suspendedactivity', $activityId, $userId,$attempt, false);

        // ONCE THE DELIVERY BEGINS (How should I check that?).

//        if (isset($act->tracked) || ($act->tracked == 0)) {
//            // How should I track the info and what should I do to not record the information for the activity during delivery?
//            $atabsdur = $scormScoesTrackService->getScormScoesTrackElementInfoByAttempt($courseRegId,$modResId,$scoId,$userId,'attemptabsoluteduration',$attempt);
//            $atexpdur = $scormScoesTrackService->getScormScoesTrackElementInfoByAttempt($courseRegId,$modResId,$scoId,$userId,'attemptexperiencedduration',$attempt);
//        }
        return $seq;
    }

    private function scorm_sequencing_exception($seq)
    {
        if ($seq->exception != null) {
            switch ($seq->exception) {
                case 'NB.2.1-1':
                    echo Yii::t("common", "Sequencing session has already begun");
                    break;
                case 'NB.2.1-2':
                    echo Yii::t("common", "Sequencing session has not begun");
                    break;
                case 'NB.2.1-3':
                    echo Yii::t("common", "Suspended activity is not defined");
                    break;
                case 'NB.2.1-4':
                    echo Yii::t("common", "Flow Sequencing Control Model Violation");
                    break;
                case 'NB.2.1-5':
                    echo Yii::t("common", "Flow or Forward only Sequencing Control Model Violation");
                    break;
                case 'NB.2.1-6':
                    echo Yii::t("common", "No activity is previous to the root");
                    break;
                case 'NB.2.1-7':
                    echo Yii::t("common", "Unsupported Navigation Request");
                    break;
                case 'NB.2.1-8':
                    echo Yii::t("common", "Choice Exit Sequencing Control Model Violation");
                    break;
                case 'NB.2.1-9':
                    echo Yii::t("common", "No activities to consider");
                    break;
                case 'NB.2.1-10':
                    echo Yii::t("common", "Choice Sequencing Control Model Violation");
                    break;
                case 'NB.2.1-11':
                    echo Yii::t("common", "Target Activity does not exist");
                    break;
                case 'NB.2.1-12':
                    echo Yii::t("common", "Current Activity already terminated");
                    break;
                case 'NB.2.1-13':
                    echo Yii::t("common", "Undefined Navigation Request");
                    break;

                case 'TB.2.3-1':
                    echo Yii::t("common", "Current Activity already terminated");
                    break;
                case 'TB.2.3-2':
                    echo Yii::t("common", "Current Activity already terminated");
                    break;
                case 'TB.2.3-4':
                    echo Yii::t("common", "Current Activity already terminated");
                    break;
                case 'TB.2.3-5':
                    echo Yii::t("common", "Nothing to suspend; No active activities");
                    break;
                case 'TB.2.3-6':
                    echo Yii::t("common", "Nothing to abandon; No active activities");
                    break;

                case 'SB.2.1-1':
                    echo Yii::t("common", "Last activity in the tree");
                    break;
                case 'SB.2.1-2':
                    echo Yii::t("common", "Cluster has no available children");
                    break;
                case 'SB.2.1-3':
                    echo Yii::t("common", "No activity is previous to the root");
                    break;
                case 'SB.2.1-4':
                    echo Yii::t("common", "Forward Only Sequencing Control Model Violation");
                    break;

                case 'SB.2.2-1':
                    echo Yii::t("common", "Flow Sequencing Control Model Violation");
                    break;
                case 'SB.2.2-2':
                    echo Yii::t("common", "Activity unavailable");
                    break;

                case 'SB.2.3-1':
                    echo Yii::t("common", "Forward Traversal Blocked");
                    break;
                case 'SB.2.3-2':
                    echo Yii::t("common", "Forward Only Sequencing Control Model Violation");
                    break;
                case 'SB.2.3-3':
                    echo Yii::t("common", "No activity is previous to the root");
                    break;

                case 'SB.2.5-1':
                    echo Yii::t("common", "Sequencing session has already begun");
                    break;

                case 'SB.2.6-1':
                    echo Yii::t("common", "Sequencing session has already begun");
                    break;
                case 'SB.2.6-2':
                    echo Yii::t("common", "No Suspended activity is defined");
                    break;

                case 'SB.2.7-1':
                    echo Yii::t("common", "Sequencing session has not begun");
                    break;
                case 'SB.2.7-2':
                    echo Yii::t("common", "Flow Sequencing Control Model Violation");
                    break;

                case 'SB.2.8-1':
                    echo Yii::t("common", "Sequencing session has not begun");
                    break;
                case 'SB.2.8-2':
                    echo Yii::t("common", "Flow Sequencing Control Model Violation");
                    break;

                case 'SB.2.9-1':
                    echo Yii::t("common", "No target for Choice");
                    break;
                case 'SB.2.9-2':
                    echo Yii::t("common", "Target Activity does not exist or is unavailable");
                    break;
                case 'SB.2.9-3':
                    echo Yii::t("common", "Target Activity hidden from choice");
                    break;
                case 'SB.2.9-4':
                    echo Yii::t("common", "Choice Sequencing Control Model Violation");
                    break;
                case 'SB.2.9-5':
                    echo Yii::t("common", "No activities to consider");
                    break;
                case 'SB.2.9-6':
                    echo Yii::t("common", "Unable to activate target; target is not a child of the Current Activity");
                    break;
                case 'SB.2.9-7':
                    echo Yii::t("common", "Choice Exit Sequencing Control Model Violation");
                    break;
                case 'SB.2.9-8':
                    echo Yii::t("common", "Unable to choose target activity - constrained choice");
                    break;
                case 'SB.2.9-9':
                    echo Yii::t("common", "Choice Request Prevented by Flow-only Activity");
                    break;

                case 'SB.2.10-1':
                    echo Yii::t("common", "Sequencing session has not begun");
                    break;
                case 'SB.2.10-2':
                    echo Yii::t("common", "Current Activity is active or suspended");
                    break;
                case 'SB.2.10-3':
                    echo Yii::t("common", "Flow Sequencing Control Model Violation");
                    break;

                case 'SB.2.11-1':
                    echo Yii::t("common", "Sequencing session has not begun");
                    break;
                case 'SB.2.11-2':
                    echo Yii::t("common", "Current Activity has not been terminated");
                    break;

                case 'SB.2.12-2':
                    echo Yii::t("common", "Undefined Sequencing Request");
                    break;

                case 'DB.1.1-1':
                    echo Yii::t("common", "Cannot deliver a non-leaf activity");
                    break;
                case 'DB.1.1-2':
                    echo Yii::t("common", "Nothing to deliver");
                    break;
                case 'DB.1.1-3':
                    echo Yii::t("common", "Activity unavailable");
                    break;

                case 'DB.2-1':
                    echo Yii::t("common", "Identified activity is already active");
                    break;

            }

        }
    }

    private function scorm_seq_is($courseRegId,$modResId,$element, $scoId, $userId, $attempt = "1") {

        // Check if passed activity $what is active.
        $result = false;
        $scormScoesTrackService = new ScormScoesTrackService();
        $track = $scormScoesTrackService->getScormScoesTrackElementInfoByAttempt($courseRegId,$modResId,$scoId,$element,$attempt);
        if (!empty($track)) {
            $result = true;
        }
        return $result;
    }

    private function scorm_seq_navigation($courseRegId,$modResId,$scorm,$scoId, $userId, $action, $attempt) {
        $scormId = $scorm->kid;
        $scormScoesTrackService = new ScormScoesTrackService();
        $scormScoesService = new ScormScoesService();
        $sco = $this->scorm_get_sco($scoId);

        // Sequencing structure.
        $seq = new stdClass();
        $seq->currentactivity = $sco;
        $seq->traversaldir = null;
        $seq->nextactivity = null;
        $seq->deliveryvalid = null;
        $seq->attempt = $attempt;

        $seq->identifiedactivity = null;
        $seq->delivery = null;
        $seq->deliverable = false;
        $seq->active = $this->scorm_seq_is($courseRegId,$modResId,'active', $scoId, $userId, $attempt);
        $seq->suspended = $this->scorm_seq_is($courseRegId,$modResId,'suspended', $scoId, $userId, $attempt);
        $seq->navigation = null;
        $seq->termination = null;
        $seq->sequencing = null;
        $seq->target = null;
        $seq->endsession = null;
        $seq->exception = null;
        $seq->reachable = true;
        $seq->prevact = true;

        switch ($action) {
            case 'start_':
                if (empty($seq->currentactivity)) {
                    $seq->navigation = true;
                    $seq->sequencing = 'start';
                } else {
                    $seq->exception = 'NB.2.1-1'; // Sequencing session already begun.
                }
                break;
            case 'resumeall_':
                if (empty($seq->currentactivity)) {
                    // TODO: I think it's suspend instead of suspendedactivity.
                    $track = $scormScoesTrackService->getScormScoesTrackElementInfoByAttempt($courseRegId,$modResId,$scoId,'suspendedactivity',$attempt);
                    if (!empty($track)) {
                        $seq->navigation = true;
                        $seq->sequencing = 'resumeall';
                    } else {
                        $seq->exception = 'NB.2.1-3'; // No suspended activity found.
                    }
                } else {
                    $seq->exception = 'NB.2.1-1'; // Sequencing session already begun.
                }
                break;
            case 'continue_':
            case 'previous_':
                if (!empty($seq->currentactivity)) {
                    $sco = $seq->currentactivity;
                    if ($sco->parent != '/') {
                        if ($parentsco = $this->scorm_get_parent($sco)) {

                            if (isset($parentsco->flow) && ($parentsco->flow == true)) { // I think it's parentsco.
                                // Current activity is active!
                                if ($this->scorm_seq_is($courseRegId,$modResId,'active', $scoId, $userId, $attempt)) {
                                    if ($action == 'continue_') {
                                        $seq->navigation = true;
                                        $seq->termination = 'exit';
                                        $seq->sequencing = 'continue';
                                    } else {
                                        if (!isset($parentsco->forwardonly) || ($parentsco->forwardonly == false)) {
                                            $seq->navigation = true;
                                            $seq->termination = 'exit';
                                            $seq->sequencing = 'previous';
                                        } else {
                                            $seq->exception = 'NB.2.1-5'; // Violates control mode.
                                        }
                                    }
                                }
                            }

                        }
                    }
                } else {
                    $seq->exception = 'NB.2.1-2'; // Current activity not defined.
                }
                break;
            case 'forward_':
            case 'backward_':
                $seq->exception = 'NB.2.1-7'; // None to be done, behavior not defined.
                break;
            case 'exit_':
            case 'abandon_':
                if (!empty($seq->currentactivity)) {
                    // Current activity is active !
                    $seq->navigation = true;
                    $seq->termination = substr($action, 0, -1);
                    $seq->sequencing = 'exit';
                } else {
                    $seq->exception = 'NB.2.1-2'; // Current activity not defined.
                }
            case 'exitall_':
            case 'abandonall_':
            case 'suspendall_':
                if (!empty($seq->currentactivity)) {
                    $seq->navigation = true;
                    $seq->termination = substr($action, 0, -1);
                    $seq->sequencing = 'exit';
                } else {
                    $seq->exception = 'NB.2.1-2'; // Current activity not defined.
                }
                break;
            default: // Example {target=<STRING>}choice.
                $targetsco = $scormScoesService->getScormScoesByIdentifier($scormId,$action);
                if (!empty($targetsco)) {
                    if ($targetsco->parent != '/') {
                        $seq->target = $action;
                    } else {
                        if ($parentsco = $this->scorm_get_parent($targetsco)) {
                            if (!isset($parentsco->choice) || ($parentsco->choice == true)) {
                                $seq->target = $action;
                            }
                        }
                    }
                    if ($seq->target != null) {
                        if (empty($seq->currentactivity)) {
                            $seq->navigation = true;
                            $seq->sequencing = 'choice';
                        } else {
//                            if (!$sco) {
//                                return $seq;
//                            }
                            if ($sco->parent != $targetsco->parent) {
                                $ancestors = $this->scorm_get_ancestors($sco);
                                $commonpos = $this->scorm_find_common_ancestor($ancestors, $targetsco);
                                if ($commonpos !== false) {
                                    if ($activitypath = array_slice($ancestors, 0, $commonpos)) {
                                        foreach ($activitypath as $activity) {
                                            if (($this->scorm_seq_is($courseRegId,$modResId,'active', $activity->kid, $userId, $attempt)) &&
                                                (isset($activity->choiceexit) && ($activity->choiceexit == false))) {
                                                $seq->navigation = false;
                                                $seq->termination = null;
                                                $seq->sequencing = null;
                                                $seq->target = null;
                                                $seq->exception = 'NB.2.1-8'; // Violates control mode.
                                                return $seq;
                                            }
                                        }
                                    } else {
                                        $seq->navigation = false;
                                        $seq->termination = null;
                                        $seq->sequencing = null;
                                        $seq->target = null;
                                        $seq->exception = 'NB.2.1-9';
                                    }
                                }
                            }
                            // Current activity is active !
                            $seq->navigation = true;
                            $seq->sequencing = 'choice';
                        }
                    } else {
                        $seq->exception = 'NB.2.1-10';  // Violates control mode.
                    }
                } else {
                    $seq->exception = 'NB.2.1-11';  // Target activity does not exists.
                }
                break;
        }
        return $seq;
    }

    private function scorm_seq_set($courseRegId,$courseCompleteProcessId,$courseCompleteFinalId,$modResId, $scorm, $element, $scoId, $userId, $attempt="1", $value=true) {
        $scormScoesTrackService = new ScormScoesTrackService();
        //$sco = $this->scorm_get_sco($scoId);

        // Set passed activity to active or not.
        if ($value == false) {
            $scormScoesTrackService->deleteScoesTrackInfoByElement($courseRegId,$modResId,$scoId,$element,$attempt);
        } else {
            $scormScoesTrackService->insertTrackData($courseRegId,$courseCompleteProcessId,$courseCompleteFinalId,$modResId,$scorm,$scoId,$userId,$element,  $attempt,  $value);
        }
    }

    private function scorm_seq_end_attempt($courseRegId,$courseCompleteProcessId,$courseCompleteFinalId,$modResId,$scorm, $sco, $userId,  $attempt) {
        $scoId = $sco->kid;
        if ($this->scorm_is_leaf($sco)) {
            if (!isset($sco->tracked) || ($sco->tracked == 1)) {
                if (!$this->scorm_seq_is($courseRegId,$modResId,'suspended', $scoId, $userId,$attempt)) {
                    if (!isset($sco->completionsetbycontent) || ($sco->completionsetbycontent == 0)) {
                        if (!$this->scorm_seq_is($courseRegId,$modResId,'attemptprogressstatus', $scoId, $userId, $attempt)) {
                            $scormScoesTrackService = new ScormScoesTrackService();
                            $track = $scormScoesTrackService->getScormScoesTrackElementInfoByAttempt($courseRegId,$modResId,$scoId,'cmi.completion_status',$attempt);
                            if (!empty($track))
                            {
                                $incomplete = $track->value;
                            }
                            if ($incomplete != 'incomplete') {
                                $this->scorm_seq_set($courseRegId,$courseCompleteProcessId,$courseCompleteFinalId,$modResId,$scorm,'attemptprogressstatus', $scoId, $userId, $attempt);
                                $this->scorm_seq_set($courseRegId,$courseCompleteProcessId,$courseCompleteFinalId,$modResId,$scorm,'attemptcompletionstatus', $scoId, $userId, $attempt);
                            }
                        }
                    }
                    if (!isset($sco->objectivesetbycontent) || ($sco->objectivesetbycontent == 0)) {
                        $scormSeqObjectiveService = new ScormSeqObjectiveService();
                        $objectives = $scormSeqObjectiveService->GetScormSeqObjectiveByScormScoId($scoId);
                        if (!empty($objectives)) {
                            foreach ($objectives as $objective) {
                                if ($objective->primaryobj) {
                                    if (!$this->scorm_seq_is($courseRegId,$modResId,'objectiveprogressstatus', $scoId, $userId, $attempt)) {
                                        $this->scorm_seq_set($courseRegId,$courseCompleteProcessId,$courseCompleteFinalId,$modResId,$scorm,'objectiveprogressstatus', $scoId, $userId, $attempt);
                                        $this->scorm_seq_set($courseRegId,$courseCompleteProcessId,$courseCompleteFinalId,$modResId,$scorm,'objectivesatisfiedstatus', $scoId, $userId, $attempt);
                                    }
                                }
                            }
                        }
                    }
                }
            }
        } else if ($children = $this->scorm_get_children($sco)) {
            $suspended = false;
            foreach ($children as $child) {
                $scoId = $child->kid;
                if ($this->scorm_seq_is($courseRegId,$modResId,'suspended', $scoId, $userId, $attempt)) {
                    $suspended = true;
                    break;
                }
            }
            if ($suspended) {
                $this->scorm_seq_set($courseRegId,$courseCompleteProcessId,$courseCompleteFinalId,$modResId,$scorm,'suspended', $scoId, $userId, $attempt);
            } else {
                $this->scorm_seq_set($courseRegId,$courseCompleteProcessId,$courseCompleteFinalId,$modResId,$scorm,'suspended', $scoId, $userId, $attempt, false);
            }
        }
        $this->scorm_seq_set($courseRegId,$courseCompleteProcessId,$courseCompleteFinalId,$modResId,$scorm,'active', $scoId, $userId, $attempt, false);
        $this->scorm_seq_overall_rollup($courseRegId,$courseCompleteProcessId,$courseCompleteFinalId,$modResId,$scorm,$sco, $userId, $attempt);
    }

    private function scorm_seq_overall_rollup($courseRegId,$courseCompleteProcessId,$courseCompleteFinalId,$modResId,$scorm,$sco, $userId, $attempt) {
        if ($ancestors = $this->scorm_get_ancestors($sco)) {
            foreach ($ancestors as $ancestor) {
                if (!$this->scorm_is_leaf($ancestor)) {
                    $this->scorm_seq_measure_rollup($courseRegId,$courseCompleteProcessId,$courseCompleteFinalId,$modResId,$scorm,$sco, $userId, $attempt);
                }
                $this->scorm_seq_objective_rollup($courseRegId,$courseCompleteProcessId,$courseCompleteFinalId,$modResId, $scorm, $sco, $userId, $attempt);
                $this->scorm_seq_activity_progress_rollup($courseRegId,$courseCompleteProcessId,$courseCompleteFinalId,$modResId, $scorm, $sco, $userId, $attempt);
            }
        }
    }

    private function scorm_seq_objective_rollup($courseRegId,$courseCompleteProcessId,$courseCompleteFinalId,$modResId,$scorm,$sco, $userId, $attempt = "1") {

        $this->scorm_seq_objective_rollup_measure($courseRegId,$courseCompleteProcessId,$courseCompleteFinalId,$modResId,$scorm,$sco, $userId, $attempt);
        $this->scorm_seq_objective_rollup_rules($courseRegId,$courseCompleteProcessId,$courseCompleteFinalId,$modResId,$scorm,$sco, $userId, $attempt);
        $this->scorm_seq_objective_rollup_default($courseRegId,$courseCompleteProcessId,$courseCompleteFinalId,$modResId,$scorm,$sco, $userId, $attempt);
    }

    private function scorm_seq_objective_rollup_measure($courseRegId,$courseCompleteProcessId,$courseCompleteFinalId,$modResId,$scorm,$sco, $userId, $attempt = "1") {
        $scoId = $sco->kid;
        $targetobjective = null;

        $scormSeqObjectiveService = new ScormSeqObjectiveService();
        $objectives = $scormSeqObjectiveService->GetScormSeqObjectiveByScormScoId($scoId);
        foreach ($objectives as $objective) {
            if ($objective->primary_obj == true) {
                $targetobjective = $objective;
                break;
            }
        }
        if ($targetobjective != null) {
            if ($targetobjective->satisfied_by_measure) {
                if (!$this->scorm_seq_is($courseRegId,$modResId,'objectiveprogressstatus', $scoId, $userId, $attempt)) {
                    $this->scorm_seq_set($courseRegId,$courseCompleteProcessId,$courseCompleteFinalId,$modResId,$scorm,'objectiveprogressstatus', $scoId, $userId, $attempt,false);
                } else {
                    if ($this->scorm_seq_is($courseRegId,$modResId,'active', $sco->kid, $userId, $attempt)) {
                        $isactive = true;
                    } else {
                        $isactive = false;
                    }

                    $scormScoesTrackService = new ScormScoesTrackService();
                    $normalizedmeasure = $scormScoesTrackService->getScormScoesTrackElementInfoByAttempt($courseRegId,$modResId,$scoId,'objectivenormalizedmeasure',$attempt);

                    $sco = $this->scorm_get_sco($sco->kid);

                    if (!$isactive || ($isactive &&
                            (!isset($sco->measuresatisfactionifactive) || $sco->measuresatisfactionifactive == true))) {
                        if (isset($normalizedmeasure->value) && ($normalizedmeasure->value >= $targetobjective->min_normalized_measure)) {
                            $this->scorm_seq_set($courseRegId,$courseCompleteProcessId,$courseCompleteFinalId,$modResId,$scorm,'objectiveprogressstatus', $scoId, $userId, $attempt);
                            $this->scorm_seq_set($courseRegId,$courseCompleteProcessId,$courseCompleteFinalId,$modResId,$scorm,'objectivesatisfiedstatus', $scoId, $userId, $attempt);
                        } else {
                            // TODO: handle the case where cmi.success_status is passed and objectivenormalizedmeasure undefined.
                            $this->scorm_seq_set($courseRegId,$courseCompleteProcessId,$courseCompleteFinalId,$modResId,$scorm,'objectiveprogressstatus', $scoId, $userId, $attempt);
                        }
                    } else {
                        $this->scorm_seq_set($courseRegId,$courseCompleteProcessId,$courseCompleteFinalId,$modResId,$scorm,'objectiveprogressstatus', $scoId, $userId, $attempt,false);
                    }
                }
            }
        }
    }

    private function scorm_seq_objective_rollup_default($courseRegId,$courseCompleteProcessId,$courseCompleteFinalId,$modResId,$scorm,$sco, $userId, $attempt = "0") {

        $scormSeqRollupruleService = new ScormSeqRollupruleService();
        $scormSeqRolluprulecondService = new ScormSeqRolluprulecondService();
        $scoId = $sco->kid;
        if (!($this->scorm_seq_rollup_rule_check($courseRegId,$modResId,$sco, $userId, 'incomplete',$attempt))
            && !($this->scorm_seq_rollup_rule_check($courseRegId,$modResId,$sco, $userId, 'completed',$attempt))) {

            $rolluprules = $scormSeqRollupruleService->GetScormSeqRollupruleByScormScoId($scoId);
            if (!empty($rolluprules)) {
                foreach ($rolluprules as $rolluprule) {
                    $rollupruleconds = $scormSeqRolluprulecondService->GetScormSeqRolluprulecondByScormScoId($rolluprule->kid);
                    foreach ($rollupruleconds as $rolluprulecond) {
                        if ($rolluprulecond->cond != 'satisfied' && $rolluprulecond->cond != 'completed' &&
                            $rolluprulecond->cond != 'attempted') {
                            $this->scorm_seq_set($courseRegId,$courseCompleteProcessId,$courseCompleteFinalId,$modResId,$scorm,'objectivesatisfiedstatus', $scoId, $userId, $attempt,false);
                            break;
                        }
                    }
                }
            }
        }
    }


    private function scorm_seq_objective_rollup_rules($courseRegId,$courseCompleteProcessId,$courseCompleteFinalId,$modResId,$scorm,$sco, $userId, $attempt = "1") {
        $scoId = $sco->kid;

        $targetobjective = null;
        $scormSeqObjectiveService = new ScormSeqObjectiveService();
        $objectives = $scormSeqObjectiveService->GetScormSeqObjectiveByScormScoId($scoId);
        foreach ($objectives as $objective) {
            if ($objective->primaryobj == true) {// Objective contributes to rollup I'm using primaryobj field, but not.
                $targetobjective = $objective;
                break;
            }
        }
        if ($targetobjective != null) {

            if ($this->scorm_seq_rollup_rule_check($courseRegId,$modResId,$sco, $userId, 'notsatisfied',$attempt)) {// With not satisfied rollup for the activity.
                $this->scorm_seq_set($courseRegId,$courseCompleteProcessId,$courseCompleteFinalId,$modResId,$scorm,'objectiveprogressstatus', $scoId, $userId, $attempt);
                $this->scorm_seq_set($courseRegId,$courseCompleteProcessId,$courseCompleteFinalId,$modResId,$scorm,'objectivesatisfiedstatus', $scoId, $userId, $attempt, false);
            }
            if ($this->scorm_seq_rollup_rule_check($courseRegId,$modResId,$sco, $userId, 'satisfied',$attempt)) {// With satisfied rollup for the activity.
                $this->scorm_seq_set($courseRegId,$courseCompleteProcessId,$courseCompleteFinalId,$modResId,$scorm,'objectiveprogressstatus', $scoId, $userId, $attempt);
                $this->scorm_seq_set($courseRegId,$courseCompleteProcessId,$courseCompleteFinalId,$modResId,$scorm,'objectivesatisfiedstatus', $scoId, $userId, $attempt);
            }

        }
    }

    private function scorm_seq_activity_progress_rollup($courseRegId,$courseCompleteProcessId,$courseCompleteFinalId,$modResId,$scorm,$sco, $userId, $attempt = "1") {
        $scoId = $sco->kid;

        if ($this->scorm_seq_rollup_rule_check($courseRegId,$modResId,$sco, $userId, 'incomplete',$attempt)) {
            // Incomplete rollup action.
            $this->scorm_seq_set($courseRegId,$courseCompleteProcessId,$courseCompleteFinalId,$modResId,$scorm,'attemptcompletionstatus', $scoId, $userId, $attempt, false);
            $this->scorm_seq_set($courseRegId,$courseCompleteProcessId,$courseCompleteFinalId,$modResId,$scorm,'attemptprogressstatus', $scoId, $userId, $attempt);

        }
        if ($this->scorm_seq_rollup_rule_check($courseRegId,$modResId,$sco, $userId, 'completed',$attempt)) {
            // Incomplete rollup action.
            $this->scorm_seq_set($courseRegId,$courseCompleteProcessId,$courseCompleteFinalId,$modResId,$scorm,'attemptcompletionstatus', $scoId, $userId, $attempt);
            $this->scorm_seq_set($courseRegId,$courseCompleteProcessId,$courseCompleteFinalId,$modResId,$scorm,'attemptprogressstatus', $scoId, $userId, $attempt);
        }
    }

    private function scorm_seq_rollup_rule_check($courseRegId,$modResId,$sco, $userId, $action,$attempt) {
        $scoId = $sco->kid;
        $scormSeqRollupruleService = new ScormSeqRollupruleService();
        $scormSeqRolluprulecondService = new ScormSeqRolluprulecondService();

        $rolluprules = $scormSeqRollupruleService->GetScormSeqRollupruleByScormScoId($scoId,$action);
        if (!empty($rolluprules)) {
            $childrenbag = Array ();
            $children = $this->scorm_get_children($sco);

            foreach ($rolluprules as $rolluprule) {
                foreach ($children as $child) {

                    /*$tracked = $DB->get_records('scorm_scoes_track', array('scoid'=>$child->id, 'userid'=>$userid));
                    if ($tracked && $tracked->attemp != 0) {*/
                    $child = $this->scorm_get_sco ($child);
                    if (!isset($child->tracked) || ($child->tracked == 1)) {
                        if ($this->scorm_seq_check_child($courseRegId,$modResId,$child, $action, $userId,$attempt)) {
                            $rollupruleconds = $scormSeqRolluprulecondService->GetScormSeqRolluprulecondByScormScoId($rolluprule->kid);
                            $evaluate = $this->scorm_seq_evaluate_rollupcond($courseRegId,$modResId,$child, $rolluprule->condition_combination,
                                $rollupruleconds, $userId,$attempt);
                            if ($evaluate == 'unknown') {
                                array_push($childrenbag, 'unknown');
                            } else {
                                if ($evaluate == true) {
                                    array_push($childrenbag, true);
                                } else {
                                    array_push($childrenbag, false);
                                }
                            }
                        }
                    }
                }
                $change = false;

                switch ($rolluprule->child_activity_set) {

                    case 'all':
                        // I think I can use this condition instead equivalent to OR.
                        if ((array_search(false, $childrenbag) === false) && (array_search('unknown', $childrenbag) === false)) {
                            $change = true;
                        }
                        break;

                    case 'any':
                        // I think I can use this condition instead equivalent to OR.
                        if (array_search(true, $childrenbag) !== false) {
                            $change = true;
                        }
                        break;

                    case 'none':
                        // I think I can use this condition instead equivalent to OR.
                        if ((array_search(true, $childrenbag) === false) && (array_search('unknown', $childrenbag) === false)) {
                            $change = true;
                        }
                        break;

                    case 'atleastcount':
                        // I think I can use this condition instead equivalent to OR.
                        foreach ($childrenbag as $itm) {
                            $cont = 0;
                            if ($itm === true) {
                                $cont++;
                            }
                            if ($cont >= $rolluprule->minimum_count) {
                                $change = true;
                            }
                        }
                        break;

                    case 'atleastcount':
                        foreach ($childrenbag as $itm) {// I think I can use this condition instead equivalent to OR.
                            $cont = 0;
                            if ($itm === true) {
                                $cont++;
                            }
                            if ($cont >= $rolluprule->minimum_count) {
                                $change = true;
                            }
                        }
                        break;

                    case 'atleastpercent':
                        foreach ($childrenbag as $itm) {// I think I can use this condition instead equivalent to OR.
                            $cont = 0;
                            if ($itm === true) {
                                $cont++;
                            }
                            if (($cont / count($childrenbag)) >= $rolluprule->minimum_count) {
                                $change = true;
                            }
                        }
                        break;
                }
                if ($change == true) {
                    return true;
                }
            }
        }
        return false;
    }

    private function scorm_seq_evaluate_rollupcond($courseRegId,$modResId,$sco, $conditioncombination, $rollupruleconds, $userId, $attempt) {
        $bag = Array();
        $con = "";
        $val = false;
        $unk = false;
        foreach ($rollupruleconds as $rolluprulecond) {
            $condit = $this->scorm_evaluate_condition($courseRegId,$modResId,$rolluprulecond, $sco, $userId, $attempt);
            if ($rolluprulecond->operator == 'not') { // If operator is not, negate the condition.
                if ($rolluprulecond->cond != 'unknown') {
                    if ($condit) {
                        $condit = false;
                    } else {
                        $condit = true;
                    }
                } else {
                    $condit = 'unknown';
                }
                array_push($childrenbag, $condit);
            }
        }
        if (empty($bag)) {
            return 'unknown';
        } else {
            $i = 0;
            foreach ($bag as $b) {
                if ($rolluprulecond->condition_combination == 'all') {
                    $val = true;
                    if ($b == 'unknown') {
                        $unk = true;
                    }
                    if ($b === false) {
                        return false;
                    }
                } else {
                    $val = false;

                    if ($b == 'unknown') {
                        $unk = true;
                    }
                    if ($b === true) {
                        return true;
                    }
                }
            }
        }
        if ($unk) {
            return 'unknown';
        }
        return $val;
    }

    private function scorm_evaluate_condition($courseRegId,$modResId,$rollupruleconds, $sco, $userId,$attempt) {
        $scoId = $sco->kid;
        $res = false;

        if (strpos($rollupruleconds, 'and ')) {
            $rollupruleconds = array_filter(explode(' and ', $rollupruleconds));
            $conditioncombination = 'all';
        } else {
            $rollupruleconds = array_filter(explode(' or ', $rollupruleconds));
            $conditioncombination = 'or';
        }

        foreach ($rollupruleconds as $rolluprulecond) {
            $notflag = false;
            if (strpos($rolluprulecond, 'not') !== false) {
                $rolluprulecond = str_replace('not', '', $rolluprulecond);
                $notflag = true;
            }
            $conditionarray['condition'] = $rolluprulecond;
            $conditionarray['notflag'] = $notflag;
            $conditions[] = $conditionarray;
        }

        $scormScoesTrackService = new ScormScoesTrackService();

        if (!empty($conditions)) {
            foreach ($conditions as $condition) {
                $checknot = true;
                $res = false;
                if ($condition['notflag']) {
                    $checknot = false;
                }
                switch ($condition['condition']) {
                    case 'satisfied':
                        $r = $scormScoesTrackService->getScormScoesTrackElementInfoByAttempt($courseRegId,$modResId,$scoId,'objectivesatisfiedstatus',$attempt);
                        if ((!isset($r->value) && !$checknot) || (isset($r->value) && ($r->value == $checknot))) {
                            $r = $scormScoesTrackService->getScormScoesTrackElementInfoByAttempt($courseRegId,$modResId,$scoId,'objectiveprogressstatus',$attempt);
                            if ((!isset($r->value) && !$checknot) || (isset($r->value) && ($r->value == $checknot))) {
                                $res = true;
                            }
                        }
                        break;

                    case 'objectiveStatusKnown':
                        $r = $scormScoesTrackService->getScormScoesTrackElementInfoByAttempt($courseRegId,$modResId,$scoId,'objectiveprogressstatus',$attempt);
                        if ((!isset($r->value) && !$checknot) || (isset($r->value) && ($r->value == $checknot))) {
                            $res = true;
                        }
                        break;

                    case 'notobjectiveStatusKnown':
                        $r = $scormScoesTrackService->getScormScoesTrackElementInfoByAttempt($courseRegId,$modResId,$scoId,'objectiveprogressstatus',$attempt);
                        if ((!isset($r->value) && !$checknot) || (isset($r->value) && ($r->value == $checknot))) {
                            $res = true;
                        }
                        break;

                    case 'objectiveMeasureKnown':
                        $r = $scormScoesTrackService->getScormScoesTrackElementInfoByAttempt($courseRegId,$modResId,$scoId,'objectivemeasurestatus',$attempt);
                        if ((!isset($r->value) && !$checknot) || (isset($r->value) && ($r->value == $checknot))) {
                            $res = true;
                        }
                        break;

                    case 'notobjectiveMeasureKnown':
                        $r = $scormScoesTrackService->getScormScoesTrackElementInfoByAttempt($courseRegId,$modResId,$scoId,'objectivemeasurestatus',$attempt);
                        if ((!isset($r->value) && !$checknot) || (isset($r->value) && ($r->value == $checknot))) {
                            $res = true;
                        }
                        break;

                    case 'completed':
                        $r = $scormScoesTrackService->getScormScoesTrackElementInfoByAttempt($courseRegId,$modResId,$scoId,'attemptcompletionstatus',$attempt);
                        if ((!isset($r->value) && !$checknot) || (isset($r->value) && ($r->value == $checknot))) {
                            $r = $scormScoesTrackService->getScormScoesTrackElementInfoByAttempt($courseRegId,$modResId,$scoId,'attemptprogressstatus',$attempt);
                            if ((!isset($r->value) && !$checknot) || (isset($r->value) && ($r->value == $checknot))) {
                                $res = true;
                            }
                        }
                        break;

                    case 'attempted':
                        $attempt = $scormScoesTrackService->getDistinctAttempts($courseRegId,$modResId,'x.start.time',$scoId);
                        if ($checknot && $attempt > 0) {
                            $res = true;
                        } else if (!$checknot && $attempt <= 0) {
                            $res = true;
                        }
                        break;

                    case 'attemptLimitExceeded':
                        $r = $scormScoesTrackService->getScormScoesTrackElementInfoByAttempt($courseRegId,$modResId,$scoId,'activityprogressstatus',$attempt);
                        if ((!isset($r->value) && !$checknot) || (isset($r->value) && ($r->value == $checknot))) {
                            $r = $scormScoesTrackService->getScormScoesTrackElementInfoByAttempt($courseRegId,$modResId,$scoId,'limitconditionattemptlimitcontrol',$attempt);
                            if ((!isset($r->value) && !$checknot) || (isset($r->value) && ($r->value == $checknot))) {
                                $r = $scormScoesTrackService->getDistinctAttempts($courseRegId,$modResId,null,$scoId);
                                $r2 = $scormScoesTrackService->getScormScoesTrackElementInfoByAttempt($courseRegId,$modResId,$scoId,'limitconditionattemptlimit',$attempt);
                                if ($r && $r2) {
                                    if ($checknot && ($r->value >= $r2->value)) {
                                        $res = true;
                                    } else if (!$checknot && ($r->value < $r2->value)) {
                                        $res = true;
                                    }
                                }
                            }
                        }
                        break;

                    case 'activityProgressKnown':
                        $r = $scormScoesTrackService->getScormScoesTrackElementInfoByAttempt($courseRegId,$modResId,$scoId,'activityprogressstatus',$attempt);
                        if ((!isset($r->value) && !$checknot) || (isset($r->value) && ($r->value == $checknot))) {
                            $r = $scormScoesTrackService->getScormScoesTrackElementInfoByAttempt($courseRegId,$modResId,$scoId,'attemptprogressstatus',$attempt);
                            if ((!isset($r->value) && !$checknot) || (isset($r->value) && ($r->value == $checknot))) {
                                $res = true;
                            }
                        }
                        break;
                }

                if ($conditioncombination == 'all' && !$res) {
                    break;
                } else if ($conditioncombination == 'or' && $res) {
                    break;
                }
            }
        }

        return $res;
    }

    private function scorm_seq_measure_rollup($courseRegId,$courseCompleteProcessId,$courseCompleteFinalId,$modResId,$scorm,$sco, $userId, $attempt = "1") {
        $scoId = $sco->kid;
        $totalmeasure = 0; // Check if there is something similar in the database.
        $valid = false; // Same as in the last line.
        $countedmeasures = 0; // Same too.
        $targetobjective = null;
        $scormSeqObjectiveService = new ScormSeqObjectiveService();
        $objectives = $scormSeqObjectiveService->GetScormSeqObjectiveByScormScoId($scoId);

        foreach ($objectives as $objective) {
            if ($objective->primary_obj == true) { // Objective contributes to rollup.
                $targetobjective = $objective;
                break;
            }

        }
        if ($targetobjective != null) {
            $childrenScos = $this->scorm_get_children($sco);
            if (!empty ($childrenScos)) {
                foreach ($childrenScos as $childrenSco) {
                    $scoId = $childrenSco->kid;
                    $child = $this->scorm_get_sco($scoId);
                    if (!isset($child->tracked) || ($child->tracked == 1)) {
                        $rolledupobjective = null;// We set the rolled up activity to undefined.
                        $objectives = $scormSeqObjectiveService->GetScormSeqObjectiveByScormScoId($scoId);
                        foreach ($objectives as $objective) {
                            if ($objective->primary_obj == true) {// Objective contributes to rollup I'm using primaryobj field, but not.
                                $rolledupobjective = $objective;
                                break;
                            }
                        }
                        if ($rolledupobjective != null) {
                            $child = $this->scorm_get_sco($child->kid);
                            $countedmeasures = $countedmeasures + ($child->measureweight);

                            if (!$this->scorm_seq_is($courseRegId,$modResId,'objectivemeasurestatus', $scoId, $userId, $attempt)) {
                                $scormScoesTrackService = new ScormScoesTrackService();
                                $normalizedmeasure = $scormScoesTrackService->getScormScoesTrackElementInfoByAttempt($courseRegId,$modResId,$scoId,'cmi.objectivenormalizedmeasure',$attempt);
                                $totalmeasure = $totalmeasure + (($normalizedmeasure->value) * ($child->measureweight));
                                $valid = true;
                            }
                        }
                    }
                }
            }

            if (!$valid) {
                $this->scorm_seq_set($courseRegId,$courseCompleteProcessId,$courseCompleteFinalId,$modResId,$scorm,'objectiveprogressstatus', $scoId, $userId, $attempt,false);
            } else {
                if ($countedmeasures > 0) {
                    $this->scorm_seq_set($courseRegId,$courseCompleteProcessId,$courseCompleteFinalId,$modResId,$scorm,'objectivemeasurestatus', $scoId, $userId, $attempt);
                    $val = $totalmeasure / $countedmeasures;
                    $this->scorm_seq_set($courseRegId,$courseCompleteProcessId,$courseCompleteFinalId,$modResId,$scorm,'objectivenormalizedmeasure', $scoId, $userId, $attempt,$val);
                } else {
                    $this->scorm_seq_set($courseRegId,$courseCompleteProcessId,$courseCompleteFinalId,$modResId,$scorm,'objectivemeasurestatus', $scoId, $userId, $attempt,false);
                }
            }
        }
    }

    private function scorm_seq_termination($courseRegId,$courseCompleteProcessId,$courseCompleteFinalId,$modResId,$scorm, $scoId, $userId, $seq, $attempt) {
        if (empty($seq->currentactivity)) {
            $seq->termination = false;
            $seq->exception = 'TB.2.3-1';
            return $seq;
        }

        $sco = $seq->currentactivity;

        if ((($seq->termination == 'exit') || ($seq->termination == 'abandon')) && !$seq->active) {
            $seq->termination = false;
            $seq->exception = 'TB.2.3-2';
            return $seq;
        }
        switch ($seq->termination) {
            case 'exit':
                $this->scorm_seq_end_attempt($courseRegId,$courseCompleteProcessId,$courseCompleteFinalId,$modResId,$scorm, $sco, $userId, $attempt);
                $seq = $this->scorm_seq_exit_action_rules($courseRegId,$courseCompleteProcessId,$courseCompleteFinalId,$modResId,$scorm, $seq, $userId, $attempt);
                do {
                    $exit = false;// I think this is false. Originally this was true.
                    $seq = $this->scorm_seq_post_cond_rules($seq);
                    if ($seq->termination == 'exitparent') {
                        if ($sco->parent != '/') {
                            $sco = $this->scorm_get_parent($sco);
                            $seq->currentactivity = $sco;
                            $seq->active = $this->scorm_seq_is($courseRegId,$modResId,'active', $scoId, $userId, $attempt);
                            $this->scorm_seq_end_attempt($courseRegId,$courseCompleteProcessId,$courseCompleteFinalId,$modResId,$scorm, $sco, $userId, $attempt);
                            $exit = true; // I think it's true. Originally this was false.
                        } else {
                            $seq->termination = false;
                            $seq->exception = 'TB.2.3-4';
                            return $seq;
                        }
                    }
                } while (($exit == false) && ($seq->termination == 'exit'));
                if ($seq->termination == 'exit') {
                    $seq->termination = true;
                    return $seq;
                }
            case 'exitall':
                if ($seq->active) {
                    $this->scorm_seq_end_attempt($courseRegId,$courseCompleteProcessId,$courseCompleteFinalId,$modResId,$scorm, $sco, $userId, $attempt);
                }
                // Terminate Descendent Attempts Process.

                if ($ancestors = $this->scorm_get_ancestors($sco)) {
                    foreach ($ancestors as $ancestor) {
                        $sco = $ancestor;
                        $this->scorm_seq_end_attempt($courseRegId,$courseCompleteProcessId,$courseCompleteFinalId,$modResId,$scorm, $sco, $userId, $attempt);
                        $seq->currentactivity = $ancestor;
                    }
                }

                $seq->active = $this->scorm_seq_is($courseRegId,$modResId,'active', $seq->currentactivity->kid, $userId);
                $seq->termination = true;
                $seq->sequencing = 'exit';
                break;
            case 'suspendall':
                if (($seq->active) || ($seq->suspended)) {
                    $this->scorm_seq_set($courseRegId,$courseCompleteProcessId,$courseCompleteFinalId,$modResId,'suspended', $scoId, $userId, $attempt);
                } else {
                    if ($sco->parent != '/') {
                        $parentsco = $this->scorm_get_parent($sco);
                        $this->scorm_seq_set($courseRegId,$courseCompleteProcessId,$courseCompleteFinalId,$modResId,'suspended', $parentsco->kid, $userId, $attempt);
                    } else {
                        $seq->termination = false;
                        $seq->exception = 'TB.2.3-3';
                    }
                }
                if ($ancestors = $this->scorm_get_ancestors($sco)) {
                    foreach ($ancestors as $ancestor) {
                        $this->scorm_seq_set($courseRegId,$courseCompleteProcessId,$courseCompleteFinalId,$modResId,'active', $ancestor->kid, $userId, $attempt, false);
                        $this->scorm_seq_set($courseRegId,$courseCompleteProcessId,$courseCompleteFinalId,$modResId,'suspended', $ancestor->kid, $userId, $attempt);
                        $seq->currentactivity = $ancestor;
                    }
                    $seq->termination = true;
                    $seq->sequencing = 'exit';
                } else {
                    $seq->termination = false;
                    $seq->exception = 'TB.2.3-5';
                }
                break;
            case 'abandon':
                $this->scorm_seq_set($courseRegId,$courseCompleteProcessId,$courseCompleteFinalId,$modResId,'active', $scoId, $userId, $attempt, false);
                $seq->active = null;
                $seq->termination = true;
                break;
            case 'abandonall':
                if ($ancestors = $this->scorm_get_ancestors($sco)) {
                    foreach ($ancestors as $ancestor) {
                        $this->scorm_seq_set($courseRegId,$courseCompleteProcessId,$courseCompleteFinalId,$modResId,'active', $ancestor->kid, $userId, $attempt, false);
                        $seq->currentactivity = $ancestor;
                    }
                    $seq->termination = true;
                    $seq->sequencing = 'exit';
                } else {
                    $seq->termination = false;
                    $seq->exception = 'TB.2.3-6';
                }
                break;
            default:
                $seq->termination = false;
                $seq->exception = 'TB.2.3-7';
                break;
        }
        return $seq;
    }

    private function scorm_seq_check_child($courseRegId,$modResId,$sco, $action, $userId,$attempt) {
        $scoId = $sco->kid;

        $included = false;
        $sco = $this->scorm_get_sco($scoId);
        $scormScoesTrackService = new ScormScoesTrackService();

        $r = $scormScoesTrackService->getScormScoesTrackElementInfoByAttempt($courseRegId,$modResId,$scoId,'activityattemptcount',$attempt);
        if ($action == 'satisfied' || $action == 'notsatisfied') {
            if (!$sco->rollupobjectivesatisfied) {
                $included = true;
                if (($action == 'satisfied' && $sco->requiredforsatisfied == 'ifnotsuspended') ||
                    ($action == 'notsatisfied' && $sco->requiredfornotsatisfied == 'ifnotsuspended')) {

                    if (!$this->scorm_seq_is($courseRegId,$modResId,'activityprogressstatus', $scoId, $userId, $attempt) ||
                        ((($r->value) > 0) && !$this->scorm_seq_is($courseRegId,$modResId,'suspended', $scoId, $userId, $attempt))) {
                        $included = false;
                    }

                } else {
                    if (($action == 'satisfied' && $sco->requiredforsatisfied == 'ifattempted') ||
                        ($action == 'notsatisfied' && $sco->requiredfornotsatisfied == 'ifattempted')) {
                        if (!$this->scorm_seq_is($courseRegId,$modResId,'activityprogressstatus', $scoId, $userId, $attempt) || (($r->value) == 0)) {
                            $included = false;
                        }
                    } else {
                        if (($action == 'satisfied' && $sco->requiredforsatisfied == 'ifnotskipped') ||
                            ($action == 'notsatisfied' && $sco->requiredfornotsatisfied == 'ifnotskipped')) {
                            $rulch = $this->scorm_seq_rules_check($scoId, 'skip');
                            if ($rulch != null) {
                                $included = false;
                            }
                        }
                    }
                }
            }
        }
        if ($action == 'completed' || $action == 'incomplete') {
            if (!$sco->rollupprogresscompletion) {
                $included = true;

                if (($action == 'completed' && $sco->requiredforcompleted == 'ifnotsuspended') ||
                    ($action == 'incomplete' && $sco->requiredforincomplete == 'ifnotsuspended')) {

                    if (!$this->scorm_seq_is($courseRegId,$modResId,'activityprogressstatus', $scoId, $userId, $attempt) ||
                        ((($r->value) > 0)&& !$this->scorm_seq_is($courseRegId,$modResId,'suspended', $scoId, $userId, $attempt))) {
                        $included = false;
                    }

                } else {

                    if (($action == 'completed' && $sco->requiredforcompleted == 'ifattempted') ||
                        ($action == 'incomplete' && $sco->requiredforincomplete == 'ifattempted')) {
                        if (!$this->scorm_seq_is($courseRegId,$modResId,'activityprogressstatus', $scoId, $userId, $attempt) || (($r->value) == 0)) {
                            $included = false;
                        }

                    } else {
                        if (($action == 'completed' && $sco->requiredforsatisfied == 'ifnotskipped') ||
                            ($action == 'incomplete' && $sco->requiredfornotsatisfied == 'ifnotskipped')) {
                            $rulch = $this->scorm_seq_rules_check($scoId, 'skip');
                            if ($rulch != null) {
                                $included = false;
                            }
                        }
                    }
                }
            }
        }
        return $included;
    }


    private function scorm_seq_rules_check($scoId, $action) {
        $act = null;

        $scormSeqRulecondsService = new ScormSeqRulecondsService();
        $rules = $scormSeqRulecondsService->GetScormSeqRulecondsByScormScoId($scoId, $action);
        if (!empty($rules)) {
            foreach ($rules as $rule) {
                if ($act = $this->scorm_seq_rule_check($scoId, $rule)) {
                    return $act;
                }
            }
        }
        return $act;
    }

    private function scorm_seq_rule_check($scoId, $rule) {
        $bag = Array();
        $cond = '';
        $scormSeqRulecondService = new ScormSeqRulecondService();
        $ruleconds = $scormSeqRulecondService->GetScormSeqRulecondByScormScoId($scoId,$rule->kid);
        foreach ($ruleconds as $rulecond) {
            if ($rulecond->operator == 'not') {
                if ($rulecond->cond != 'unknown' ) {
                    $rulecond->cond = 'not'.$rulecond->cond;
                }
            }
            $bag[] = $rulecond->cond;
        }
        if (empty($bag)) {
            $cond = 'unknown';
            return $cond;
        }

        if ($rule->condition_combination == 'all') {
            foreach ($bag as $con) {
                $cond = $cond.' and '.$con;
            }
        } else {
            foreach ($bag as $con) {
                $cond = $cond.' or '.$con;
            }
        }
        return $cond;
    }

    private function scorm_seq_exit_action_rules($courseRegId,$courseCompleteProcessId,$courseCompleteFinalId,$modResId,$scorm,$seq, $userId,$attempt) {
        $sco = $seq->currentactivity;
        $ancestors = $this->scorm_get_ancestors($sco);
        $exittarget = null;
        foreach (array_reverse($ancestors) as $ancestor) {
            $scoId = $ancestor->kid;
            if ($this->scorm_seq_rules_check($scoId, 'exit') != null) {
                $exittarget = $ancestor;
                break;
            }
        }
        if ($exittarget != null) {
            $commons = array_slice($ancestors, 0, $this->scorm_find_common_ancestor($ancestors, $exittarget));

            // Terminate Descendent Attempts Process.
            if ($commons) {
                foreach ($commons as $ancestor) {
                    $this->scorm_seq_end_attempt($courseRegId,$courseCompleteProcessId,$courseCompleteFinalId,$modResId,$scorm,$ancestor, $userId, $attempt);
                    $seq->currentactivity = $ancestor;
                }
            }
        }
        return $seq;
    }

    private function scorm_seq_post_cond_rules($seq) {
        $sco = $seq->currentactivity;
        $scoId = $sco->kid;
        if (!$seq->suspended) {
            if ($action = $this->scorm_seq_rules_check($scoId, 'post') != null) {
                switch($action) {
                    case 'retry':
                    case 'continue':
                    case 'previous':
                        $seq->sequencing = $action;
                        break;
                    case 'exitparent':
                    case 'exitall':
                        $seq->termination = $action;
                        break;
                    case 'retryall':
                        $seq->termination = 'exitall';
                        $seq->sequencing = 'retry';
                        break;
                }
            }
        }
        return $seq;
    }

    private function scorm_seq_sequencing($courseRegId,$courseCompleteProcessId,$courseCompleteFinalId,$modResId,$scorm,$scoId, $userId, $seq, $attempt) {
        switch ($seq->sequencing) {
            case 'start':
                // We'll see the parameters we have to send, this should update delivery and end.
                $seq = $this->scorm_seq_start_sequencing($courseRegId,$modResId,$scoId, $userId, $seq, $attempt);
                $seq->sequencing = true;
                break;

            case 'resumeall':
                // We'll see the parameters we have to send, this should update delivery and end.
                $seq = $this->scorm_seq_resume_all_sequencing($courseRegId,$modResId,$scoId, $userId, $seq, $attempt);
                $seq->sequencing = true;
                break;

            case 'exit':
                // We'll see the parameters we have to send, this should update delivery and end.
                $seq = $this->scorm_seq_exit_sequencing($courseRegId,$modResId,$scoId, $userId, $seq, $attempt);
                $seq->sequencing = true;
                break;

            case 'retry':
                // We'll see the parameters we have to send, this should update delivery and end.
                $seq = $this->scorm_seq_retry_sequencing($courseRegId,$modResId,$scoId, $userId, $seq, $attempt);
                $seq->sequencing = true;
                break;

            case 'previous':
                // We'll see the parameters we have to send, this should update delivery and end.
                $seq = $this->scorm_seq_previous_sequencing($courseRegId,$modResId,$scoId, $userId, $seq, $attempt);
                $seq->sequencing = true;
                break;

            case 'choice':
                // We'll see the parameters we have to send, this should update delivery and end.
                $seq = $this->scorm_seq_choice_sequencing($courseRegId,$courseCompleteProcessId,$courseCompleteFinalId,$modResId,$scorm,$scoId, $userId, $seq, $attempt);
                $seq->sequencing = true;
                break;
        }

        if ($seq->exception != null) {
            $seq->sequencing = false;
            return $seq;
        }

        $seq->sequencing = true;
        return $seq;
    }

    private function scorm_seq_choice_sequencing($courseRegId,$courseCompleteProcessId,$courseCompleteFinalId,$modResId,$scorm,$scoId, $userId, $seq, $attempt) {
        $sco =  $this->scorm_get_sco($scoId);
        $avchildren = Array ();
        $comancestor = null;
        $traverse = null;

        if (empty($sco)) {
            $seq->delivery = null;
            $seq->exception = 'SB.2.9-1';
            return $seq;
        }

        $ancestors = $this->scorm_get_ancestors($sco);
        $arrpath = array_reverse($ancestors);
        array_push ($arrpath, $sco); // Path from the root to the target.

        foreach ($arrpath as $activity) {
            if ($activity->parent != '/') {
                $avchildren = $this->scorm_get_available_children($this->scorm_get_parent($activity));
                $position = array_search($avchildren, $activity);
                if ($position !== false) {
                    $seq->delivery = null;
                    $seq->exception = 'SB.2.9-2';
                    return $seq;
                }
            }

            if ($this->scorm_seq_rules_check($activity, 'hidefromchoice' != null)) {
                $seq->delivery = null;
                $seq->exception = 'SB.2.9-3';
                return $seq;
            }
        }

        if ($sco->parent != '/') {
            $parent = $this->scorm_get_parent($sco);
            if ( isset($parent->choice) && ($parent->choice == false)) {
                $seq->delivery = null;
                $seq->exception = 'SB.2.9-4';
                return $seq;
            }
        }

        if ($seq->currentactivity != null) {
            $commonpos = $this->scorm_find_common_ancestor($ancestors, $seq->currentactivity);
            $comancestor = $arrpath [$commonpos];
        } else {
            $comancestor = $arrpath [0];
        }

        if ($seq->currentactivity === $sco) {
            return $seq;
        }

        $sib = $this->scorm_get_siblings($seq->currentactivity);
        $pos = array_search($sib, $sco);

        if ($pos !== false) {
            $siblings = array_slice($sib, 0, $pos - 1);
            if (empty($siblings)) {
                $seq->delivery = null;
                $seq->exception = 'SB.2.9-5';
                return $seq;
            }

            $children = $this->scorm_get_children($this->scorm_get_parent($sco));
            $pos1 = array_search($children, $sco);
            $pos2 = array_search($seq->currentactivity, $sco);
            if ($pos1 > $pos2) {
                $traverse = 'forward';
            } else {
                $traverse = 'backward';
            }

            foreach ($siblings as $sibling) {
                $seq = $this->scorm_seq_choice_activity_traversal($sibling, $userId, $seq, $traverse);
                if (!$seq->reachable) {
                    $seq->delivery = null;
                    return $seq;
                }
            }
            return $seq;
        }

        if ($seq->currentactivity == null || $seq->currentactivity == $comancestor) {
            $commonpos = $this->scorm_find_common_ancestor($ancestors, $seq->currentactivity);
            // Path from the common ancestor to the target activity.
            $comtarget = array_slice($ancestors, 1, $commonpos - 1);
            $comtarget = array_reverse($comtarget);

            if (empty($comtarget)) {
                $seq->delivery = null;
                $seq->exception = 'SB.2.9-5';
                return $seq;
            }
            foreach ($comtarget as $act) {
                $scoId = $act->kid;
                $seq = $this->scorm_seq_choice_activity_traversal($scoId, $userId, $seq, 'forward');
                if (!$seq->reachable) {
                    $seq->delivery = null;
                    return $seq;
                }
                $act = $this->scorm_get_sco($scoId);
                if ($this->scorm_seq_is($courseRegId,$modResId,'active', $scoId, $userId,$attempt)
                    && ($scoId != $comancestor->kid && $act->preventactivation)) {
                    $seq->delivery = null;
                    $seq->exception = 'SB.2.9-6';
                    return $seq;
                }
            }
            return $seq;
        }

        if ($comancestor->kid == $sco->kid) {

            $ancestorscurrent = $this->scorm_get_ancestors($seq->currentactivity);
            $possco = array_search($ancestorscurrent, $sco);
            // Path from the current activity to the target.
            $curtarget = array_slice($ancestorscurrent, 0, $possco);

            if (empty($curtarget)) {
                $seq->delivery = null;
                $seq->exception = 'SB.2.9-5';
                return $seq;
            }
            $i = 0;
            foreach ($curtarget as $activ) {
                $i++;
                if ($i != count($curtarget)) {
                    if (isset($activ->choiceexit) && ($activ->choiceexit == false)) {
                        $seq->delivery = null;
                        $seq->exception = 'SB.2.9-7';
                        return $seq;
                    }
                }
            }
            return $seq;
        }

        if (array_search($ancestors, $comancestor) !== false) {
            $ancestorscurrent = $this->scorm_get_ancestors($seq->currentactivity);
            $commonpos = $this->scorm_find_common_ancestor($ancestors, $sco);
            $curcommon = array_slice($ancestorscurrent, 0, $commonpos - 1);
            if (empty($curcommon)) {
                $seq->delivery = null;
                $seq->exception = 'SB.2.9-5';
                return $seq;
            }

            $constrained = null;
            foreach ($curcommon as $act) {
                $sco = $this->scorm_get_sco($act->kid);
                if (isset($sco->choiceexit) && ($sco->choiceexit == false)) {
                    $seq->delivery = null;
                    $seq->exception = 'SB.2.9-7';
                    return $seq;
                }
                if ($constrained == null) {
                    if ($sco->constrainchoice == true) {
                        $constrained = $sco;
                    }
                }
            }
            if ($constrained != null) {
                $fwdir = $this->scorm_get_preorder($constrained);

                if (array_search($fwdir, $sco) !== false) {
                    $traverse = 'forward';
                } else {
                    $traverse = 'backward';
                }
                $seq = $this->scorm_seq_choice_flow($constrained, $traverse, $seq);
                $actconsider = $seq->identifiedactivity;
                //$avdescendents = Array();
                $avdescendents = $this->scorm_get_available_children($actconsider);
                if (array_search ($avdescendents, $sco) !== false && $sco->kid != $actconsider->kid && $constrained->kid != $sco->kid) {
                    $seq->delivery = null;
                    $seq->exception = 'SB.2.9-8';
                    return $seq;
                }
                // CONTINUE 11.5.5 !
            }

            $commonpos = $this->scorm_find_common_ancestor($ancestors, $seq->currentactivity);
            $comtarget = array_slice($ancestors, 1, $commonpos - 1);// Path from the common ancestor to the target activity.
            $comtarget = array_reverse($comtarget);

            if (empty($comtarget)) {
                $seq->delivery = null;
                $seq->exception = 'SB.2.9-5';
                return $seq;
            }

            $fwdir = $this->scorm_get_preorder($seq->currentactivity);

            if (array_search($fwdir, $sco) !== false) {
                foreach ($comtarget as $act) {
                    $scoId = $act->kid;
                    $seq = $this->scorm_seq_choice_activity_traversal($scoId, $userId, $seq, 'forward');
                    if (!$seq->reachable) {
                        $seq->delivery = null;
                        return $seq;
                    }
                    $act = $this->scorm_get_sco($scoId);
                    if ($this->scorm_seq_is($courseRegId,$modResId,'active', $scoId, $userId,$attempt) && ($scoId != $comancestor->kid &&
                            ($act->preventactivation == true))) {
                        $seq->delivery = null;
                        $seq->exception = 'SB.2.9-6';
                        return $seq;
                    }
                }

            } else {
                foreach ($comtarget as $act) {
                    $scoId = $act->kid;
                    $act = $this->scorm_get_sco($scoId);
                    if ($this->scorm_seq_is($courseRegId,$modResId,'active', $scoId, $userId,$attempt) && ($scoId != $comancestor->kid &&
                            ($act->preventactivation == true))) {
                        $seq->delivery = null;
                        $seq->exception = 'SB.2.9-6';
                        return $seq;
                    }
                }
            }
            return $seq;
        }

        if ($this->scorm_is_leaf($sco)) {
            $seq->delivery = $sco;
            $seq->exception = 'SB.2.9-6';
            return $seq;
        }

        $seq = $this->scorm_seq_flow($courseRegId,$modResId,$sco, 'forward', $seq, true, $userId,$attempt);
        if ($seq->deliverable == false) {
            $this->scorm_terminate_descendent_attempts($courseRegId,$courseCompleteProcessId,$courseCompleteFinalId,$modResId,$scorm,$comancestor, $userId, $seq,$attempt);
            $this->scorm_seq_end_attempt($courseRegId,$courseCompleteProcessId,$courseCompleteFinalId,$modResId,$scorm, $comancestor, $userId, $attempt);
            $seq->currentactivity = $sco;
            $seq->delivery = null;
            $seq->exception = 'SB.2.9-9';
            return $seq;
        } else {
            return $seq;
        }

    }

    private function scorm_terminate_descendent_attempts($courseRegId,$courseCompleteProcessId,$courseCompleteFinalId,$modResId,$scorm, $activity, $userId, $seq,$attempt) {
        $ancestors = $this->scorm_get_ancestors($seq->currentactivity);
        $commonpos = $this->scorm_find_common_ancestor($ancestors, $activity);
        if ($commonpos !== false) {
            if ($activitypath = array_slice($ancestors, 1, $commonpos - 2)) {
                if (!empty($activitypath)) {
                    foreach ($activitypath as $sco) {
                        $this->scorm_seq_end_attempt($courseRegId,$courseCompleteProcessId,$courseCompleteFinalId,$modResId,$scorm, $sco, $userId,  $attempt);
                    }
                }
            }
        }
    }

    private function scorm_seq_choice_activity_traversal($scoId, $userId, $seq, $direction) {
        if ($direction == 'forward') {
            $act = $this->scorm_seq_rules_check($scoId, 'stopforwardtraversal');

            if ($act != null) {
                $seq->reachable = false;
                $seq->exception = 'SB.2.4-1';
                return $seq;
            }
            $seq->reachable = false;
            return $seq;
        }

        if ($direction == 'backward') {
            $parentsco = $this->scorm_get_parent($scoId);
            if ($parentsco != null) {
                if (isset($parentsco->forwardonly) && ($parentsco->forwardonly == true)) {
                    $seq->reachable = false;
                    $seq->exception = 'SB.2.4-2';
                    return $seq;
                } else {
                    $seq->reachable = false;
                    $seq->exception = 'SB.2.4-3';
                    return $seq;
                }
            }
        }
        $seq->reachable = true;
        return $seq;
    }

    private function scorm_seq_start_sequencing($courseRegId,$modResId,$scoId, $userId, $seq, $attempt) {
        if (!empty($seq->currentactivity)) {
            $seq->delivery = null;
            $seq->exception = 'SB.2.5-1';
            return $seq;
        }

        $scormScoesService = new ScormScoesService();
        $sco = $scormScoesService->getScormScoesByScormId($scoId);
        if (($sco->parent == '/') && $this->scorm_is_leaf($sco)) { // If the activity is the root and is leaf.
            $seq->delivery = $sco;
        } else {
            $ancestors = $this->scorm_get_ancestors($sco);
            $ancestorsroot = array_reverse($ancestors);
            $res = $this->scorm_seq_flow($courseRegId,$modResId,$ancestorsroot[0], 'forward', $seq, true, $userId,$attempt);
            if ($res) {
                return $res;
            }
        }
    }

    private function scorm_seq_resume_all_sequencing($courseRegId,$modResId,$scoId, $userId, $seq, $attempt) {
        $scormScoesTrackService = new ScormScoesTrackService();
        $scormScoesService = new ScormScoesService();

        if (!empty($seq->currentactivity)) {
            $seq->delivery = null;
            $seq->exception = 'SB.2.6-1';
            return $seq;
        }
        $track = $scormScoesTrackService->getScormScoesTrackElementInfoByAttempt($courseRegId,$modResId,$scoId, 'suspendedactivity', $attempt);
        if (!$track) {
            $seq->delivery = null;
            $seq->exception = 'SB.2.6-2';
            return $seq;
        }

        $sco = $scormScoesService->getScormScoesByScormId($scoId);
        // We assign the sco to the delivery.
        $seq->delivery = $sco;
    }

    private function scorm_seq_continue_sequencing($courseRegId,$modResId,$scoId, $userId, $seq,$attempt) {
        if (empty($seq->currentactivity)) {
            $seq->delivery = null;
            $seq->exception = 'SB.2.7-1';
            return $seq;
        }
        $currentact = $seq->currentactivity;
        if ($currentact->parent != '/') {
            // If the activity is the root and is leaf.
            $parent = $this->scorm_get_parent($currentact);

            if (!isset($parent->flow) || ($parent->flow == false)) {
                $seq->delivery = null;
                $seq->exception = 'SB.2.7-2';
                return $seq;
            }
            $res = $this->scorm_seq_flow($courseRegId,$modResId,$currentact, 'forward', $seq, false, $userId,$attempt);
            if ($res) {
                return $res;
            }
        }
    }

    private function scorm_seq_previous_sequencing($courseRegId,$modResId,$scoId, $userId, $seq, $attempt) {
        if (empty($seq->currentactivity)) {
            $seq->delivery = null;
            $seq->exception = 'SB.2.8-1';
            return $seq;
        }

        $currentact = $seq->currentactivity;
        if ($currentact->parent != '/') { // If the activity is the root and is leaf.
            $parent = $this->scorm_get_parent($currentact);
            if (!isset($parent->flow) || ($parent->flow == false)) {
                $seq->delivery = null;
                $seq->exception = 'SB.2.8-2';
                return $seq;
            }

            $res = $this->scorm_seq_flow($courseRegId,$modResId,$currentact, 'backward', $seq, false, $userId, $attempt);
            if ($res) {
                return $res;
            }
        }
    }

    private function scorm_seq_retry_sequencing($courseRegId,$modResId,$scoId, $userId, $seq, $attempt) {
        if (empty($seq->currentactivity)) {
            $seq->delivery = null;
            $seq->exception = 'SB.2.10-1';
            return $seq;
        }
        if ($seq->active || $seq->suspended) {
            $seq->delivery = null;
            $seq->exception = 'SB.2.10-2';
            return $seq;
        }

        if (!$this->scorm_is_leaf($seq->currentactivity)) {
            $res = $this->scorm_seq_flow($courseRegId,$modResId,$seq->currentactivity, 'forward', $seq, true, $userId, $attempt);
            if ($res != null) {
                return $res;
            } else {
                // Return deliver.
                $seq->delivery = null;
                $seq->exception = 'SB.2.10-3';
                return $seq;
            }
        } else {
            $seq->delivery = $seq->currentactivity;
            return $seq;
        }

    }

    private function scorm_seq_exit_sequencing($courseRegId,$modResId,$scoId, $userId, $seq, $attempt) {
        if (empty($seq->currentactivity)) {
            $seq->delivery = null;
            $seq->exception = 'SB.2.11-1';
            return $seq;
        }

        if ($seq->active) {
            $seq->endsession = false;
            $seq->exception = 'SB.2.11-2';
            return $seq;
        }
        $currentact = $seq->currentactivity;
        if ($currentact->parent == '/') {
            $seq->endsession = true;
            return $seq;
        }

        $seq->endsession = false;
        return $seq;
    }

    private function scorm_seq_flow_tree_traversal($activity, $direction, $childrenflag, $prevdirection, $seq, $userid, $skip = false) {
        $revdirection = false;
        $parent = $this->scorm_get_parent($activity);
        if (!empty($parent)) {
            $children = $this->scorm_get_available_children($parent);
        } else {
            $children = array();
        }
        $childrensize = count($children);

        if (($prevdirection != null && $prevdirection == 'backward') && ($children[$childrensize - 1]->kid == $activity->kid)) {
            $direction = 'backward';
            $activity = $children[0];
            $revdirection = true;
        }

        if ($direction == 'forward') {
            $ancestors = $this->scorm_get_ancestors($activity);
            $ancestorsroot = array_reverse($ancestors);
            $preorder = array();
            $preorder = $this->scorm_get_preorder($preorder, $ancestorsroot[0]);
            $preordersize = count($preorder);
            if (($activity->kid == $preorder[$preordersize - 1]->kid) || (($activity->parent == '/') && !($childrenflag))) {
                $seq->endsession = true;
                $seq->nextactivity = null;
                return $seq;
            }
            if ($this->scorm_is_leaf ($activity) || !$childrenflag) {
                if ($children[$childrensize - 1]->kid == $activity->kid) {
                    $seq = $this->scorm_seq_flow_tree_traversal ($parent, $direction, false, null, $seq, $userid);
                    if ($seq->nextactivity->launch == null) {
                        $seq = $this->scorm_seq_flow_tree_traversal ($seq->nextactivity, $direction, true, null, $seq, $userid);
                    }
                    return $seq;
                } else {
                    $position = 0;
                    foreach ($children as $sco) {
                        if ($sco->kid == $activity->kid) {
                            break;
                        }
                        $position++;
                    }
                    if ($position != ($childrensize - 1)) {
                        $seq->nextactivity = $children[$position + 1];
                        $seq->traversaldir = $direction;
                        return $seq;
                    } else {
                        $siblings = $this->scorm_get_siblings($activity);
                        $children = $this->scorm_get_children($siblings[0]);
                        $seq->nextactivity = $children[0];
                        return $seq;
                    }
                }
            } else {
                $children = $this->scorm_get_available_children($activity);
                if (!empty($children)) {
                    $seq->traversaldir = $direction;
                    $seq->nextactivity = $children[0];
                    return $seq;
                } else {
                    $seq->traversaldir = null;
                    $seq->nextactivity = null;
                    $seq->exception = 'SB.2.1-2';
                    return $seq;
                }
            }
        } else if ($direction == 'backward') {
            if ($activity->parent == '/') {
                $seq->traversaldir = null;
                $seq->nextactivity = null;
                $seq->exception = 'SB.2.1-3';
                return $seq;
            }
            if ($this->scorm_is_leaf($activity) || !$childrenflag) {
                if (!$revdirection) {
                    if (isset($parent->forwardonly) && ($parent->forwardonly == true && !$skip)) {
                        $seq->traversaldir = null;
                        $seq->nextactivity = null;
                        $seq->exception = 'SB.2.1-4';
                        return $seq;
                    }
                }
                if ($children[0]->kid == $activity->kid) {
                    $seq = $this->scorm_seq_flow_tree_traversal($parent, 'backward', false, null, $seq, $userid);
                    return $seq;
                } else {
                    $ancestors = $this->scorm_get_ancestors($activity);
                    $ancestorsroot = array_reverse($ancestors);
                    $preorder = array();
                    $preorder = $this->scorm_get_preorder($preorder, $ancestorsroot[0]);
                    $position = 0;
                    foreach ($preorder as $sco) {
                        if ($sco->kid == $activity->kid) {
                            break;
                        }
                        $position++;
                    }
                    if (isset($preorder[$position])) {
                        $seq->nextactivity = $preorder[$position - 1];
                        $seq->traversaldir = $direction;
                    }
                    return $seq;
                }
            } else {
                $children = $this->scorm_get_available_children($activity);
                if (!empty($children)) {
                    if (isset($parent->flow) && ($parent->flow == true)) {
                        $seq->traversaldir = 'forward';
                        $seq->nextactivity = $children[0];
                        return $seq;
                    } else {
                        $seq->traversaldir = 'backward';
                        $seq->nextactivity = $children[count($children) - 1];
                        return $seq;
                    }
                } else {
                    $seq->traversaldir = null;
                    $seq->nextactivity = null;
                    $seq->exception = 'SB.2.1-2';
                    return $seq;
                }
            }
        }
    }

    private function scorm_seq_flow($courseRegId,$modResId,$activity, $direction, $seq, $childrenflag, $userId,$attempt) {
        // TODO: $PREVDIRECTION NOT DEFINED YET.
        $prevdirection = null;
        $seq = $this->scorm_seq_flow_tree_traversal($activity, $direction, $childrenflag, $prevdirection, $seq, $userId);
        if ($seq->nextactivity == null) {
            $seq->nextactivity = $activity;
            $seq->deliverable = false;
            return $seq;
        } else {
            $activity = $seq->nextactivity;
            $seq = $this->scorm_seq_flow_activity_traversal($courseRegId,$modResId,$activity, $direction, $childrenflag, null, $seq, $userId,$attempt);
            return $seq;
        }
    }

    // Returns the next activity on the tree, traversal direction, control returned to the LTS, (may) exception.
    private function scorm_seq_flow_activity_traversal($courseRegId,$modResId,$activity, $direction, $childrenflag, $prevdirection, $seq, $userId,$attempt) {
        $parent = $this->scorm_get_parent($activity);
        if (!isset($parent->flow) || ($parent->flow == false)) {
            $seq->deliverable = false;
            $seq->exception = 'SB.2.2-1';
            $seq->nextactivity = $activity;
            return $seq;
        }

        $rulecheck = $this->scorm_seq_rules_check($activity, 'skip');
        if ($rulecheck != null) {
            $skip = $this->scorm_evaluate_condition($courseRegId,$modResId,$rulecheck, $activity, $userId,$attempt);
            if ($skip) {
                $seq = $this->scorm_seq_flow_tree_traversal($activity, $direction, false, $prevdirection, $seq, $userId, $skip);
                $seq = $this->scorm_seq_flow_activity_traversal($courseRegId,$modResId,$seq->nextactivity, $direction,
                    $childrenflag, $prevdirection, $seq, $userId,$attempt);
            } else if (!empty($seq->identifiedactivity)) {
                $seq->nextactivity = $activity;
            }
            return $seq;
        }

        $ch = $this->scorm_check_activity($courseRegId,$modResId,$activity, $userId,$attempt);
        if ($ch) {
            $seq->deliverable = false;
            $seq->exception = 'SB.2.2-2';
            $seq->nextactivity = $activity;
            return $seq;
        }

        if (!$this->scorm_is_leaf($activity)) {
            $seq = $this->scorm_seq_flow_tree_traversal ($activity, $direction, true, null, $seq, $userId);
            if ($seq->identifiedactivity == null) {
                $seq->deliverable = false;
                $seq->nextactivity = $activity;
                return $seq;
            } else {
                if ($direction == 'backward' && $seq->traversaldir == 'forward') {
                    $seq = $this->scorm_seq_flow_activity_traversal($courseRegId,$modResId,$seq->identifiedactivity,
                        'forward', $childrenflag, 'backward', $seq, $userId, $attempt);
                } else {
                    $seq = $this->scorm_seq_flow_activity_traversal($courseRegId,$modResId, $seq->identifiedactivity,
                        $direction, $childrenflag, null, $seq, $userId, $attempt);
                }
                return $seq;
            }

        }

        $seq->deliverable = true;
        $seq->nextactivity = $activity;
        $seq->exception = null;
        return $seq;

    }

    private function scorm_check_activity($courseRegId,$modResId,$activity, $userId,$attempt) {
        $act = $this->scorm_seq_rules_check($activity, 'disabled');
        if ($act != null) {
            return true;
        }
        if ($this->scorm_limit_cond_check($courseRegId,$modResId,$activity, $userId,$attempt)) {
            return true;
        }
        return false;
    }

    private function scorm_limit_cond_check($courseRegId,$modResId, $sco, $userId, $attempt = "1") {
        $scoId = $sco->kid;
        $scormScoesTrackService = new ScormScoesTrackService();
        if (isset($sco->tracked) && ($sco->tracked == 0)) {
            return false;
        }

        if ($this->scorm_seq_is($courseRegId,$modResId,'active', $scoId, $userId, $attempt)
            || $this->scorm_seq_is($courseRegId,$modResId,'suspended', $scoId, $userId, $attempt)) {
            return false;
        }

        if (!isset($sco->limitcontrol) || ($sco->limitcontrol == 1)) {
            $r = $scormScoesTrackService->getScormScoesTrackElementInfoByAttempt($courseRegId,$modResId,$scoId,'activityattemptcount',$attempt);
            if ($this->scorm_seq_is($courseRegId,$modResId,'activityprogressstatus', $scoId, $userId, $attempt)
                && ($r->value >= $sco->limitattempt)) {
                return true;
            }
        }

        if (!isset($sco->limitabsdurcontrol) || ($sco->limitabsdurcontrol == 1)) {
            $r = $scormScoesTrackService->getScormScoesTrackElementInfoByAttempt($courseRegId,$modResId,$scoId,'activityabsoluteduration',$attempt);
            if ($this->scorm_seq_is($courseRegId,$modResId,'activityprogressstatus', $scoId, $userId, $attempt)
                && ($r->value >= $sco->limitabsduration)) {
                return true;
            }
        }

        if (!isset($sco->limitexpdurcontrol) || ($sco->limitexpdurcontrol == 1)) {
            $r = $scormScoesTrackService->getScormScoesTrackElementInfoByAttempt($courseRegId,$modResId,$scoId,'activityexperiencedduration',$attempt);
            if ($this->scorm_seq_is($courseRegId,$modResId,'activityprogressstatus', $scoId, $userId, $attempt)
                && ($r->value >= $sco->limitexpduration)) {
                return true;
            }
        }

        if (!isset($sco->limitattabsdurcontrol) || ($sco->limitattabsdurcontrol == 1)) {
            $r = $scormScoesTrackService->getScormScoesTrackElementInfoByAttempt($courseRegId,$modResId,$scoId,'attemptabsoluteduration',$attempt);
            if ($this->scorm_seq_is($courseRegId,$modResId,'activityprogressstatus', $scoId, $userId, $attempt)
                && ($r->value >= $sco->limitattabsduration)) {
                return true;
            }
        }

        if (!isset($sco->limitattexpdurcontrol) || ($sco->limitattexpdurcontrol == 1)) {
            $r = $scormScoesTrackService->getScormScoesTrackElementInfoByAttempt($courseRegId,$modResId,$scoId,'attemptexperiencedduration',$attempt);
            if ($this->scorm_seq_is($courseRegId,$modResId,'activityprogressstatus', $scoId, $userId, $attempt)
                && ($r->value >= $sco->limitattexpduration)) {
                return true;
            }
        }

        if (!isset($sco->limitbegincontrol) || ($sco->limitbegincontrol == 1)) {
            $r = $scormScoesTrackService->getScormScoesTrackElementInfoByAttempt($courseRegId,$modResId,$scoId,'begintime',$attempt);
            if (isset($sco->limitbegintime) && time() >= $sco->limitbegintime) {
                return true;
            }
        }

        if (!isset($sco->limitbegincontrol) || ($sco->limitbegincontrol == 1)) {
            if (isset($sco->limitbegintime) && time() < $sco->limitbegintime) {
                return true;
            }
        }

        if (!isset($sco->limitendcontrol) || ($sco->limitendcontrol == 1)) {
            if (isset($sco->limitendtime) && time() > $sco->limitendtime) {
                return true;
            }
        }
        return false;
    }

    private function scorm_seq_choice_flow($constrained, $traverse, $seq) {
        $seq = $this->scorm_seq_choice_flow_tree($constrained, $traverse, $seq);
        if ($seq->identifiedactivity == null) {
            $seq->identifiedactivity = $constrained;
            return $seq;
        } else {
            return $seq;
        }
    }

    private function scorm_seq_choice_flow_tree($constrained, $traverse, $seq) {
        $islast = false;
        $parent = $this->scorm_get_parent($constrained);
        if ($traverse == 'forward') {
            $preorder = $this->scorm_get_preorder($constrained);
            if (count($preorder) == 0 || (count($preorder) == 0 && $preorder[0]->kid = $constrained->kid)) {
                // TODO: undefined.
                $islast = true; // The function is the last activity available.
            }
            if ($constrained->parent == '/' || $islast) {
                $seq->nextactivity = null;
                return $seq;
            }
            $avchildren = $this->scorm_get_available_children($parent); // Available children.
            if ($avchildren[count($avchildren) - 1]->kid == $constrained->kid) {
                $seq = $this->scorm_seq_choice_flow_tree($parent, 'forward', $seq);
                return $seq;
            } else {
                $i = 0;
                while ($i < count($avchildren)) {
                    if ($avchildren [$i]->kid == $constrained->kid) {
                        $seq->nextactivity = $avchildren [$i + 1];
                        return $seq;
                    } else {
                        $i++;
                    }
                }
            }
        }

        if ($traverse == 'backward') {
            if ($constrained->parent == '/' ) {
                $seq->nextactivity = null;
                return $seq;
            }

            $avchildren = $this->scorm_get_available_children($parent); // Available children.
            if ($avchildren [0]->kid == $constrained->kid) {
                $seq = $this->scorm_seq_choice_flow_tree($parent, 'backward', $seq);
                return $seq;
            } else {
                $i = count($avchildren) - 1;
                while ($i >= 0) {
                    if ($avchildren [$i]->kid == $constrained->kid) {
                        $seq->nextactivity = $avchildren [$i - 1];
                        return $seq;
                    } else {
                        $i--;
                    }
                }
            }
        }
    }
}