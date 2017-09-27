/*浏览器判断*/
(function($){

	function detectUA($, userAgent) {
            $.os = {};
            $.os.webkit = userAgent.match(/WebKit\/([\d.]+)/) ? true: false;
            $.os.android = userAgent.match(/(Android)\s+([\d.]+)/) || userAgent.match(/Silk-Accelerated/) ? true: false;
            $.os.androidICS = $.os.android && userAgent.match(/(Android)\s4/) ? true: false;
            $.os.ipad = userAgent.match(/(iPad).*OS\s([\d_]+)/) ? true: false;
            $.os.iphone = !$.os.ipad && userAgent.match(/(iPhone\sOS)\s([\d_]+)/) ? true: false;
            $.os.ios7 = ($.os.ipad || $.os.iphone) && userAgent.match(/7_/) ? true: false;
            $.os.webos = userAgent.match(/(webOS|hpwOS)[\s\/]([\d.]+)/) ? true: false;
            $.os.touchpad = $.os.webos && userAgent.match(/TouchPad/) ? true: false;
            $.os.ios = $.os.ipad || $.os.iphone;
            $.os.playbook = userAgent.match(/PlayBook/) ? true: false;
            $.os.blackberry10 = userAgent.match(/BB10/) ? true: false;
            $.os.blackberry = $.os.playbook || $.os.blackberry10 || userAgent.match(/BlackBerry/) ? true: false;
            $.os.chrome = userAgent.match(/Chrome/) ? true: false;
            $.os.opera = userAgent.match(/Opera/) ? true: false;
            $.os.fennec = userAgent.match(/fennec/i) ? true: userAgent.match(/Firefox/) ? true: false;
            $.os.ie = userAgent.match(/MSIE 10.0/i) || userAgent.match(/Trident\/7/i) ? true: false;
            $.os.ieTouch = $.os.ie && userAgent.toLowerCase().match(/touch/i) ? true: false;
            $.os.tizen = userAgent.match(/Tizen/i) ? true: false;
            $.os.supportsTouch = ((window.DocumentTouch && document instanceof window.DocumentTouch) || 'ontouchstart' in window);
            $.feat = {};
            var head = document.documentElement.getElementsByTagName("head")[0];
            $.feat.nativeTouchScroll = typeof(head.style["-webkit-overflow-scrolling"]) !== "undefined" && ($.os.ios || $.os.blackberry10);
            $.feat.cssPrefix = $.os.webkit ? "Webkit": $.os.fennec ? "Moz": $.os.ie ? "ms": $.os.opera ? "O": "";
            $.feat.cssTransformStart = !$.os.opera ? "3d(": "(";
            $.feat.cssTransformEnd = !$.os.opera ? ",0)": ")";
            if ($.os.android && !$.os.webkit) $.os.android = false
    }
    detectUA($, navigator.userAgent);

	$.fn.cssTranslate = function(val) {
		return this.vendorCss("Transform", "translate" + $.feat.cssTransformStart + val + $.feat.cssTransformEnd)
	};

	$.fn.vendorCss = function(attribute, value, obj) {
        return this.css1($.feat.cssPrefix + attribute, value, obj)
    };

	$.fn.offset1 = function() {
		var obj;
		if (this.length === 0) return this;
		if (this[0] == window) return {
			left: 0,
			top: 0,
			right: 0,
			bottom: 0,
			width: window.innerWidth,
			height: window.innerHeight
		};
		else obj = this[0].getBoundingClientRect();
		return {
			left: obj.left + window.pageXOffset,
			top: obj.top + window.pageYOffset,
			right: obj.right + window.pageXOffset,
			bottom: obj.bottom + window.pageYOffset,
			width: obj.right - obj.left,
			height: obj.bottom - obj.top
		}
	};

	$.fn.height1 = function(val) {
		if (this.length === 0) return this;
		if (val != undefined) return this.css1("height", val);
		if (this[0] == this[0].window) return window.innerHeight + '';
		if (this[0].nodeType == this[0].DOCUMENT_NODE) return this[0].documentElement.offsetheight + '';
		else {
			var tmpVal = this.css1("height").replace("px", "");
			if (tmpVal) return tmpVal;
			else return this.offset1().height + ''
		}
	}

	$.fn.width1 = function(val) {
		if (this.length === 0) return this;
		if (val != undefined) return this.css1("width", val);
		if (this[0] == this[0].window) return window.innerWidth + '';
		if (this[0].nodeType == this[0].DOCUMENT_NODE) return this[0].documentElement.offsetwidth + '';
		else {
			var tmpVal = this.css1("width").replace("px", "");
			if (tmpVal) return tmpVal;
			else return this.offset().width + ''
		}
	}

	$.fn.css1 = function(attribute, value, obj) {
		var toAct = obj != undefined ? obj: this[0];
		if (this.length === 0) return this;
		if (value == undefined && typeof(attribute) === "string") {
			var styles = window.getComputedStyle(toAct);
			return toAct.style[attribute] ? toAct.style[attribute] : window.getComputedStyle(toAct)[attribute]
		}
		for (var i = 0; i < this.length; i++) {
			if ($.isObject1(attribute)) {
				for (var j in attribute) {
					this[i].style[j] = _addPx(j, attribute[j])
				}
			} else {
				this[i].style[attribute] = _addPx(attribute, value)
			}
		}
		return this
	}

	$.isObject1 = function(obj) {
        return typeof obj === "object" && obj !== null
    };

	function _addPx(prop, val) {
		var cssNumber = {
            "columncount": true,
            "fontweight": true,
            "lineheight": true,
            "column-count": true,
            "font-weight": true,
            "line-height": true,
            "opacity": true,
            "orphans": true,
            "widows": true,
            "zIndex": true,
            "z-index": true,
            "zoom": true
		};

		return (typeof(val) === "number") && !cssNumber[prop.toLowerCase()] ? val + "px": val
	}

})($);
/*
 */
