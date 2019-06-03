<!-- Header -->
<header data-am-widget="header" class="am-header am-header-default">
<a href="javascript:void(0)" class="goBack testCase_back">返回</a>
<h1 class="am-header-title">
  <a href="javascript:void(0)">调查进行中</a>
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
<!-- Slider -->
<div data-module="play-investigation"></div>
<!-- 课程播放导航 -->
<div class="am-popup" id="my-popup">
<div class="am-popup-inner">
  <div class="am-popup-hd">
    <h4 class="am-popup-title">课程内导航</h4>
    <span data-am-modal-close class="am-close">&times;</span>
  </div>
  <div class="am-list-news-bd">
    <div class="am-u-sm-12 am-u-md-10">
      <div class="am-list-item-text" id="lesson-chapters"></div>
    </div>
  </div>
</div>
</div>
<script src="<?=$static_root?>/lib/tpl.js"></script>
<script>
if (TPL.env.isDev) {
document.write('<script src="<?=$static_root?>/lib/jquery.js,t3.js,react-0.14.7.js,react-dom.js.merge().0.2.js"></scr' + 'ipt><script src="<?=$static_root?>/lib/babel.js"></sc' + 'ript>');
} else {
document.write('<script src="<?=$static_root?>/lib/jquery.js,t3.js,react.js,react-dom.js.merge().0.2.js"></scr' + 'ipt>');
}
</script>
<script src="<?=$static_root?>/proto/assets/js/amazeui.min.js"></script>
<script src="<?=$static_root?>/proto/assets/js/fastclick.js"></script>
<script src="<?=$static_root?>/proto/assets/js/main.js"></script>
<script>
    if (TPL.env.isDev) {
    document.write('<script type="text/babel" src="<?=$static_root?>/lib/template.raw/tpl.PlayInvestigation.js"></scr' + 'ipt>');
    } else {
    document.write('<script src="<?=$static_root?>/lib/template/tpl.PlayInvestigation.js"></scr' + 'ipt>');
    }
</script>
<script src="<?=$static_root?>/lib/api/interface.js"></script>
<script src="<?=$static_root?>/lib/weapp.js"></script>