<!-- Header -->
<header data-am-widget="header" class="am-header am-header-default">
<a href="javascript:void(0)" class="goBack testCase">返回</a>
<h1 class="am-header-title">
<a href="#title-link">搜索结果</a>
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
<!-- Search Input -->
<div class="am-onePic search_result_input" style="height: 60px;">
<div class="search_area">
  <input type="text" id="search-lesson-input" placeholder="当前搜索的关键字">
  <a href="javascript:void(0)" title="搜索"><i class="am-icon-search" id="search-lesson"></i></a>
</div>
</div>
<!-- List -->
<div data-am-widget="list_news" class="am-list-news am-list-news-default">
<!--列表标题-->
<div class="am-list-news-hd am-cf lesson_filter">
  <!--带更多链接-->
  <a href="javascript:void(0)">
    <h2>以下是包含关键字 "<span id="h2kw"></span>" 的课程</h2>
  </a>
</div>
<div class="am-list-news-bd" data-module="list-lessonSearch"></div>
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
        document.write('<script type="text/babel" src="<?=$static_root?>/lib/template.raw/tpl.ListLessonSearch.js"></scr'+'ipt>');
    }
    else
    {
        document.write('<script src="<?=$static_root?>/lib/template/tpl.ListLessonSearch.js"></scr'+'ipt>');
    }
</script>

<script src="<?=$static_root?>/lib/api/interface.js"></script>
<script src="<?=$static_root?>/lib/weapp.js"></script>

<script>
    $(function ()
    {
        $("#search-lesson").click(function ()
        {
            var kw = $.trim($("#search-lesson-input").val());
            if (!kw)
            {
                return;
            }
            location.hash = encodeURIComponent(kw);
            $('#h2kw').text(kw);
            TPL.ListLessonSearch.showLoading();
            AI.list.lessonSearch({kw:kw}, function (tplVals)
            {
                if (tplVals)
                {
                    TPL.ListLessonSearch.replace(tplVals);
                    //$(window).scroll(autoScroll.listPending);
                    return;
                }
                ReactDOM.render(React.createElement(React.createClass(TPL.ListLessonSearch), {list:[]}), $('div[data-module=list-lessonSearch]')[0]);
                TPL.ListLessonSearch.showEmpty();
                console.log('tplVals 解析失败');
            });
        });
    });
</script>