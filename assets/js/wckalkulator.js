(function ($) {
    "use strict";
    $(document).ready(function ($) {
        var userTimeout;
        var _form = wck_ajax_object.form;

        function calculatePrice() {
            var data = $(_form).serialize().replace('add-to-cart', 'atc') + '&action=wckalkulator_calculate_price' + '&_wck_ajax_nonce=' + wck_ajax_object._wck_ajax_nonce;
            $.each($(_form + " input[type=file].wck_imageupload"), function(){
                data += "&" + $(this).attr("name") + "=" + (($(this)[0].files.length === 1) ? $(this)[0].files[0].size : 0);
            });
            $.post(wck_ajax_object.ajax_url, data, function (response) {
                if (response) {
                    $("#wckalkulator-price").html(response);
                }
            });
        }

        $('input.wck_imageupload').on('change', function(e){
           var size = $(this)[0].files[0].size;
           if( size > $(this).data("maxfilesize") * 1000000) {
               alert(wck_ajax_object._wck_i18n_maxfilesize + " Max. " + $(this).data("maxfilesize") + "MB");
               $(this).val('');
           }
        });

        if (wck_ajax_object._wck_has_expression === "1") {
            $(document).on('change', _form + ' input, ' + _form + ' select', function () {
                clearTimeout(userTimeout);
                userTimeout = setTimeout(function () {
                    $("#wckalkulator-price").html('<img style="display:inline" src="data:image/gif;base64,R0lGODlhEAAQAPUVAHt7e729vf///4R7e+/v762trZSUlKWlpZycnPf39+bm5t7e3tbW1s7OzoSEhMXFxc7FzpSMjJyUlP/397WtraWlnM7FxbW1tb21tebe3tbOzqWcnIyEhHNzc3tzc4yMjK2lpbWttcW9vffv76Wtpa2lra2tpb21vcXFzt7e1qWlrdbe1pScnO/v5gAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAACH/C05FVFNDQVBFMi4wAwEAAAAh+QQJBwAVACwAAAAAEAAQAEAGt8CKsGLZfDyez8YyrAAekAdA09R0GoLGE8BgLDQIhwORSSQIDqYxELhcCoXDARHeCBmRSGOh6BMICxIRDBUOAQ0NBiBNIBsTAgscGwgPDxcABYgFHnQSSkMFAAYHcAgRHQVNQgUDmX0NFA6pQxsGC7d9Cn8ECRt2GwddDwMgiCAOC2YYGwCIWlRDGgACAgkADhiVEYtDIAMGAQYcUmxtHwDXc3QdDxV4DhRucHIIAIOqRRxIkkxDQQAh+QQFBwAQACwAAAAAEAAQAAAGr0CIEGLZfDyez8YybBYAh8diYSkMCk1G5PNgrKYKBcMQYQg3hgZjDQ4TEBvIA/BoNCySgQMBIRAUDkVRDQEDIHYgDgx+FxsOFw8PBiBNIAgJCQscAAGRABpNGgCYBACcAQGfoaMJpQ4hqB+UQyAbAgKaGwgXF08FdgUdF7cBG3O8vQ6meypiAw8QRr0FBwcI1wgHDnEQWg4H1NbXBgBlTh4G2dYfHVhNQkUcSBxLTUEAIfkECQcAEAAsAQABAA4ADgAABnBAiFAYYDAeoKEy0GgYF4uG8vN4NJ9QxTBQfQgHQoViMUAEAhiAMqygFM6ftZBAYFzu8jlBcb/kISN0IAUFHHIICQkMBoQHHkoOGYkXHgUkBwdKCgIJBA4QDpgHCAgGFwICE5lfpKQGRQsIch+tBmtBACH5BAUHABQALAEAAQAOAA4AAAaaQAqlAEBcHo3AAVAQMiKO4wPSaDAsnwhj8wkEHuAqg7EwbAAXbyAyGBgaiwVjILlcQgBQFTSALwoHdh8gQkIgEgoKDQWMABqFFBoAiQsHjB6PhZKJChUHBw6EhSAIBAQMBp8IHgVVBQ4UphceBwi2AwAOBwoJBAoOGw62CG8CEwnIGxtPRRIBAtAJCghaQgUdHwYMAgsXDk0UQQAh+QQFBwAQACwBAAEADgAOAAAGcECIECIpBB4XxHBZuASOD5RoyblYn49HYyv0FL4XCECoZTTGhwJoSWYwQIjDwcEWMhYP+cFThywWDAiCfX5/gkp9CgoNH4IGiQoFEHEFDxt0GwsECgtCAxAECQIJpASmiBAeDQKjpQsSbAMYC6QMbEEAIfkEBQcAEAAsAQABAA4ADgAABnZAiNAjOVwCBaFSCDgUCpdjAMNZHpzQU0D0eESEVycHAIgEuo8yAgHxLAGBRkO4Xi7lIoSBbRcyGA0SCB99fn8GD3N9EgsLDxwEAhkOdg2NBQAjAgIKSggMCo0DEAcTCacjBAQKrEsICqeqqwx9DhgMqgwUo0JBACH5BAkHAAAALAEAAQAOAA4AAAVjICACEVIUhzGuyHGc10WtANK+RRwEo92ulx0PIKnRAJfHY2MIGAbHiBLTEDSOogfkkRAIoMdG47EQTDZYcQCTSBwHDwbj4AC0MwgAVLOQi9AEgQqDC4UqIwuChFdHFBqDjCMhACH5BAUHABMALAEAAQAOAA4AAAaTwMmk0PlIDgeEwVMQMiIAA2KKLBwGEcbGkZwiqoULZzNgEC4IBwDAKYQvgIBA8OiAGg0Q4I1YzA8gQkIgHwEBIQmJAxqCExoehgEEioyCGgAPDwELiQiBgiAGmRgXBAQMDgV4BQMiDRAHDgqmEwgDAwYQeBYAGwgECsELCwzFDQYbTxIMwsMMDxFZQqyqww8He0JBACH5BAUHAAMALAEAAQAOAA4AAAZzwIEQYBggEIaPcGlUKBqG4xGw3CQEgkAUcTiYhA7CVcHskgqeSyKRcTA9h0LBwFhvmEJO6FIi+PFLF4IKflR4HgGCDH+AHwEBBQNOGngAGA8BCAMLTgMSSw+hIksKC6YMDA0NoRFMDaepDQGAAyAPqLNMQQAh+QQFBwAYACwBAAEADgAOAAAFZSAmOtciMGKaSlgivAGrIm0yCY2B7KlCJJiDQ7RDHEQEgiplPGKStGXwUFFYAVJMoXBYWLPabcMqW14uh8JigRksKWfJgLHOYQCRgP4ikiwYDA0ND4R6SxaBDRCEfEtYAYIXUSIhACH5BAkHABAALAEAAQAOAA4AAAZyQIhQyEgkFhjHcLggGBMCAcOzbBEII6PAeBhABgsFYbGBOA6iwgEBKSgUjKXQwEJ8Gm/5EMFf+PVCfAgMf4AHhw8LcXpnayAMDA96IAUFQg0MDZIAQheVBVQQDaMPDwEBF6kcQx8ipaaoBUpyGxemlktBACH5BAUHABAALAEAAQAOAA4AAAaRQAikMCg0CATGxVEQMiIGhgJJSCQUiAhjI1F4p1Vr4rAZLBYKBsLh2CgEggSgcG44QI0GyAFJED4PKQsSIEJCewEBUQwMABqGEBodHwgSDYyOkJIInAF5BoWGe5wIB3kBc3kFHgcHWQAPsQEfAAAcBa0HHhsGsokXBcEHbU8fF78XwMNaQgUACAUBFwcGHk0QQQA7" alt="...">');
                    calculatePrice();
                }, 1000);
            });
            calculatePrice();
        }

        $("span.wck-field-tip").tipTip({
            attribute: 'title',
            defaultPosition: 'left'
        });

        $("input[type=checkbox][data-type='checkboxgroup']").change(function(e){
            var group = $(this).data("group");
            var required = $(this).data("required") === 1;
            var limit = $(this).data("limit");
            var checked_count = $("input[type=checkbox][data-group='" + group + "']:checked").length;

            if (limit > 0) {
                if (checked_count > limit) {
                    $(this).prop("checked", false).blur();
                }
            }

            if (required && checked_count === 0) {
                $(this).prop("checked", true);
            }
            checked_count = $("input[type=checkbox][data-group='" + group + "']:checked").length;
            if (checked_count > 0) {
                $("input[type=checkbox][data-group=" + group + "]").get(0).setCustomValidity("");
            }
        });

        $(_form).submit(function (e) {
            var lastGroup = "";
            var _form = $(this).get(0);
            $("input[type=checkbox][data-type='checkboxgroup']").each(function () {
                var group = $(this).data("group");
                if (group !== lastGroup) {
                    if ($(this).data("required") === 1 && $("input[type=checkbox][data-group="+group+"]:checked").length === 0) {
                        $("input[type=checkbox][data-group="+group+"]").get(0).setCustomValidity(wck_ajax_object._wck_i18n_required);
                        _form.reportValidity();
                        e.preventDefault();
                        return false;
                    }
                }
                lastGroup = group;
            });
        });

    });
})(jQuery);