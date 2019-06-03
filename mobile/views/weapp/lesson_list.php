<!-- Header -->
<header data-am-widget="header" class="am-header am-header-default">
    <h1 class="am-header-title">
        <a href="javascript:void(0)">企业学习平台</a>
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
<!-- Search -->
<div class="am-onePic" style="height: 50px;">
    <div class="search_area">
        <input type="text" id="search-lesson-input" placeholder="您想要学习什么课程?">
        <a href="javascript:void(0)" title="搜索"><i class="am-icon-search" id="search-lesson"></i></a>
    </div>
</div>
<!-- lesson_category -->
<div data-module="list-lessonType">
    <button type="button" class="am-btn am-btn-success btn-wide" data-am-modal="{target: '#my-popup'}" data-type="browse">按课程类别浏览</button>
    <div class="am-popup" id="my-popup">
        <div class="am-popup-inner">
            <div class="am-popup-hd">
                <h4 class="am-popup-title">课程类别</h4>
                <span data-am-modal-close class="am-close">&times;</span>
            </div>
            <div class="am-list-news-bd"></div>
        </div>
    </div>
</div>
<!-- List -->
<div data-am-widget="list_news" class="am-list-news am-list-news-default">
    <!--列表标题-->
    <!--div class="am-list-news-hd am-cf lesson_filter">
        <a href="javascript:void(0)">
            <h2>课程列表</h2>
        </a>
        <select data-am-selected id="selectOrder">
            <option value="a" selected>热门</option>
            <option value="b">最新</option>
        </select>
    </div-->
    <div class="am-list-news-bd" data-module="list" style="padding-bottom:20px">
        <div data-layout="layout"></div>
        <div data-tpl="typeL1">
            <center><img src="<?=$static_root?>/assets/img/load.gif" alt=""></center>
        </div>
        <div data-tpl="typeO1"></div>
        <div data-tpl="typeL2"></div>
    </div>
    <div id="loading"><center></center></div>
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
        document.write('<script type="text/babel" src="<?=$static_root?>/lib/template.raw/tpl.ListLesson.js"></scr'+'ipt><script type="text/babel" src="<?=$static_root?>/lib/template.raw/tpl.ListLessonType.js"></scr'+'ipt>');
    }
    else
    {
        document.write('<script src="<?=$static_root?>/lib/template/tpl.ListLesson.js"></scr'+'ipt><script src="<?=$static_root?>/lib/template/tpl.ListLessonType.js"></scr'+'ipt>');
    }
</script>

<script src="<?=$static_root?>/lib/api/interface.js"></script>
<script src="<?=$static_root?>/lib/weapp.js"></script>

<script>
    $(function ()
    {
        //判断排序
        var $selectOrder = $('#selectOrder');
        window.ISHOT = !~location.href.indexOf('new');
        if (!ISHOT)
        {
            $selectOrder.find('option').attr('selected', false);
            $selectOrder.find('option[value=b]').attr('selected', true);
        }
        $selectOrder.change(function ()
        {
            TPL.tick.redirect('a' === $(this).val() ? 'lesson_list.html?order=hot' : 'lesson_list.html?order=new');
        });

        $("#search-lesson").click(function ()
        {
        	var kw = $.trim($("#search-lesson-input").val());
        	if (kw)
        	{
        		TPL.tick.redirect('lesson_search.html#'+encodeURIComponent(kw));
        	}
        });
    });
</script>