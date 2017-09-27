define(['core', 'tpl'], function(core, tpl) {
    var modal = {};
    modal.init = function(params) {
        modal.params = params;
        modal.params.level = 0;
        $('.fui-stars').stars({
            'clearIcon': 'icon icon-round_close',
            'icon': 'icon icon-favor',
            'selectedIcon': 'icon icon-favorfill',
            'onSelected': function(value) {
                modal.params.level = value
            }
        });
        $('.btn-submit').click(function() {
            if ($(this).attr('stop')) {
                return
            }
            if (modal.params.iscomment == 0 && modal.params.level < 1) {
                FoxUI.toast.show('还没有评分');
                return
            }
            if ($('#comment').isEmpty()) {
                FoxUI.toast.show('说点什么吧!');
                return
            }
            var e = $('#comment').val();
            $(this).html('正在处理...').attr('stop', 1);
            core.json('order/evaluate/submit', {
                's': modal.params.ordersn,
                'q': modal.params.orderid,
                'l': modal.params.level,
                'e': e,
                't': modal.params.token
            }, function(ret) {
                if (ret.status == 1) {
                    FoxUI.toast.show(ret.result.message);
                    location.href = core.getUrl('order');
                    return
                }
                $('.btn-submit').removeAttr('stop').html('提交评价');
                FoxUI.toast.show(ret.result.message)
            }, true, true)
        })
    };
    return modal
});