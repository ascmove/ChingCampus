define(['core', 'tpl'], function(core, tpl) {
    var modal = {
        params: {}
    };
    modal.initd = false;
    modal.reinit = function (params) {
        var defaults = {
            orderid: 0,
            wechat: {
                success: false
            },
            cash: {
                success: false
            },
        };
        modal.params = $.extend(defaults, params || {});
        core.json('order/pay/check', {
            id: modal.params.orderid,
            t: modal.params.t,
            l: modal.params.l,
            p: modal.params.p
        }, function(pay_json) {
            if (pay_json.status == 1) {
                modal.params.b = pay_json.result.message;
                modal.initd = true;
            } else {
                FoxUI.toast.show(pay_json.result.message)
            }
        }, false, true)
    }
    modal.init = function(params) {
        var defaults = {
            orderid: 0,
            wechat: {
                success: false
            },
            cash: {
                success: false
            },
        };
        modal.params = $.extend(defaults, params || {});
        core.json('order/pay/check', {
            id: modal.params.orderid,
            t: modal.params.t,
            l: modal.params.l,
            p: modal.params.p
        }, function(pay_json) {
            if (pay_json.status == 1) {
                modal.params.b = pay_json.result.message;
                modal.initd = true;
            } else {
                FoxUI.toast.show(pay_json.result.message)
            }
        }, false, true)
        
        
        
        $('.pay-btn').unbind('click').click(function() {
            var btn = this;
            modal.pay(btn);
        });

        $('#coupondiv').unbind('click').click(function() {
            $('#couponloading').show();
            core.json('member/util/queryCoupon', {
                orderid: modal.params.orderid,
                type: 0,
            }, function(rjson) {
                $('#couponloading').hide();
                if (rjson.result.coupons.length > 0 || rjson.result.wxcards.length > 0) {
                    $('#coupondiv').show().find('.badge').html(rjson.result.coupons.length + rjson.result.wxcards.length).show();
                    $('#coupondiv').find('.text').hide();
                    require(['biz/sale/coupon/picker'], function(picker) {
                        picker.show({
                            couponid: modal.params.couponid,
                            coupons: rjson.result.coupons,
                            wxcards: rjson.result.wxcards,
                            onCancel: function() {
                                if(!modal.params.couponid)
                                {
                                    return true;
                                }
                                modal.params.contype = 0;
                                modal.params.couponid = 0;
                                modal.params.wxid = 0;
                                modal.params.wxcardid = "";
                                modal.params.wxcode = "";
                                modal.params.couponmerchid = 0;
                                $('#coupondiv').find('.fui-cell-label').html('优惠券');
                                $('#coupondiv').find('.fui-cell-info').html('');
                                // modal.calcCouponPrice()

                                core.json('order/pay/init', {
                                    id: modal.params.orderid,
                                    t: modal.params.t,
                                    l: modal.params.l,
                                    p: modal.params.p
                                }, function(getjson) {
                                    if (getjson.status == 1) {
                                        modal.params.orderid = getjson.result.orderid;
                                        $('.orderno').text(getjson.result.orderid);
                                        $('.deduct').hide();
                                        $('.real').hide();
                                        modal.init(getjson.result);
                                    } else {
                                        FoxUI.toast.show(getjson.result.message);
                                    }
                                    // return modal.totalPrice(deductprice)
                                }, true, true)
                            },
                            onSelected: function(data) {
                                if(data.couponid == modal.params.couponid)
                                {
                                    return true;
                                }
                                modal.params.contype = data.contype;
                                modal.params.couponid = data.couponid;
                                if (modal.params.contype == 1) {
                                    modal.params.couponid = 0;
                                    modal.params.wxid = data.wxid;
                                    modal.params.wxcardid = data.wxcardid;
                                    modal.params.wxcode = data.wxcode;
                                    modal.params.couponmerchid = data.merchid;
                                    $('#coupondiv').find('.fui-cell-label').html('已选择');
                                    $('#coupondiv').find('.fui-cell-info').html(data.couponname);
                                    $('#coupondiv').data(data);
                                    modal.calcCouponPrice()
                                } else if (modal.params.contype == 2) {
                                    core.json('order/pay/init', {
                                        couponid: data.couponid,
                                        id: modal.params.orderid,
                                        t: modal.params.t,
                                        l: modal.params.l,
                                        p: modal.params.p
                                    }, function(getjson) {
                                        if (getjson.status == 1) {
                                            if(getjson.result.discount > 0)
                                            {
                                                $('.orderno').text(getjson.result.orderid);
                                                $('.deductprice').text('-￥'+getjson.result.discount);
                                                $('.realprice').text('￥'+getjson.result.money);
                                                $('.deduct').show();
                                                $('.real').show();
                                            }else if (getjson.result.discount == 0){

                                            }
                                            modal.params.orderid = getjson.result.orderid;
                                            modal.init(getjson.result);
                                            modal.params.couponid = data.couponid;
                                        } else {
                                            FoxUI.toast.show(getjson.result.message);
                                        }
                                        // return modal.totalPrice(deductprice)
                                    }, true, true)



                                    modal.params.couponid = data.couponid;
                                    modal.params.wxid = 0;
                                    modal.params.wxcardid = "";
                                    modal.params.wxcode = "";
                                    modal.params.couponmerchid = data.merchid;
                                    $('#coupondiv').find('.fui-cell-label').html('已选择');
                                    $('#coupondiv').find('.fui-cell-info').html(data.couponname);
                                    $('#coupondiv').data(data);
                                    modal.calcCouponPrice()
                                } else {
                                    modal.reinit({"orderid":"CH20170917191324921620","logid":10292,"t":"pay","l":"10292","p":"e0d5db92429591ce829f9238645ddcdaa9d7863a","b":"","credit":{"success":false},"wechat":{"appId":"wxd397a95654c3a6141","nonceStr":"UB4lH4RKpBiuB22ip94Rrgkn7LwXlumWz","package":"prepay_id=wx2017091722w46221cfe8a4d8e0182060657","signType":"MD5","timeStamp":"150565w9582","paySign":"3E8FFAA7FB0BF6DDw4190D42922DFA257","success":true,"weixin":true},"cash":{"success":false},"money":"66.00"});
                                    modal.params.contype = 0;
                                    modal.params.couponid = 0;
                                    modal.params.wxid = 0;
                                    modal.params.wxcardid = "";
                                    modal.params.wxcode = "";
                                    modal.params.couponmerchid = 0;
                                    $('#coupondiv').find('.fui-cell-label').html('优惠券');
                                    $('#coupondiv').find('.fui-cell-info').html('');
                                    modal.calcCouponPrice()
                                }
                            }
                        })
                    })
                } else {
                    FoxUI.toast.show('暂无优惠券');
                    modal.hideCoupon()
                }
            }, false, true)
        })
    };
    modal.pay = function(btn) {
        var type = $(btn).data('type') || '';
        var btn = $('.pay-btn');
        if (type == '') {
            return
        }
        if (btn.attr('stop')) {
            return
        }
        btn.attr('stop', 1);
        if (type == 'wechat') {
            modal.payWechat(btn)
        } else {
            modal.complete(btn, type)
        }
    };
    modal.payWechat = function(btn) {
        var wechat = modal.params.wechat;
        if (!wechat.success) {
            return
        }
        if (wechat.weixin) {
            WeixinJSBridge.invoke('getBrandWCPayRequest', {
                'appId': wechat.appid ? wechat.appid : wechat.appId,
                'timeStamp': wechat.timeStamp,
                'nonceStr': wechat.nonceStr,
                'package': wechat.package,
                'signType': wechat.signType,
                'paySign': wechat.paySign,
            }, function(res) {
                if (res.err_msg == 'get_brand_wcpay_request:ok') {
                    FoxUI.toast.show('正在获取支付结果...');
                    modal.complete(btn, 'wechat');
                } else if (res.err_msg == 'get_brand_wcpay_request:cancel') {
                    core.json('order/pay/status', {
                        id: modal.params.orderid,type:'wechat',status:'cancel'
                    }, function(pay_json) {
                        FoxUI.toast.show('取消支付')
                    }, false, true)
                } else {
                    core.json('order/pay/status', {
                        id: modal.params.orderid,type:'wechat',status:res.err_msg
                    }, function(pay_json) {
                        FoxUI.toast.show(res.err_msg)
                    }, false, true)
                }
                btn.removeAttr('stop')
            })
        }
    };
    modal.complete = function(btn, type) {
        core.json('order/pay/complete', {
            id: modal.params.orderid,
            type: type,
            t: modal.params.t,
            l: modal.params.l,
            b: modal.params.b
        }, function(pay_json) {
            modal.query = parseInt(modal.query) + parseInt(1);
            if (pay_json.status == 1) {
                FoxUI.toast.show(pay_json.result.message);
                location.href = core.getUrl('order/pay/success', {
                    id: modal.params.orderid,
                    result: pay_json.result.result
                });
                return
            }else {
                FoxUI.loader.hide();
                if(modal.query > 7)
                {
                    FoxUI.toast.show(pay_json.result.message);
                    return false;
                }
                setTimeout(modal.complete(0,type),200);
            }
        }, false, true);
    };
    modal.hideCoupon = function() {
        $('#coupondiv').hide();
        $('#coupondiv').find('.badge').html('0').hide();
        $('#coupondiv').find('.text').show()
    };
    modal.calcCouponPrice = function() {
        var goodsprice = core.getNumber($('.goodsprice').html());
        $('#coupondeduct_div').hide();
        $('#coupondeduct_text').html('');
        $('#coupondeduct_money').html('0');
        var deductprice = 0;
        var taskdiscountprice = core.getNumber($(".taskdiscountprice").val());
        var lotterydiscountprice = core.getNumber($(".lotterydiscountprice").val());
        var discountprice = core.getNumber($(".discountprice").val());
        var isdiscountprice = core.getNumber($(".isdiscountprice").val());
        if (modal.params.couponid == 0 && modal.params.wxid == 0) {
            if (taskdiscountprice > 0) {
                $(".showtaskdiscountprice").html($(".taskdiscountprice").val());
                $('.istaskdiscount').show()
            } else {
                $('.istaskdiscount').hide()
            }
            if (lotterydiscountprice > 0) {
                $(".showlotterydiscountprice").html($(".lotterydiscountprice").val());
                $('.islotterydiscount').show()
            } else {
                $('.islotterydiscount').hide()
            }
            if (discountprice > 0) {
                $(".showdiscountprice").html($(".discountprice").val());
                $('.discount').show()
            } else {
                $('.discount').hide()
            }
            if (isdiscountprice > 0) {
                $(".showisdiscountprice").html($(".isdiscountprice").val());
                $('.isdiscount').show()
            } else {
                $('.isdiscount').hide()
            }
            // return modal.totalPrice(0)
        }
        core.json('order/pay/getcouponprice', {
            // goods: modal.params.coupon_goods,
            // goodsprice: goodsprice,
            couponid: modal.params.couponid,
            // contype: modal.params.contype,
            // wxid: modal.params.wxid,
            // wxcardid: modal.params.wxcardid,
            // wxcode: modal.params.wxcode,
            // discountprice: discountprice,
            // isdiscountprice: isdiscountprice
        }, function(getjson) {
            if (getjson.status == 1) {
                $('#coupondeduct_text').html(getjson.result.coupondeduct_text);
                deductprice = getjson.result.deductprice;
                var discountpricenew = getjson.result.discountprice;
                var isdiscountpricenew = getjson.result.isdiscountprice;
                if (discountpricenew > 0) {
                    $(".showdiscountprice").html(discountpricenew);
                    $('.discount').show()
                } else {
                    $(".showdiscountprice").html(0);
                    $('.discount').hide()
                }
                if (isdiscountpricenew > 0) {
                    $(".showisdiscountprice").html(isdiscountpricenew);
                    $('.isdiscount').show()
                } else {
                    $(".showisdiscountprice").html(0);
                    $('.isdiscount').hide()
                }
                if (deductprice > 0) {
                    $('#coupondeduct_div').show();
                    $('#coupondeduct_money').html(core.number_format(deductprice, 2))
                }
            } else {
                if (discountprice > 0) {
                    $(".showdiscountprice").html($(".discountprice").val());
                    $('.discount').show()
                } else {
                    $('.discount').hide()
                }
                if (isdiscountprice > 0) {
                    $(".showisdiscountprice").html($(".isdiscountprice").val());
                    $('.isdiscount').show()
                } else {
                    $('.isdiscount').hide()
                }
                deductprice = 0
            }
            // return modal.totalPrice(deductprice)
        }, true, true)
    };
    return modal
});