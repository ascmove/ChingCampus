/* 虚拟键盘的前往或者回车事件 */
$.fn.keyboardEnter = function(fun){
	$(this).keypress(function(event){
		event = (event) ? event:((window.event) ? window.event:"");
		var keycode = (event.keyCode ? event.keyCode : (event.which? event.which:event.charCode));
	    if(keycode === 13 && $.isFunction(fun) ){
	    	fun.call(this);
	    }
	});
	return $(this);
};

/*日期格式化功能*/
Date.prototype.format = function(format){
	var o = {
	"M+" : this.getMonth(), //month
	"d+" : this.getDate(), //day
	"h+" : this.getHours(), //hour
	"m+" : this.getMinutes(), //minute
	"s+" : this.getSeconds(), //second
	"q+" : Math.floor((this.getMonth()+3)/3), //quarter
	"S" : this.getMilliseconds() //millisecond
	}
	if (/(y+)/.test(format)) {
		format = format.replace(RegExp.$1, (this.getFullYear() + "").substr(4 - RegExp.$1.length));
	}
	for ( var k in o) {
		if (new RegExp("(" + k + ")").test(format)) {
			format = format.replace(RegExp.$1, RegExp.$1.length == 1 ? o[k]:("00" + o[k]).substr(("" + o[k]).length));
		}
	}
	return format;
}


/**
 * 爱吾享弹层
 */
$.i5xwxLayer = {
	close:function(closeObj){
		if (closeObj) {closeObj.remove();}
	},
	share:function(){
		this.bgDivId = "i5xwxLayer" + new Date().getTime();
		var shareDiv = '<div style="width:300px;height:206px;top:0px;right:15px;z-index:100001;position:fixed;background-repeat: no-repeat;background-size:300px 206px;background-image:url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAANwAAACXCAYAAAB+xTUuAAAAAXNSR0IArs4c6QAAAARnQU1BAACxjwv8YQUAAAAZdEVYdFNvZnR3YXJlAEFkb2JlIEltYWdlUmVhZHlxyWU8AAADZGlUWHRYTUw6Y29tLmFkb2JlLnhtcAAAAAAAPD94cGFja2V0IGJlZ2luPSLvu78iIGlkPSJXNU0wTXBDZWhpSHpyZVN6TlRjemtjOWQiPz4gPHg6eG1wbWV0YSB4bWxuczp4PSJhZG9iZTpuczptZXRhLyIgeDp4bXB0az0iQWRvYmUgWE1QIENvcmUgNS4wLWMwNjAgNjEuMTM0Nzc3LCAyMDEwLzAyLzEyLTE3OjMyOjAwICAgICAgICAiPiA8cmRmOlJERiB4bWxuczpyZGY9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkvMDIvMjItcmRmLXN5bnRheC1ucyMiPiA8cmRmOkRlc2NyaXB0aW9uIHJkZjphYm91dD0iIiB4bWxuczp4bXBNTT0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wL21tLyIgeG1sbnM6c3RSZWY9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9zVHlwZS9SZXNvdXJjZVJlZiMiIHhtbG5zOnhtcD0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wLyIgeG1wTU06T3JpZ2luYWxEb2N1bWVudElEPSJ4bXAuZGlkOjRBNjdGNTE4N0ZDQ0U0MTFCQTlFOEIzRDJEMkZENDUyIiB4bXBNTTpEb2N1bWVudElEPSJ4bXAuZGlkOkJDMjhGQ0E2Q0M4NTExRTRCNEU0Q0ZCRTA4MTA0M0VDIiB4bXBNTTpJbnN0YW5jZUlEPSJ4bXAuaWlkOkJDMjhGQ0E1Q0M4NTExRTRCNEU0Q0ZCRTA4MTA0M0VDIiB4bXA6Q3JlYXRvclRvb2w9IkFkb2JlIFBob3Rvc2hvcCBDUzUgV2luZG93cyI+IDx4bXBNTTpEZXJpdmVkRnJvbSBzdFJlZjppbnN0YW5jZUlEPSJ4bXAuaWlkOjRFNjdGNTE4N0ZDQ0U0MTFCQTlFOEIzRDJEMkZENDUyIiBzdFJlZjpkb2N1bWVudElEPSJ4bXAuZGlkOjRBNjdGNTE4N0ZDQ0U0MTFCQTlFOEIz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);"></div>';
		var $jQuerBgObj = $('<div id="' + this.bgDivId + '" style="top:0;left:0;width:100%;height:100%;background:rgba(0, 0, 0, 0.60);position:fixed;z-index:10000;">' + shareDiv + '</div>');
		$(document.body).append($jQuerBgObj);
		$jQuerBgObj.click(function(){
			close($(this));
		});
		return $jQuerBgObj;
	},
	loading:function(content){
		var loadingDiv = '<div style="height: 120px;background: #000;opacity: 0.6;color: #fff;text-align: center;margin: -60px 50px 0 50px;top: 50%;position: relative;padding: 20px;border-radius: 6px;"><img style="margin-bottom:10px" src="data:image/gif;base64,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"><br>&nbsp;' + content + '</div>';
		var $jQueryObj = $('<div style="top:0;left:0;width:100%;height:100%;line-height:100%;display:block;position:fixed;z-index:10003;">' + loadingDiv + '</div>');
		$(document.body).append($jQueryObj);
		//$jQueryObj.click(function(){
			//close($(this));
		//});
		return $jQueryObj;
	}
};

