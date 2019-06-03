var isOpen=false;
var bgArr=[];//已存在的弹幕内容
var loadedArr=[];//已加载过的弹幕内容
var bgTime=15000;//15秒读取一次弹幕
var nTime=-1;
var getBarrageTime=null;
var liveNum=0;//直播时用来记录已发送过的弹幕的最新编号
// var cksarr=parseInt(ckstyle()['cpt_barrage'].split('|')[4])==1?isOpen=true:isOpen=false;
function loadedHandler(){
	var cksarr = parseInt(ckstyle()['cpt_barrage'].split('|')[4]);
	//console.log(cksarr);
	if (cksarr == 1) {
		isOpen = true;
	}else {
		isOpen = false;
	}

	if(!CKobject.getObjectById('ckplayer_a1').getType()){//只有在flash播放器情况下使用该监听
		CKobject.getObjectById('ckplayer_a1').addListener('barrageShow','barrageShowHandler');
		if(!isLive){//不是直播才需要来监听时间
			CKobject.getObjectById('ckplayer_a1').addListener('time','timeHandler');
		}
	}
	//alert(getDanMuUrl);
	ajaxData(getDanMuUrl,
		"POST",
		null,
		"json",
		function(data){
			if(data){
				bgArr=data['ckplayer'];
				// console.log(bgArr[0]);
				// console.log(bgArr[1]);
				// bgArr=obj['ckplayer'];
				// alert(bgArr);
			}
		}
	);
	// CKobject.ajax('post','utf-8',getDanMuUrl,function(data){
	// 	if(data){
	// 		var obj=eval('(' + data + ')');
	// 		bgArr=obj['ckplayer'];
	// 	}
	// });
	//console.log(isOpen);
	if(isOpen){
		openOrclose(true);
	}
}
function barrageShowHandler(b){
	//alert('barrageShowHandler');
	if(b){//如果是关闭的现在需要开启则开始调用弹幕
		openOrclose(true);
	}
	else{
		openOrclose(false);
	}
	isOpen=b;
}
function barrage(s){//写弹幕，写完同时把所有的读取出来
	if (s != "") {
		var nt = parseInt(nTime);
		if (!nt)nt = 0;
		if (nt < 0)nt = 0;
		var newPostDanMuUrl = postDanMuUrl + '&s=' + encodeURIComponent(s) + '&j=' + nt;
		//alert(newPostDanMuUrl);
		var postData = {};

		postData['s'] = encodeURIComponent(s);
		postData['j'] = nt;
		ajaxData(newPostDanMuUrl,
			"POST",
			postData,
			"json",
			function (data) {
				if (data) {
					bgArr = data['ckplayer'];
					//console.log(bgArr);
				}
				if (s && !isLive) {
					CKobject.getObjectById('ckplayer_a1').loadBarrage('您刚刚发布的弹幕内容是：' + s);
				}
				if (isLive && bgArr[1].length > 0 && bgArr[1].length - 1 > liveNum) {//如果是直播，则把所有最新的结果发送给播放器
					if (liveNum > 0) {
						for (var i = liveNum + 1; i < bgArr[1].length; i++) {
							CKobject.getObjectById('ckplayer_a1').loadBarrage(bgArr[1][i]);
						}
					}
					liveNum = bgArr[1].length - 1;
				}
			}
		);
	}

}
function analysisBarrage(){
	barrage('');
}
function openOrclose(b){
	//alert('openOrclose');
	if(b){
		if(getBarrageTime){
			window.clearInterval(getBarrageTime);
			getBarrageTime=null;
		}
		barrage('');
		getBarrageTime=window.setInterval(analysisBarrage,bgTime);
	}
	else{
		if(getBarrageTime){
			window.clearInterval(getBarrageTime);
			getBarrageTime=null;
		}
	}
}
function timeHandler(t){
	//console.log(t);
	if(isOpen && t>0 && nTime!=parseInt(t) && bgArr){
		nTime=parseInt(t);
		var j=bgArr[0];//time
		var s=bgArr[1];//text
		var kid=bgArr[2];//kid
		//var nj=getNewArr(j,nTime);
		// console.log(j.length);
		// console.log(s);

		if (j.length > 0) {
			for (var i = 0; i < j.length; i++) {
				var currentKid = kid[i];
				if (loadedArr.indexOf(currentKid) == -1) {
					var tempTime = j[i];
					if (tempTime <= nTime) {
						var tempText = s[i];
						loadedArr.push(currentKid);
						console.log(tempText);
						CKobject.getObjectById('ckplayer_a1').loadBarrage(tempText);
					}
				}
			}
		}
	}
}
