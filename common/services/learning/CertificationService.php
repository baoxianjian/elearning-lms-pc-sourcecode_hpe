<?php

namespace common\services\learning;


use common\models\learning\LnCourse;
use common\models\learning\LnCourseCertification;
use common\models\learning\LnCourseComplete;
use common\models\learning\LnTeacher;
use common\services\framework\UserService;
use common\services\framework\WechatService;
use common\services\framework\WechatTemplateService;
use common\base\BaseActiveRecord;
use common\helpers\TFileHelper;
use common\helpers\TTimeHelper;
use Exception;
use mPDF;
use Yii;
use common\models\learning\LnCertification;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;
use yii\db\Query;
use common\models\learning\LnCertificationTemplate;
use common\models\learning\LnUserCertification;
use common\models\message\MsSelectTemp;
use common\services\framework\UserDomainService;
use common\models\framework\FwUser;
use common\services\learning\RecordService;
use common\models\framework\FwOrgnization;
use yii\helpers\Html;
use common\services\framework\PointRuleService;
use common\services\message\MessageService;
use common\models\framework\FwUserDisplayInfo;
use yii\data\Pagination;


class CertificationService extends LnCertification
{
	const STATUS_NORMAL = '1';
	const STATUS_DISABLE = '2';
	
	const SIZE =10;

	public function editCertification($params){
		$id=$params['kid'];
		$certification_template_id=$params['certification_template_id'];
		$lnCertification = LnCertification::findOne($id);
		$lnCertification->certification_display_name = Html::encode($params['certification_display_name']);
		$lnCertification->certification_name=Html::encode($params['certification_name']);
		$lnCertification->description=Html::encode($params['description']);
		$lnCertification->certification_template_id=$certification_template_id;


		//查找证书模板
		$lnCertificationTemplate=LnCertificationTemplate::findOne($certification_template_id);

		$lnCertification->print_type=$lnCertificationTemplate->print_type;
		$lnCertification->print_orientation=$lnCertificationTemplate->print_orientation;

		$lnCertification->seal_top=$lnCertificationTemplate->seal_top;
		$lnCertification->seal_left=$lnCertificationTemplate->seal_left;

		$lnCertification->name_top=$lnCertificationTemplate->name_top;
		$lnCertification->name_left=$lnCertificationTemplate->name_left;
		$lnCertification->name_size=$lnCertificationTemplate->name_size;
		$lnCertification->name_font=$lnCertificationTemplate->name_font;
		$lnCertification->name_color=$lnCertificationTemplate->name_color;

		$lnCertification->score_top=$lnCertificationTemplate->score_top;
		$lnCertification->score_left=$lnCertificationTemplate->score_left;
		$lnCertification->score_size=$lnCertificationTemplate->score_size;
		$lnCertification->score_font=$lnCertificationTemplate->score_font;
		$lnCertification->score_color=$lnCertificationTemplate->score_color;

		$lnCertification->certify_date_top=$lnCertificationTemplate->certify_date_top;
		$lnCertification->certify_date_left=$lnCertificationTemplate->certify_date_left;
		$lnCertification->certify_date_size=$lnCertificationTemplate->certify_date_size;
		$lnCertification->certify_date_font=$lnCertificationTemplate->certify_date_font;
		$lnCertification->certify_date_color=$lnCertificationTemplate->certify_date_color;

		$lnCertification->certification_name_top=$lnCertificationTemplate->certification_name_top;
		$lnCertification->certification_name_left=$lnCertificationTemplate->certification_name_left;
		$lnCertification->certification_name_size=$lnCertificationTemplate->certification_name_size;
		$lnCertification->certification_name_font=$lnCertificationTemplate->certification_name_font;
		$lnCertification->certification_name_color=$lnCertificationTemplate->certification_name_color;

		$lnCertification->serial_number_top=$lnCertificationTemplate->serial_number_top;
		$lnCertification->serial_number_left=$lnCertificationTemplate->serial_number_left;
		$lnCertification->serial_number_size=$lnCertificationTemplate->serial_number_size;
		$lnCertification->serial_number_font=$lnCertificationTemplate->serial_number_font;
		$lnCertification->serial_number_color=$lnCertificationTemplate->serial_number_color;
		$lnCertification->is_display_certify_date=$lnCertificationTemplate->is_display_certify_date;


		$lnCertification->expire_time=$params['expire_time'];
		$lnCertification->expire_time_type=$params['expire_time_type'];

		$lnCertification->is_email_user=$params['is_email_user'];
		$lnCertification->is_email_teacher=$params['is_email_teacher'];
		$lnCertification->is_print_score=$params['is_print_score'];
		$lnCertification->is_auto_certify=$params['is_auto_certify'];

		if (empty($lnCertification->certification_display_name)){
			$lnCertification->certification_display_name=$lnCertificationTemplate->certification_display_name;
		}

		if (empty($lnCertification->description)){
			$lnCertification->description=$lnCertificationTemplate->description;
		}

		$lnCertification->template_url=$lnCertificationTemplate->template_url;
		$lnCertification->seal_url=$lnCertificationTemplate->seal_url;



		$zipUrl = Yii::$app->basePath.'/..' . $lnCertification->template_url;


		$filePath =  str_replace("/certification-template/","/certification/",$lnCertificationTemplate->file_path);
		TFileHelper::unzip($zipUrl,Yii::$app->basePath.'/..' .$filePath);

//                    substr($filePath,0,strlen($filePath)-4) . 'merge.'.substr($filePath,strlen($filePath)-3);
		$lnCertification->file_path = $filePath;
//		$lnCertification->certification_img_url = $this->MergeCertificationTemplate($lnCertification);

		$lnCertification->save();
		return true;
	}