var jsea = $;
(function (C) {
	C.fn.seaScrollbar = function (D) {
		return new A(D, this)
	};
	var B = new Array();
	var A = function (F, E) {
		var D = this;
		D.element = C(E);
		D.opt = {
			moveY : 0,
			maxMoveY : 0
		};
		D.scrollNode = null;
		D.barNode = null;
		D.barNodeHeight = 0;
		D.barNodeLock = 0;
		D.param = C.extend({}, {
				isShowBar : false //滚动条是否显示
			}, F);
		D.start();
		B.push(D)
	};
	A.prototype = {
		start : function () {
			var O = this,
			E = this.element,
			L = 0,
			P,
			M;
			var F = E.height1();
			if (F.indexOf("%") >= 0 || isNaN(F)) {
				var D = E.parent().height1();
				F = (D > window.innerHeight ? window.innerHeight : D) * parseFloat(F.replace("%", "")) / 100
			}
			var J = false;
			if (E.css1({
					"overflow" : "hidden",
					"height" : F + "px"
				}).children().eq(0).attr("scrollbarBox") != "seaScrollbar") {
				E.html('<div scrollbarBox="seaScrollbar" style="width:100%;">' + E.html() + "</div>");
				J = true
			}
			var K = E.children().eq(0);
			O.scrollNode = K;
			O.opt.maxMoveY = F - K.height1();
			O.opt.maxMoveY = O.opt.maxMoveY > 0 ? 0 : O.opt.maxMoveY;
			if (O.param.isShowBar) {
				var N = E.css1("position");
				if (N != "absolute" && N != "fixed") {
					E.css1("position", "relative")
				}
				if (J) {
					var H = document.createElement("div");
					E.append(H);
					var G = O.barNode = C(H);
					G.attr("barNode", "yes")
				} else {
					var G = O.barNode = E.find("[barNode]");
					E.unbind("touchstart touchmove touchend")
				}
				var I = F * F / K.height1();
				I = I > F ? F : I;
				G.css1({
					"position" : "absolute",
					"top" : "0",
					"right" : "2px",
					"width" : "5px",
					"height" : I + "px",
					"background-color" : "#000",
					"opacity" : "0.3",
					"border-radius" : "5px",
					"display" : "none"
				});
				O.barNodeHeight = G.height1()
			}
			E.bind("touchstart", function (Q) {
				Q.stopPropagation();
				K.css1({
					"-webkit-transition-duration" : "0s",
					"transition-duration" : "0s"
				});
				O.barNodeLock = 1;
				if (O.param.isShowBar) {
					G.css1({
						"-webkit-transition-duration" : "0ms",
						"transition-duration" : "0ms"
					});
					G.css1("opacity", "0.3");
					G.show()
				}
				M = Q.originalEvent.touches[0].pageY;
				L = Q.originalEvent.touches[0].pageY - O.opt.moveY;
				P = Q.originalEvent.timeStamp || Date.now()
			});
			E.bind("touchmove", function (Q) {
				Q.preventDefault();
				Q.stopPropagation();
				O.barNodeScolling();
				var R = Q.originalEvent.touches[0].pageY;
				O.opt.moveY = R - L;
				K.cssTranslate("0," + O.opt.moveY + "px");
				var S = Q.timeStamp || Date.now();
				if (S - P > 300) {
					M = R;
					P = S
				}
			});
			E.bind("touchend", function (R) {
				var T = (R.originalEvent.changedTouches[0].timeStamp || Date.now()) - P;
				var S = R.originalEvent.changedTouches[0].pageY - M;
				if (T < 300 && Math.abs(S) > 10 && O.opt.moveY < 0 && O.opt.moveY > O.opt.maxMoveY) {
					var Q = O.momentum(S, T);
					O.opt.moveY += Q.dist;
					if (O.opt.moveY > 0) {
						O.opt.moveY = 20
					} else {
						if (O.opt.moveY < O.opt.maxMoveY) {
							O.opt.moveY = O.opt.maxMoveY - 20
						}
					}
					K.css1({
						"-webkit-transition-duration" : Q.time + "ms",
						"transition-duration" : Q.time + "ms"
					});
					K.cssTranslate("0," + O.opt.moveY + "px");
					O.barNodeScolling(Q.time);
					O.rebound(Q.time)
				} else {
					O.rebound()
				}
			})
		},
		rebound : function (D) {
			var E = this;
			var F = 0;
			if (!D) {
				D = 0
			}
			setTimeout(function () {
				$tmp = E.scrollNode;
				$tmp.css1({
					"-webkit-transition-duration" : "0.2s",
					"transition-duration" : "0.2s"
				});
				if (E.opt.moveY > 0) {
					$tmp.cssTranslate("0,0");
					E.opt.moveY = 0;
					F = 300
				} else {
					if (E.opt.moveY < E.opt.maxMoveY) {
						$tmp.cssTranslate("0," + E.opt.maxMoveY + "px");
						E.opt.moveY = E.opt.maxMoveY;
						F = 300
					}
				}
				E.barNodeScolling(F);
				E.barNodeLock = 0;
				setTimeout(function () {
					if (!E.barNodeLock && E.param.isShowBar) {
						var G = E.barNode;
						G.css1({
							"-webkit-transition-duration" : "500ms",
							"transition-duration" : "500ms"
						});
						G.css1("opacity", "0");
						setTimeout(function () {
							if (!E.barNodeLock) {
								G.hide();
								G.css1({
									"-webkit-transition-duration" : "0ms",
									"transition-duration" : "0ms"
								});
								G.css1("opacity", "0.3")
							}
						}, 500)
					}
				}, F)
			}, D)
		},
		momentum : function (H, I) {
			var G = 0.001,
			E = Math.abs(H) / I,
			F = (E * E) / (2 * G),
			D = E / G;
			return {
				dist : H > 0 ? F : -F,
				time : D
			}
		},
		barNodeScolling : function (E, G) {
			var I = this;
			if (I.param.isShowBar) {
				var F = I.scrollNode;
				var D = I.element;
				var K = I.barNode;
				if (!E) {
					E = 0
				}
				var L = I.opt.moveY / (F.height1() / D.height1());
				var J = D.height1() - 5;
				K.css1({
					"-webkit-transition-duration" : E + "ms",
					"transition-duration" : E + "ms"
				});
				K.cssTranslate("0," + (L > 0 ? 0 : L < -J ? J : -L) + "px");
				var H = I.barNodeHeight - (L > 0 ? L : L <  - (J - I.barNodeHeight + 5) ?  - (J - I.barNodeHeight + 5) - L : 0);
				H = H < 5 ? 5 : H;
				K.css1("height", H + "px")
			}
		},
		setMoveY:function( val ){
			var self = this;
			this.opt.moveY = val;
		}
	}
})(jsea);
(function (D) {
	var C;
	D.seaPopup = D.fn.seaPopup = function (E) {
		return new B(E)
	};
	var A = new Array();
	var B = function (E) {
		var F = this;
		F.popupNode = C;
		//下面的os的这个方法得写写
		F.windowHeight = D.os.android ? (window.innerHeight > 480 ? window.innerHeight : 480) : window.innerHeight;
		F.isLock = 0;
		F.hasPopupPosition = 0;
		F.isBlurPosition = false;
		F.opt = D.extend({}, {
				bgStyle : "",
				boxStyle : "",
				title : "",
				titleStyle : "",
				message : "",
				btnStyle : "",
				cancelText : "取消",
				cancelStyle : "",
				cancelCallback : function () {},
				doneText : "确认",
				doneStyle : "",
				doneCallback : function () {},
				cancelOnly : false,
				noBtn : false,
				position : "center",
				height : 0.7,
				duration : 300,
				closeText : "",
				closeCallBack : function(){},
				closeStyle : "",
				isScroll : true,
				onShow : function () {}

			}, E);
		F.seaprepend();
		A.push(F)
	};
	B.fn = B.prototype = {
		seaprepend : function () {
			var W = this;
			var Y = W.opt.bgStyle,
			L = W.opt.boxStyle,
			M = W.opt.title,
			R = W.opt.titleStyle,
			X = W.opt.message,
			Q = W.opt.btnStyle,
			S = W.opt.cancelText,
			V = W.opt.cancelStyle,
			J = W.opt.cancelCallback,
			I = W.opt.doneText,
			T = W.opt.doneStyle,
			N = W.opt.doneCallback,
			G = W.opt.cancelOnly,
			F = W.opt.noBtn,
			U = W.opt.position,
			O = W.opt.closeText,
			K = W.opt.closeStyle,
			P = W.opt.onShow;
			closeCallBack = W.opt.closeCallBack;
			var E = document.createElement("div");
			var H = O == "" ? "" : '<div class="seaPopupCloseBtn85js93" style="position:absolute;height:28px;line-height:28px;width:50px;top:8px;right:8px;color:#868686;font-weight:normal;' + K + '">' + O + "</div>";
			var Z = '<div class="seaPopupBgDiv847fd8" style="position:fixed;width:100%;height:100%;top:0;left:0; background-color:#666; opacity:0.5; z-index:' + (3000 + A.length) + ";" + Y + '"></div><div class="seaPopupBox39fj7d" style="position:fixed;display:none;left:0;' + (U == "center" ? "top:50%;left:-80%;width:80%;border-radius:5px;" : U == "bottom" ? "bottom:-100%;left:0;width:100%;" : "top:-100%;left:0;width:100%;") + "z-index:" + (3001 + A.length) + ";color:#454545;background-color:#fff;" + L + '">' + (M == "" ? "" : '<div class="seaPopupTitle384jx8" style="padding:10px 10px;line-height:26px;text-align:left;font-size:1.2em;position:relative;text-overflow:ellipsis;white-space:nowrap;overflow:hidden;' + R + '">' + M + H + "</div>") + '<div class="seaPopupContent98fd3" style="width:100%;line-height:22px;text-align:center;">' + X + "</div>";
			if (!F && U == "center") {
				Z += '<ul class="seaPopupBtn739jg0s" style="position:relative;z-index:9999;width:100%;color:#007aff;height:45px;line-height:45px;border-top:1px solid #bcbcbc;margin-top:15px;' + Q + '">	<li style="float:left;width:50%;text-align:center;' + V + '">' + S + "</li>" + (G ? "" : '<li style="float:left;width:50%;box-sizing:border-box;text-align:center;border-left:1px solid #bcbcbc;' + T + '">' + I + '</li><li style="height:0;clear:both;"></li>') + "</ul>"
			}
			Z += "</div>";
			E.innerHTML = Z;
			W.popupNode = E;
			$el = D(E);
			D("body").append(E);
			if (!F && U == "center") {
				$el.find(".seaPopupBtn739jg0s li").eq(0).bind("click", function () {
					J();
					W.remove(0)
				});
				if (!G) {
					$el.find(".seaPopupBtn739jg0s li").eq(1).bind("click", function () {
						N();
						W.remove(0)
					})
				}
			} else {
				$el.find(".seaPopupBgDiv847fd8").bind("click", function () {
					W.remove()
				})
			}
			$el.find(".seaPopupCloseBtn85js93").bind("click", function () {
				closeCallBack();
				W.remove()
			});
			$el.bind("orientationchange", function () {
				W.positionPopup()
			});
			W.inputPosition();
			D(window).bind("touchmove", function (a) {
				a.preventDefault()
			});
			if (G) {
				$el.find(".seaPopupBtn739jg0s li").eq(0).css1({
					"float" : "none",
					"width" : "100%",
					"border" : "0"
				})
			}
			W.opt.duration = U == "center" ? 100 : 300;
			W.positionPopup();
			P()
		},
		inputPosition : function () {
			var E = this;
			D(E.popupNode).find("input").unbind("focus").bind("focus", function (F) {
				if (E.opt.position != "bottom") {
					E.isBlurPosition = false;
					var G = this;
					var I = G.getBoundingClientRect().bottom;
					//console.log(G.getBoundingClientRect());
					var H = I - ((E.windowHeight - 260) / 2 + 20) + E.hasPopupPosition;
					E.positionPopup(H);
					E.hasPopupPosition = H;
					D(G).unbind("blur").bind("blur", function () {
						E.isBlurPosition = true;
						setTimeout(function () {
							if (E.isBlurPosition) {
								E.positionPopup();
								E.hasPopupPosition = 0
							}
						}, 100)
					})
				}
			})
		},
		lock : function () {
			this.isLock = 1
		},
		unlock : function () {
			this.isLock = 0
		},
		show : function () {
			D(this.popupNode).show();
			D(window).unbind("touchmove").bind("touchmove", function (E) {
				E.preventDefault()
			})
		},
		hide : function (F) {
			var E = this;
			var H = E.opt.position;
			var J = isNaN(F) ? E.opt.duration : F;
			var I = D(E.popupNode).find(".seaPopupBox39fj7d");
			I.css1({
				"-webkit-transition-duration" : J + "ms"
			});
			var G = I.css1(H == "center" ? "top" : H);
			I.css1(H == "center" ? "top" : H, -E.opt.height * E.windowHeight);
			setTimeout(function () {
				D(window).unbind("touchmove");
				D(E.popupNode).hide();
				I.css1({
					"-webkit-transition-duration" : "0ms"
				});
				I.css1(H == "center" ? "top" : H, G)
			}, J)
		},
		remove : function (F) {
			var E = this;
			if (!E.isLock) {
				var G = E.opt.position;
				var I = isNaN(F) ? E.opt.duration : F;
				var H = D(E.popupNode).find(".seaPopupBox39fj7d");
				H.css1({
					"-webkit-transition-duration" : I + "ms"
				});
				H.css1(G == "center" ? "top" : G, -E.opt.height * E.windowHeight);
				setTimeout(function () {
					A.splice(0, 1);
					D(window).unbind("touchmove");
					D(E.popupNode).unbind("orientationchange").remove();
					if (A.length > 0) {
						A[0].show()
					}
				}, I)
			}
		},
		positionPopup : function (I) {

			var N = this;
			//console.log(this);
			var E = I ? I : 0;
			var L = N.opt.position;
			var G = $(this.popupNode).find(".seaPopupBox39fj7d");
			var F = G.width1() ;
			if (F.indexOf("%") > 0) {
				F = F.substring(0, F.length - 1) / 100 * window.innerWidth
			}
			var H = N.opt.duration;
			G.css1({
				"left" : (window.innerWidth - F) / 2,
				"display" : "block",
				"-webkit-transition-duration" : H + "ms"
			});
			var J = N.windowHeight;
			var O = N.opt.title == "" ? 0 : G.find(".seaPopupTitle384jx8").offset1().height;
			var K = N.opt.noBtn || L != "center" ? 0 : G.find(".seaPopupBtn739jg0s").offset1().height;
			var M = (1 - N.opt.height) * J;
			if (G.offset1().height > J - M || L == "bottom") {
				G.find(".seaPopupContent98fd3").css1({
					"height" : (J - O - K - M) + "px",
					"overflow" : "hidden"
				});
				if (N.opt.isScroll) {
					$el.find(".seaPopupContent98fd3").seaScrollbar();
					N.inputPosition()
				}
			}
			//var tttt = L == "center" ? "top" : L;
			//var tttts = L == "center" ? (J - E * 2 - G.offset1().height) / 2 : L == "bottom" ? E : -E;
			G.css1(L == "center" ? "top" : L, L == "center" ? (J - E * 2 - G.offset1().height) / 2 : L == "bottom" ? E : -E)
			//G.css({tttt:tttts+"px"});
		}
	}
})(jsea);
(function (A) {
	A.fn.seaSelectDlg = function (B, C) {
		if (!A.isArray(B)) {
			return console.log("first param is not array!")
		}
		var G = this,
		E = A(this);
		var D = E.data("value");
		var F = E.html();
		if (D == "" || F == "") {
			D = B[0]["value"];
			F = B[0]["text"];
			E.data("value", D);
			E.html(F)
		}
		E.unbind("click").bind("click", function () {
			D = E.data("value");
			var I = A.extend({}, {
					liStyle : "",
					selectedStyle : "",
					selectedImgUrl : "",
					selectedImgStyle : "",
					selectedCallback : function () {},
					boxStyle : "background:#f5f5f5;",
					title : "请选择",
					titleStyle : "background:#f9f9f9;border-bottom:1px solid #ddd;",
					position : "bottom"
				}, C);
			var H = !!I.selectedImgUrl ? '<img src="' + I.selectedImgUrl + '" style="padding-left:15px;width:13px;vertical-align:middle;position:absolute;top:16px;' + I.selectedImgStyle + '" />' : "";
			var K = '<ul class="seaSelectDlg37dj8">';
			for (var M in B) {
				var J = B[M];
				K += '<li data-value="' + J["value"] + '" data-text="' + J["text"] + '" style="font-size: 24px;line-height:22px; padding:12px 0 12px; color:#454545; border-bottom:1px solid #eceaea;text-align:center;position:relative;' + I.liStyle + (D == J["value"] ? (!I.selectedImgUrl ? "background:#f7941e;color:#fff;" : "") + I.selectedStyle : "") + '">' + J["text"] + (D == J["value"] ? H : "") + "</li>"
			}
			K += "</ul>";
			I = A.extend({}, {
					message : K
				}, I);
			var L = A.seaPopup(I);
			A(".seaSelectDlg37dj8 li").bind("click", function () {
				D = A(this).data("value");
				F = A(this).data("text");
				E.data("value", D);
				E.html(F);
				L.remove();
				I.selectedCallback({
					value : D,
					text : F
				})
			})
		})
	}



	A.fn.selectPeopleNumDlg = function (B, C) {
		if (!A.isArray(B)) {
			return console.log("first param is not array!")
		}
		var G = this,
		E = A(this);
		var D = E.data("value");
		var F = E.html();
		if (D == "" || F == "") {
			D = B[0]["value"];
			F = B[0]["text"];
			E.data("value", D);
			E.html(F)
		}
		E.unbind("click").bind("click", function () {
			D = E.data("value");
			var I = A.extend({}, {
					liStyle : "",
					selectedStyle : "",
					selectedImgUrl : "",
					selectedImgStyle : "",
					selectedCallback : function () {},
					boxStyle : "background:#f5f5f5;",
					title : "请选择用餐人数",
					titleStyle : "background:#f9f9f9;text-align:left;font-size:1.2em",
					position : "bottom"
				}, C);
			var H = !!I.selectedImgUrl ? '<img src="' + I.selectedImgUrl + '" style="padding-left:15px;width:13px;vertical-align:middle;position:absolute;top:16px;' + I.selectedImgStyle + '" />' : "";
			var K = '<ul class="selectPeopleNumDlg37dj8" style="text-align:left;padding:0 10px;">';
			for (var M in B) {
				var J = B[M];
				K += '<li data-value="' + J["value"] + '" data-text="' + J["text"] + '" style="display:inline-block;position: relative;font-size: 0.9375em;color: #454545;border: 1px solid #a9a9a9;text-align: center;width:23.66%;padding:4px 0;border-radius: 4px;margin: 2px 2px;' + I.liStyle + (D == J["value"] ? (!I.selectedImgUrl ? "background:#f7941e;color:#fff;" : "") + I.selectedStyle : "") + '">' + J["text"] + (D == J["value"] ? H : "") + "</li>"
			}
			K += "</ul>";
			I = A.extend({}, {
					message : K
				}, I);
			var L = A.seaPopup(I);
			A(".selectPeopleNumDlg37dj8 li").bind("click", function () {
				D = A(this).data("value");
				F = A(this).data("text");
				E.data("value", D);
				E.html(F);
				L.remove();
				I.selectedCallback({
					value : D,
					text : F
				})
			})
		})
	}
})(jsea);
function alertDlg(B, A) {

	$("body").append("<div class='weui_mask_transition weui_fade_toggle' id='alert_mask' style='display: none;'></div><div class='weui_dialog_alert' id='alertDialogImage' style='display: none;'><div class='weui_mask'></div><div class='weui_dialog' id='alertAppearImage'><div class='weui_dialog_hd'><strong class='weui_dialog_title' id='alertImage' ></strong></div><div id='alertMessage' class='weui_dialog_bd'></div><div class='weui_dialog_ft'><a  id='alertimage_ok' href='javascript:;' class='weui_btn_dialog primary'></a></div></div></div>");

	$( "#alertImage" ).html( "提示" );
	$( "#alertMessage" ).html( B );
	$( "#alertimage_ok" ).html( "确定" );
	$( "#alertDialogImage").css("display","block");
    $( "#alert_mask").css("display","block");
    $( ".weui_dialog").css("z-index","99999");
    $( ".weui_mask_transition").css("z-index","99998");
    $(function() {
    	$('#alertimage_ok').unbind("click").on("click", function(){
    		$('#alertDialogImage').hide();
            $('#alert_mask').hide();
            if(A!=null)A();
            $(".weui_dialog").css("z-index","1");
            $(".weui_mask_transition").css("z-index","1");
    	})
    })


}
function alertImageDlg(A,B,C,D) {

	$("body").append("<div class='weui_mask_transition weui_fade_toggle' id='alert_mask' style='display: none;'></div><div class='weui_dialog_alert' id='alertDialogImage' style='display: none;'><div class='weui_mask'></div><div class='weui_dialog' id='alertAppearImage'><div class='weui_dialog_hd'><i id='alertImage' style='font-size:3.0em;' class='iconfont'></i></div><div id='alertMessage' class='weui_dialog_bd'></div><div class='weui_dialog_ft'><a  id='alertimage_ok' href='javascript:;' class='weui_btn_dialog primary'></a></div></div></div>");

	$( "#alertImage" ).html( A );
	$( "#alertMessage" ).html( B );
	$( "#alertimage_ok" ).html( C );
	$( "#alertDialogImage").css("display","block");
    $( "#alert_mask").css("display","block");
    $( ".weui_dialog").css("z-index","99999");
    $( ".weui_mask_transition").css("z-index","99998");
    $(function() {
    	$('#alertimage_ok').unbind("click").on("click", function(){
            $('#alertDialogImage').hide();
            $('#alert_mask').hide();
            if(D!=null)D();
            $(".weui_dialog").css("z-index","1");
            $(".weui_mask_transition").css("z-index","1");
        });

    })

}
function alertDefineDlg(A, B,C) {


	$("body").append("<div class='weui_mask_transition weui_fade_toggle' id='alert_mask' style='display: none;'></div><div class='weui_dialog_alert' id='alertDialogImage' style='display: none;'><div class='weui_mask'></div><div class='weui_dialog' id='alertAppearImage'><div class='weui_dialog_hd'><strong class='weui_dialog_title' id='alertImage' ></strong></div><div id='alertMessage' class='weui_dialog_bd'></div><div class='weui_dialog_ft'><a  id='alertimage_ok' href='javascript:;' class='weui_btn_dialog primary'></a></div></div></div>");

	$( "#alertMessage" ).html( A );
	$( "#alertimage_ok" ).html( B );
	$( "#alertDialogImage").css("display","block");
    $( "#alert_mask").css("display","block");
    $( ".weui_dialog").css("z-index","99999");
    $( ".weui_mask_transition").css("z-index","99998");
    $(function() {
    	$('#alertimage_ok').unbind("click").on("click", function(){
            $('#alertDialogImage').hide();
            $('#alert_mask').hide();
            if(C!=null)C();
            $(".weui_dialog").css("z-index","1");
            $(".weui_mask_transition").css("z-index","1");
        });

    })

}
function confirmDefineDlg(A,B,C,D,E) {

	$("body").append("<div class='weui_mask_transition weui_fade_toggle' id='alert_mask' style='display: none;'></div><div class='weui_dialog_confirm' id='confirmDialog' style='display: none;'><div class='weui_mask'></div><div class='weui_dialog' id='confirmAppear'><div class='weui_dialog_hd'><strong id='confirmTitle' class='weui_dialog_title'></strong></div><div id='confirmMessage' class='weui_dialog_bd'></div><div class='weui_dialog_ft'><a id='confirm_cancle' href='javascript:;' class='weui_btn_dialog default'></a><a id='confirm_ok' href='javascript:;' class='weui_btn_dialog primary'></a></div></div></div>");
	$( "#confirmMessage" ).html( A );
	$( "#confirm_ok" ).html( B );
	$( "#confirm_cancle" ).html( C );

	$("#confirmDialog").css("display","block");
    $("#alert_mask").css("display","block");
    $(".weui_dialog").css("z-index","99999");
    $(".weui_mask_transition").css("z-index","99998");
    $(function() {
    	$('#confirm_ok').unbind("click").on("click", function(){
            $('#confirmDialog').hide();
            $('#alert_mask').hide();
            if(D!=null)D();
            $(".weui_dialog").css("z-index","1");
            $(".weui_mask_transition").css("z-index","1");
        });

    	$('#confirm_cancle').unbind("click").on("click", function(){

             $('#confirmDialog').hide();
             $('#alert_mask').hide();
             if(E!=null)E();
             $(".weui_dialog").css("z-index","1");
             $(".weui_mask_transition").css("z-index","1");
         });

    })

}
function confirmDlg(C, B, A) {

	$("body").append("<div class='weui_mask_transition weui_fade_toggle' id='alert_mask' style='display: none;'></div><div class='weui_dialog_confirm' id='confirmDialog' style='display: none;'><div class='weui_mask'></div><div class='weui_dialog' id='confirmAppear'><div class='weui_dialog_hd'><strong id='confirmTitle' class='weui_dialog_title'></strong></div><div id='confirmMessage' class='weui_dialog_bd'></div><div class='weui_dialog_ft'><a id='confirm_cancle' href='javascript:;' class='weui_btn_dialog default'></a><a id='confirm_ok' href='javascript:;' class='weui_btn_dialog primary'></a></div></div></div>");
	$( "#confirmMessage" ).html( C );
	$( "#confirm_ok" ).html( "确定" );
	$( "#confirm_cancle" ).html( "取消" );
	$( "#confirmTitle" ).html( "提示" );
	$("#confirmDialog").css("display","block");
    $("#alert_mask").css("display","block");
    $(".weui_dialog").css("z-index","99999");
    $(".weui_mask_transition").css("z-index","99998");
    $(function() {
    	$('#confirm_ok').unbind("click").on("click", function(){

            $('#confirmDialog').hide();
            $('#alert_mask').hide();
            if(B!=null)B();
            $(".weui_dialog").css("z-index","1");
            $(".weui_mask_transition").css("z-index","1");
        });

    	$('#confirm_cancle').unbind("click").on("click", function(){

             $('#confirmDialog').hide();
             $('#alert_mask').hide();
             if(A!=null)A();
             $(".weui_dialog").css("z-index","1");
             $(".weui_mask_transition").css("z-index","1");
         });

    })
}
/*
(function (A) {
	A.seaUIBind = A.fn.seaUIBind = function () {
		var D = A("[sea-dom]");
		for (var B = 0; B < D.length; B++) {
			var H = A(D[B]);
			var I = H.attr("sea-dom");
			if (I == "body") {
				if (A(".jFoot").length > 0) {
					var C = window.innerHeight - A(".jFoot").offset1().height
				} else {
					var C = window.innerHeight
				}
				H.css1({
					"height" : C,
					"margin" : "0",
					"padding" : "0"
				})
			}
		}
		var F = A("[sea-js]");
		for (var B = 0; B < F.length; B++) {
			F = A("[sea-js]");
			var E = A(F[B]);
			var G = E.attr("sea-js");
			if (G == "scroll") {
				E.seaScrollbar({
					isShowBar : false
				})
			} else {
				if (G == "scrollbar") {
					E.seaScrollbar()
				}
			}
		}
	}
})(sea);

J = $.seaPopup({
						title : "选择预订日期",
						titleStyle : "background:#f9f9f9;border-bottom:1px solid #ddd;",
						boxStyle : "background:#f5f5f5;",
						closeText : 'x',
						message : "<input type='text' />",
						position : "bottom",
						isScroll : true,
                        duration : 5000
					});
*/
/* 日期插件 */
(function($){
	$.fn.datePopup = function(MZ){
		var now = new Date();
		var startYear = MZ.startYear || now.getFullYear() - 40;
		var endYear = MZ.endYear || now.getFullYear();
		var initPopupDate = MZ.initPopupDate;

		//var htmlInIt = "<div id='mainCan'><div id='pauseCan'><input /><br/><input /><br/><input /><br/><input /><br/><input /><br/><input /><br/><input /><br/><input /><br/><input /><br/><input /><br/><input /><br/><input /><br/><input /><br/><input value='asdasdsd' /><br/><input /><br/><input /><br/><input /><br/><input /><br/><input /><br/><input /><br/><input /><br/><input /><br/><input value='asdasdsd' /><br/><input value='asdasdsd' /><br/><input value='asdasdsd' /><br/></div><div id='pauseCan1'><input /><br/><input /><br/><input /><br/><input /><br/><input /><br/><input /><br/><input /><br/><input /><br/><input /><br/><input /><br/><input /><br/><input /><br/><input /><br/><input value='asdasdsd' /><br/><input /><br/><input /><br/><input /><br/><input /><br/><input /><br/><input /><br/><input /><br/><input /><br/><input value='asdasdsd' /><br/><input value='asdasdsd' /><br/><input value='asdasdsd' /><br/></div></div>";
		var htmlInIt = function( content ){
			return "<div id='mainCan'>" + content + "</div>";
		};
		//var yearCan = "<div id='yearCan'><ul><li>1980</li><li>1980</li><li>1980</li><li>1980</li><li>1980</li><li>1980</li><li>2012</li><li>1980</li><li>1980</li><li>1980</li><li>1980</li><li>1980</li><li>1980</li><li>1980</li><li>1980</li></ul></div>"
		//var monthCan = "<div id='monthCan'><ul><li>1月</li><li>2月</li><li>3月</li><li>4月</li><li>5月</li><li>6月</li><li>7月</li><li>8月</li><li>9月</li><li>10月</li><li>11月</li><li>12月</li></div><input type='hidden' id='monthCanVal'/>";
		//var dayCan = "<div id='dayCan'><ul><li>1日</li><li>2日</li><li>3日</li><li>4日</li><li>5日</li><li>6日</li><li>7日</li><li>8日</li><li>9日</li><li>10日</li><li>11日</li><li>12日</li><li>13日</li><li>14日</li><li>15日</li><li>16日</li><li>17日</li><li>18日</li><li>19日</li><li>20日</li><li>21日</li><li>22日</li><li>23日</li><li>24日</li><li>25日</li><li>26日</li><li>27日</li><li>28日</li><li>29日</li><li>30日</li><li>31日</li></ul></div><input type='hidden' id='dayCanVal'/>";

		var self = this;
		$(this).attr("readonly","readonly");

		var yearCan = function( content ){
			return "<div id='yearCan'><ul>" + content + "</ul></div><input type='hidden' id='yearCanVal'/>";
		};

		var monthCan = function( content ){
			return "<div id='monthCan'><ul>" + content + "</ul></div><input type='hidden' id='monthCanVal'/>";
		};

		var dayCan = function( content ){
			return "<div id='dayCan'><ul>" + content + "</ul></div><input type='hidden' id='dayCanVal'/>" ;
		};

		var yearCanStr = "";
		var monthCanStr = "";
		var dayCanStr = "";
		//var startYear = 1980;
		//var endYear = 2014;

		var isSelectedYear = false;
		var isSelectedMonth = false;
		var isSelectedDay = false;

		//设置一个默认值
		//$(this).val($(this).val() || (startYear + "-01-01"));
		$(this).val($(this).val() || '');

		for(var i = startYear;i<endYear+1;i++){yearCanStr+="<li val=\"" + i + "\">"+ i +"年</li>"};
		for(var i = 1;i<13;i++){monthCanStr+="<li val=\"" + i + "\">"+ i +"月</li>"};
		function generateDay(day){
			dayCanStr = "";
			for(var i=1;i<day+1;i++){dayCanStr+="<li val=\"" + i + "\">"+ i +"日</li>"};
		}
		function getLastDate(indate) {
			//alert(name);
			var year = parseInt(indate.split("/")[0]);
			var month = parseInt(indate.split("/")[1]);
			//run nian
			var isrun = false;
			if ((year % 4 == 0 && year % 100 != 0) || year % 400 == 0)
				isrun = true;
			switch (month) {
			case 2:
				if (isrun) {
					return 29;
				} else {
					return 28;
				}
			case 1:
			case 3:
			case 5:
			case 7:
			case 8:
			case 10:
			case 12:
				return 31;
			default:
				return 30;
			}
		}
		function checkDate(){
			var lastDate = getLastDate( $("#yearCanVal").val() + "/" + $("#monthCanVal").val() + "/1" );
			var currentDate = $("#dayCan ul li").length;
			if( lastDate > currentDate ){
				for( var i = 0;i < lastDate - currentDate;i++ ){
					var addItem = $("<li></li>");
					addItem.attr("val",i+currentDate+1);
					addItem.html( i+currentDate+1 + "日" );
					$("#dayCan ul").append(addItem);
					addItem.on("click",function(){
						$("#dayCan .active").removeClass("active");
						$(this).addClass("active");
						$("#dayCanVal").val($(this).attr("val"));
					});
				}
			}else if( lastDate < currentDate ){
				$("#dayCan ul li").slice(lastDate).remove();
				if( $("#dayCan ul li.active").length == 0 ){
					$("#dayCan ul li:last").addClass("active");
					$("#dayCanVal").val($("#dayCan ul li:last").attr("val"));
				}
			}
		}
		generateDay(31);


		function returnTwoNum(str){
			return Number(str) >= 10 || str=="" ? str : "0" + Number(str);
		}

		$(this).on("click",function(event){
			var currentDateStr = self.val();
			var currentDateArr = [];
			if( currentDateStr != "" ){
				currentDateArr = currentDateStr.split("-");
			} else {
				// 当$(this)值为空时的初始弹窗值
				var _initPopupDate = initPopupDate?initPopupDate.getFullYear() + "-" + returnTwoNum(initPopupDate.getMonth()+1) + "-" + returnTwoNum(initPopupDate.getDate()):'';
				currentDateStr = _initPopupDate || startYear+ "-01-01";
				currentDateArr = currentDateStr.split("-");
			}

			//console.log(currentDateArr);

			event.preventDefault();
			event.stopPropagation();
			var popUp = $.seaPopup({
				title:"请选择日期",
				titleStyle:"background:#f9f9f9;border-bottom:1px solid #ddd;",
				//closeText:"<span style=\"color:green;font-weight:bold;width:80px;border:1px solid #ff0000;\">&radic;</span>",
				closeText:"<span style=\"color:#828282;border:1px solid #828282;border-radius:3px;font-size:14px;display:block;text-align:center;\">完成</span>",
				closeCallBack:function(){
					//$(self).val("");
					var yearStr =  $("#yearCanVal").val();
					var monthStr = returnTwoNum( $("#monthCanVal").val() );
					var dayStr = returnTwoNum( $("#dayCanVal").val() );

					//if( yearStr != ""  && monthStr != "" && dayStr != "" ){
					$(self).val( yearStr + "-" + monthStr + "-" + dayStr );
						//popUp.remove();
					//}
				},
				message:htmlInIt( yearCan(yearCanStr) + monthCan(monthCanStr) + dayCan(dayCanStr) + "<div style=\"clear:both\"></div>" ),
				position:"bottom",
				isScroll : false
			});

			var scrollHeight = $("#mainCan").parent().css("height");
			//scrollHeight = Number(scrollHeight.slice(0,-2));

			$("#mainCan").css("height", scrollHeight );
			$("#yearCan").css("height", scrollHeight );
			$("#monthCan").css("height", scrollHeight );
			$("#dayCan").css("height", scrollHeight );
			var yearCanJ = $("#yearCan").seaScrollbar();
			var monthCanJ = $("#monthCan").seaScrollbar();
			var dayCanJ = $("#dayCan").seaScrollbar();

			$("#yearCanVal").val(currentDateArr[0]);
			$("#monthCanVal").val(currentDateArr[1]);
			$("#dayCanVal").val(currentDateArr[2]);
			checkDate();

			//$("#yearCan li").eq(0).addClass("active");
			var currentYearIndex = $("#yearCan li").index( $("#yearCan li[val=" + currentDateArr[0] + "]").addClass("active") );
			$("#yearCan").children().eq(0).css($.feat.cssPrefix+"-transform","translate3d(0px,"+ currentYearIndex * 50*-1 +"px, 0px)");
			yearCanJ.setMoveY(currentYearIndex * 50*-1);
			//$("#yearCan").children().eq(0).cssTranslate( currentYearIndex * 51*-1 );
			$("#yearCanVal").val(currentDateArr[0]);

			var currentMonthIndex =$("#monthCan li").index( $("#monthCan li[val=" + Number(currentDateArr[1]) + "]").addClass("active"));
			$("#monthCan").children().eq(0).css($.feat.cssPrefix+"-transform","translate3d(0px,"+ currentMonthIndex * 50*-1 +"px, 0px)");
			monthCanJ.setMoveY(currentMonthIndex * 50*-1);
			//$("#monthCan").children().eq(0).cssTranslate( currentMonthIndex * 51*-1 );
			$("#monthCanVal").val(currentDateArr[1]);

			var currentDayIndex = $("#dayCan li").index( $("#dayCan li[val=" + Number(currentDateArr[2]) + "]").addClass("active") );
			$("#dayCan").children().eq(0).css($.feat.cssPrefix+"-transform","translate3d(0px,"+ currentDayIndex * 50*-1 +"px, 0px)");
			dayCanJ.setMoveY(currentDayIndex * 50*-1);
			//$("#dayCan").children().eq(0).cssTranslate( currentDayIndex * 51*-1 );
			$("#dayCanVal").val(currentDateArr[2]);

			$("#yearCan li").on("click",function(){
				$("#yearCan .active").removeClass("active");
				$(this).addClass("active");
				$("#yearCanVal").val($(this).attr("val"));
				checkDate();
			});

			$("#monthCan li").on("click",function(){
				$("#monthCan .active").removeClass("active");
				$(this).addClass("active");
				$("#monthCanVal").val($(this).attr("val"));
				checkDate();
				//generateDay( getLastDate( $("#yearCanVal").val() + "/" + $("#monthCanVal").val() + "/1" ) );
				//$("#dayCan ul").html(dayCanStr);
				//var lastDate = getLastDate( $("#yearCanVal").val() + "/" + $("#monthCanVal").val() + "/1" );
				//var currentDate = $("#dayCan ul li").length;
				//console.log( lastDate );
				//console.log( currentDate );
				/*
				if( lastDate > currentDate ){
					for( var i = 0;i < lastDate - currentDate;i++ ){
						var addItem = $("<li></li>");
						addItem.attr("val",i+currentDate+1);
						addItem.html( i+currentDate+1 + "日" );
						$("#dayCan ul").append(addItem);
						addItem.on("click",function(){
							$("#dayCan .active").removeClass("active");
							$(this).addClass("active");
							$("#dayCanVal").val($(this).attr("val"));
						});
					}
				}else if( lastDate < currentDate ){
					$("#dayCan ul li").slice(lastDate).remove();
				}
				*/

				//$("#dayCan li").on("click",function(){
				//	$("#dayCan .active").removeClass("active");
				//	$(this).addClass("active");
				//	$("#dayCanVal").val($(this).attr("val"));
				//});
			});

			$("#dayCan li").on("click",function(){
				$("#dayCan .active").removeClass("active");
				$(this).addClass("active");
				$("#dayCanVal").val($(this).attr("val"));
			});

			/*
			$("#pauseCan").css("height", $("#mainCan").parent().css("height"));
			$("#pauseCan1").css("height", $("#mainCan").parent().css("height"));
			$("#pauseCan").seaScrollbar();
			$("#pauseCan1").seaScrollbar();
			*/
			return popUp;

		});
		/*
		function showLastDate() {
			//alert(name);
			var reg = /^\d{4}\/\d{1,2}\/\d{1,2}$/;
			var inputdate = $("#indate").val();
			if (!reg.test(inputdate)) {
				alert("please input date like:2013/1/14");
				return;
			}
			var month = parseInt(inputdate.split("/")[1]);
			if (month > 12 || month == 0) {
				alert("please input month range from 1-12");
				return;
			}
			var showdate = getLastDate(inputdate);
			$("#lastdate").val(showdate);
		}*/


	}
})($)