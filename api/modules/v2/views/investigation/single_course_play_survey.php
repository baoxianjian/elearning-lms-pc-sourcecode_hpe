<?php
use yii\helpers\Html;
use components\widgets\TLinkPager;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use components\widgets\TBreadcrumbs;
?>

<?= Html::jsFile('/static/mobile/proto/assets/js/main.js') ?>
<?= Html::cssFile('/static/mobile/proto/assets/css/app.css') ?>
<?= Html::hiddenInput("currentModResId",$modResId,['id'=>'currentModResId'])?>
<?= Html::hiddenInput("currentCoursewareId",$coursewareId,['id'=>'currentCoursewareId'])?>
<?= Html::hiddenInput("currentCourseId",$courseId,['id'=>'currentCourseId'])?>
<?= Html::hiddenInput("currentComponentCode",$componentCode,['id'=>'currentComponentCode'])?>
<style>.lesson-btn{display: none;}</style>
<div data-am-widget="tabs" class="am-tabs am-tabs-d2">
	<ul class="am-tabs-nav am-cf">
		<li class="am-active p10"><a href="#" id="survey-title"></a></li>
	</ul>
	<div class="am-tabs-bd">
		<div data-tab-panel-0 class="am-tab-panel am-active p0">
			<div class="am-list-item-text">
				<ul class="course-chapter" id="survey-container">

				</ul>
			</div>
		</div>
	</div>
</div>
<div class="lesson-btn am-cf m10">
	<button type="button"  id="prev-page" class="am-btn am-btn-primary am-btn-xs fr" >上一步</button>
	<button type="button" id="next-page" class="am-btn am-btn-primary am-btn-xs fr" >下一步</button>
</div>

<div class="lesson-btn am-cf m10">
	<button type="button" id="submit" class="am-btn am-btn-primary am-btn-xs fr">提 交</button>

</div>
<div class="lesson-btn am-cf lesson-btn-fix">
	<button type="button" id="preview-result" class="am-btn am-btn-success am-btn-xs fr">查看统计结果</button>
</div>

<!-- 成功弹出框 -->
<div class="am-modal am-modal-no-btn" tabindex="-1" id="survey_success">
	<div class="am-modal-dialog">
		<div class="am-modal-hd">您已完成本次调查!
<!--			<a href="javascript: void(0)" class="am-close am-close-spin" data-am-modal-close>&times;</a>-->
		</div>
		<div class="am-modal-bd">
			<hr>
			<a href="#" id="go-preview-result" class="am-btn am-btn-success am-btn-md" style="width: 60%;">查看历史记录</a>
<!--			<a href="#" id="go-todo-list" class="am-btn am-btn-primary am-btn-md" style="width: 60%; margin-top: 10px;" >返回待办事项</a>-->

		</div>
	</div>
</div>
<div class="am-popup" id="my-popup">
	<div class="am-popup-inner">
		<div class="am-popup-hd">
			<h4 class="am-popup-title">调查统计结果</h4>
			<span data-am-modal-close="" class="am-close">×</span>
		</div>
		<div class="am-list-news-bd">
			<ul class="course-chapter" id="survey-result">

			</ul>
		</div>
	</div>
</div>

<script type="text/template" id="item-radio">
	<li style="<%=display%>" data-page_position="<%=calcPage%>" data-item_type="radio" data-question_title="<%=title%>" data-question_id="<%=qid%>" data-question_type="0">
		<ul class="course-sections">
			<p class="course-des"><strong><%= index%>. <%= title%></strong></p>
			<%_.each(options,function(item){%>
			<li class="options">
				<label for="<%=item.kid%>"><input <%=item.isCheck%> <%=disabled?'disabled':''%> name="<%=qid%>" id="<%=item.kid%>" type="radio" value="<%=item.kid%>" data-title="<%= item.option_title%>"><%= item.option_title%></label>
			</li>
			<%});%>
		</ul>
	</li>
</script>