	public function getCertificationContent($model){
		$fileUrl = $model->file_path . 'index.html';

		try {
			$imgSrc = Yii::$app->basePath . '/..' . $fileUrl;

			if (file_exists($imgSrc)) {
				$html = file_get_contents($imgSrc);


				$html = str_replace("href='", "href='".$model->file_path, $html);
				$html = str_replace("src='", "src='".$model->file_path, $html);
				$html = str_replace('href="', 'href="'.$model->file_path, $html);
				$html = str_replace('src="', 'src="'.$model->file_path, $html);


//                copy($imgSrc, $imgTarget);
//                $imgSrc = $imgTarget;
//
//                //显示印章
//                if (!empty($model->seal_url)) {
//                    $markImg = Yii::$app->basePath . '/..' . $model->seal_url;//水印图片
//                    if (file_exists($markImg)) {
//                        $fontColor = null;
//                        $fontType = null;//字体
//                        $markType = "img";
//                        $markText = null;
//                        $fontSize = null;
//                        $top = $model->seal_top;
//                        $left = $model->seal_left;
//                        $certificationTemplateService->setWater($imgSrc, $markImg, $markText, $fontSize, $fontColor, $top, $left, $fontType, $markType);
//                    }
//                }
//
//
//                $markImg = null;//水印图片
//                $fontColor = $model->score_color;
//                $fontType = $model->score_font;//字体
//                $markType = "text";
//
				//显示成绩
				if ($model->is_print_score == LnCertification::IS_PRINT_SCORE_YES) {
					$markText = "100分";
				} else {
					$markText = "合格";
				}

				$html = str_replace('[:score:]', $markText, $html);


				//显示学分
				if ($model->is_print_score == LnCertification::IS_PRINT_SCORE_YES) {
					$markText = "10学分";
				} else {
					$markText = "";
				}

				$html = str_replace('[:grade:]', $markText, $html);

//                $fontSize = $model->score_size;
//                $top = $model->score_top;
//                $left = $model->score_left;
//                $certificationTemplateService->setWater($imgSrc, $markImg, $markText, $fontSize, $fontColor, $top, $left, $fontType, $markType);
//
//
				//显示颁证日期
				if ($model->is_display_certify_date == LnCertification::IS_DISPLAY_CERTIFY_DATE_YES) {
					$markImg = null;//水印图片
					$fontColor = $model->certify_date_color;
					$fontType = $model->certify_date_font;//字体
					$markType = "text";
					$issued_at = date("Y年m月d日", time());
					$markText = $issued_at;
					$fontSize = $model->certify_date_size;
					$top = $model->certify_date_top;
					$left = $model->certify_date_left;

					$html = str_replace('[:certify_date:]', $markText, $html);
//                    $certificationTemplateService->setWater($imgSrc, $markImg, $markText, $fontSize, $fontColor, $top, $left, $fontType, $markType);
				}
				else {
					$html = str_replace('[:certify_date:]', "", $html);
				}

				$markText = "《测试课程》";
				if (!empty($markText)) {
					$html = str_replace('[:course_name:]', $markText, $html);
				}
				else {
					$html = str_replace('[:course_name:]', "", $html);
				}
//
				//显示姓名
				$markImg = null;//水印图片
				$fontColor = $model->name_color;
				$fontType = $model->name_font;//字体
				$markType = "text";
				$markText = "测试人员";
				$fontSize = $model->name_size;
				$top = $model->name_top;
				$left = $model->name_left;
				$html = str_replace('[:user_name:]', $markText, $html);
//                $certificationTemplateService->setWater($imgSrc, $markImg, $markText, $fontSize, $fontColor, $top, $left, $fontType, $markType);
//
				//显示证书名称
				if (!empty($model->certification_display_name)) {
					$markImg = null;//水印图片
					$fontColor = $model->certification_name_color;
					$fontType = $model->certification_name_font;//字体
					$markType = "text";
					$markText = $model->certification_display_name;
					$fontSize = $model->certification_name_size;
					$top = $model->certification_name_top;
					$left = $model->certification_name_left;
					$html = str_replace('[:certification_display_name:]', $markText, $html);
//                    $certificationTemplateService->setWater($imgSrc, $markImg, $markText, $fontSize, $fontColor, $top, $left, $fontType, $markType);
				}
				else {
					$html = str_replace('[:certification_display_name:]', "", $html);
				}
//
//
				//显示证书编号
				$markImg = null;//水印图片
				$fontColor = $model->serial_number_color;
				$fontType = $model->serial_number_font;//字体
				$markType = "text";
				$markText = "ELN-1443493796-001";
				$fontSize = $model->serial_number_size;
				$top = $model->serial_number_top;
				$left = $model->serial_number_left;
				$html = str_replace('[:serial_number:]', $markText, $html);
//                $certificationTemplateService->setWater($imgSrc, $markImg, $markText, $fontSize, $fontColor, $top, $left, $fontType, $markType);


				//显示备注
				$markText = $model->description;
				$html = str_replace('[:description:]', $markText, $html);

				//显示系统名称
				$markText = Yii::t('system','frontend_name');
				$html = str_replace('[:platform_name:]', $markText, $html);


			}
			else {
				$html = null;
			}
		}
		catch (Exception $e)
		{
			$e;
			$html = null;
		}

		return $html;
	}

