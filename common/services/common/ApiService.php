<?php
/**
 * Created by PhpStorm.
 * User: Liu Cheng
 * Date: 2015/10/27
 * Time: 16:11
 */

// deprecated by GROOT at 2016.05.11

namespace common\services\common;


use common\services\framework\ExternalSystemService;
use common\services\framework\ServiceService;
use common\helpers\TStringHelper;
use Yii;
use common\services\framework\UserDomainService;
use common\helpers\TNetworkHelper;
use components\widgets\TPagination;
use yii\helpers\ArrayHelper;
use yii\web\User;

class ApiService
{
    protected $api_url;
    protected $img_server_url;

    public function __construct()
    {
        $externalSystemService = new ExternalSystemService();
        $search_api_url = $externalSystemService->getExternalSystemInfoByExternalSystemCode("elearning-solr-service")->api_address;
        $this->api_url = $search_api_url;
        $this->img_server_url = Yii::$app->params['img_server_url'];
    }

//    public function GetSearchCourseData($key, $user_id, $size, $count, $is_mobile = false, $is_highlight = false)
//    {
//        if ($is_highlight) {
//            $method = 'ml_search/api/v3/search/queryHLCourse';
//        } else {
//            $method = 'ml_search/api/v3/search/queryCourse';
//        }
//        $userDomainService = new UserDomainService();
//        $domainIds = $userDomainService->getSearchListByUserId($user_id);
//
//        if (isset($domainIds) && $domainIds != null) {
//            $domainIds = ArrayHelper::map($domainIds, 'kid', 'kid');
//
//            $domainIds = array_keys($domainIds);
//
//            $domainIdStr = '';
//
//            foreach ($domainIds as $dom) {
//                $domainIdStr .= 'domainId:' . $dom . ' ';
//            }
//            $domainIdStr = rtrim($domainIdStr);
//        }
//
//        $pages = new TPagination(['defaultPageSize' => $size, 'totalCount' => $count]);
//        $data = array('start' => $pages->offset, 'rows' => $size, 'q' => $key, 'fc' => $domainIdStr, 'fd' => $is_mobile ? 'isDisplayMobile:1' : 'isDisplayPc:1');
//
//        $var = TNetworkHelper::HttpPost($this->api_url . $method, $data);
//
//        $data = json_decode($var['content']);
//
//        if ($is_mobile) {
//            foreach ($data as $v) {
//                $v->themeUrl = $this->img_server_url . $v->themeUrl;
//            }
//        }
//        $result = array();
//
//        $result['page'] = $pages;
//        $result['data'] = $data;
//
//        return $result;
//    }
//
//    public function GetSearchQuestionData($key, $company_id, $size, $count, $is_highlight = false)
//    {
//        if ($is_highlight) {
//            $method = 'ml_search/api/v3/search/queryHlQa';
//        } else {
//            $method = 'ml_search/api/v3/search/queryQuestion';
//        }
//
//        $pages = new TPagination(['defaultPageSize' => $size, 'totalCount' => $count]);
//        $data = array('start' => $pages->offset, 'rows' => $size, 'q' => $key, 'fc' => 'companyId:' . $company_id);
//
//        $var = TNetworkHelper::HttpPost($this->api_url . $method, $data);
//
//        $data = json_decode($var['content']);
//
//        $result = array();
//
//        $result['page'] = $pages;
//        $result['data'] = $data;
//
//        return $result;
//    }
//
//    public function GetSearchPersonData($key, $company_id, $size, $count, $is_highlight = false)
//    {
//        if ($is_highlight) {
//            $method = 'ml_search/api/v3/search/queryHlFwUser';
//        } else {
//            $method = 'ml_search/api/v3/search/queryFwUser';
//        }
//
//        $pages = new TPagination(['defaultPageSize' => $size, 'totalCount' => $count]);
//        $data = array('start' => $pages->offset, 'rows' => $size, 'q' => $key, 'fc' => 'companyId:' . $company_id);
//
//        $var = TNetworkHelper::HttpPost($this->api_url . $method, $data);
//
//        $data = json_decode($var['content']);
//
//        $result = array();
//
//        $result['page'] = $pages;
//        $result['data'] = $data;
//
//        return $result;
//    }
//
//    public function GetSearchShareData($key, $type, $company_id, $size, $count, $is_highlight = false)
//    {
//        if ($is_highlight) {
//            $method = 'ml_search/api/v3/search/queryHlShare';
//        } else {
//            $method = 'ml_search/api/v3/search/queryShare';
//        }
//
//        $pages = new TPagination(['defaultPageSize' => $size, 'totalCount' => $count]);
//        $data = array('start' => $pages->offset, 'rows' => $size, 'q' => $key);
//
//        if ($type !== null) {
//            $data['fc'] = 'recordType:' . $type;
//        }
//
//        $var = TNetworkHelper::HttpPost($this->api_url . $method, $data);
//
//        $data = json_decode($var['content']);
//
//        $result = array();
//
//        $result['page'] = $pages;
//        $result['data'] = $data;
//
//        return $result;
//    }

    
    public function GetSearchCourseData($key, $user_id, $size, $count, $is_mobile = false)
    {
        $method = 'course/search';

        $userDomainService = new UserDomainService();
        $domainIds = $userDomainService->getSearchListByUserId($user_id);

        if (isset($domainIds) && $domainIds != null) {
            $domainIds = ArrayHelper::map($domainIds, 'kid', 'kid');

            $domainIds = array_keys($domainIds);

            $domainIdStr = '';

            foreach ($domainIds as $dom) {
                $domainIdStr .= 'domain_id:' . $dom . ' ';
            }
            $domainIdStr = rtrim($domainIdStr);
        }

        $pages = new TPagination(['defaultPageSize' => $size, 'totalCount' => $count]);
        $data = array('start' => $pages->offset, 'rows' => $size, 'q' => $key, 'fq1' => $domainIdStr, 'fq2' => ($is_mobile ? 'is_display_mobile:1' : 'is_display_pc:1'));

        $var = TNetworkHelper::HttpGet($this->api_url . $method, $data);

        $data = json_decode($var['content']);

        $new = array();
        foreach ($data->response->docs as $v) {
            $kid = $v->kid;
            $temp = new \stdClass();
            $temp = clone $v;
            if (isset($data->highlighting->$kid->course_name) && !empty($data->highlighting->$kid->course_name[0])) {
                $temp->course_name = $data->highlighting->$kid->course_name[0];
            }
            if (isset($data->highlighting->$kid->course_desc_nohtml_all) && !empty($data->highlighting->$kid->course_desc_nohtml_all[0])) {
                $temp->course_desc_nohtml = $data->highlighting->$kid->course_desc_nohtml_all[0];
            }
            if (isset($data->highlighting->$kid->course_code) && !empty($data->highlighting->$kid->course_code[0])) {
                $temp_code = str_replace($temp->course_code, $key, $data->highlighting->$kid->course_code[0]);
                $temp->course_code = str_replace($key, $temp_code, $temp->course_code);
            }
            if (isset($data->highlighting->$kid->content) && !empty($data->highlighting->$kid->content[0])) {
                $temp->content = $data->highlighting->$kid->content[0];
            }
            else
            {
                 $temp->content=TStringHelper::subStr($temp->content,150);
            }
            
          //  unset($temp->file_contents);
            
            if (isset($data->highlighting->$kid->file_contents_all) && !empty($data->highlighting->$kid->file_contents_all))
            {
                foreach ($data->highlighting->$kid->file_contents_all as $k2=>$v2)
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

            if (isset($data->highlighting->$kid->file_names) && !empty($data->highlighting->$kid->file_names))
            {
                foreach($data->highlighting->$kid->file_names as $k2=>$v2)
                {
                    //文件名高亮 或 内容高亮
                    if(is_int(strpos($v2,TStringHelper::getHighlightTag('pre'))) || $temp->file_contents[$k2.'_hi'])
                    {                                               
                        $temp->file_names[$k2] = $v2;  
                    }

                }
                        
                $temp->file_names=array_unique($temp->file_names);
            }
            if (isset($data->highlighting->$kid->tag_value) && !empty($data->highlighting->$kid->tag_value))
            {
                foreach ($data->highlighting->$kid->tag_value as $k2=>$v2)
                {
                    if($v2 && is_int(strpos($v2,TStringHelper::getHighlightTag('pre'))))
                    {
                        $temp->tag_value[$k2] = $v2;
                    } 
                }
            }
            $new[] = $temp;
        }

        if ($is_mobile) {
            foreach ($new as $v) {
                if (!empty($v->theme_url)) {
                    $v->theme_url = $this->img_server_url . $v->theme_url;
                }
            }
        }
        $result = array();

        $result['page'] = $pages;
        $result['data'] = $new;

        return $result;
    }

    public function GetSearchQuestionData($key, $company_id, $size, $count)
    {
        $method = 'question/search';

        $pages = new TPagination(['defaultPageSize' => $size, 'totalCount' => $count]);
        $data = array('start' => $pages->offset, 'rows' => $size, 'q' => $key, 'fq' => 'company_id:' . $company_id);

        $var = TNetworkHelper::HttpGet($this->api_url . $method, $data);

        $data = json_decode($var['content']);

        $new = array();
        foreach ($data->response->docs as $v) {
            $kid = $v->kid;
            $temp = new \stdClass();
            $temp = clone $v;
            if (isset($data->highlighting->$kid->title) && !empty($data->highlighting->$kid->title[0])) {
                $temp->title = $data->highlighting->$kid->title[0];
            }
            if (isset($data->highlighting->$kid->question_content) && !empty($data->highlighting->$kid->question_content[0])) {
                $temp->question_content = $data->highlighting->$kid->question_content[0];
            }
            $new[] = $temp;
        }

        $result = array();

        $result['page'] = $pages;
        $result['data'] = $new;

        return $result;
    }

    public function GetSearchPersonData($key, $company_id, $size, $count)
    {
        $method = 'user/search';

//        $blank_key = TStringHelper::StringAddBlank($key);

        $key_u="real_name:{$key} OR orgnization_name:{$key} OR position_name:{$key}";
        $fq2_u='{!frange l=0.1}query($q)'; 

        $pages = new TPagination(['defaultPageSize' => $size, 'totalCount' => $count]);
        $data = array('start' => $pages->offset, 'rows' => $size, 'q' => $key_u, 'fq1' => 'company_id:' . $company_id,'fq2'=>$fq2_u);

        $var = TNetworkHelper::HttpGet($this->api_url . $method, $data);

        $data = json_decode($var['content']);

        $new = array();
        foreach ($data->response->docs as $v) {
            $kid = $v->kid;
            $temp = new \stdClass();
            $temp = clone $v;
            $temp->old_real_name = $temp->real_name;
            if (isset($data->highlighting->$kid->real_name) && !empty($data->highlighting->$kid->real_name[0])) {
                $temp->real_name = $data->highlighting->$kid->real_name[0];
            }
            if (isset($data->highlighting->$kid->orgnization_name) && !empty($data->highlighting->$kid->orgnization_name[0])) {
                $temp->orgnization_name = $data->highlighting->$kid->orgnization_name[0];
            }
            if (isset($data->highlighting->$kid->position_name) && !empty($data->highlighting->$kid->position_name[0])) {
                $temp->position_name = $data->highlighting->$kid->position_name[0];
            }
            $new[] = $temp;
        }

        $result = array();

        $result['page'] = $pages;
        $result['data'] = $new;

        return $result;
    }

    public function GetSearchShareData($key, $type, $company_id, $size, $count)
    {
        $method = 'share/search';

        $pages = new TPagination(['defaultPageSize' => $size, 'totalCount' => $count]);
        $data = array('start' => $pages->offset, 'rows' => $size, 'q' => $key, 'fq1' => 'company_id:' . $company_id);

        if ($type !== null) {
            $data['fq2'] = 'record_type:' . $type;
        }

        $var = TNetworkHelper::HttpGet($this->api_url . $method, $data);

        $data = json_decode($var['content']);

        $new = array();
        foreach ($data->response->docs as $v) {
            $kid = $v->kid;
            $temp = new \stdClass();
            $temp = clone $v;
            if (isset($data->highlighting->$kid->title) && !empty($data->highlighting->$kid->title[0])) {
                $temp->title = $data->highlighting->$kid->title[0];
            }
            if (isset($data->highlighting->$kid->content) && !empty($data->highlighting->$kid->content[0])) {
                $temp->content = $data->highlighting->$kid->content[0];
            }
            $new[] = $temp;
        }

        $result = array();

        $result['page'] = $pages;
        $result['data'] = $new;

        return $result;
    }
}