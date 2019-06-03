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
				<ul class="course-chapter" id="vote-container">

				</ul>
			</div>
		</div>
	</div>
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
			<ul class="course-chapter" id="vote-result">

			</ul>
		</div>
	</div>
</div>

<script type="text/template" id="item-radio">
	<li data-item_type="radio" data-question_title="<%=item.question_title%>" data-question_id="<%=item.investigation_question_id%>" data-question_type="<%=item.question_type%>">
		<ul class="course-sections">
			<%_.each(item.options,function(option,index){%>
			<li class="options">
				<label for="<%=option.kid%>"><input id="<%=option.kid%>" <%=option.isCheck%> <%=disabled?'disabled':''%> name="<%=item.investigation_question_id%>" type="radio" value="<%=option.kid%>" data-title="<%= option.option_title%>"><%= option.option_title%></label>
			</li>
			<%});%>
		</ul>
	</li>
</script>

<script type="text/template" id="item-checkbox">
	<li data-item_type="checkbox" data-question_title="<%=item.question_title%>" data-question_id="<%=item.investigation_question_id%>" data-question_type="<%=item.question_type%>">
		<ul class="course-sections">
			<%_.each(item.options,function(option,index){%>
			<li class="options">
				<label for="<%=option.kid%>"><input id="<%=option.kid%>" type="checkbox" <%=disabled?'disabled':''%> value="<%=option.kid%>" name="<%=item.investigation_question_id%>" data-title="<%= option.option_title%>" <%=option.isCheck%>><%= option.option_title%></label>
			</li>
			<%});%>
		</ul>
	</li>
</script>

