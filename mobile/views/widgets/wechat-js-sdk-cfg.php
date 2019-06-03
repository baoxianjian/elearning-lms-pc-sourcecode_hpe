<script src="/static/mobile/lib/jweixin-1.0.0.js"></script>
<?php
use yii\helpers\Json;
?>
<script>
    window.wxShareData = {
        title : false,
        desc : false,
        link : false,
        imgUrl : false,
        success : false,
        cancel : false
    };
    wx.config({
        debug: <?php echo $config['debug']?'true':'false';?>,
        appId: '<?php echo $config['appId'];?>',
        timestamp: '<?php echo $config['timestamp'];?>',
        nonceStr: '<?php echo $config['nonceStr'];?>',
        signature: '<?php echo $config['signature'];?>',
        jsApiList: ['onMenuShareTimeline','onMenuShareAppMessage','scanQRCode']
    });
    wx.ready(function(){
        wx.onMenuShareTimeline({
            title: wxShareData.title || '<?php echo isset($shareData['title'])?$shareData['title']:''?>', // 分享标题
            link: wxShareData.link || '<?php echo isset($shareData['link'])?$shareData['link']:''?>', // 分享链接
            imgUrl: wxShareData.imgUrl || '<?php echo isset($shareData['imgUrl'])?$shareData['imgUrl']:''?>', // 分享图标
            success: function () {
                try{<?php echo isset($shareData['success'])? $shareData['success'].'()':'';?>}catch(e){}
            },
            cancel: function () {
                try{<?php echo isset($shareData['cancel'])? $shareData['cancel'].'()':'';?>}catch(e){}
            }
        });
        wx.onMenuShareAppMessage({
            title: wxShareData.title || '<?php echo isset($shareData['title'])?$shareData['title']:''?>', // 分享标题
            desc: wxShareData.desc || '<?php echo isset($shareData['desc'])?$shareData['desc']:''?>', // 分享描述
            link: wxShareData.link || '<?php echo isset($shareData['link'])?$shareData['link']:''?>', // 分享链接
            imgUrl: wxShareData.imgUrl || '<?php echo isset($shareData['imgUrl'])?$shareData['imgUrl']:''?>', // 分享图标
            type: 'link', // 分享类型,music、video或link，不填默认为link
            dataUrl: '', // 如果type是music或video，则要提供数据链接，默认为空
            success: function () {
                try{<?php echo isset($shareData['success'])? $shareData['success'].'()':'';?>}catch(e){}
            },
            cancel: function () {
                try{<?php echo isset($shareData['cancel'])? $shareData['cancel'].'()':'';?>}catch(e){}
            }
        });
        wx.scanQRCode({
            needResult: 0, // 默认为0，扫描结果由微信处理，1则直接返回扫描结果，
            scanType: ["qrCode","barCode"], // 可以指定扫二维码还是一维码，默认二者都有
            success: function (res) {
                var result = res.resultStr; // 当needResult 为 1 时，扫码返回的结果
                try{<?php echo isset($shareData['success'])? $shareData['success'].'()':'';?>}catch(e){}
            }
        });
    });
</script>