	/**
	 * @param $userCertificationModel LnUserCertification
	 * @return mixed|string
     */
	public function GetUserCertificationContent($userCertificationModel){
		$model = LnCertification::findOne($userCertificationModel->certification_id);
		if (!empty($model)) {
			$fileUrl = $model->file_path . 'index.html';

			try {
				$imgSrc = Yii::$app->basePath . '/..' . $fileUrl;

				if (file_exists($imgSrc)) {
					$html = file_get_contents($imgSrc);


					$html = str_replace("href='", "href='" . $model->file_path, $html);
					$html = str_replace("src='", "src='" . $model->file_path, $html);
					$html = str_replace('href="', 'href="' . $model->file_path, $html);
					$html = str_replace('src="', 'src="' . $model->file_path, $html);


//                copy($imgSrc, $imgTarget);
//                $imgSrc = $imgTarget;
//
//                //显示印章
//                if (!empty($model->seal_url)) {
//                    $markImg = Yii::$app->basePath . '/..' . $model->seal_url;//水印图片
//                    if (file_exists($markImg)) {
//                        $fontColor = null;
//                        $fontType = null;//字体
//                        $markType = "img";
//                        $markText = null;
//                        $fontSize = null;
//                        $top = $model->seal_top;
//                        $left = $model->seal_left;
//                        $certificationTemplateService->setWater($imgSrc, $markImg, $markText, $fontSize, $fontColor, $top, $left, $fontType, $markType);
//                    }
//                }
//
//
//                $markImg = null;//水印图片
//                $fontColor = $model->score_color;
//                $fontType = $model->score_font;//字体
//                $markType = "text";
//
					$score = $userCertificationModel->complete_score;
					//显示成绩
					if ($model->is_print_score == LnCertification::IS_PRINT_SCORE_YES) {
						if (empty($score)) {
							$markText = "合格";
						} else {
							$markText = $score . "分";
						}
					} else {
						$markText = "合格";
					}

					$html = str_replace('[:score:]', $markText, $html);


					$grade = $userCertificationModel->complete_grade;
					//显示学分
					if ($model->is_print_score == LnCertification::IS_PRINT_SCORE_YES) {
						if (empty($grade)) {
							$markText = "";
						} else {
							$markText = $grade . "学分";
						}
					}
					else {
						$markText = "";
					}

					$html = str_replace('[:grade:]', $markText, $html);

//                $fontSize = $model->score_size;
//                $top = $model->score_top;
//                $left = $model->score_left;
//                $certificationTemplateService->setWater($imgSrc, $markImg, $markText, $fontSize, $fontColor, $top, $left, $fontType, $markType);
//
//
					//显示颁证日期
					if ($model->is_display_certify_date == LnCertification::IS_DISPLAY_CERTIFY_DATE_YES) {
						$markImg = null;//水印图片
						$fontColor = $model->certify_date_color;
						$fontType = $model->certify_date_font;//字体
						$markType = "text";
						$issued_at = date("Y年m月d日", $userCertificationModel->issued_at);
						$markText = $issued_at;
						$fontSize = $model->certify_date_size;
						$top = $model->certify_date_top;
						$left = $model->certify_date_left;

						$html = str_replace('[:certify_date:]', $markText, $html);
//                    $certificationTemplateService->setWater($imgSrc, $markImg, $markText, $fontSize, $fontColor, $top, $left, $fontType, $markType);
					} else {
						$html = str_replace('[:certify_date:]', "", $html);
					}

					if (!empty($userCertificationModel->course_id)) {
						$courseModel = LnCourse::findOne($userCertificationModel->course_id);
						$courseName = $courseModel->course_name;
						if (!empty($courseName)) {
							$markText = "《" . $courseModel->course_name . "》";
						}
						else {
							$markText = null;
						}
						if (!empty($markText)) {
							$html = str_replace('[:course_name:]', $markText, $html);
						} else {
							$html = str_replace('[:course_name:]', "", $html);
						}
					}
					else {
						$html = str_replace('[:course_name:]', "", $html);
					}
//
					//显示姓名
					$markImg = null;//水印图片
					$fontColor = $model->name_color;
					$fontType = $model->name_font;//字体
					$markType = "text";
					$markText = FwUser::findOne($userCertificationModel->user_id)->real_name;
					$fontSize = $model->name_size;
					$top = $model->name_top;
					$left = $model->name_left;
					$html = str_replace('[:user_name:]', $markText, $html);
//                $certificationTemplateService->setWater($imgSrc, $markImg, $markText, $fontSize, $fontColor, $top, $left, $fontType, $markType);
//
					//显示证书名称
					if (!empty($model->certification_display_name)) {
						$markImg = null;//水印图片
						$fontColor = $model->certification_name_color;
						$fontType = $model->certification_name_font;//字体
						$markType = "text";
						$markText = $model->certification_display_name;
						$fontSize = $model->certification_name_size;
						$top = $model->certification_name_top;
						$left = $model->certification_name_left;
						$html = str_replace('[:certification_display_name:]', $markText, $html);
//                    $certificationTemplateService->setWater($imgSrc, $markImg, $markText, $fontSize, $fontColor, $top, $left, $fontType, $markType);
					} else {
						$html = str_replace('[:certification_display_name:]', "", $html);
					}
//
//
					//显示证书编号
					$markImg = null;//水印图片
					$fontColor = $model->serial_number_color;
					$fontType = $model->serial_number_font;//字体
					$markType = "text";
					$markText = $userCertificationModel->serial_number;
					$fontSize = $model->serial_number_size;
					$top = $model->serial_number_top;
					$left = $model->serial_number_left;
					$html = str_replace('[:serial_number:]', $markText, $html);
//                $certificationTemplateService->setWater($imgSrc, $markImg, $markText, $fontSize, $fontColor, $top, $left, $fontType, $markType);


					//显示备注
					$markText = $model->description;
					$html = str_replace('[:description:]', $markText, $html);


					//显示系统名称
					$markText = Yii::t('system','frontend_name');
					$html = str_replace('[:platform_name:]', $markText, $html);




				} else {
					$html = null;
				}
			} catch (Exception $e) {
				$e;
				$html = null;
			}

			return $html;
		}
		else {
			return null;
		}
	}

