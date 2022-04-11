(function ($) {
    "use strict";
    $(document).ready(function ($) {
        var from = {};
        var to = {};
        var default_required = {};
        Object.keys(wck_range_date_picker_options).forEach(function (field_name) {
            default_required[field_name] = $("input.wck-range-date-picker[name=wck\\[" + field_name + "\\]\\[from\\]]").prop("required");
            from[field_name] = $("input.wck-range-date-picker[name=wck\\[" + field_name + "\\]\\[from\\]]")
                .datepicker(wck_range_date_picker_options[field_name])
                .on("change", function () {
                    to[field_name].datepicker("option", "minDate", getDate(this));
                    if (!default_required[field_name]) {
                        if (this.value === "") {
                            $(to[field_name]).prop("required", false);
                        } else {
                            $(to[field_name]).prop("required", true);
                        }
                    }
                });

            to[field_name] = $("input.wck-range-date-picker[name=wck\\[" + field_name + "\\]\\[to\\]]")
                .datepicker(wck_range_date_picker_options[field_name])
                .on("change", function () {
                    from[field_name].datepicker("option", "maxDate", getDate(this));
                });
        });

        function getDate(element) {
            var date;
            try {
                date = $.datepicker.parseDate("yy-mm-dd", element.value);
            } catch (error) {
                date = null;
            }
            return date;
        }
    });
})(jQuery);