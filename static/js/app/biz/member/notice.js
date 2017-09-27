define(['core', 'tpl'], function(core, tpl) {
    var modal = {};
    modal.init = function() {
        $('.fui-switch').change(function() {
            core.json('member/update_notice', {
                s: $(this).data('type'),
                h: $(this).data('id'),
                u: $(this).prop('checked') ? 1 : 0
            }, function() {}, true, true)
        })
    };
    return modal
});