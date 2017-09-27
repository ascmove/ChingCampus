define(['core', 'tpl'], function(core, tpl) {
    var modal = {};
    modal.init = function(token) {
        $('.btn-submit').click(function() {
            var btn = $(this);
            if (btn.attr('stop')) {
                return
            }
            var html = btn.html();
            var data = {
                name: $('#name').val(),
                desc: $('#desc').val(),
                contact: $('#contact').val()
            };
            btn.attr('stop', 1).html('正在处理...');
            core.json('member/feedback_submit', {
                name: $('#name').val(),
                desc: $('#desc').val(),
                token: token,
                contact: $('#contact').val()
            }, function(pjson) {
                FoxUI.toast.show(pjson.result.message);
                if (pjson.status == 0) {
                    btn.removeAttr('stop').html(html);
                    FoxUI.toast.show(pjson.result.message);
                    return
                }
                history.go(-1);
            }, true, true)
        })
    };
    return modal
});