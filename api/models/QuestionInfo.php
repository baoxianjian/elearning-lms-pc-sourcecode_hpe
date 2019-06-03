<?php
namespace api\models;
use Yii;

class QuestionInfo extends BaseModel {
	public $kid;              //问题ID		
	public $user_id;          //用户ID
	public $company_id;          //企业ID		
	public $obj_id;           //相关内容ID		
	public $obj_title;           //相关内容名称	
	public $title;            //标题		
	public $question_content; //问题内容
	public $tags; 			 //问题标签
	public $browse_num;       //浏览次数	
	public $attention_num;    //关注度	
	public $collect_num;      //收藏次数
	public $praise_num;      //点赞次数
	public $share_num;      	//分享次数
	public $answer_num;      	//回复次数
	public $question_type;    //问题类型	
	public $is_resolved;      //是否解决	
	public $version;          //版本号	
	public $created_by;       //创建人ID	
	public $created_at;       //创建时间	
	public $updated_by;       //更新人ID	
	public $updated_at;       //更新时间		
	public $is_deleted;       //删除标记		
	public $real_name;        //提问人名     	
	public $thumb;		  //头像
	public $gender;		  //性别
	public $canOperate;		  //当前用户是否可以操作设置为答案
	public $isCollect;		  //当前用户是否已收藏
	public $isCare;		  //判断用户是否已经关注问题
	public $isUserCared;		  //判断用户是否已经被关注
	public $isShare;		//用户是否已经分享过该问题
}