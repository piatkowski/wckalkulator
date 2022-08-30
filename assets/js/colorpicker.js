jQuery(document).ready(function ($) {
    $('input.wck-color-picker').wpColorPicker({
        "change": function (event, ui) {
            $(this).trigger('change');
        }
    });
});