	public function saveCertification($params, &$error){

		$certification_template_id = $params['certification_template_id'];
		$lnCertification = new LnCertification();
		$lnCertification->certification_name = Html::encode($params['certification_name']);
		$lnCertification->certification_display_name = Html::encode($params['certification_display_name']);
		$lnCertification->description = Html::encode($params['description']);
		$lnCertification->certification_template_id = $certification_template_id;

		//查找证书模板
		$lnCertificationTemplate = LnCertificationTemplate::findOne($certification_template_id);
		$lnCertification->print_type=$lnCertificationTemplate->print_type;
		$lnCertification->print_orientation=$lnCertificationTemplate->print_orientation;

		$lnCertification->seal_top=$lnCertificationTemplate->seal_top;
		$lnCertification->seal_left=$lnCertificationTemplate->seal_left;

		$lnCertification->name_top=$lnCertificationTemplate->name_top;
		$lnCertification->name_left=$lnCertificationTemplate->name_left;
		$lnCertification->name_size=$lnCertificationTemplate->name_size;
		$lnCertification->name_font=$lnCertificationTemplate->name_font;
		$lnCertification->name_color=$lnCertificationTemplate->name_color;

		$lnCertification->score_top=$lnCertificationTemplate->score_top;
		$lnCertification->score_left=$lnCertificationTemplate->score_left;
		$lnCertification->score_size=$lnCertificationTemplate->score_size;
		$lnCertification->score_font=$lnCertificationTemplate->score_font;
		$lnCertification->score_color=$lnCertificationTemplate->score_color;

		$lnCertification->certify_date_top=$lnCertificationTemplate->certify_date_top;
		$lnCertification->certify_date_left=$lnCertificationTemplate->certify_date_left;
		$lnCertification->certify_date_size=$lnCertificationTemplate->certify_date_size;
		$lnCertification->certify_date_font=$lnCertificationTemplate->certify_date_font;
		$lnCertification->certify_date_color=$lnCertificationTemplate->certify_date_color;

		$lnCertification->certification_name_top=$lnCertificationTemplate->certification_name_top;
		$lnCertification->certification_name_left=$lnCertificationTemplate->certification_name_left;
		$lnCertification->certification_name_size=$lnCertificationTemplate->certification_name_size;
		$lnCertification->certification_name_font=$lnCertificationTemplate->certification_name_font;
		$lnCertification->certification_name_color=$lnCertificationTemplate->certification_name_color;

		$lnCertification->serial_number_top=$lnCertificationTemplate->serial_number_top;
		$lnCertification->serial_number_left=$lnCertificationTemplate->serial_number_left;
		$lnCertification->serial_number_size=$lnCertificationTemplate->serial_number_size;
		$lnCertification->serial_number_font=$lnCertificationTemplate->serial_number_font;
		$lnCertification->serial_number_color=$lnCertificationTemplate->serial_number_color;

		$lnCertification->is_display_certify_date=$lnCertificationTemplate->is_display_certify_date;
		if (empty($lnCertification->certification_display_name)){
			$lnCertification->certification_display_name=$lnCertificationTemplate->certification_display_name;
		}

		if (empty($lnCertification->description)){
			$lnCertification->description=$lnCertificationTemplate->description;
		}

		$lnCertification->seal_url=$lnCertificationTemplate->seal_url;
		$lnCertification->expire_time=$params['expire_time'];
		$lnCertification->expire_time_type=$params['expire_time_type'];

		$lnCertification->is_email_user=$params['is_email_user'];
		$lnCertification->is_email_teacher=$params['is_email_teacher'];
		$lnCertification->is_print_score=$params['is_print_score'];
		$lnCertification->is_auto_certify=$params['is_auto_certify'];

		$company_id=Yii::$app->user->identity->company_id;
		$lnCertification->template_url = $lnCertificationTemplate->template_url;

		$lnCertification->company_id=$company_id;

		$zipUrl = Yii::$app->basePath.'/..' . $lnCertification->template_url;


		$filePath =  str_replace("/certification-template/","/certification/",$lnCertificationTemplate->file_path);
		TFileHelper::unzip($zipUrl,Yii::$app->basePath.'/..' .$filePath);

//                    substr($filePath,0,strlen($filePath)-4) . 'merge.'.substr($filePath,strlen($filePath)-3);
		$lnCertification->file_path = $filePath;
//		$lnCertification->certification_img_url = $this->MergeCertificationTemplate($lnCertification);

        if ($lnCertification->save()) {
            return true;
        }
        else {
            $error = $lnCertification->getErrors();
			return false;
            //var_dump($lnCertification->getErrors());
        }

	}


	public function getTemplates(){
		$company_id=Yii::$app->user->identity->company_id;
		//本公司独享+其他所有公司共享
		return LnCertificationTemplate::find(false)
		   ->andWhere("(company_id ='".$company_id."' and share_flag='0') or (share_flag='1')")

		   ->all();
	}

	public function getCertification($id){
		 return LnCertification::find(false)
		    ->andWhere(LnCertification::tableName().".kid ='".$id."'")
		    ->leftjoin('{{%ln_certification_template}} as t1','t1.kid = '.LnCertification::tableName().".certification_template_id")
		    ->select(LnCertification::tableName().".*,t1.template_name,t1.template_url")
		    ->asArray()
		    ->one();
	}


	public function nameValidate($params){
		$lnCertification=LnCertification::find(false)
		->andFilterWhere(["=","certification_name",$params['name']])
		->asArray()
		->all();
		if(count($lnCertification)>0){
			return true;
		}else{
			return false;
		}
	}

	public function search($params)
	{

		$query = LnCertification::find(false);
		
		if (isset($params['keyword'])) {
			$keyword = $params['keyword'];
			$keyword = trim($keyword);
		}
		else {
			$keyword = "";
		}
		
		if($keyword){
			$query->andWhere("certification_name like '%{$keyword}%'");
		}

		$query
		->andFilterWhere(["=","company_id",Yii::$app->user->identity->company_id]);

		$dataProvider = new ActiveDataProvider([
				'query' => $query,
		]);

		$this->load($params);



		$dataProvider->setSort(false);
		$query->addOrderBy(['created_at' => SORT_DESC]);
		/*echo ($query->createCommand()->getRawSql());*/
		return $dataProvider;
	}

