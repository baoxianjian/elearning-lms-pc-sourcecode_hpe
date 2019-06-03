<?php
/**
 * User: GROOT (pzyme@outlook.com)
 * Date: 2016/5/11
 * Time: 11:33
 */

namespace common\services\api;

use Yii;
use common\services\framework\ExternalSystemService;
use common\models\framework\FwUser;
use common\services\framework\UserDomainService;
use yii\helpers\ArrayHelper;
use common\helpers\TNetworkHelper;
use yii\web\NotAcceptableHttpException;
use yii\web\MethodNotAllowedHttpException;
use common\helpers\TStringHelper;
use common\services\social\UserAttentionService;
use common\services\social\ShareService;
use common\services\framework\TagService;
use common\traits\ResponseTrait;
/**
 * Class SearchService
 * @package common\services\api
 */
class SearchService {
    use ResponseTrait;
    /*
     * 搜索类型
     */
    const TYPE_COURSE = 'course';
    const TYPE_QUESTION = 'question';
    const TYPE_PERSON = 'person';
    const TYPE_SHARE = 'share';
    const TYPE_HISTORY = 'history';
    const TYPE_TAG = 'tag';

    const API_URI_COURSE = 'course/search';
    const API_URI_QUESTION = 'question/search';
    const API_URI_PERSON = 'user/search';
    const API_URI_SHARE = 'share/search';

    protected $user;
    public $system_key;
    private $search_api_server = '';
    private $image_server = '';
    private $params = [];
    private $offset = 0;
    private $limit = 10;
    private $is_mobile = true;

    public function __construct(FwUser $user,$system_key) {
        $this->user = $user;
        $this->system_key = $system_key;
        $externalSystemService = new ExternalSystemService();
        $this->search_api_server = $externalSystemService->getExternalSystemInfoByExternalSystemCode("elearning-solr-service")->api_address;
        //$this->search_api_server = 'http://115.28.211.33:9389/';
        $this->image_server = Yii::$app->params['img_server_url'];
    }

    /**
     * 查询
     * @param $type 类型
     * @param $q 关键字
     * @param $offset 分页offset
     * @param $limit  分页limit
     * @param bool $is_mobile
     * @return mixed
     * @throws MethodNotAllowedHttpException
     */
    public function get($type,$q,$offset,$limit,$is_mobile = true) {
        $this->offset = $offset;
        $this->limit = $limit;
        $this->is_mobile = $is_mobile;
        $this->setArrayParam([
            'start' => $offset,
            'rows' => $limit,
            'q' => $q
        ]);
        $args = func_get_args();
        if(!method_exists($this,$type)) {
            throw new MethodNotAllowedHttpException('search method not exists.');
        }
        return call_user_func_array([$this,$type],array_merge([$q],array_slice($args,5)));
    }

    /**
     * 获取记录总数
     * @param $uri
     * @param $q
     * @return int
     * @throws NotAcceptableHttpException
     */
    public function count($uri,$q = null) {
        $this->setArrayParam([
            'start' => 0,
            'rows' => 0
        ]);
        $result = (object) $this->request($uri);
        try{
            return intval($result->response->numFound);
        } catch(\Exception $e) {
            return -1;
        }
    }

    /**
     * 设置参数
     */
    public function setParam() {
        $args = func_get_args();
        $this->params[$args[0]] = $args[1];
    }

    /**
     * 批量设置参数
     * @param array $array
     */
    public function setArrayParam(array $array) {
        foreach($array as $key => $val) {
            $this->setParam($key,$val);
        }
    }

    /**
     * 清空参数
     */
    public function resetParams() {
        $this->params = [];
    }

