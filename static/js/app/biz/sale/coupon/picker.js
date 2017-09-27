define(['core', 'tpl'], function(core, tpl) {
    var modal = {
        coupons: [],
        wxcards: [],
        contype: "",
        wxid: 0,
        wxcardid: "",
        wxcode: "",
        couponid: 0,
        couponname: '',
        merchid: 0
    };
    var Picker = function(params) {
        var self = this;
        self.params = $.extend({}, params || {});
        self.data = {
            coupons: self.params.coupons,
            wxcards: self.params.wxcards
        };
        self.show = function() {
            if (self.data.coupons !== modal.coupons) {
                modal.pickerHTML = tpl('tpl_coupons', self.data)
            }
            modal.coupons = self.data.coupons;
            modal.wxcards = self.data.wxcards;
            modal.picker = new FoxUIModal({
                content: modal.pickerHTML,
                extraClass: 'picker-modal',
                maskClick: function() {
                    modal.picker.close()
                }
            });
            modal.picker.show();
            $('.coupon-picker-new').find('.coupon-item .active').find(".coupon-btn").html("选择");
            $('.coupon-picker-new').find('.coupon-item .active').removeClass('active');
            $('.coupon-picker-new').find(".coupon-item[data-couponid='" + self.params.couponid + "']").addClass('active');
            $('.coupon-picker-new').find(".coupon-item[data-couponid='" + self.params.couponid + "']").find(".coupon-btn").html("已选择");
            $('.coupon-picker-new').find('.coupon-item').click(function() {
                $('.coupon-picker-new').find('.coupon-item.active').find(".coupon-btn").html("选择");
                $('.coupon-picker-new').find('.coupon-item.active').removeClass('active');
                $(this).addClass('active');
                $(this).find(".coupon-btn").html("已选择");
                modal.contype = $(this).data('contype');
                if (modal.contype == "1") {
                    modal.wxid = $(this).data('wxid');
                    modal.wxcardid = $(this).data('wxcardid');
                    modal.wxcode = $(this).data('wxcode');
                    modal.couponid = 0;
                    modal.couponname = $(this).data('couponname');
                    modal.merchid = $(this).data('merchid')
                } else if (modal.contype == "2") {
                    modal.couponid = $(this).data('couponid');
                    modal.wxid = 0;
                    modal.wxcardid = "";
                    modal.wxcode = "";
                    modal.couponname = $(this).data('couponname');
                    modal.merchid = $(this).data('merchid')
                }
            });
            $('.coupon-picker-new').find('.btn-cancel').click(function() {
                modal.couponid = 0;
                modal.merchid = 0;
                modal.couponname = '';
                modal.picker.close();
                if (self.params.onCancel) {
                    self.params.onCancel()
                }
            });
            $('.coupon-picker-new').find('.btn-confirm').click(function() {
                var item = $('.coupon-picker-new').find('.coupon-item.active');
                if (item.length <= 0) {
                    FoxUI.toast.show('未选择优惠券');
                    return
                }
                var data = {
                    contype: modal.contype,
                    wxid: modal.wxid,
                    wxcardid: modal.wxcardid,
                    wxcode: modal.wxcode,
                    couponid: modal.couponid,
                    couponname: modal.couponname,
                    merchid: modal.merchid
                };
                if (self.params.onSelected) {
                    self.params.onSelected(data)
                }
                modal.picker.close()
            })
        }
    };
    modal.show = function(params) {
        var picker = new Picker(params);
        picker.show()
    };
    return modal
});