	/**
	 * @param $params
	 * @return array|\yii\db\ActiveRecord[]
	 */
	public function courseSearchCertification($params)
	{
		if (empty($params)) return false;
		$query = LnCertification::find(false);
		$keyword=$params['keyword'];
		if($keyword){
			$query->andWhere("certification_name like '%{$keyword}%'");
		}
		if (isset($params['is_auto_certify'])){
			$query->andFilterWhere(['=','is_auto_certify',$params['is_auto_certify']]);
		}
		/*判断有效期*/
//		$query->andWhere("(expire_time_type = '2' OR (expire_time_type='1' AND ((UNIX_TIMESTAMP(expire_time) + 86399) > UNIX_TIMESTAMP())) OR (expire_time_type='0' and (created_at + expire_time*86400 > UNIX_TIMESTAMP())))");
		$query->andWhere("(expire_time_type='0' OR expire_time_type = '2' OR (expire_time_type='1' AND ((UNIX_TIMESTAMP(expire_time) + 86399) > UNIX_TIMESTAMP())))");
		if (empty($params['companyId'])){
			$companyId = Yii::$app->user->identity->company_id;
		}else{
			$companyId = $params['companyId'];
		}
		$result = $query->andFilterWhere(["=","company_id",$companyId])
			->addOrderBy(['created_at' => SORT_DESC])
			->select(['kid','certification_name'])
			->asArray()
			->all();
		/*echo ($query->createCommand()->getRawSql());*/
		return $result;
	}
	
	public function search_pub($params){
		$params['size']=CertificationService::SIZE;
		
		$keyword=$params['keyword'];
		$valid_status=$params['valid_status'];
		$created_channel=$params['created_channel'];
		
		
		$query_tmp = LnUserCertification::find(false)
			->andWhere(LnUserCertification::tableName().".certification_id ='".$params['certification_id']."'")
			->andWhere(LnUserCertification::tableName().".status ='".CertificationService::STATUS_FLAG_NORMAL."'")
			->andWhere(LnUserCertification::tableName().".valid_status ='".$valid_status."'");
		
		if($created_channel!=LnCertification::ALL_CREATED_CHANNEL){
			$query_tmp->andWhere(LnUserCertification::tableName().".created_channel ='".$created_channel."'");
		}
		
		$query=$query_tmp
			->orderBy('created_at desc' )
		    ->select("created_at,user_id,issued_at,valid_status,kid,certification_from");
		
		$query_tmp=$query ->createCommand ()->rawSql;
		
		$sql_user_info=FwUserDisplayInfo::tableName();
		if($keyword){
			$sql_user_info="(select user_id,real_name,orgnization_name,position_name,user_name from  ".FwUserDisplayInfo::tableName()." where  real_name like '%$keyword%')";
			$query_tmp="select t1.* from (" .$query_tmp.") t1  join ".$sql_user_info." u_info on  t1.user_id=u_info.user_id ";
		}
		
		$sql_count = "select count(1) as c from ($query_tmp) tt ";
		$db = \Yii::$app->db;
		$count_ = $db->createCommand ( $sql_count )->queryAll ();
		$count = $count_ [0] ['c'];
		$pages = new Pagination ( [
				'defaultPageSize' => $params ['size'],
				'totalCount' => $count
		] );
		
		$result ['pages'] = $pages;
	
		
		$result_sql_tmp=$query_tmp. " limit $pages->offset,$pages->limit";
		
		$result_sql = "select t1.*,user.real_name,user.user_name,user.orgnization_name,user.position_name 
				from ( " . $result_sql_tmp . ") t1  join " . $sql_user_info. "  user on t1.user_id=user.user_id 
				  order by created_at desc";
		
		$sub_result_arr = $db->createCommand ( $result_sql )->queryAll ();
		$datas = [ ];
		
		foreach ( $sub_result_arr as $ch ) {
			if ($ch ['issued_at'] != null) {
				$ch ['issued_at'] = date ( "Y年m月d日 H:m:s", $ch ['issued_at'] );
			} else {
				$ch ['issued_at'] = "";
			}
			
			if ($ch ['valid_status'] ==LnCertification::HISTORY_CERTIFI_STATUS) {
				$ch ['valid_status'] = Yii::t('frontend', 'cer_no_effective') ;
			} else {
				$ch ['valid_status'] =  Yii::t('frontend', 'cer_effective');
			}
			
			array_push ( $datas, $ch );
		}
		
		$result ['data'] = $datas;
		
		return $result;
	}


