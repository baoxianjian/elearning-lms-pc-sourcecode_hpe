<!-- Header -->
<header data-am-widget="header" class="am-header am-header-default">
	<a href="javascript:void(0)" class="goBack testCase_back">返回</a>
	<h1 class="am-header-title">
		<a href="#title-link">调查结果</a>
	</h1>
</header>
<!-- Menu -->
<nav data-am-widget="menu" class="am-menu  am-menu-offcanvas1" data-am-menu-offcanvas>
    <a href="javascript:void(0)" class="am-menu-toggle">
        <i class="am-menu-toggle-icon am-icon-bars"></i>
    </a>
    <div class="am-offcanvas">
        <div class="am-offcanvas-bar">
            <ul class="am-menu-nav sm-block-grid-1">
                <li>
                    <a href="pending_list.html?pending#pending">待完成</a>
                </li>
                <li>
                    <a href="pending_list.html?complete#complete">已完成</a>
                </li>
                <li>
                    <a href="lesson_list.html">浏览课程</a>
                </li>
            </ul>
        </div>
    </div>
</nav>
<div data-am-widget="tabs" class="am-tabs am-tabs-d2" style="padding-bottom: 30px;">
<ul class="am-tabs-nav am-cf">
	<li class="am-active p10"><a href="[data-tab-panel-0]">调查:惠普大学最受人喜爱的开发人员</a></li>
</ul>
<div class="am-tabs-bd">
  <div data-tab-panel-0 class="am-tab-panel am-active p0">
    <div class="am-list-item-text">
      <ul class="course-chapter">
        <li>
          <ul class="course-sections">
            <p class="course-des"><strong>1. 你最喜欢的开发同事是谁</strong></p>
            <li class="options">
              <label for="quest101">
                <input type="radio" value="0" id="quest101" name="quest1">刘程</label>
            </li>
            <li class="options">
              <label for="quest102">
                <input type="radio" value="0" id="quest102" name="quest1">刘程程和他周围的同事</label>
            </li>
            <li class="options">
              <label for="quest103">
                <input type="radio" value="0" id="quest103" name="quest1" checked="checked">刘大成旁边的同事</label>
            </li>
            <li class="options">
              <label for="quest104">
                <input type="radio" value="0" id="quest104" name="quest1">刘不成前面的同事</label>
            </li>
          </ul>
        </li>
        <li>
          <ul class="course-sections">
            <p class="course-des"><strong>2. 你喜欢这位开发同事的原因有下面哪几项</strong></p>
            <li class="options">
              <label for="quest201">
                <input type="checkbox" value="0" id="quest201" name="quest2" checked="checked">因为幽默风趣,是2B</label>
            </li>
            <li class="options">
              <label for="quest202">
                <input type="checkbox" value="0" id="quest202" name="quest2" checked="checked">长相出众, 身材好</label>
            </li>
            <li class="options">
              <label for="quest203">
                <input type="checkbox" value="0" id="quest203" name="quest2">很明显上一条是不实际的</label>
            </li>
            <li class="options">
              <label for="quest204">
                <input type="checkbox" value="0" id="quest204" name="quest2">编到这里感觉内心好崩溃</label>
            </li>
          </ul>
        </li>
        <li>
          <ul class="course-sections">
            <p class="course-des"><strong>3. 你想对这位同事说些什么,或者是有什么建议</strong></p>
            <li class="options">
              <textarea name="quest3" id="quest301" placeholder="请填写你想要说的话">这位仁兄长得太胖, 基本没有脸了.希望以后好好减肥,重新做人.lol</textarea>
            </li>
          </ul>
        </li>
      </ul>
    </div>
  </div>
