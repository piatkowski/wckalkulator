(function ($) {
    "use strict";
    $(document).ready(function ($) {
        $("a.wck-toggle-publish").on("click", function (e) {
            e.preventDefault();
            var data = 'action=wckalkulator_fieldset_post_type_toggle_publish' +
                '&_wck_ajax_nonce=' + wck_ajax_fieldset._wck_ajax_nonce +
                '&post_id=' + $(this).data("post-id");
            var toggleButton = $(this);
            toggleButton.css('pointer-events', 'none');
            $.post(wck_ajax_fieldset.ajax_url, data, function (response) {
                if (typeof response === "object" && response.status === "success") {
                    toggleButton.find(".woocommerce-input-toggle").removeClass("woocommerce-input-toggle--enabled").removeClass("woocommerce-input-toggle--disabled").addClass("woocommerce-input-toggle--" + response.state);
                    toggleButton.css('pointer-events', '');
                }
            });
        });
    });
})(jQuery);