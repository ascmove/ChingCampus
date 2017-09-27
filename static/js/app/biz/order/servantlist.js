define(['core', 'tpl', 'biz/order/op'], function(c, d, e) {
    var f = {
        page: 1,
        status: '',
    };
    f.init = function(a) {
        f.status = a.status;
        e.init();
        $('.fui-content').infinite({
            onLoading: function() {
                f.getList()
            }
        });
        if (f.page == 1) {
            f.getList()
        }
        $('.icon-delete').click(function() {
            $('.fui-tab-danger a').removeClass('active');
            f.changeTab(5)
        });
        FoxUI.tab({
            container: $('#tab'),
            handlers: {
                tab: function() {
                    f.changeTab('')
                },
                tab1: function() {
                    f.changeTab(1)
                }
            }
        })
    };
    f.changeTab = function(a) {
        if (a == 5) {
            $('.icon-delete').css('color', 'red')
        } else {
            $('.icon-delete').css('color', '#999')
        }
        $('.fui-content').infinite('init');
        $('.content-empty').hide(), $('.container').html(''), $('.infinite-loading').show();
        f.page = 1, f.status = a, f.getList()
    };
    f.loading = function() {
        f.page++
    };
    f.getList = function() {
        c.json('order/servant_get_list', {
            page: f.page,
            status: f.status,
        }, function(a) {
            var b = a.result;
            if (b.total <= 0) {
                $('.content-empty').show();
                $('.fui-content').infinite('stop')
            } else {
                $('.content-empty').hide();
                $('.fui-content').infinite('init');
                if (b.list.length <= 0 || b.list.length < b.pagesize) {
                    $('.fui-content').infinite('stop')
                }
            }
            f.page++;
            c.tpl('.container', 'tpl_order_index_list', b, f.page > 1);
            e.init()
        })
    };
    return f
});