</div>
</div>
<div class="lesson-btn am-cf lesson-btn-fix">
<button type="button" class="am-btn am-btn-success am-btn-xs fr" data-am-modal="{target: '#my-popup'}">查看统计结果</button>
</div>
<!-- 弹出调查结果页面 -->
<div class="am-popup" id="my-popup">
<div class="am-popup-inner">
  <div class="am-popup-hd">
    <h4 class="am-popup-title">调查统计结果</h4>
    <span data-am-modal-close="" class="am-close">×</span>
  </div>
  <div class="am-list-news-bd">
    <ul class="course-chapter">
      <li>
        <ul class="course-sections">
          <p class="course-des"><strong>1. 你最喜欢的开发同事是谁</strong></p>
          <li class="options">
            <label for="quest101">刘程</label>
            <div class="am-progress">
              <div class="am-progress-bar" style="width: 15%"></div>
              <p class="progress-number">12%</p>
            </div>
          </li>
          <li class="options">
            <label for="quest102">刘程程和他周围的同事</label>
            <div class="am-progress">
              <div class="am-progress-bar" style="width: 45%"></div>
              <p class="progress-number">45%</p>
            </div>
          </li>
          <li class="options">
            <label for="quest103">刘大成旁边的同事</label>
            <div class="am-progress">
              <div class="am-progress-bar" style="width: 15%"></div>
              <p class="progress-number">15%</p>
            </div>
          </li>
          <li class="options">
            <label for="quest104">刘不成前面的同事</label>
            <div class="am-progress">
              <div class="am-progress-bar" style="width: 45%"></div>
              <p class="progress-number">45%</p>
            </div>
          </li>
        </ul>
      </li>
      <li>
		<ul class="course-sections">
		  <p class="course-des"><strong>2. 你最喜欢的开发同事是谁</strong></p>
		  <li class="options">
		    <label for="quest101">刘程</label>
		    <div class="am-progress">
		      <div class="am-progress-bar" style="width: 15%"></div>
		      <p class="progress-number">12%</p>
		    </div>
		  </li>
		  <li class="options">
		    <label for="quest102">刘程程和他周围的同事</label>
		    <div class="am-progress">
		      <div class="am-progress-bar" style="width: 45%"></div>
		      <p class="progress-number">45%</p>
		    </div>
		  </li>
		  <li class="options">
		    <label for="quest103">刘大成旁边的同事</label>
		    <div class="am-progress">
		      <div class="am-progress-bar" style="width: 15%"></div>
		      <p class="progress-number">15%</p>
		    </div>
		  </li>
		  <li class="options">
		    <label for="quest104">刘不成前面的同事</label>
		    <div class="am-progress">
		      <div class="am-progress-bar" style="width: 45%"></div>
		      <p class="progress-number">45%</p>
		    </div>
		  </li>
		</ul>
      </li>
      <li>
        <ul class="course-sections">
          <p class="course-des"><strong>3. 你想对这位同事说些什么,或者是有什么建议</strong></p>
          <li class="options">
            <p>主观题,无统计数据</p>
          </li>
        </ul>
      </li>
    </ul>
  </div>
</div>
</div>

<script src="<?=$static_root?>/lib/tpl.js"></script>
<script>
    if (TPL.env.isDev)
    {
        document.write('<script src="<?=$static_root?>/lib/jquery.js,t3.js,react-0.14.7.js,react-dom.js.merge().0.2.js"></scr'+'ipt><script src="<?=$static_root?>/lib/babel.js"></sc'+'ript>');
    }
    else
    {
        document.write('<script src="<?=$static_root?>/lib/jquery.js,t3.js,react.js,react-dom.js.merge().0.2.js"></scr'+'ipt>');
    }
</script>

<script src="<?=$static_root?>/proto/assets/js/amazeui.min.js"></script>
<script src="<?=$static_root?>/proto/assets/js/fastclick.js"></script>
<script src="<?=$static_root?>/proto/assets/js/main.js"></script>

<script>
    if (TPL.env.isDev)
    {
        //document.write('<script type="text/babel" src="<?=$static_root?>/lib/template.raw/tpl.LessonDetailOnline.js"></scr'+'ipt><script type="text/babel" src="<?=$static_root?>/lib/template.raw/tpl.ListAnswerLesson.js"></sc'+'ript>');
    }
    else
    {
        //document.write('<script src="<?=$static_root?>/lib/template/tpl.LessonDetailOnline.js"></scr'+'ipt><script src="<?=$static_root?>/lib/template/tpl.ListAnswerLesson.js"></sc'+'ript>');
    }
</script>

<script src="<?=$static_root?>/lib/api/interface.js"></script>
<script src="<?=$static_root?>/lib/weapp.js"></script>