<script type="text/template" id="item-checkbox">
	<li style="<%=display%>" data-page_position="<%=calcPage%>" data-item_type="checkbox" data-question_title="<%=title%>" data-question_id="<%=qid%>" data-question_type="1">
		<ul class="course-sections">
			<p class="course-des"><strong><%= index%>. <%= title%></strong></p>
			<%_.each(options,function(item){%>
			<li class="options">
				<label for="<%=item.kid%>"><input type="checkbox" <%=disabled?'disabled':''%> value="<%=item.kid%>" id="<%=item.kid%>" name="<%=qid%>" data-title="<%= item.option_title%>" <%=item.isCheck%>><%= item.option_title%></label>
			</li>
			<%});%>
		</ul>
	</li>
</script>

<script type="text/template" id="item-input">
	<li style="<%=display%>" data-page_position="<%=calcPage%>" data-item_type="input" data-question_title="<%=title%>" data-question_id="<%=qid%>" data-question_type="2">
		<ul class="course-sections">
			<p class="course-des"><strong><%= index%>. <%= title%></strong></p>
			<li class="options">
				<textarea name="<%=qid%>" id="<%=qid%>" placeholder="<%= placeholder%>" <%=readonly?'readonly':''%>><%=item.option_result%></textarea>
			</li>
		</ul>
	</li>
</script>

<script type="text/template" id="item-result">
	<li>
		<ul class="course-sections">
			<p class="course-des"><strong><%=index%>. <%=item.question_title%></strong></p>
			<%_.each(item.options,function(item){%>
			<li class="options">
				<label for="quest101"><%=item.option_title%></label>
				<div class="am-progress">
					<div class="am-progress-bar" style="width: <%=item.submit_num_rate/total * 100 %>%"></div>
					<p class="progress-number"><%=item.submit_num_rate/total * 100 %>%</p>
				</div>
			</li>
			<%});%>
		</ul>
	</li>
</script>