<script type="text/template" id="item-result">
	<li>
		<ul class="course-sections">
			<p class="course-des"><strong><%=item.question_title%></strong></p>
			<%_.each(item.options,function(option){%>
			<li class="options">
				<label for="quest101"><%=option.option_title%></label>
				<div class="am-progress">
					<div class="am-progress-bar" style="width: <%=option.submit_num_rate/total * 100 %>%"></div>
					<p class="progress-number"><%=option.submit_num_rate/total * 100 %>%</p>
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
	var URLs = {};

	<?php if(!$stand){?>
	CONST.COURSE_ID = '<?=$courseId?>';
	CONST.COURSE_REG_ID = '<?=$course_reg_id?>';
	CONST.MOD_ID = '<?=$mod_id?>';
	CONST.MOD_RES_ID = '<?=$modResId?>';
	CONST.COURSE_ACTIVITY_ID = '<?=$courseactivity_id?>';
	CONST.COMPONENT_ID = '<?=$component_id?>';
	CONST.COURSE_COMPLETE_ID = '<?=$courseCompleteFinalId?>';
	CONST.COURSE_COMPLETE_PROCESS_ID = '<?=$courseCompleteProcessId?>';
	URLs = {
		'init' : "<?php echo Url::toRoute(['investigation/get-play-investigation-submit-result'])?>",
		'data' : '<?php echo Url::toRoute(['investigation/get-vote'])?>',
		'submit' : '<?php echo Url::toRoute(['investigation/investigation-submit-result','system_key'=>$system_key,'access_token'=>$access_token])?>',
		'history' : '<?php echo Url::toRoute(['investigation/get-sub-vote-result',])?>',
		'result' : '<?php echo Url::toRoute(['investigation/course-play-vote-result',])?>?dataType=json',
		'courseComplete' : '<?php echo Url::toRoute(['investigation/play-investigation-res-complete',])?>'
	};
	<?php } else {?>
	URLs = {
		'init' : "<?php echo Url::toRoute(['investigation/get-single-play-investigation-submit-result'])?>",
		'data' : '<?php echo Url::toRoute(['investigation/get-vote'])?>',
		'submit' : '<?php echo Url::toRoute(['investigation/single-investigation-submit-result','system_key'=>$system_key,'access_token'=>$access_token])?>',
		'history' : '<?php echo Url::toRoute(['investigation/get-single-sub-vote-result',])?>',
		'result' : '<?php echo Url::toRoute(['investigation/single-course-play-vote-result',])?>?dataType=json'
	};
	<?php }?>
</script>
<script>
	(function($,context){
		'use strict';
		function Vote() {
			var exports;
			var	dispatcher = new Dispatcher(context);
			var protocol = standProtocol();
			var urls;
			var constData;
			var queryString = 'id={INVESTIGATION_ID}&investigation_id={INVESTIGATION_ID}&system_key={SYSTEM_KEY}&access_token={ACCESS_TOKEN}';
			var template;
			var voted = false;
			var response;

			function setUrls(url) {
				urls = url;
			}
			function setProtocol(p) {
				protocol = p;
			}
			function setTemplate(t) {
				template = t;
			}
			function setVoteStatus(status) {
				voted = status;
			}

			function standProtocol() {
				return context.standProtocol || {
						getUrl : function(type) {
							var url = urls[type];
							var delimiter = url.indexOf('?') > -1 ? '&' : '?';
							queryString = queryString
								.replace(/\{INVESTIGATION_ID\}/g,constData.INVESTIGATION_ID)
								.replace(/\{SYSTEM_KEY\}/g,constData.SYSTEM_KEY)
								.replace(/\{ACCESS_TOKEN\}/g,constData.ACCESS_TOKEN);
							return url + delimiter + queryString;
						},
						buildSubmitData : function(question_id,title,question_type,option_title,option_id) {
							return {
								investigation_id : constData.INVESTIGATION_ID,
								investigation_question_id : question_id,
								question_title : title,
								question_type : question_type,
								attempt : constData.ATTEMPT,
								investigation_option_id : option_id,
								option_title : option_title,
								option_result : option_title
							};
						},
						complete : function() {}
					};
			}
			function courseProtocol() {
				queryString += '&mod_id={MOD_ID}&course_id={COURSE_ID}&mod_res_id={MODE_RES_ID}&course_complete_process_id={COURSE_COMPLETE_PROCESS_ID}&course_complete_id={COURSE_COMPLETE_ID}&course_reg_id={COURSE_REG_ID}';
				return context.courseProtocol || {
						getUrl : function(type) {
							var url = urls[type];
							var delimiter = url.indexOf('?') > -1 ? '&' : '?';
							queryString = queryString
								.replace(/\{INVESTIGATION_ID\}/g,constData.INVESTIGATION_ID)
								.replace(/\{SYSTEM_KEY\}/g,constData.SYSTEM_KEY)
								.replace(/\{ACCESS_TOKEN\}/g,constData.ACCESS_TOKEN)
								.replace(/\{MOD_ID\}/g,constData.MOD_ID)
								.replace(/\{COURSE_ID\}/g,constData.COURSE_ID)
								.replace(/\{MODE_RES_ID\}/g,constData.MOD_RES_ID)
								.replace(/\{COURSE_COMPLETE_ID\}/g,constData.COURSE_COMPLETE_ID)
								.replace(/\{COURSE_COMPLETE_PROCESS_ID\}/g,constData.COURSE_COMPLETE_PROCESS_ID)
								.replace(/\{COURSE_REG_ID\}/g,constData.COURSE_REG_ID);
							return url + delimiter + queryString;
						},
						buildSubmitData : function(question_id,title,question_type,option_title,option_id) {
							return {
								investigation_id : constData.INVESTIGATION_ID,
								investigation_question_id : question_id,
								question_title : title,
								question_type : question_type,
								attempt : constData.ATTEMPT,
								investigation_option_id : option_id,
								option_title : option_title,
								option_result : option_title,
								course_id : constData.COURSE_ID,
								course_reg_id : constData.COURSE_REG_ID,
								mod_id : constData.MOD_ID,
								mod_res_id : constData.MOD_RES_ID,
								courseactivity_id : constData.COURSE_ACTIVITY_ID,
								component_id : constData.COMPONENT_ID,
								course_complete_id : constData.COURSE_COMPLETE_ID,
								//course_complete_process_id : constData.COURSE_COMPLETE_PROCESS_ID
							};
						},
						complete : function() {
							$.get(protocol.getUrl('courseComplete') + "&complete_type=0",function(){});
							$.get(protocol.getUrl('courseComplete') + "&complete_type=1",function(){});
						}
					};
			}

			function setConst(data) {
				constData = data;
			}
			function Dispatcher(dispatchContext) {
				this.context = dispatchContext;
			}
			Dispatcher.prototype.init = function(callback) {
				$.get(protocol.getUrl('init'),function(res){
					voted = res.data.result == 'yes' && STAND;
					try{
						callback(voted);
					} catch(e) {}
				},'json');
			};
			Dispatcher.prototype.getData = function(callback) {
				var type = voted ? 'history' : 'data';
				$.get(protocol.getUrl(type),function(res){
					response = res.data.result;
					try{
						callback(response);
					} catch(e) {}
				},'json');
			};
			Dispatcher.prototype.render = function(data) {
				var vote = data || response;
				switch(parseInt(vote.question_type)) {
					case 0:
						template.makeItemRadio(vote,voted);
						break;
					case 1:
						template.makeItemCheckbox(vote,voted);
						break;
				}
			};
			Dispatcher.prototype.validator = function() {
				var ele = $("li[data-question_id]"),errors = [];
				var data = [];
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
							var temp = protocol.buildSubmitData(id,title,question_type,__.data('title'),__.val());
							data.push(temp);
						})
					}

				});
				return {
					success : errors.length == 0,
					data : data,
					error : errors
				};
			};
			Dispatcher.prototype.submit = function(param,cb) {
				$.post(protocol.getUrl('submit'),{
					param : param,
					investigation_type : 'vote',
					_csrf : CONST.CSRF
				},function(res){
					try{
						cb(res)
					} catch(e){}
				},'json');
			};
			Dispatcher.prototype.previewResult = function() {
				template.resultSelector.empty();
				$.get(protocol.getUrl('result'),function(res){
					var sum = 0;
					_.map(res.data.options,function(i){
						sum += i.submit_num_rate
					});
					template.makeResultItem(res.data,sum);
				});
			};
			Dispatcher.prototype.complete = function() {
				protocol.complete();
			};
			exports = dispatcher;
			exports.setProtocol = setProtocol;
			exports.setUrls = setUrls;
			exports.setConst = setConst;
			exports.courseProtocol = courseProtocol;
			exports.setTemplate = setTemplate;
			exports.setVoteStatus = setVoteStatus;
			return exports;
		}
		context.Vote = Vote();
	})(jQuery,window);
