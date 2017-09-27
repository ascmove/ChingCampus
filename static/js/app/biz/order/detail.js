define(['core', 'tpl', 'biz/order/op'], function(core, tpl, op) {
    var modal = {
        params: {}
    };
    modal.init = function(params) {
        modal.params.orderid = params.orderid;
        op.init({
            fromDetail: true
        });
        $('.btn-cancel').click(function() {
            if ($(this).attr('stop')) {
                return
            }
            FoxUI.confirm('确定您要取消申请?', '', function() {
                $(this).attr('stop', 1).attr('buttontext', $(this).html()).html('正在处理..');
                core.json('order/refund/cancel', {
                    'id': modal.params.orderid
                }, function(postjson) {
                    if (postjson.status == 1) {
                        location.href = core.getUrl('order/detail', {
                            id: modal.params.orderid
                        });
                        return
                    } else {
                        FoxUI.toast.show(postjson.result.message)
                    }
                    $('.btn-cancel').removeAttr('stop').html($('.btn-cancel').attr('buttontext')).removeAttr('buttontext')
                }, true, true)
            })
        });
        $('.look-diyinfo').click(function() {
            var data = $(this).attr('data');
            var id = "diyinfo_" + data;
            var hide = $(this).attr('hide');
            if (hide == '1') {
                $("." + id).slideDown()
            } else {
                $("." + id).slideUp()
            }
            $(this).attr('hide', hide == '1' ? '0' : '1')
        });
        core.json('order/op/query', {
            's': modal.params.orderid
        }, function(json) {
            if (json.status == 1) {
                location.href = core.getUrl('order/detail', {
                    outTradeNo: modal.params.orderid
                });
            }
        }, true, true)
    };
    return modal
});