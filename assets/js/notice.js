(function ($) {
    "use strict";
    $(document).ready(function ($) {
        $(document).on("click", ".notice.wck-notice button.notice-dismiss", function(e){
            e.preventDefault();
            $.post(ajax_object.ajax_url, {
                "action": "wck_notice_dismiss",
                "_wck_ajax_nonce": ajax_object._wck_ajax_nonce,
                "_wck_notice_dismiss": true
            }, function (response) {
                console.log(response);
            });
        });
    });
})(jQuery);

