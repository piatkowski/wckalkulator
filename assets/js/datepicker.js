jQuery(document).ready(function ($) {
    Object.keys(wck_date_picker_options).forEach(function (field_name) {
        $("input.wck-date-picker[name=wck\\[" + field_name + "\\]]").datepicker(wck_date_picker_options[field_name]);
    });
});