</script>

<script>
	function Template(selector) {
		this.itemRadio = null;
		this.itemCheckbox = null;
		this.itemResult = null;
		this.selector = typeof selector == 'string' ? $(selector) : selector;
		this.submitBtn = $("#submit");
		this.resultSelector = $("#vote-result");
		this.init();
	}
	Template.prototype.init = function() {
		this.itemRadio = $("#item-radio").html();
		this.itemCheckbox = $("#item-checkbox").html();
		this.itemResult = $("#item-result").html();
	};
	Template.prototype.setTitle = function(txt) {
		$("#survey-title").html(txt);
	};
	Template.prototype.makeItemRadio = function(item,disabled) {
		var html = _.template(this.itemRadio)({
			item : item,
			disabled : disabled
		});
		this.selector.append(html);
	};
	Template.prototype.makeItemCheckbox = function(item,disabled) {
		var html = _.template(this.itemCheckbox)({
			item : item,
			disabled : disabled
		});
		this.selector.append(html);
	};

	Template.prototype.toggleResultBtn = function(voted,stand) {
		var btn = $("#preview-result"),submitBtn = $("#submit");
		if(voted) {
			submitBtn.hide();
		} else {
			submitBtn.show();
		}
		if(voted && stand) {
			btn.show();
		}
		else btn.hide();
	};
	Template.prototype.makeResultItem = function(item,total) {
		var html = _.template(this.itemResult)({item: item,total:total});
		this.resultSelector.append(html);
	};
	Template.prototype.submitState = function(loading) {
		this.submitBtn.attr("disabled",loading);
		this.submitBtn.text(loading ? '正在处理' : '提交');
	};
	Template.prototype.showActions = function(){
		$(".lesson-btn").show();
	};

	$(document).ready(function(){
		var template = new Template("#vote-container");

		if(!STAND) Vote.setProtocol(Vote.courseProtocol());
		Vote.setUrls(URLs);
		Vote.setConst(CONST);
		Vote.setTemplate(template);
		Vote.init(function(voted){
			Vote.getData(function(res){
				template.setTitle(res.question_title);
				template.toggleResultBtn(voted,STAND);
				template.showActions();
				Vote.render();
			});
		});

		$("#submit").on("click",function(){
			var validator = Vote.validator();
			if(validator.success) {
				template.submitState(true);
				Vote.submit(validator.data,function(){
					Vote.complete();
					if(STAND) {
						$("#survey_success").modal();
					} else {
						setTimeout(function(){
							//location.reload();
						},100)
					}
					template.submitState(false);
				});
			} else {
				alert(validator.error[0]);
			}
		});
		$("#preview-result").on("click",function(){
			$("#my-popup").modal();
			Vote.previewResult();
		});
		$("#go-preview-result").on('click',function(){
			location.reload();
		});
		$("#go-todo-list").on('click',function(){
			app.showMsg("developing...");
		});
	});
</script>