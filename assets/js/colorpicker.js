jQuery(document).ready(function ($) {
    "use strict";
    var changeTimeout;

    function triggerChange(event, ui = null)  {
        clearTimeout(changeTimeout);
        changeTimeout = setTimeout(function () {
            $(event.target).trigger('change');
        }, 500);
    }

    $('input.wck-color-picker').wpColorPicker({
        "change": triggerChange,
        "clear": triggerChange
    });

});