	public function search_pub_bak($params){

		//$t1_sql="(select fu.*,org.orgnization_name from {{%fw_user}} fu left join {{%fw_orgnization}} org on (fu.orgnization_id=org.kid and fu.is_deleted='0' and org.is_deleted='0'))";
		//$t2_sql="(select u_pos.*,pos.position_name from {{%fw_user_position}} u_pos left join {{%fw_position}} pos on (u_pos.position_id=pos.kid and u_pos.is_deleted='0' and pos.is_deleted='0'))";

		$query = LnUserCertification::find(false)
						->andWhere(LnUserCertification::tableName().".certification_id ='".$params['certification_id']."'")
						->andWhere(LnUserCertification::tableName().".status ='".CertificationService::STATUS_FLAG_NORMAL."'")
						->innerJoin(FwUserDisplayInfo::tableName().' as t1','t1.user_id = '.LnUserCertification::tableName().".user_id")
						
						->innerJoin(FwUserDisplayInfo::tableName().' as t3','t3.user_id = '.LnUserCertification::tableName().".issued_by")
						;

		$keyword=$params['keyword'];
		if($keyword){
			$query->andWhere("t1.real_name like '%{$keyword}%'");
		}

		$query->select(LnUserCertification::tableName().".kid,".LnUserCertification::tableName().".created_at
				,t1.real_name,t1.orgnization_name,t1.position_name,t3.real_name as real_name1");


		$dataProvider = new ActiveDataProvider([
				'query' => $query,
		]);

		$this->load($params);



		$dataProvider->setSort(false);
		$query->addOrderBy([LnUserCertification::tableName().'.created_at' => SORT_DESC]);
		$query->asArray();
		//echo ($query->createCommand()->getRawSql());
		return $dataProvider;

	}

	public  function getAllUsers($certification_id){
		$userDomainService = new UserDomainService();
		$user_id = Yii::$app->user->getId();
		$domainIds = $userDomainService->getSearchListByUserId($user_id);
		$company_id= Yii::$app->user->identity->company_id;

		$sub_sql="";


		if (isset($domainIds) && $domainIds != null) {
			$domainIds = ArrayHelper::map($domainIds, 'kid', 'kid');

			$domainIds = array_keys($domainIds);
		}
		foreach ($domainIds as $domain_id){

			$sub_sql.="'".$domain_id."'".",";
		};
		$sub_sql=trim($sub_sql,',');

		$sql="select kid as select_id from ".FwUser::tableName()." t where "
				."  not exists (select * from ".LnUserCertification::tableName()." ucr where t.kid=ucr.user_id and ucr.status='1'  and ucr.certification_id='".$certification_id."') "
				." and  t.company_id='".$company_id."' "." and t.domain_id in (".$sub_sql.")";

		$query=MsSelectTemp::findBySql($sql);
		$data= $query->asArray()
		->all();



		return $data;

	}

	public  function getUsers()
	{

		$user_id = Yii::$app->user->getId();

		$userDomainService = new UserDomainService();
		$domainIds = $userDomainService->getSearchListByUserId($user_id);

		$searchDbInforItem = Yii::$app->request->getQueryParam('q');
		$uuid = Yii::$app->request->getQueryParam('user_uuid');
		$certification_id = Yii::$app->request->getQueryParam('certification_id');
		$company_id = Yii::$app->user->identity->company_id;


		$sub_sql = "";

		if (isset($domainIds) && $domainIds != null) {
			$domainIds = ArrayHelper::map($domainIds, 'kid', 'kid');

			$domainIds = array_keys($domainIds);
		}
		foreach ($domainIds as $domain_id) {

			$sub_sql .= "'" . $domain_id . "'" . ",";
		};
		$sub_sql = trim($sub_sql, ',');
		
		$sql_search='';
		if (trim($searchDbInforItem) != '') {
			$sql_search = " and real_name like '%" . $searchDbInforItem . "%'";
		}

		$sql = "select * from (select fus.real_name, fus.user_id as kid, fus.orgnization_name from " 
				. FwUserDisplayInfo::tableName() . " fus where  company_id='" . $company_id . "' and domain_id in (" . $sub_sql . ") " .$sql_search
				. " ) t where not exists " . " (select * from " . MsSelectTemp::tableName() . " p where t.kid=p.select_id and p.mission_id= '" . $uuid . "')"
				. " and not exists (select * from " . LnUserCertification::tableName() . " ucr where t.kid=ucr.user_id and ucr.status='1'  and ucr.certification_id='" . $certification_id . "') "				
				;
		

		$query = MsSelectTemp::findBySql($sql);
		$data = $query->asArray()
				->all();


		return $data;

	}

	public function deleteCertification($id){

		$lnCertification=new LnCertification();

		$delModel=$lnCertification->findOne($id);

		$delModel->delete();

	}

	public function cancelCertificationUser($id,$mission_id){

		$model=LnUserCertification::findOne($id);

		$messageService=new MessageService();
		$messageService->deleteSelected($model->user_id, $mission_id);

		$lnUserCertification=new LnUserCertification();

		$attributes = [

				'status' =>CertificationService::STATUS_DISABLE,

		];

		$lnUserCertification->updateAll($attributes,"kid = '".$id."'");

		$recordService=new RecordService();
		$recordService->addByCancelCertification($model->user_id, $model->certification_id, $model->kid);
		
		//取消积分
		$pointRuleService=new PointRuleService();
		$userInfo = FwUser::find(false)->andFilterWhere(['kid'=>$model->user_id])->select('company_id')->one();
		$pointRuleService->checkActionForPoint($userInfo->company_id,$model->user_id,'Revoke-Certification','Certification',$model->certification_id);
	}

    /**
     * 学习管理员颁发证书
     * @param $params
     */
    public function saveCertificationUsers($params){
        $users=null;
        //所有人
        if("1"==$params['all_users_chk']){
            $users=$this->getAllUsers($params['certification_id']);
        }else{
            //选择人的时候
            //$users=MsSelectTemp::find(false)
// 			->andFilterWhere(["=","mission_id",$params['mission_id']])
// 			->asArray()
// 			->all();

            $users=$params['pub_user_list_tmp'];
        }

        $certification_id = $params['certification_id'];


        //$users=$params['users'];
        $issuedBy = Yii::$app->user->getId();

		$certificationFrom = Yii::$app->user->identity->real_name;


        foreach ($users as $user) {
            $userId = $user['select_id'];

            $completeScore = null;
			$completeGrade = null;

			
            $userCertificationId = $this->createUserCertification($certification_id,$userId,$issuedBy,$completeScore,$completeGrade,null,$certificationFrom);
        }
        
        //获得积分
        $pointRuleService=new PointRuleService();
        foreach ($users as $user) {
        	$userInfo = FwUser::find(false)->andFilterWhere(['kid'=>$user['select_id']])->select('company_id')->one();
        	$pointRuleService->checkActionForPoint($userInfo->company_id,$user['select_id'],'Get-Certification','Certification',$certification_id);
        	
        }


    }

	/**
	 * @param $userId
	 * @param $cert LnCertification
	 * @param $lnUserCertification LnUserCertification
	 * @return bool
     */
	public function sendEmailToUser($userId, $cert, $lnUserCertification)
	{
		$emailSwitch = false;
		if (isset(Yii::$app->params['email_switch'])) {
			$emailSwitch = Yii::$app->params['email_switch'];
		}

		if ($emailSwitch) {
			$user = FwUser::findOne($userId);
			if ($user && !empty($user->email)) {
				$userService = new UserService();
				if ($userService->isEmailRepeat($user->email)) {
					return false;
				}
				else {
					$message = Yii::t('common', 'certify_content_{value}_user_{number}',
						['value' => $cert->certification_name, 'number' => $lnUserCertification->serial_number]);


					return Yii::$app->mailer->compose(['html' => 'certificationToUser-html', 'text' => 'certificationToUser-text'],
						['user' => $user, 'message' => $message])
						->setFrom([Yii::$app->params['supportEmail'] => Yii::t('system', 'system_robot')])
						->setTo($user->email)
						->setSubject(Yii::t('system', 'frontend_name') . '-' . Yii::t('common', 'certify_subject'))
						->send();
				}
			} else
				return false;
		} else
			return false;
	}

	/**
	 * 给用户发微信消息
	 * @param string $userId 用户ID
	 * @param LnCertification $cert 证书模型
	 * @param LnUserCertification $lnUserCertification 用户证书信息
	 * @return bool 成功与否
     */
	public function sendWechatToUser($userId, $cert, $lnUserCertification)
	{
		$user = FwUser::findOne($userId);
		$companyId = $user->company_id;
		$wechatTemplateService = new WechatTemplateService();
		$templateCode = "";
		$templateModel = $wechatTemplateService->getWechatTemplateByCode($companyId,$templateCode);
		if (!empty($templateModel)) {
			$templateUrl = null;
			$templateId = $templateModel->wechat_template_id;
			$wechatService = new WechatService();
			$model = $wechatService->getWechatAccount($userId);
			if (!empty($model) && !empty($model->open_id)) {
				$toUserId = $model->open_id;
				$data = [
					"certificationName" => [
						"value" => $cert->certification_name,
						"color" => "#173177"
					],
					"certificationNo" => [
						"value" => $lnUserCertification->serial_number,
						"color" => "#173177"
					],
					"certificationTime" => [
						"value" => TTimeHelper::toDate($lnUserCertification->issued_at),
						"color" => "#173177"
					],
					"remark" => [
						"value" => '请保持努力，再接再厉！',
						"color" => "#173177"
					]
				];

				$wechatService->sendMessageByTemplate($companyId, $toUserId, $templateId, $templateUrl, $data, $result, $errMessage);
			}

			return true;
		}
		else {
			return true;
		}
	}

	public function sendEmailToTeacher($courseId,$userList,$teacherId, $cert)
	{
		$emailSwitch = false;
		if (isset(Yii::$app->params['email_switch'])){
			$emailSwitch = Yii::$app->params['email_switch'];
		}

		if ($emailSwitch) {
			$teacher = LnTeacher::findOne($teacherId);
			if (!empty($teacher->user_id)) {
				$teacherUser = FwUser::findOne($teacher->user_id);
				$course = LnCourse::findOne($courseId);
				if (!empty($teacherUser->email)) {
					$userService = new UserService();
					if ($userService->isEmailRepeat($teacherUser->email)) {
						return false;
					}
					else {
						return Yii::$app->mailer->compose(['html' => 'certificationToTeacher-html', 'text' => 'certificationToTeacher-text'],
							['course' => $course, 'teacher' => $teacherUser, 'userList' => $userList, 'cert' => $cert])
							->setFrom([Yii::$app->params['supportEmail'] => Yii::t('system', 'system_robot')])
							->setTo($teacherUser->email)
							->setSubject(Yii::t('system', 'frontend_name') . '-' . Yii::t('common', 'certify_subject'))
							->send();
					}
				}
			}
		}
		else
			return false;
	}

	/**
	 * 获取课程相关所有证书
	 * @param $courseId
	 * @return array|LnCertification[]
	 */
	public function getCertificationListByCourseId($courseId)
	{
		$courseIdSql = LnCourseCertification::find(false)
			->andFilterWhere(['=','course_id',$courseId])
			->andFilterWhere(['=','status',LnCourseCertification::STATUS_FLAG_NORMAL])
			->select('certification_id')
			->distinct()
			->createCommand()
			->getRawSql();

		return  LnCertification::find(false)
			->andWhere('kid in (' . $courseIdSql . ')')
			->all();
	}

	/**
	 * 获取历史证书
	 * @param $certificationId
	 * @param $userId
	 * @return array|null|\yii\db\ActiveRecord
     */
	public function getHistoryUserCertification($certificationId, $userId){
		$lnUserCertification = new LnUserCertification();
		$result = $lnUserCertification->find(false)
			->andFilterWhere(['=','certification_id', $certificationId])
			->andFilterWhere(['=','user_id', $userId])
			->andFilterWhere(['=','status', LnUserCertification::STATUS_FLAG_NORMAL])
			->one();

		return $result;
	}

    /**
     * 颁发用户证书
     * @param $certificationId
     * @param $userId
     * @param $score
     * @return null|string
     */
    public function createUserCertification($certificationId,$userId,$issuedBy,$completeScore,$completeGrade,$courseId = null,$certificationFrom = null,$systemKey = null)
	{
		$userCertificationId = null;
		$lnUserCertification = null;
		$model = LnCertification::findOne($certificationId);
		if (!empty($model)) {
			$result = $this->getHistoryUserCertification($certificationId, $userId);
			if (!empty($result)) {
				$result->valid_status = LnUserCertification::VALID_STATUS_HISTORY;
				$result->systemKey = $systemKey;
				$result->save();
			}

			$currentTime = time();
//			$issued_at = date("Y年m月d日", $currentTime);

			$tempNo = strval($currentTime) . "-" . str_pad(strval(mt_rand(1, 999)), 3, "0", STR_PAD_LEFT);
//			$num = md5($tempNo);
			$serial_number = "ELN-" . $tempNo;

			$lnUserCertification = new LnUserCertification();

			$lnUserCertification->certification_id = $certificationId;
			$lnUserCertification->user_id = $userId;
			$lnUserCertification->complete_score = $completeScore;
			$lnUserCertification->complete_grade = $completeGrade;
			$lnUserCertification->serial_number = $serial_number;
			$lnUserCertification->course_id = $courseId;
			$lnUserCertification->issued_at = $currentTime;
			$lnUserCertification->issued_by = $issuedBy;
			$lnUserCertification->start_at = $currentTime;
			$lnUserCertification->valid_status = LnUserCertification::VALID_STATUS_CURRENT;
			$lnUserCertification->certification_from = $certificationFrom;

			if ($courseId != null){
				
				$cerCourse=LnCourse::findOne($courseId);
				if($cerCourse->course_type==LnCourse::COURSE_TYPE_ONLINE){
					$lnUserCertification->created_channel = LnUserCertification::CREATED_CHANNEL_COURSE;		
				}else{
					$lnUserCertification->created_channel = LnUserCertification::CREATED_CHANNEL_MANUAL;					
				}
				
			}
			else {
				$lnUserCertification->created_channel = LnUserCertification::CREATED_CHANNEL_MANUAL;
			}

			//天数
			if ($model->expire_time_type == LnCertification::EXPIRE_TIME_TYPE_DATE) {
				$expire_time = $model->expire_time;
				$lnUserCertification->end_at = strtotime($expire_time . " 23:59:59");
			} else if ($model->expire_time_type == LnCertification::EXPIRE_TIME_TYPE_DAY) {
				$expireDay = $model->expire_time;
				$expire_time = $currentTime + (intval($expireDay) * 24 * 60 * 60);
				$lnUserCertification->end_at = $expire_time;
			} else if ($model->expire_time_type == LnCertification::EXPIRE_TIME_TYPE_NEVER) {
				$lnUserCertification->end_at = null;
			}

			$lnUserCertification->certification_name = $model->certification_name;
			$lnUserCertification->certification_type = LnUserCertification::CERTIFICATION_TYPE_SYSTEM;

			$lnUserCertification->status = CertificationService::STATUS_NORMAL;
			$lnUserCertification->systemKey = $systemKey;
			$lnUserCertification->needReturnKey = true;
			if ($lnUserCertification->save()) {
				$userCertificationId = $lnUserCertification->kid;
			}
		}

		if ($userCertificationId != null) {
			//邮件通知学员
			if ($model->is_email_user == LnCertification::IS_EMAIL_USER_YES) {
				$this->sendEmailToUser($userId, $model, $lnUserCertification);
				$this->sendWechatToUser($userId, $model, $lnUserCertification);
			}

			$recordService = new RecordService();
			$recordService->addByCertification($userId, $certificationId, $userCertificationId, $systemKey);
		}

		/*添加积分*/
		/*	$pointRuleService = new PointRuleService();
            $user = FwUser::find(false)->andFilterWhere(['kid'=>$userId])->select('company_id')->one();
            $companyId = $user->company_id;
            $pointRuleService->checkActionForPoint($companyId, $userId, 'Get-Certification', 'Certification', $courseId);*/

		return $userCertificationId;
	}

    /**
     * 教师颁发证书
     * @param $courseCert
     * @param $users
     * @param $courseId
     */
    public function teacherCertificationUsers($courseCert,$users,$courseId){
        $certification_id = $courseCert['certification_id'];
        $issuedBy = Yii::$app->user->getId();

		if (!is_array($users)) $users = array($users);
		$courseModel = LnCourse::findOne($courseId);
		$certificationFrom = $courseModel->course_name;

		$userList = [];
        foreach ($users as $u) {
            $userId = $u;


            $scoreObj = LnCourseComplete::find(false)
				->andFilterWhere(['or',['=', 'complete_status', LnCourseComplete::COMPLETE_STATUS_DONE],
					['=', 'is_retake', LnCourseComplete::IS_RETAKE_YES]])
				->andFilterWhere(['=','complete_type',LnCourseComplete::COMPLETE_TYPE_FINAL])
				->andFilterWhere(['=','course_id',$courseId])
				->andFilterWhere(['=','user_id',$userId])
				->one();

            $completeScore = ($scoreObj && $scoreObj->complete_score) ? $scoreObj->complete_score : null ;

			$completeGrade = ($scoreObj && $scoreObj->complete_grade) ? $scoreObj->complete_grade : null ;



            $userCertificationId = $this->createUserCertification($certification_id,$userId,$issuedBy,$completeScore,$completeGrade,$courseId,$certificationFrom);

			if (!empty($userCertificationId)) {
				array_push($userList, $userId);
				/*添加积分*/
				$pointRuleService = new PointRuleService();
				$user = FwUser::find(false)->andFilterWhere(['kid'=>$userId])->select('company_id')->one();
				$companyId = $user->company_id;
				$pointRuleService->checkActionForPoint($companyId, $userId, 'Get-Certification', 'Certification', $courseId);
			}
        }


		if (!empty($courseId)) {
			$certificationModel = LnCertification::findOne($certification_id);
			//邮件通知讲师
			if ($certificationModel->is_email_teacher == LnCertification::IS_EMAIL_TEACHER_YES) {
				$t = new LnCourse();
				if ($teacher = $t->getLnCourseTeacher($courseId)) {
					$teacherId = $teacher['kid'];
					$this->sendEmailToTeacher($courseId,$userList, $teacherId, $certificationModel);
				}
			}
		}
    }
	
	
}