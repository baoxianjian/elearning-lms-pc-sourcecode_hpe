<?php

namespace frontend\controllers;

use common\models\framework\FwCompanySetting;
use common\models\framework\FwUser;
use common\models\learning\LnScormScoesTrack;
use common\models\learning\LnScormScoesTrackMongo;
use common\models\treemanager\FwTreeType;
use common\services\interfaces\service\RightInterface;
use common\services\learning\ExaminationQuestionService;
use common\services\learning\ExaminationService;
use common\services\learning\ResourceService;
use common\services\framework\WechatService;
use common\base\BaseActiveRecord;
use common\helpers\TBaseHelper;
use frontend\base\BaseFrontController;
use stdClass;
use Yii;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\db;
use yii\mongodb\Query;

/**
 * Site controller
 */
class DemoController extends BaseFrontController
{
    public $layout = 'demo';
    public $enableCsrfValidation = false;

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['logout', 'signup'],
                'rules' => [
                    [
                        'actions' => ['signup'],
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    public function actionMongo()
    {
        $condition = "kid=:kid";
        $params = [':kid' => "1"];
        LnScormScoesTrack::deleteAll($condition, $params);
//        $temp = Yii::$app->mongodb;
//        $collection = $temp->getCollection(LnScormScoesTrack::calculateTableName());

//        $query = new Query;
//// compose the query
//        $query
//            ->from(LnScormScoesTrack::calculateTableName())
//            ->limit(10);
//// execute the query
//        $rows = $query->all();

//        $result = LnScormScoesTrackMongo::find()
//            ->andFilterWhere(['>','created_at',1])
//            ->all();
//
//        $mo = new LnScormScoesTrackMongo();
//        LnScormScoesTrackMongo::deleteAll(['_id'=>"573dc0e39d75d033030041a7"]);
////        $mo->elementlist=["elementlist"=>"3"];
//        $array = ["favorite book" => "War and Peace"];
//        $mo->unsetAll(['a','b'],['_id'=>'57405db69d75d091040041a1']);
//        $mo->updateAll(['elementlist.test'=>'test2'],['_id'=>'57405db69d75d091040041a1'],[],true);
//        $mo->updateAll(['$push' => array('comments' => array('test'=>"1"))],['_id'=>'573dc0e39d75d033030041a7'],['upsert'=>true]);
//        $mo->updateAll(['$push' => array('comments' => array('test'=>"1"))],['_id'=>'573dc0e39d75d033030041a1']);

//        $collection->update(array('_id' => new MongoId($id)),
//            array('$push' =>array('comments' => $varcomment)),
//            array('upsert'=>true));


//        $mo->physicalDelete();
//        $mo->scorm_id = "test";
//        $mo->save();
//        Yii::t('common','msm', ['param1'=>'xxx','param2'=>'yyy'])
//        $model = new LnScormScoesTrackMongo();
//        $model->scorm_sco_id = "1";
//        $model->user_id="2";
//        $model->scorm_id="3";
//        $model->attempt="2";
//        $model->elementlist=["element"=>"2"];
//        $model->save();
//        $collection->insert(['name' => 'John Smith', 'status' => 1]);

//        $models = [];
//        $model = new LnScormScoesTrackMongo();
//        $model->course_reg_id = "1";
//        $model->scorm_sco_id = "1";
//        $model->user_id="3";
//        $model->scorm_id="3";
//        $model->mod_res_id="2";
//        $model->attempt=2;
//        $model->elementlist=["element"=>"2"];
//        array_push($models, $model);
//        array_push($models, $model);
//        array_push($models, $model);
//
//        $result = LnScormScoesTrackMongo::batchInsert($models);

//        $model = LnScormScoesTrackMongo::findOne('57405db69d75d091040041a7');
//
//        $model->attempt =2;
//        $model->save();

//        $model = new LnScormScoesTrackMongo();
//        $lastattempt = $model->find(true)//track表不能可能逻辑删除
//            ->andFilterWhere(['=', 'course_reg_id', "1"])
//            ->andFilterWhere(['=', 'mod_res_id', "2"])
//            ->andFilterWhere(['=', 'user_id', "3"])
//            ->max('attempt');

        $result = new stdClass();
        $temp = LnScormScoesTrackMongo::findOne("5741525b9d75d043040041a7");
            $result->kid = $temp->getPrimaryKey();
            $result->value = $temp->elementlist["element"];

//        $condition = [
//            'scorm_id' => "3",
//        ];
//
//        $model = new LnScormScoesTrackMongo();
//        $model->physicalDeleteAll($condition);
        
        return 'true';
    }

    public function actionHardware()
    {
        $macAddress = TBaseHelper::getMacAddress();
        TBaseHelper::getMachineCode($macAddress,$result,$errorMessage);
        echo $result;
    }

    public function actionLicense()
    {
        if (isset(Yii::$app->params['license'])) {
            $license = Yii::$app->params['license'];
            echo $license;
        }
        else {
            echo "";
        }
    }

    public function actionTestGuid(){
        $type = new FwTreeType();
        $type->kid="1";
        $type->tree_type_code ="test";
        $type->tree_type_name ="test";
        $type->sequence_number = 1;
        $type->save();
        
        $a = $type->kid;
    }

    public function actionWechat()
    {


        $wechatService = new WechatService();

//        $qrsceneValue = "111";
//
//        if ($wechatService->CreateQRCode($companyId, WechatService::QR_CODE_TEMP,1000, WechatService::QR_SCENE_ACTION_BIND_USER, $qrsceneValue, $result, $errMessage)) {
//            echo $result;
//            $v = new WechatQrsceneService();
//            var_dump($v->GetQrsceneByQrSceneId($companyId,"1",$result));
//        } else {
//            echo $errMessage;
//        }

//        $openIdList = ['oN8tps3NhuvrWEoCIyhgTcVzJZNM','oN8tps4odjD-XYLm-b_MMGvWRyfg','oN8tps43hFAT0Q47uzc-zqLnxeKw','oN8tps-uRhDMA_mg0rcqULn_21y8'];
//        $toUserId = 'oN8tps3NhuvrWEoCIyhgTcVzJZNM';
//        if ($wechatService->MassSendToOpenIdMessage($companyId, "你丫收到没", $openIdList, "text" , $result, $errMessage)) {
//            echo $result;
//        } else {
//            echo $errMessage;
//        }

//        $openIdList = ['oN8tps3NhuvrWEoCIyhgTcVzJZNM','oN8tps4odjD-XYLm-b_MMGvWRyfg','oN8tps43hFAT0Q47uzc-zqLnxeKw','oN8tps-uRhDMA_mg0rcqULn_21y8'];
//        $toUserId = 'oN8tps-uRhDMA_mg0rcqULn_21y8';
//        $templateId = "J4Hwxi9oFb5gSNxSYU92qeyZVe-76S0vOeE4oYkJWrY";
//        $templateUrl = "http://demo.hpe-online.com";
//        $data = [
//            'first' => [
//                'value' => '恭喜你注册成为在线学习平台会员！',
//                'color' => '#173177',
//            ],
//            'keyword1' => [
//                'value' => '3232',
//                'color' => '#173177',
//            ],
//            'keyword2' => [
//                'value' => 'XXX',
//                'color' => '#173177',
//            ],
//            'keyword3' => [
//                'value' => '开户',
//                'color' => '#173177',
//            ],
//            'keyword4' => [
//                'value' => '2015年12月22日',
//                'color' => '#173177',
//            ],
//            'remark' => [
//                'value' => '如有问题，请咨询系统管理员！',
//                'color' => '#173177',
//            ],
//        ];
//        if ($wechatService->SendMessageByTemplate($companyId,$toUserId, $templateId, $templateUrl, $data , $result, $errMessage)) {
//            echo $result;
//        } else {
//            echo $errMessage;
//        }

//        $wechatService->GetDataFromRobot("test",$test);

        $companyId = "33600670-F53E-B142-5D8E-D54486876426";
        $userId = "A0CAB213-BA06-80EE-B7FF-1F5F91603BBB";
        $openId = "ow3Pfw2z0gMt2bkxk7dZpu4H-OhI";
        if ($wechatService->bindWechatAccount($companyId, $userId, $openId, $errMessage)) {
            $userModel = FwUser::findOne($userId);
            $contentStr = "系统已为您绑定账户：" . $userModel->user_name;
        }
        else {
            $contentStr = "绑定账户失败：" . $errMessage;
        }
        echo  $contentStr;

//        $filePath = Yii::$app->basePath . '/..' . '/static/frontend/images/32px.png';
//
//        if ($wechatService->UploadTempMedia($companyId, $filePath, "image", $result, $errMessage)) {
//            $mediaId = $result;
//            echo $mediaId;
//            if ($wechatService->GetTempMedia($companyId, $mediaId, $result, $errMessage))
//            {
//                $filename = "D://down_wechat_image.jpg";
//                $wechatService->SaveWechatFile($filename, $result["body"]);
//            }
//        } else {
//            echo $errMessage;
//        }

    }

    public function actionExamination(){
        $result = "";
        $examinationPaperUserId = "";
        $examinationResultFinalId = "";
        $examinationResultProcessId = "";
        $errMessage = "";
        $examinationService = new ExaminationService();
        $examinationQuestionService = new ExaminationQuestionService();
        $examinationQuestionService->GeneratePaperQuestionByScore("testno",
            "3C7163EF-0AEE-BB31-A0E7-666252F9628C",
            "","","","","1",
            "2DEE410D-8C19-7E76-C266-009A55F6E0D2", $result, $message);

//        $examinationService->generateUserPaperByExam('32C6CD16-E4BA-3822-9887-58F6E395C5E6',
//            '3C7163EF-0AEE-BB31-A0E7-666252F9628C',
//            '2DEE410D-8C19-7E76-C266-009A55F6E0D2',
//            'FA3070A8-FB11-4FB3-A33A-6382B296CF45',
//            'D9DE6363-6D9A-9717-5A39-A0D07F4E2779',
//            '668F0751-FDBB-BD81-F615-B7243781E72C',
//            'A35A2640-2923-6866-0DE1-424AA5F95313',
//            1,
//            $result, $examinationPaperUserId, $examinationResultFinalId,
//            $examinationResultProcessId, $errMessage);
        echo $result;
        echo $examinationPaperUserId;
        echo $examinationResultFinalId;
        echo $examinationResultProcessId;
        echo $errMessage;
    }

    public function actionBatchInsert($companyId,$count){
        $models1 = [];
        $models2 = [];
        $models3 = [];
        for ($i = 0; $i < $count; $i++) {
            $model1 = new FwCompanySetting();
            $model1->company_id = $companyId;
            $model1->code = strval($i);
            $model1->value = strval($i);
            array_push($models1, $model1);

//            $model2 = new FwCompanySetting();
//            $model2->company_id = $companyId;
//            $model2->code = strval($i);
//            $model2->value = strval($i);
//            array_push($models2, $model2);
//
//            $model3 = new FwCompanySetting();
//            $model3->company_id = $companyId;
//            $model3->code = strval($i);
//            $model3->value = strval($i);
//            array_push($models3, $model3);
        }


        $this->batchInsertSqlArray($models1,$count);
//        $this->batchInsertNormalMode($models2,$count);
//        $this->singleInsert($models3,$count);
    }

    private function batchInsertNormalMode($models,$count){
        $startTime = time();
        $resultId = [];
        $errMsg = "";
        $result = BaseActiveRecord::batchInsertNormalMode($models,$errMsg);
        $endTime = time();
        $resultTime = $endTime - $startTime;
        echo "常规模式批量导入" . strval($count) . "条数据，执行耗时：" . strval($resultTime) . "秒". "<br>";
        if (!empty($errMsg)) {
            echo $errMsg. "<br>";
        }
    }

    private function batchInsertSqlArray($models,$count){
        $startTime = time();
        $resultId = [];
        $errMsg = "";
        $result = BaseActiveRecord::batchInsertSqlArray($models,$errMsg);
        $endTime = time();
        $resultTime = $endTime - $startTime;
        echo "SQL数组模式批量导入" . strval($count) . "条数据，执行耗时：" . strval($resultTime) . "秒" . "<br>";
        if (!empty($errMsg)) {
            echo $errMsg. "<br>";
        }
    }

    public function actionTestPerformance(){
//        $startTime = microtime(true);
//        $count = 10000000;
//        $result = 0;
//        for ($i = 0; $i < $count; $i++) {
//            $result += $result;
//        }
//        $endTime = microtime(true);
//        $resultTime = $endTime - $startTime;
//        echo "循环" . strval($count) . "条数据，执行耗时：" . strval($resultTime) . "秒";
        return $this->render('test-performance');
    }
    public function actionTestGetResourceInfoNoDirectCount(){
        $courseId = "16333566-0C0A-E721-C428-449E3936907F";
        $resourceService = new ResourceService();
        $modResInfoCount = $resourceService->getResourceInfoNoDirectCount($courseId);
        return $modResInfoCount;

    }
    public function actionTestTemp(){
        ini_set('max_execution_time', '300');
        header("Content-type: text/html; charset=utf-8");

        //$dir='./commom'; //检测的目录
        $dir='./frontend/views'; //检测的目录
        $exclude_dir=array('./common/messages'); //排除检测的目录列表
        $file_exts=array('.php','.js','.html'); //对这些文件后缀进行检测

        $r= self :: get_dir_files($dir,$exclude_dir,$file_exts);
        print_r($r);
    }
    public function actionTestExcel(){
        $str =  '{"NETWORKERROR":"网络错误，请稍后再试","LOADING":"加载中，请稍后","GONGYOU":"共有","RENPINGFEN":"人评分","MUQIANWURENPINGFEN":"目前无人评分","SOUSUORENYUAN":"搜索人员","SOUSUO":"搜索","SUOYOUREN":"所有人","QUEDING":"确定","TREE_NODE_DELETE":"删除","TREE_NODE_EDIT":"编辑","WUCHAXUNJIEGUO":"无查询结果"}';
        $str2 = '{"MONTHS":"一月_二月_三月_四月_五月_六月_七月_八月_九月_十月_十一月_十二月","MONTHS_SHORT":"1月_2月_3月_4月_5月_6月_7月_8月_9月_10月_11月_12月","WEEKDAYS":"星期日_星期一_星期二_星期三_星期四_星期五_星期六","WEEKDAYS_SHORT":"周日_周一_周二_周三_周四_周五_周六","WEEKDAYS_MIN":"日_一_二_三_四_五_六","LONG_DATE_FORMAT_LT":"Ah点mm","LONG_DATE_FORMAT_LTS":"Ah点m分s秒","LONG_DATE_FORMAT_LL":"YYYY年MMMD日","LONG_DATE_FORMAT_LLL":"YYYY年MMMD日LT","LONG_DATE_FORMAT_LLLL":"YYYY年MMMD日ddddLT","LONG_DATE_FORMAT_ll":"YYYY年MMMD日","LONG_DATE_FORMAT_lll":"YYYY年MMMD日LT","LONG_DATE_FORMAT_llll":"YYYY年MMMD日ddddLT","MERIDIEM":["凌晨","早上","上午","中午","下午","晚上"],"SAME_DAY":["[今天]Ah[点整]","[今天]LT"],"NEXT_DAY":["[明天]Ah[点整]","[明天]LT"],"LAST_DAY":["[昨天]Ah[点整]","[昨天]LT"],"NEXT_WEEK":["[下]","[本]","dddAh点整","dddAh点mm"],"LAST_WEEK":["[上]","[本]","dddAh点整","dddAh点mm"],"ORDINAL":["日","月","周"],"RELATIVE_TIME":["%s内","%s前","几秒","1分钟","%d分钟","1小时","%d小时","1天","%d天","1个月","%d个月","1年","%d年"],"CLOSE_TEXT":"关闭","PREV_TEXT":"&#x3C;上月","NEXT_TEXT":"下月&#x3E;","CURRENT_TEXT":"今天","DAY_NAMES_MIN":["日","一","二","三","四","五","六"],"WEEK_HEADER":"周","YEAR_SUFFIX":"年","LIST":"日程","ALL_DAY_TEXT":"全天","EVENT_LIMIT_TEXT":["另外 "," 个"]}';//"MOMENT":
        $arr = json_decode($str2,true);
       // print_r($arr);die;
      /*  $file1 = 'common/messages/zh/data.php';
        for($i=0; $i<1800; $i++){
            $arr  = require $file1;
            //$arr  = parse_ini_file($file2);
         //   $arr  = unserialize(require $file3);
        }*/
        $key = array_keys($arr);
        $value = array_values($arr);
        $data = array();
        foreach($key as $k=>$v){
            if(is_array($value[$k])){
                array_push($data, array($v,implode(',',$value[$k])));
            }else{
                array_push($data, array($v,$value[$k]));
            }
        };
    //    $data = array(
   //         array( '123', '哈哈哈', '我去' ),
    //        array( 'row_2_col_1', 'row_2_col_2', 'row_2_col_3' ),
    //        array( 'row_3_col_1', 'row_3_col_2', 'row_3_col_3' ),
    //    );
        $filename = "js1";

        header("Content-type: text/csv");
        header("Content-Disposition: attachment; filename={$filename}.csv");
        header("Pragma: no-cache");
        header("Expires: 0");

        self::outputCSV($data);

    }
    function outputCSV($data) {
        $outputBuffer = fopen("php://output", 'w');
        foreach($data as $val) {
            foreach ($val as $key => $val2) {
                $val[$key] = iconv('utf-8', 'gbk', $val2);// CSV的Excel支持GBK编码，一定要转换，否则乱码
            }
            fputcsv($outputBuffer, $val);
        }
        fclose($outputBuffer);
    }
    /**
     * 递归得到目录下的文件
     * @param string $dir 要检测的目录
     * @param array $exclude_dir  要排除的目录
     * @param  array $file_exts 要检测的文件后缀限制
     * @return array
     */
    function get_dir_files($dir,$exclude_dir,$file_exts)
    {
        $list=array();
        if($handle=opendir($dir))
        {
            while(false!==($files=readdir($handle)))
            {
                if($files=='.' || $files=='..'){continue;}
                $file_str=$dir."/".$files;
                if(is_dir($file_str))
                {
                    if(in_array($file_str,$exclude_dir))
                    {
                        continue;
                    }
                    $list=array_merge($list,self::get_dir_files($file_str,$exclude_dir,$file_exts));
                }
                else
                {
                    $file_ext=substr($files, strrpos($files, '.'));
                    if(!in_array($file_ext,$file_exts))
                    {
                        continue;
                    }


                    $file_content = file_get_contents($file_str);
                    $file_content = self::clear_comments($file_content);

                    $a=array();
                    if(preg_match_all("/[\x{4e00}-\x{9fa5}]+/u",$file_content,$a))
                    {
                        $list[]=array('name'=>$file_str,'matched'=>$a);
                    }
                    //preg_match_all("/[\x{4e00}-\x{9fa5}]+/u",$str,$a);

                }
            }
            closedir($handle);
        }
        return $list;
    }

    /**
     * @param $str 字符串
     * @return string
     */
    function clear_comments($str)
    {
        $str=preg_replace("/\\/\*([\s\S]*?)\*\\//",'',$str); /*xxx*/
        $str=preg_replace("/\\/\\/(.*?)\r\n/",'',$str); //xx
        $str=preg_replace("/\#(.*?)\r\n/",'',$str); #xx
        $str=preg_replace("/\\/\*\*([\s\S]*?)\*\\//",'',$str); /** xx */
        $str=preg_replace("/<\!--.*?-->/si","",$str);
        return $str;
    }
}
