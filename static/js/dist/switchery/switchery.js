/*
 * Copyright (c) 2017. Lorem ipsum dolor sit amet, consectetur adipiscing elit.
 * Morbi non lorem porttitor neque feugiat blandit. Ut vitae ipsum eget quam lacinia accumsan.
 * Etiam sed turpis ac ipsum condimentum fringilla. Maecenas magna.
 * Proin dapibus sapien vel ante. Aliquam erat volutpat. Pellentesque sagittis ligula eget metus.
 * Vestibulum commodo. Ut rhoncus gravida arcu.
 */
;(function () {

    var Switchery = function (elem, params) {


        var obj = $(elem);
        obj.hide();
        var small = obj.hasClass('small');
        var checked = elem.checked;
        var switchery = $('<div class="switchery ' + (small ? "switchery-small" : "") + '"><small></small></div>');
        obj.after(switchery);
        if (checked) {
            switchery.addClass('checked');
        }
        switchery.click(function (e) {
            switchery.toggleClass('checked');
            obj.click && obj.trigger('click', e);
        });
        return switchery;
    }

    $.fn.switchery = function (params) {
        var lists = this,
            retval = this;

        lists.each(function () {
            var plugin = $(this).data("switchery");

            if (!plugin) {
                $(this).data("switchery", new Switchery(this, params));
                $(this).data("switchery-id", new Date().getTime());
            } else {
                if (typeof params === 'string' && typeof plugin[params] === 'function') {
                    retval = plugin[params]();
                }
            }
        });

        return retval || lists;
    };

})();
