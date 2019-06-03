<style>
    .am-badge-secondary{
        margin-right: 3px;
    }
</style>
<!-- Header -->
<header data-am-widget="header" class="am-header am-header-default">
    <h1 class="am-header-title">
        <a href="javascript:void(0)" id="h1title"></a>
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
<!-- List -->
<div data-am-widget="list_news" class="am-list-news am-list-news-default">
<!--列表标题-->
<div class="am-list-news-hd am-cf lesson_filter">
    <!--带更多链接-->
    <a href="javascript:void(0)">
        <h2 id="h2title"></h2>
    </a>
    <!--select data-am-selected id="select1">
        <option value="all" selected  id="selected1title"></option>
        <option value="lesson">课程</option>
        <option value="investigation">调查</option>
        <option value="exam">考试</option>
    </select-->
</div>
<div class="am-list-news-bd" data-module="list-pending"><img id="loader" src="<?=$static_root?>/assets/img/load.gif" alt=""></div>
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
        document.write('<script type="text/babel" src="<?=$static_root?>/lib/template.raw/tpl.ListPending.js"></scr'+'ipt>');
    }
    else
    {
        document.write('<script src="<?=$static_root?>/lib/template/tpl.ListPending.js"></scr'+'ipt>');
    }
</script>

<script src="<?=$static_root?>/lib/api/interface.js"></script>
<script src="<?=$static_root?>/lib/weapp.js"></script>

<script>
    //init display
    $(function ()
    {
        window.ISCOMPLETE = ~location.hash.indexOf('complete');
        if (ISCOMPLETE)
        {
            $('#h1title').text('已完成');
            $('#h2title').text('完成事项');
            $('#selected1title').text('全部完成');
        }
        else
        {
            $('#h1title').text('待完成');
            $('#h2title').text('待办事项');
            $('#selected1title').text('全部待办');
        }
        $('#select1').change(function ()
        {
            var PENDING = window.PENDING = $(this).val()
            ,   tick = TPL.tick;
            TPL.ListPending.showLoading();
            autoScroll.reset();
            if (ISCOMPLETE)
            {
                if ('lesson' === PENDING)
                {
                    AI.list.lessonPending({type: 'finished', page: 1, ts: tick.ts().toString(), uid: localStorage.userId}, function (tplVals)
                    {
                        if (tplVals)
                        {
                            TPL.ListPending.replace(tplVals);
                            if (!$('#ul-am-list').html())
                            {
                                TPL.ListPending.showEmpty();
                            }
                            return;
                        }
                        TPL.ListPending.showEmpty();
                        console.log('list-pending tplVals 解析失败');
                    });
                }
                else
                {
                    AI.list.pending({type: 'finished', page: 1, ts: tick.ts().toString(), uid: localStorage.userId}, function (tplVals)
                    {
                        if (tplVals)
                        {
                            TPL.ListPending.replace(tplVals);
                            if (!$('#ul-am-list').html())
                            {
                                TPL.ListPending.showEmpty();
                            }
                            return;
                        }
                        TPL.ListPending.showEmpty();
                        console.log('list-pending tplVals 解析失败');
                    });
                }
            }
            else
            {
                if ('lesson' === PENDING)
                {
                    AI.list.lessonPending({type: 'unfinished', page: 1, ts: tick.ts().toString(), uid: localStorage.userId}, function (tplVals)
                    {
                        if (tplVals)
                        {
                            TPL.ListPending.replace(tplVals);
                            if (!$('#ul-am-list').html())
                            {
                                TPL.ListPending.showEmpty();
                            }
                            return;
                        }
                        TPL.ListPending.showEmpty();
                        console.log('list-pending tplVals 解析失败');
                    });
                }
                else
                {
                    AI.list.pending({type: 'unfinished', page: 1, ts: tick.ts().toString(), uid: localStorage.userId}, function (tplVals)
                    {
                        if (tplVals)
                        {
                            TPL.ListPending.replace(tplVals);
                            if (!$('#ul-am-list').html())
                            {
                                TPL.ListPending.showEmpty();
                            }
                            return;
                        }
                        TPL.ListPending.showEmpty();
                        console.log('list-pending tplVals 解析失败');
                    });
                }
            }
        });
    });
</script>