    /**
     * 课程搜索
     * @param $q 关键字
     * @return array
     * @throws NotAcceptableHttpException
     */
    protected function course($q) {
        $this->setArrayParam([
            'fq1' => $this->domainId(),
            'fq2' => $this->is_mobile ? 'is_display_mobile:1' : 'is_display_pc:1'
        ]);

        $result = (object) $this->request(self::API_URI_COURSE);
        $docs = $result->response->docs;

        $new = [];
        //todo just copy,need optimize.
        array_walk($docs,function(&$val,$key) use(&$result,&$new) {
            $kid = $val->kid;
            $temp = clone $val;
            if (isset($result->highlighting->$kid->course_name) && !empty($result->highlighting->$kid->course_name[0])) {
                $temp->course_name = $result->highlighting->$kid->course_name[0];
            }
            if (isset($result->highlighting->$kid->course_desc_nohtml_all) && !empty($result->highlighting->$kid->course_desc_nohtml_all[0])) {
                $temp->course_desc_nohtml = $result->highlighting->$kid->course_desc_nohtml_all[0];
            }
            if (isset($result->highlighting->$kid->course_code) && !empty($result->highlighting->$kid->course_code[0])) {
                $temp_code = str_replace($temp->course_code, $key, $result->highlighting->$kid->course_code[0]);
                $temp->course_code = str_replace($key, $temp_code, $temp->course_code);
            }
            if (isset($result->highlighting->$kid->content) && !empty($result->highlighting->$kid->content[0])) {
                $temp->content = $result->highlighting->$kid->content[0];
            }
            else
            {
                $temp->content=TStringHelper::subStr($temp->content,150);
            }

            if (isset($result->highlighting->$kid->file_contents_all) && !empty($result->highlighting->$kid->file_contents_all))
            {
                foreach ($result->highlighting->$kid->file_contents_all as $k2=>$v2)
                {
                    if(!$v2)
                    {
                        $temp->file_contents[$k2]=null;
                        continue;
                    }

                    if(is_int(strpos($v2,TStringHelper::getHighlightTag('pre'))))
                    {

                        $temp->file_contents[$k2.'_hi']=true;
                        $temp->file_contents[$k2] = $v2;
                    }
                    else
                    {
                        $temp->file_contents[$k2]=null;
                    }
                }
            }
            unset( $temp->file_names);

            if (isset($result->highlighting->$kid->file_names) && !empty($result->highlighting->$kid->file_names))
            {
                foreach($result->highlighting->$kid->file_names as $k2=>$v2)
                {
                    if(is_int(strpos($v2,TStringHelper::getHighlightTag('pre'))) || $temp->file_contents[$k2.'_hi'])
                    {
                        $temp->file_names[$k2] = $v2;
                    }

                }
                $temp->file_names=array_unique($temp->file_names);
            }
            if (isset($result->highlighting->$kid->tag_value) && !empty($result->highlighting->$kid->tag_value))
            {
                foreach ($result->highlighting->$kid->tag_value as $k2=>$v2)
                {
                    if($v2 && is_int(strpos($v2,TStringHelper::getHighlightTag('pre'))))
                    {
                        $temp->tag_value[$k2] = $v2;
                    }
                }
            }
            $new[] = $temp;
        });

        if($this->is_mobile) {
            $url = $this->image_server;
            array_walk($new,function(&$val) use(&$url){
                $val->theme_url =  $url . $val->theme_url;
            });
        }

        return [
            'count' => $this->count(self::API_URI_COURSE,$q),
            'data' => $new
        ];
    }

    /**
     * 问题搜索
     * @param $q
     * @return array
     * @throws NotAcceptableHttpException
     */
    protected function question($q) {
        $this->setParam('fq','company_id:'.$this->user->company_id);
        $result = (object) $this->request(self::API_URI_QUESTION);

        $new = [];
        foreach ($result->response->docs as $v) {
            $kid = $v->kid;
            $temp = new \stdClass();
            $temp = clone $v;
            if (isset($result->highlighting->$kid->title) && !empty($result->highlighting->$kid->title[0])) {
                $temp->title = $result->highlighting->$kid->title[0];
            }
            if (isset($result->highlighting->$kid->question_content) && !empty($result->highlighting->$kid->question_content[0])) {
                $temp->question_content = $result->highlighting->$kid->question_content[0];
            }
            $new[] = $temp;
        }

        return [
            'count' => $this->count(self::API_URI_QUESTION,$q),
            'data' => $new
        ];
    }