<script>
	var STAND = <?php echo $stand ? 'true' : 'false';?>;
	var CONST = {
		INVESTIGATION_ID : '<?php echo $id?>',
		SYSTEM_KEY : '<?php echo $system_key?>',
		ACCESS_TOKEN : '<?php echo $access_token?>',
		ATTEMPT : '<?php echo $attempt?>',
		CSRF : '<?php echo Yii::$app->request->getCsrfToken()?>'
	};
	var COURSE = {};

	<?php if(!$stand){?>
	COURSE.COURSE_ID = '<?=$courseId?>';
	COURSE.COURSE_REG_ID = '<?=$course_reg_id?>';
	COURSE.MOD_ID = '<?=$mod_id?>';
	COURSE.MOD_RES_ID = '<?=$modResId?>';
	COURSE.COURSE_ACTIVITY_ID = '<?=$courseactivity_id?>';
	COURSE.COMPONENT_ID = '<?=$component_id?>';
	COURSE.COURSE_COMPLETE_ID = '<?=$courseCompleteFinalId?>';
	COURSE.COURSE_COMPLETE_PROCESS_ID = '<?=$courseCompleteProcessId?>';
	<?php }?>

	function getUrl(type) {
		var stand_urls = {
			'init' : "<?php echo Url::toRoute(['investigation/get-single-play-investigation-submit-result'])?>",
			'data' : '<?php echo Url::toRoute(['investigation/get-survey'])?>',
			'submit' : '<?php echo Url::toRoute(['investigation/single-investigation-submit-result','system_key'=>$system_key,'access_token'=>$access_token])?>',
			'history' : '<?php echo Url::toRoute(['investigation/get-single-sub-survey-result',])?>',
			'result' : '<?php echo Url::toRoute(['investigation/single-course-play-survey-result',])?>?dataType=json'
		};
		var urls = {
			'init' : "<?php echo Url::toRoute(['investigation/get-play-investigation-submit-result'])?>",
			'data' : stand_urls['data'],
			'submit' : '<?php echo Url::toRoute(['investigation/investigation-submit-result','system_key'=>$system_key,'access_token'=>$access_token])?>',
			'history' : '<?php echo Url::toRoute(['investigation/get-sub-survey-result',])?>',
			'result' : '<?php echo Url::toRoute(['investigation/course-play-survey-result',])?>?dataType=json',
			'courseComplete' : '<?php echo Url::toRoute(['investigation/play-investigation-res-complete',])?>'
		};
		var url = STAND ? stand_urls[type] : urls[type];
		var queryString = 'id={INVESTIGATION_ID}&investigation_id={INVESTIGATION_ID}&system_key={SYSTEM_KEY}&access_token={ACCESS_TOKEN}'
			.replace(/\{INVESTIGATION_ID\}/g,CONST.INVESTIGATION_ID)
			.replace(/\{SYSTEM_KEY\}/g,CONST.SYSTEM_KEY)
			.replace(/\{ACCESS_TOKEN\}/g,CONST.ACCESS_TOKEN);
		if(!STAND) {
			queryString += '&mod_id={MOD_ID}&course_id={COURSE_ID}&mod_res_id={MODE_RES_ID}&course_complete_id={COURSE_COMPLETE_ID}&course_reg_id={COURSE_REG_ID}&course_complete_process_id={COURSE_COMPLETE_PROCESS_ID}'
				.replace(/\{MOD_ID\}/g,COURSE.MOD_ID)
				.replace(/\{COURSE_ID\}/g,COURSE.COURSE_ID)
				.replace(/\{MODE_RES_ID\}/g,COURSE.MOD_RES_ID)
				.replace(/\{COURSE_COMPLETE_ID\}/g,COURSE.COURSE_COMPLETE_ID)
				.replace(/\{COURSE_COMPLETE_PROCESS_ID\}/g,COURSE.COURSE_COMPLETE_PROCESS_ID)
				.replace(/\{COURSE_REG_ID\}/g,COURSE.COURSE_REG_ID);
		}
		var delimiter = url.indexOf('?') > -1 ? '&' : '?';
		return url + delimiter + queryString;
	}
	function Course() {
		this.completeType = -1;
		this.isStand = true;
	}
	Course.prototype.setStand = function(isStand) {
		this.isStand = isStand;
	};
	Course.prototype.complete = function(type) {
		this.completeType = type;
		var url = getUrl('courseComplete');
		if( ! this.isStand) {
			setTimeout(function(){
				$.get(url + "&complete_type=0",function(){});
			},100);
			setTimeout(function(){
				$.get(url + "&complete_type=1",function(){});
			},200);
			setTimeout(function(){
				//location.reload();
			},300);
		} else {
			console.log('stand survey');
		}
	};
	function Template(selector) {
		this.itemRadio = null;
		this.itemCheckbox = null;
		this.itemInput = null;
		this.itemResult = null;
		this.selector = typeof selector == 'string' ? $(selector) : selector;
		this.next = $("#next-page");
		this.prev = $("#prev-page");
		this.submitBtn = $("#submit");
		this.resultSelector = $("#survey-result");
		this.surveyState = false;
		this.init();
	}
	Template.prototype.init = function() {
		this.itemRadio = $("#item-radio").html();
		this.itemCheckbox = $("#item-checkbox").html();
		this.itemInput = $("#item-input").html();
		this.itemResult = $("#item-result").html();
	};
	Template.prototype.setTitle = function(txt) {
		$("#survey-title").html(txt);
	};
	Template.prototype.makeItemRadio = function(item,index,display,calcPage) {
		var html = _.template(this.itemRadio)({
			index:index,
			qid:item.id,
			title:item.question_title,
			options:item.options,
			display:display,
			calcPage:calcPage,
			disabled : this.surveyState
		});
		this.selector.append(html);
	};
	Template.prototype.makeItemCheckbox = function(item,index,display,calcPage) {
		var html = _.template(this.itemCheckbox)({
			index:index,
			qid:item.id,
			title:item.question_title,
			options:item.options,
			display:display,
			calcPage:calcPage,
			disabled : this.surveyState
		});
		this.selector.append(html);
	};
	Template.prototype.makeItemInput = function(item,index,display,calcPage) {
		var html = _.template(this.itemInput)({
			index:index,
			qid:item.id,
			title:item.question_title,
			item:item,
			placeholder:item.question_description,
			display:display,
			calcPage:calcPage,
			readonly : this.surveyState
		});
		this.selector.append(html);
	};
	Template.prototype.makePagination = function(current,total,surveied) {
		var showPrev = current > 1,showNext = current < total,showSubmit = current == total;
		this.next.css('display',showNext && !surveied ?'block':'none');
		this.prev.css('display',showPrev && !surveied ?'block':'none');
		this.submitBtn.css('display',showSubmit && !surveied ?'block':'none');
		if(!surveied) {
			$("li[data-page_position]").hide();
			$("li[data-page_position='"+current+"']").show();
		}
	};
	Template.prototype.itemVisible = function(p1,p2,surveied) {
		return p1 == p2 || surveied ? 'display:block' : 'display:none';
	};
	Template.prototype.toggleResultBtn = function(surveied,stand) {
		var btn = $("#preview-result");
		if(surveied) {
			this.submitBtn.hide();
		} else {
			this.submitBtn.show();
		}
		if(surveied && stand) btn.show();
		else btn.hide();
	};
	Template.prototype.makeResultItem = function(index,item,total) {
		var html = _.template(this.itemResult)({index: index, item: item,total:total});
		this.resultSelector.append(html);
	};
	Template.prototype.submitState = function(loading) {
		this.submitBtn.attr("disabled",loading);
		this.submitBtn.text(loading ? '正在处理' : '提交');
	};
	Template.prototype.setSurveyState = function(state) {
		this.surveyState = state;
	};
	Template.prototype.showActions = function(){
		$(".lesson-btn").show();
	};
	function Survey() {
		this.id = null;
		this.currentPage = 1;
		this.totalPage = null;
		this.surveied = false;
		this.response = [];
		this.template = null;
	}
	Survey.prototype.init = function(cb) {
		var that = this;
		$.get(getUrl('init'),function(res){
			that.surveied = res.data.result == 'yes' && STAND;
			cb(that.surveied)
		});
	};
	Survey.prototype.getData = function(callback) {
		var that = this, cb = callback || function() {};
		$.get(getUrl(that.surveied ? 'history' : 'data'),function(res){
			if(res.success) {
				var p = _.countBy(res.data.result.question,function(item) {
					return item.question_type == 3 ? 'page' : 'question'
				}).page;
				that.totalPage = p == undefined ? 1 : p + 1;
				that.response = res.data.result;
				cb(that.response);
			} else {
				app.showMsg("fetch survey data error");
			}
		},'json');
	};
	Survey.prototype.render = function(question) {
		var q = question || this.response.question,that = this,index = 1,calcPage = 1;
		that.template.setSurveyState(that.surveied);
		_.each(q,function(item) {
			var display = that.template.itemVisible(that.currentPage,calcPage,that.surveied);
			switch (parseInt(item.question_type)) {
				case 0://radio
					that.template.makeItemRadio(item,index,display,calcPage);
					index ++;
					break;
				case 1://checkbox
					that.template.makeItemCheckbox(item,index,display,calcPage);
					index ++;
					break;
				case 2://input
					that.template.makeItemInput(item,index,display,calcPage);
					index ++;
					break;
				case 3://page
					calcPage ++;
					//that.template.makePagination(that.currentPage,that.totalPage,item,calcPage);
					break;
				default:
					console.log('question type not support.');
			}
		});
		that.template.makePagination(that.currentPage,that.totalPage,that.surveied);
	};
	Survey.prototype.next = function() {
		this.currentPage ++;
	};
	Survey.prototype.prev = function() {
		this.currentPage --;
	};
	Survey.prototype.validator = function() {
		var ele = $("li[data-question_id]"),errors = [];

		var data = [],obj = {};

		function _build(question_id,title,question_type,option_title,option_id) {
			obj.investigation_id = CONST.INVESTIGATION_ID;
			obj.investigation_question_id = question_id;
			obj.question_title = title;
			obj.question_type = question_type;
			obj.attempt = CONST.ATTEMPT;
			if(option_id) obj.investigation_option_id = option_id;
			obj.option_title = option_title;
			obj.option_result = option_title;
			data.push(obj);
			if(!STAND) {
				obj.course_id = COURSE.COURSE_ID;
				obj.course_reg_id = COURSE.COURSE_REG_ID;
				obj.mod_id = COURSE.MOD_ID;
				obj.mod_res_id = COURSE.MOD_RES_ID;
				obj.courseactivity_id = COURSE.COURSE_ACTIVITY_ID;
				obj.component_id = COURSE.COMPONENT_ID;
				obj.course_complete_id = COURSE.COURSE_COMPLETE_ID;
			}
			obj = {};
		}
		ele.each(function(){
			var type = $(this).data('item_type'),
				title = $(this).data('question_title'),
				id = $(this).data('question_id'),
				question_type = $(this).data('question_type');

			var selected = false;
			switch (type) {
				case 'radio':
				case 'checkbox':
					selected = $("input[name='"+id+"']:checked");
					break;
				case 'input':
					selected = $("textarea[name='"+id+"']").val();
					break;
			}
			if(selected == '' || selected == false || selected == undefined || selected.length == 0) {
				errors.push(title + (type == 'input' ? '未填写' : '未选择'));
			}

			if(typeof selected == 'object') {
				selected.each(function(){
					var __ = $(this);
					_build(id,title,question_type,__.data('title'),__.val());
				})
			} else {
				_build(id,title,question_type,selected);
			}

		});
		return {
			success : errors.length == 0,
			data : data,
			error : errors
		};
	};
	Survey.prototype.submit = function(req,cb) {
		var that = this;
		that.template.submitState(true);
		$.post(getUrl('submit'),{
			param:req,
			investigation_type:'survey',
			_csrf : CONST.CSRF
		},function(res){
			if(STAND) {
				$("#survey_success").modal();
			} else {
				//location.reload();
			}
			that.template.submitState(false);
			try{cb()}catch(e){}
		},'json');
	};
	Survey.prototype.resultPreview = function(template) {
		template.resultSelector.empty();
		$.get(getUrl('result'),function(res){
			_.each(res.data.question,function(item,i){
				var sum = 0;
				_.map(item.options,function(i){
					sum += i.submit_num_rate
				});
				switch (parseInt(item.question_type)) {
					case 0:
					case 1:
						template.makeResultItem(i,item,sum);
						break;
					case 2:
						break;
				}
			})
		});
	};
	Survey.prototype.setTemplateEngine = function(t) {
		this.template = t;
	};
	$(document).ready(function(){
		var survey = new Survey(),
			course = new Course();
			template = new Template("#survey-container");

		survey.setTemplateEngine(template);
		course.setStand(STAND);
		survey.init(function(){
			survey.getData(function(res){
				template.setTitle(res.title);
				survey.render(res.question);
			});

			template.toggleResultBtn(survey.surveied,STAND);
			template.showActions();
		});

		$("#next-page").on("click",function(){
			survey.next();
			template.makePagination(survey.currentPage,survey.totalPage,survey.surveied);
		});
		$("#prev-page").on("click",function(){
			survey.prev();
			template.makePagination(survey.currentPage,survey.totalPage,survey.surveied);
		});
		$("#submit").on("click",function(){
			var validator = survey.validator();
			if(validator.success) {
				survey.submit(validator.data,function(){
					course.complete();
				});
			} else {
				alert(validator.error[0]);
			}
		});
		$("#preview-result").on("click",function(){
			$("#my-popup").modal();
			survey.resultPreview(template);
		});
		$("#go-preview-result").on('click',function(){
			location.reload();
		});
		$("#go-todo-list").on('click',function(){
			app.showMsg("developing...");
		});

	});
</script>