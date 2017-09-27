/**
 * 工具类
 */
var ToolUtil = {};

/* *********************校验代码段***********************/
/**
 * 校验手机号码
 */
ToolUtil.isMobile = function (str) {
	var patrn = /^(13[0-9]|14[0-9]|15[0-9]|17[0-9]|18[0-9])\d{8}$/;
	return patrn.test(str);
};

/**
 * 校验电话号码
 */
ToolUtil.isPhone = function (str) {
	var patrn = /^(0[\d]{2,3}-)?\d{6,8}(-\d{3,4})?$/;
	return patrn.test(str);
};


/**
 * 校验URL地址
 */
ToolUtil.isUrl=function(str){
	var patrn= /^http[s]?:\/\/[\w-]+(\.[\w-]+)+([\w-\.\/?%&=]*)?$/;
	return patrn.test(str);
};

/**
 * 校验电邮地址
 */
ToolUtil.isEmail = function (str) {
 var patrn = /^[\w-]+@[\w-]+(\.[\w-]+)+$/;
 return patrn.test(str);
};

/**
 * 校验邮编
 */
ToolUtil.isZipCode = function (str) {
 var patrn = /^\d{6}$/;
 return patrn.test(str);
};


/**
 * 校验验证码
 */
ToolUtil.isVerificationCode = function (str) {
	 var patrn = /^\d{6}$/;
	 return patrn.test(str);
};


/**
 * 校验合法时间
 */
ToolUtil.isDate = function (str) {
  if(!/\d{4}(\.|\/|\-)\d{1,2}(\.|\/|\-)\d{1,2}/.test(str)){
    return false;
  }
  var r = str.match(/\d{1,4}/g);
  if(r==null){return false;};
  var d= new Date(r[0], r[1]-1, r[2]);
  return (d.getFullYear()==r[0]&&(d.getMonth()+1)==r[1]&&d.getDate()==r[2]);
};

/**
 * 判断字符串是否为空
 */
ToolUtil.strIsEmpty = function(str){
    return str == null || !str || typeof str == undefined || str == '' || str.replace(/(^\s*)|(\s*$)/g, "").length == 0;
}

/* *********************校验代码段***********************/


/* *********************处理代码段***********************/
/**
 * 格式化字符串，使用方式类似java中的String.format()方法
 */
ToolUtil.format = function(format){
   var args = [];
   for(var i=1; i<arguments.length; i++){
       args.push(arguments[i]);
   }
   return format.replace(/\{(\d+)\}/g, function(m, i){
       return args[i];
   });
}


/**************************************************************
将传入值转换成整数
**************************************************************/
ToolUtil.parseInteger = function(v){
    if(typeof v == 'number'){
        return v;
    }else if(typeof v == 'string'){
        var ret = parseInt(v);

        if(isNaN(ret) || !isFinite(ret)){
            return 0;
        }

        return ret;
    }else{
        return 0;
    }
}

/**************************************************************
将传入值转换成小数
**************************************************************/
ToolUtil.parseFloat = function(v){
    if(typeof v == 'number'){
        return v;
    }else if(typeof v == 'string'){
        var ret = parseFloat(v);
        if(isNaN(ret) || !isFinite(ret)){
            return 0;
        }

        return ret;
    }else{
        return 0;
    }
}

/**************************************************************
将传入值转换成小数,传入值可以是以逗号(,)分隔的数字，此方法将会过滤掉(,)
**************************************************************/
ToolUtil.parseDotFloat = function(v){
    if(typeof v == 'number'){
        return v;
    }else if(typeof v == 'string'){
        v = v.replace(/[^\d|.]/g , '');
        v = parseFloat(v);

        if(isNan(v) || !isFinite(v)){
            return 0
        }
        return ret;
    }else{
        return 0;
    }
}


/**************************************************************
检查标签值是否为空，当为空时提示

@param el {Element, string}检查的标签
@param msg {string}提示消息，当检查失败时提示
@return true检查通过，标签的值不空，false标签值为空
**************************************************************/
ToolUtil.checkIsNotEmpty = function(el, msg){
    if(typeof el == 'string'){
        el = $(el);
    }

    if(Util.strIsEmpty(el.value)){
        alert(msg);
        if(!el.disabled){
            el.focus();
            el.select();
        }
        return false;
    }
    return true;
}


/**************************************************************
字符串转换成日期
@param str {String}字符串格式的日期
@return {Date}由字符串转换成的日期
**************************************************************/
ToolUtil.str2date = function(str){
    var   re   =   /^(\d{4})\S(\d{1,2})\S(\d{1,2})$/;
    var   dt;
    if   (re.test(str)){
        dt   =   new   Date(RegExp.$1,RegExp.$2   -   1,RegExp.$3);
    }
    return dt;
}

/**************************************************************
计算2个日期之间的天数

@day1 {Date}起始日期
@day2 {Date}结束日期
@return day2 - day1的天数差
**************************************************************/
ToolUtil.dayMinus = function(day1, day2){
    var days = Math.floor((day2-day1)/(1000 * 60 * 60 * 24));
    return days;
}

