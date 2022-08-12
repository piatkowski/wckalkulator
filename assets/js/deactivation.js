(function ($) {
    "use strict";
    $("#deactivate-wc-kalkulator").on("click", function(e){
        if (confirm("This plugin is developed thanks to feedback. Do you want to give me feedback?")) {
            e.preventDefault();
            e.stopPropagation();
            window.location.href = "https://docs.google.com/forms/d/e/1FAIpQLSeEHr_kPRsS-foooly3E-SXvs4Ucwx9oXMPSZ1x1hJeYqUC3w/viewform";
        }
    });
})(jQuery);