/**
 * 倒计时 jquery 插件
 * by Sea~
 */

 (function($,window) {//1431069800513(这是毫秒),$.now(),30,"还有timer距离您的预订时间" 参数这样的
    $.fn.reverseTimer = function( startTime,currentTime,retimeMinits,remindMsg ) {
		 var self = this;
		 var millisecond = retimeMinits * 60000;
		 var lasttime = startTime  - currentTime + millisecond;
		 var hour = Math.floor( lasttime / 3600000 );
	 	 var minute,second,timeStr;
		 window.reverseTimerchangeTime = function(){
		 	if( lasttime <= 0  ){
			 	timeStr =  "00:00:00" ;
			 	self.html( remindMsg.replace(/timer/g, timeStr) ) ;
			 	clearInterval( intervalTimer );
		 	}else{

		 		hour = Math.floor( lasttime / 3600000 ) ;
			 	minute = Math.floor( (lasttime - hour * 3600000)/60000 ) ;
			 	second = Math.floor( (lasttime - hour * 3600000 - minute * 60000)/1000 ) ;
			 	timeStr = hour + ":" + minute + ":" + second ;
			 	self.html( remindMsg.replace(/timer/g, timeStr) ) ;
		 	}
		 	lasttime = lasttime - 1000;//小时60*60*1000 分钟60*1000 秒1000
		 }
		 var intervalTimer = setInterval( "reverseTimerchangeTime()",1000 );
	};
})(jQuery,window);
//$("#targettext").reverseTimer( 1431314217657,$.now(),1,"还有timer距离您的预订时间timer" );
//使用方法，传参类型 ，开始时间，当前时间，倒计时时间，显示的文字

(function($,window) {
	    $.fn.tomakeitscroll = function( speed ) {
	    	var scroller = this[0];
	    	if( scroller ){
	    		var scrollText = scroller.innerHTML;
	    	}else{
	    		return;
	    	}
	    	var cw = document.documentElement.clientWidth;
			//删除子节点
			while( scroller.hasChildNodes() ){
				scroller.removeChild( scroller.firstChild );
			}
			scroller.style.overflow = "hidden";
			scroller.style.whiteSpace = "nowrap";
			//创建新的节点
			// var scroll_div = document.createElement( "div" );
			// scroll_div.id = "scroll_div1";
			// scroller.appendChild( scroll_div );
			var scroll_begin1 = document.createElement( "div" );
			scroll_begin1.id = "scroll_begin1";
			scroll_begin1.innerHTML = scrollText;
			scroll_begin1.style.display = "inline-block";
			//scroll_begin1.style.minWidth = cw + "px";
			scroll_begin1.style.minWidth = "100%";
			scroller.appendChild( scroll_begin1 );
			var scroll_end1 = document.createElement( "div" );
			scroll_end1.id = "scroll_end1";
			scroll_end1.innerHTML = scrollText;
			scroll_end1.style.display = "inline-block";
			//scroll_end1.style.minWidth = cw + "px";
			scroll_end1.style.minWidth = "100%";
			scroller.appendChild( scroll_end1 );
			//alert( scrollText );
			function Marquee1(){
				if(scroll_end1.offsetWidth-scroller.scrollLeft<=0){
					scroller.scrollLeft-=scroll_begin1.offsetWidth;
				}
				else{
					scroller.scrollLeft++;
				}
			}
			var MyMar = setInterval(Marquee1,speed);
		};
})(jQuery,window);

//长按递增递减
(function($,window) {
    $.fn.longClick = function( time,fn) {
    	var interval = [];
		var isAboveLongPress;
		var btnDown;
		var btnUp;
		//判断是移动还是pc
        var system = {
        win: false,
        mac: false,
        xll: false
        };
        //检测平台
        var p = navigator.platform;
        system.win = p.indexOf("Win") == 0;
        system.mac = p.indexOf("Mac") == 0;
        system.x11 = (p == "X11") || (p.indexOf("Linux") == 0);
        	//跳转语句
            if (system.win || system.mac || system.xll) {//pc
                btnDown = "mousedown";
                btnUp = "mouseup";
            } else {  //手机
                btnDown = "touchstart";
                btnUp = "touchend";
            }
    	$(this).bind(btnDown,function(ev){
    		var self = this;
		    ev.preventDefault();
		    isAboveLongPress = true;
		    var self = this;
			setTimeout(function(){
				if( isAboveLongPress ){
					interval.push( setInterval(function(){
						fn( self,ev );
					},120));
				}
			},time * 1000);
		}).bind(btnUp,function(ev){
			for( var i = 0; i < interval.length ; i++ ){
				clearInterval( interval[i] );
			}
			fn( this,ev );
			isAboveLongPress = false;
		});
	}
})(jQuery,window);

/**
 *  验证码倒计时插件，seconds倒计时秒 暂时支持span按钮 李鑫
 */
$.fn.captchaCountDown = function(seconds){
	var $t = $(this);
	$t.addClass("disabled");
	console.log("$t.attr(initText)" + $t.attr("initText"));
	var initText = $t.attr("initText") || $t.text();
	var interObj = window.setInterval(function(){
		if (isNaN(parseInt(seconds)) || (seconds < 1)){
			$t.text(initText).removeClass("disabled");
			window.clearInterval(interObj);//停止计时器
		} else {
			$t.text("重新发送(" + seconds-- + ")");
		}
	}, 1000);
	return $(this);
};