/**************************************************************
设置组合列表框选择项

@param combo 组合列表框
@param value 选择值
@param defaultIdx 默认选中项的序号
**************************************************************/
ToolUtil.setComboSelected = function(combo, value, defaultIdx){
    if(typeof combo == 'string'){
        combo = $(combo);
    }

    var idx = defaultIdx;
    if(typeof defaultIdx == 'undefined'){
        idx = -1;
    }

    for(var i=0, len=combo.options.length; i<len; ++i){
        var v = combo.options[i].value;
        if(v == value){
            idx = i;
            break;
        }
    }

    combo.selectedIndex = idx;
}

/**
 * 阻止后退
 */
ToolUtil.stopBack = function(isReload){
	/* 禁止后退 */
	if (window.history && window.history.pushState) {
		alert("进入document.referrer:" + document.referrer);
		alert("进入window.location:" + window.location);
		alert("进入window.history.state:" + window.history.state);
		$(window).on('popstate',function() {
			alert("popstate document.referrer:" + document.referrer);
			alert("popstate window.location:" + window.location);
			alert("popstate window.history.state:" + window.history.state);
			if (null === window.history.state){
				window.location.reload();
				//window.location.href = document.referrer;
				//isReload?window.location.reload():window.history.pushState('forward', null, '#forward');
			}
		})
		window.history.pushState('forward', null, '#forward');
	}
}

/**
 * 数组对象放到一个对象内
 */
ToolUtil.ArrayObjToSingleObj = function(ArrayObj){
	if (ArrayObj == null) return null;
	var vObj = {}; vObj = [];
	$.each(ArrayObj, function(key, val) {
		vObj = $.merge(vObj, val);
	});
	return vObj;
}

/**
 * 还原金额，将金额还原成纯数字格式
 */
ToolUtil.rMoney = function(s){
	return parseFloat(s.replace(/[^\d\.-]/g, ""));
}

/**
 * 格式化金额，四舍五入小数，并采取千位，号分割
 */
ToolUtil.fMoney = function(s, n, m){
	m = m !== undefined ? m : "￥"; //默认人民币
	n = n > 0 && n <= 20 ? n : 2;
	s = parseFloat((s + "").replace(/[^\d\.-]/g, "")).toFixed(n) + "";
	var l = s.split(".")[0], r = s.split(".")[1];
	var idx = l.indexOf("-") < 0 ? 0 : 1, t = l.split("");
	t.splice(idx, 0, m);
	return t.join("") + (parseFloat(r) > 0 ? "." + r : "");
}

/**
 * 格式化字符串去掉 \\ \b \t .. 这样的json不能解析的字符
 */
ToolUtil.replaceSpcialChar = function( rawStr ){
	var reg  = /\\/g; //代表一个反斜线字符''\'
	var reg1 = /\b/g; //退格
	var reg2 = /\t/g; //水平制表(HT) （跳到下一个TAB位置）
	var reg3 = /\n/g; //换行(LF) ，将当前位置移到下一行开头
	var reg4 = /\f/g; //换页(FF)，将当前位置移到下页开头
	var reg5 = /\r/g; //回车(CR) ，将当前位置移到本行开头
	rawStr = rawStr.replace(reg,"\\u005c");
	rawStr = rawStr.replace(reg1,"");
	rawStr = rawStr.replace(reg2,"");
	rawStr = rawStr.replace(reg3,"<br/>");
	rawStr = rawStr.replace(reg4,"");
	rawStr = rawStr.replace(reg5,"");
	return rawStr;
}

/**
 * 将json字符串转成js对象，可以避免传统的json.parse方法不兼容的一些换行符
 */

ToolUtil.jsonStrToObj = function( jsonStr ){
	var theJsonValue;
	eval("theJsonValue = "+ToolUtil.replaceSpcialChar(jsonStr));
	return theJsonValue;
}

/**
 * 解析类似 "http://m.dianping.com/hobbit/order/scanredirect?shopid=5211238&tablenum=B123"
 * 这种url
 */

ToolUtil.getRequest = function( targeturl ){
	// "http://m.dianping.com/hobbit/order/scanredirect?shopid=5211238&tablenum=B123"
	//   var url = "?shopid=5211238&tablenum=B123"; //获取url中"?"符后的字串
	   var url = targeturl.slice( targeturl.indexOf("?") );
	   var theRequest = new Object();
	   if (url.indexOf("?") != -1) {
	      var str = url.substr(1);
	      strs = str.split("&");
	      for(var i = 0; i < strs.length; i ++) {
	         theRequest[strs[i].split("=")[0]]=unescape(strs[i].split("=")[1]);
	      }
	   }
	   return theRequest;
}

/**
 * 增加时间戳
 */
ToolUtil.addTimeStamp = function( url ) {
	return url + (url.indexOf("?") != -1?"&":"?") + "t=" + new Date().getTime();
}