    /**
     * 用户搜索
     * @param $q
     * @return array
     * @throws NotAcceptableHttpException
     */
    protected function person($q) {
        $this->setParam('fq1','company_id:'.$this->user->company_id);
        $this->setParam('fq2','{!frange l=0.1}query($q)');
        $this->setParam('q',"real_name:{$q} OR orgnization_name:{$q} OR position_name:{$q}");
        $result = (object) $this->request(self::API_URI_PERSON);

        $new = [];
        foreach ($result->response->docs as $v) {
            $kid = $v->kid;
            $temp = new \stdClass();
            $temp = clone $v;
            $temp->old_real_name = $temp->real_name;
            if (isset($result->highlighting->$kid->real_name) && !empty($result->highlighting->$kid->real_name[0])) {
                $temp->real_name = $result->highlighting->$kid->real_name[0];
            }
            if (isset($result->highlighting->$kid->orgnization_name) && !empty($result->highlighting->$kid->orgnization_name[0])) {
                $temp->orgnization_name = $result->highlighting->$kid->orgnization_name[0];
            }
            if (isset($result->highlighting->$kid->position_name) && !empty($result->highlighting->$kid->position_name[0])) {
                $temp->position_name = $result->highlighting->$kid->position_name[0];
            }
            $new[] = $temp;
        }

        //关注状态
        $attentionService = new UserAttentionService();
        $attentionUser = $attentionService->getAllAttentionUserId($this->user->kid);
        $attentionUser = ArrayHelper::map($attentionUser, 'attention_id', 'attention_id');
        $user_ids = array_keys($attentionUser);

        array_walk($new,function(&$val) use($user_ids) {
            $val->isFollow = in_array($val->kid,$user_ids);
        });

        return [
            'count' => $this->count(self::API_URI_PERSON,$q),
            'data' => $new
        ];
    }

    /**
     * 分享搜索
     * @param $q
     * @param null $record_type
     * @return array
     * @throws NotAcceptableHttpException
     */
    protected function share($q,$record_type = null) {
        $record_type = $record_type === 'all' ? null : $record_type;
        $this->setParam('fq','company_id:'.$this->user->company_id);
        if($record_type !== null) {
            $this->setParam('fq1','record_type:'.$record_type);
        }
        $result = (object) $this->request(self::API_URI_SHARE);

        $new = [];
        foreach ($result->response->docs as $v) {
            $kid = $v->kid;
            $temp = new \stdClass();
            $temp = clone $v;
            if (isset($result->highlighting->$kid->title) && !empty($result->highlighting->$kid->title[0])) {
                $temp->title = $result->highlighting->$kid->title[0];
            }
            if (isset($result->highlighting->$kid->content) && !empty($result->highlighting->$kid->content[0])) {
                $temp->content = $result->highlighting->$kid->content[0];
            }
            $new[] = $temp;
        }
        $count = $record_type === null ? -1 : $this->count(self::API_URI_SHARE,$q);

        return [
            'count' => $count,
            'data' => $new
        ];
    }

    /**
     * 分享历史搜索
     * @return array
     */
    protected function history() {
        $service = new ShareService();
        $page = $this->offset <= 0 ? 1 : $this->offset/$this->limit + 1;
        $data = $service->getShareByUid($this->user->kid, $this->limit, $page);
        return [
            'count' => -1,
            'data' => $data
        ];
    }

    /**
     * 标签搜索
     * @param $q
     * @return array|null|\yii\db\ActiveRecord[]
     */
    protected function tag($q) {
        $tagService = new TagService();
        $tags = $tagService->getLikeTagByValue($this->user->company_id, 'conversation', $q);

        array_walk($tags,function(&$val,$key){
            if(!in_array($key,['kid','tag_value'])) unset($val->{$key});
        });

        return $tags;
    }

    /**
     * request search api server
     * @param $uri
     * @return array
     * @throws NotAcceptableHttpException
     */
    private function request($uri) {
        $response = TNetworkHelper::HttpGet($this->search_api_server . $uri, $this->params);
        $decode = json_decode($response['content']);
        if($decode === null) {
            throw new NotAcceptableHttpException('search server error');
        }
        return $decode;
    }

    /**
     * 获取域id
     * @return string
     */
    private function domainId() {
        $userDomainService = new UserDomainService();
        $domainIds = $userDomainService->getSearchListByUserId($this->user->kid);
        $domainIdStr = '';
        if (isset($domainIds) && $domainIds != null) {
            $domainIds = ArrayHelper::map($domainIds, 'kid', 'kid');
            $domainIds = array_keys($domainIds);
            foreach ($domainIds as $dom) {
                $domainIdStr .= 'domain_id:' . $dom . ' ';
            }
            $domainIdStr = rtrim($domainIdStr);
        }
        return $domainIdStr;
    }
}