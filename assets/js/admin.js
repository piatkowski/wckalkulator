(function ($) {
    "use strict";
    var $WK = {};
    var $CV = {};

    $(document).ready(function ($) {

        if (!$("body").hasClass("folded")) {
            $("body").addClass("folded");
        }

        var suggest = [];
        var input_key_pressed = {};

        $("#f-field-list").sortable({
            handle: ".header",
            placeholder: "wck-sortable-placeholder",
            tolerance: "pointer"
        });

        $("#extra-inputs, #addon-inputs").sortable({
            handle: ".action-drag",
            placeholder: "wck-sortable-placeholder",
            tolerance: "pointer"
        });

        $('<input type="hidden" class="wck-global-color-picker" />').insertBefore("#wpwrap");
        $WK.colorpicker = $("input.wck-global-color-picker");
        $WK.colorpicker.iris();

        $WK.expressionLastFocusedInput = null;
        $WK.wpMediaFrame = null;
        $WK.wpMediaTarget = null;
        $WK.iconPreloader = "data:image/gif;base64,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";
        $WK.fieldsLayout = 'one-col';

        $WK.toggleButton = function (btn, e) {
            e.preventDefault();
            if (btn.hasClass("woocommerce-input-toggle--disabled")) {
                btn.removeClass("woocommerce-input-toggle--disabled").addClass("woocommerce-input-toggle--enabled");
                return true
            } else {
                btn.removeClass("woocommerce-input-toggle--enabled").addClass("woocommerce-input-toggle--disabled");
                return false;
            }
            return false;
        };

        $WK.fullscreenMode = function (state) {
            if (state) {
                $("body").css("overflow", "hidden");
                $("#postbox-container-2").addClass("fullscreen");
            } else {
                $("body").css("overflow", "auto");
                $("#postbox-container-2").removeClass("fullscreen");
            }
        };


        $(".wck-toggle-layout").on("click", function (e) {
            var state = $WK.toggleButton($(this), e);
            $WK.fieldsLayout = state ? 'two-col' : 'one-col';
            $WK.updateLayout();
        });

        $(".wck-toggle-fullscreen").on("click", function (e) {
            var state = $WK.toggleButton($(this), e);
            $WK.fullscreenMode(state);
        });

        $(".wck-toggle-expand").on("click", function (e) {
            var state = $WK.toggleButton($(this), e);
            $(".action-toggle.dashicons-arrow-" + (state ? "down" : "up") + "-alt2").trigger("click");
        });

        $("body").on("click", ".wck-toggle-colspan", function (e) {
            e.preventDefault();
            var item = $(this).parent().parent().parent();
            if ($(this).hasClass("woocommerce-input-toggle--disabled")) {
                $(this).removeClass("woocommerce-input-toggle--disabled").addClass("woocommerce-input-toggle--enabled");
                item.addClass("wck-layout-colspan");
                item.find('.f-colspan').val(2);
            } else {
                $(this).removeClass("woocommerce-input-toggle--enabled").addClass("woocommerce-input-toggle--disabled");
                item.removeClass("wck-layout-colspan");
                item.find('.f-colspan').val(1);
            }
        });

        $(".action-save-post").on("click", function (e) {
            e.preventDefault();
            $("#publish").trigger("click");
            if ($("#publish").hasClass("disabled")) {
                $(this).prop("disabled", true).addClass("disabled");
            }
        });

        $("body").on("click", ".action-duplicate", function (e) {
            e.preventDefault();
            var element = $(this).parent().parent().parent();
            var clonedElement = element.clone();
            clonedElement.hide();
            clonedElement.find(".f-title").val("");
            clonedElement.find(".f-name").val("");
            clonedElement.insertAfter(element);
            clonedElement.fadeIn(1500, function () {
                clonedElement.find(".dashicons-arrow-down-alt2").trigger("click");
                $(this).find(".f-title").fadeOut(150).fadeIn(150).fadeOut(150).fadeIn(150).fadeOut(150).fadeIn(150);
                $(this).find(".f-name").fadeOut(150).fadeIn(150).fadeOut(150).fadeIn(150).fadeOut(150).fadeIn(150);
            });
        });

        $WK.shouldHideExprToolbar = false;
        $WK.stateExprToolbar = false;

        $("body").on("focusin", "#wck_expression .input-icon input, #wck_inventory .input-icon input, input.expression-editor-enabled", function (e) {
            $WK.expressionLastFocusedInput = $(this);
            $WK.shouldHideExprToolbar = false;

            var posA = $(this).offset();
            var h = $(this).outerHeight();
            var posB = $("#wck_expression").offset();
            $("#wck-expression-toolbar").css({
                top: posA.top - posB.top - h - $("#wck-expression-toolbar").outerHeight() - 20,
                left: posA.left - posB.left
            });

            if (!$WK.stateExprToolbar) {
                $WK.stateExprToolbar = true;
                $WK.saveFields();
                $("#wck-parameters .first-selected").prop("selected", true);

                $("#wck-expression-toolbar").stop(true, false).fadeIn('fast');

            }
            $("#wck-parameters .total-price").toggle($(this).hasClass("show-total-price"));
            if (!$(this).hasClass("show-total-price") && $("#wck-parameters").val() === '{total_price}') {
                $("#wck-parameters").val("");
            }
        }).on("focusout", "#wck_expression input, #wck_inventory input, input.expression-editor-enabled", function (e) {
            $WK.shouldHideExprToolbar = !(e.relatedTarget && $("#wck-expression-toolbar").has(e.relatedTarget).length);
            setTimeout(function () {
                if ($WK.stateExprToolbar && $WK.shouldHideExprToolbar) {
                    $("#wck-expression-toolbar").stop(true, false).fadeOut('fast');
                    $WK.stateExprToolbar = false;
                }
            }, 200);
        }).on("click", "#wck-expression-toolbar", function (e) {
            if (e.target !== e.currentTarget && e.target.tagName !== "OPTION") return;
            $WK.expressionLastFocusedInput.focus();
        }).on("click", "button.add-field-to-formula, button.add-operator", function (e) {
            e.preventDefault();
            var $focused = $WK.expressionLastFocusedInput;
            if (!$focused) {
                $focused = $(".input-icon").find("input:visible").first();
                $focused.focus();
            }
            if ($focused && $focused.length) {
                var value = $(this).hasClass("add-operator") ? $(this).val() : $("#wck-parameters").val();
                if (value === null) {

                    return;
                }
                var cursorPos = $focused[0].selectionStart;
                var x = $focused.val();
                $focused.val(x.slice(0, cursorPos) + value + ($(this).data("ending") || "") + x.slice(cursorPos));
                $focused.focus();
                cursorPos += value.length;
                $focused[0].setSelectionRange(cursorPos, cursorPos);
            }
        }).on("click", ".field .pairs .action-add", function () {
            var $clone = $(this).prev(".pair").clone().insertBefore($(this));
            $clone.find("input").val("");
        }).on("click", ".field .pairs .action-showimport", function () {
            $(this).parent().find("div.importer").toggle();
        }).on("click", ".field .pairs .action-import", function () {
            var $this = $(this);
            var input = $this.parent().find("textarea").val().trim().split("\n");
            if (Array.isArray(input)) {
                input.forEach(function (el) {
                    var columns = el.trim().split(";");
                    var $last_pair = $this.parent().parent().find(".pair").last();
                    var $clone = $last_pair.clone().insertAfter($last_pair);
                    if (columns.length === 2) {
                        $clone.find("input.fs-name").val(columns[0]);
                        $clone.find("input.fs-title").val(columns[1]);
                    } else if (columns.length === 1) {
                        $clone.find("input.fs-title").val(columns[0]);
                    }
                });
            }
        }).on("click", ".field .pairs .action-removeall", function () {
            if (confirm("Are you sure?")) {
                $(this).parent().find(".pair").slice(1).remove();
            }
        }).on("click", ".field .pair .action-delete", function () {
            var $pairs = $(this).closest(".pairs");
            if ($pairs.children(".pair").length > 1) {
                if (confirm("Are you sure?")) {
                    $(this).closest(".pair").remove();
                }
            } else {
                alert("You cannot remove the last item!");
            }
        }).on("click", ".field .header .action-delete", function () {
            if (confirm("Are you sure?")) {
                var $target = $(this).closest("li");
                $target.fadeOut('slow', function () {
                    $target.remove();
                });
            }
        }).on("click", ".field .header .action-toggle", function () {
            var fieldBody = $(this).closest(".field").find(".body");
            fieldBody.slideToggle(300);
            $(this).toggleClass("dashicons-arrow-up-alt2");
            $(this).toggleClass("dashicons-arrow-down-alt2");
            if ($(this).hasClass("dashicons-arrow-down-alt2")) {
                $(this).parent().find(".name").text((fieldBody.find(".f-title").length ? "- " + fieldBody.find(".f-title").val() : "") + " {" + fieldBody.find(".f-name").val() + "}");
            } else {
                $(this).parent().find(".name").text("");
            }

        }).on("click", "#add-field-button", function (e) {
            e.preventDefault();
            var fieldType = $("#select-field").val();
            var id = $WK.addField(fieldType);
            $('html, body').animate({scrollTop: $("#" + id).offset().top - 50}, 1000);
        }).on("click", ".expression_conditional .input-group .action-delete", function () {
            var $target = $(this).closest(".input-group");
            if ($("#extra-inputs .input-group").length > 1) {
                if (confirm("Are you sure?")) {
                    $target.hide('slow', function () {
                        $target.remove();
                    });
                }
            } else {
                alert("You cannot remove the last condition!");
            }
        }).on("click", ".expression_addon .input-group .action-delete", function () {
            var $target = $(this).closest(".input-group");
            if ($("#addon-inputs .input-group").length > 1) {
                if (confirm("Are you sure?")) {
                    $target.hide('slow', function () {
                        $target.remove();
                    });
                }
            } else {
                alert("You cannot remove the last addon!");
            }
        }).on("click", ".action-add-image", function (e) {
            e.preventDefault();
            $WK.wpMediaTarget = $(this);

            if ($WK.wpMediaFrame) {
                $WK.wpMediaFrame.open();
                return;
            }
            $WK.wpMediaFrame = wp.media({
                multiple: false,
                button: {
                    text: "Select this image"
                }
            });

            $WK.wpMediaFrame.on('select', function () {
                var attachment = $WK.wpMediaFrame.state().get('selection').first().toJSON();
                if ($WK.wpMediaTarget.prop("tagName") === "A") {
                    $WK.wpMediaTarget.next('img.wp-media-image-preview').attr('src', attachment.url);
                    $WK.wpMediaTarget.next().next('input.wp-media-image-id').attr('value', attachment.id);
                    $WK.wpMediaTarget.hide();
                } else {
                    $WK.wpMediaTarget.attr('src', attachment.url);
                    $WK.wpMediaTarget.next('input.wp-media-image-id').attr('value', attachment.id);
                }
            });

            $WK.wpMediaFrame.open();
        }).on("click", ".action-add-attachment", function (e) {
            e.preventDefault();
            $WK.wpMediaTarget = $(this);

            if ($WK.wpMediaFrame) {
                $WK.wpMediaFrame.open();
                return;
            }
            $WK.wpMediaFrame = wp.media({
                multiple: false,
                button: {
                    text: "Select this file"
                }
            });

            $WK.wpMediaFrame.on('select', function () {
                var attachment = $WK.wpMediaFrame.state().get('selection').first().toJSON();
                $WK.wpMediaTarget.next('input.wp-media-attachment-id').val(attachment.id);
                $WK.wpMediaTarget.next().next('a.wp-media-attachment-preview').attr('href', attachment.url).text(attachment.url);

            });

            $WK.wpMediaFrame.open();
        }).on("focus", '[data-type="colorswatches"] input.fs-title', function (e) {
            e.preventDefault();
            var _target = $(this);
            $WK.colorpicker.iris('show');
            $(".iris-picker").css({
                top: _target.offset().top + 40,
                left: _target.offset().left
            });
            $WK.colorpicker.iris('option', 'change', function (event, ui) {
                _target.val(ui.color.toString());
                var color = ui.color.toRgb();
                var t = (color.r + color.g + color.b) / 2;
                var textColor = (t < 200) ? "#FFFFFF" : "#000000";
                _target.css({
                    "background-color": ui.color.toString(),
                    "color": textColor
                });
            });
            $WK.colorpicker.iris('color', $(this).val());

        }).on("click", "input.allowed-extensions", function () {
            var field_id = '#' + $(this).closest('li').attr('id');
            var ext = [];
            $(field_id + " input.allowed-extensions:checked").each(function () {
                //console.log($(this).data('extension'));
                ext.push($(this).data('extension'));
            });
            $(field_id + " input.fu-allowed-extensions").val(ext.join('|'));
        }).on('change', 'input.f-name', function () {
            var target = $(this);
            var newName = $(this).val().trim();
            if (newName !== "") {
                $("#wck_fields_editor input.f-name").each(function () {
                    if ($(this).val() === newName && !$(this).is(target)) {
                        alert("The name {" + newName + "} is already in use! Choose another name.");
                        target.val("");
                        return false;
                    }
                });
            }

            if ($(this)[0].checkValidity() && typeof $(this).data('lastValid') !== "undefined") {
                var oldName = $(this).data('lastValid');
                if (oldName !== newName) {
                    var oldNameInUse = false;
                    $("#wck_expression input[type=text], #wck_fields_editor input.f-visibility, #wck_fields_editor input.f-visibility-readable, #wck_fields_editor input.visibility-readable").each(function () {
                        if ($(this).val().includes(oldName)) {
                            oldNameInUse = true;
                            return false; //break
                        }
                    });
                    if (oldNameInUse && confirm("Wait! Seems like {" + oldName + "} is used in formulas! Do you want to replace {" + oldName + "} in all formulas? {" + oldName + "} will be changed to {" + newName + "}")) {
                        $("#wck_expression input[type=text], #wck_fields_editor input.f-visibility, #wck_fields_editor input.f-visibility-readable, #wck_fields_editor input.visibility-readable").each(function () {
                            $(this).val($(this).val().replaceAll("{" + oldName + "}", "{" + newName + "}").replaceAll("{" + oldName + ":", "{" + newName + ":").replaceAll('"field":"' + oldName + '"', '"field":"' + newName + '"'));
                        });
                        $(this).data('lastValid', newName);
                    }
                }
            }

        }).on('focusin', 'input.f-name', function () {
            if ($(this)[0].checkValidity()) {
                $(this).data('lastValid', $(this).val());
            }
        });

        $(".iris-square-value").on("click", function (e) {
            e.preventDefault();
        });

        $(document).click(function (e) {
            if (!$(e.target).closest('.iris-picker, [data-type="colorswatches"] input.fs-title').length) {
                if ($('.iris-picker').is(":visible")) {
                    $WK.colorpicker.iris('hide');
                }
            }
        });

        $("form#post").submit(function (e) {
            $WK.saveFields();
            if (!$WK.saved) {
                e.preventDefault();
                //alert("There are some missing fields! Fill in required fields and try again.");
                return false;
            }
            $WK.saveExpression();
            if (!$WK.expr_saved && $WK.expression.mode !== "off") {
                e.preventDefault();
                alert("Expression is required. Fill in required fields and try again!");
                return false;
            }
            return true;
        });

        $WK.init = function () {
            $WK.fieldList = $("#f-field-list");
            $WK.html = wck_fields_html;
            $WK.fields = {};
            $WK.saved = false;
            $WK.expr_saved = false;
            $WK.counter = 0;
            $WK.expression = {
                mode: $("input.expression_type:checked").val() //oneline || conditional || off
            };
            $WK.loadJSONdata();
            $WK.showExpressionEditor();
            $WK.changeAssignType();
            $("form#post").attr("novalidate", "");
            $WK.buildTooltips("");
            $WK.updateLayout();
            $("#wck-parameters .first-selected").prop("selected", true);
            //$WK.appendGlobalParameters();
        };

        $WK.updateLayout = function () {
            if ($WK.fieldsLayout === 'two-col') {
                $WK.fieldList.addClass("layout-two-col");
            } else {
                $WK.fieldList.removeClass("layout-two-col");
            }
        };

        $WK.buildTooltips = function (selector) {
            $(selector + "span.wck-field-tip").tipTip({
                attribute: 'title',
                defaultPosition: 'left'
            });
        };

        $WK.changeAssignType = function () {
            var disabled = $("#assign_type").val() === "1";
            $("#assign_products, #assign_categories, #assign_tags, #assign_attributes").prop("disabled", disabled);
            $(".hide-if-disabled").toggle(!disabled);
        };
        $("#assign_type").on("change", $WK.changeAssignType);

        $WK.preloadMedia = function (id, callback) {
            wp.media.attachment(id).fetch().then(function () {
                callback(wp.media.attachment(id));
            });
            return;
        };

        $WK.addField = function (type) {
            $WK.counter += 1;
            var id = "wkfield-" + $WK.counter;
            $("li.welcome", $WK.fieldList).remove();
            $WK.fieldList.append($('<li/>', {
                "data-type": type,
                "class": "form-invalid",
                "id": id
            }).append($WK.html[type].replace("{id}", id)));
            $WK.buildTooltips("#" + id + " ");
            if ($("#" + id).find(".pairs").length > 0) {
                $("#" + id + " .pairs").sortable({
                    placeholder: "wck-sortable-placeholder",
                    tolerance: "pointer"
                });
            }
            return id;
        };

        $(window).on('beforeunload', function () {
            if ($WK.saved === false) {
                return "";
            }
        });

        $WK.saveFields = function () {
            $WK.fields = {};
            suggest = [];
            var error = $("li .field", $WK.fieldList).length === 0;
            $("label.error").remove();
            $WK.saved = false;
            //$("#formula_fields").html(" &dash; ");
            $("#wck-parameters .defined-fields").html("");
            $WK.appendGlobalParameters();

            if (error) {
                alert("Please add at least one field before saving.");
                return false;
            }

            $("li .field", $WK.fieldList).each(function () {
                var $row = $(this);
                //$('.form-required', $row).removeClass("form-required");
                var input_fname = $row.find('input.f-name');
                var input_ftitle = $row.find('input.f-title');
                var input_fhint = $row.find('input.f-hint');
                var input_default_value = $row.find('input.f-default-value');
                var input_css_class = $row.find('input.f-css-class');
                var field = {
                    "type": $row.data("type"),
                    "use_expression": $row.data("use-expression") === true,
                    "name": input_fname.val(),
                    "title": input_ftitle.val(),
                    "before_title": $row.find('input.f-before-title').val(),
                    "after_title": $row.find('input.f-after-title').val(),
                    "hint": input_fhint.val(),
                    "default_value": input_default_value.val(),
                    "css_class": input_css_class.val(),
                    //"required": (($row.find('select.f-required').length > 0) ? $row.find('select.f-required').val() === "on" : true),
                    "layout": $WK.fieldsLayout,
                    "colspan": $row.find('input.f-colspan').val(),
                    "visibility": $row.find('input.f-visibility').val(),
                    "visibility_readable": $row.find('input.f-visibility-readable').val()
                };

                if ($row.find('select.f-required').length > 0) {
                    switch ($row.find('select.f-required').val()) {
                        case "on":
                            field.required = "1"; //true
                            break;
                        case "if-visible":
                            field.required = "2"; //if visible
                            break;
                        case "off":
                            field.required = "0"; //false
                            break;
                    }
                } else {
                    field.required = "1"; //true
                }

                var input_fprice = $row.find('input.f-price');
                if (input_fprice.length > 0) {
                    field.price = input_fprice.val();
                    if (field.price !== "") {
                        field.use_expression = true;
                    }
                }

                var input_fimgwidth = $row.find('input.fimg-width');
                if (input_fimgwidth.length > 0) {
                    field.image_size = input_fimgwidth.val();
                }

                var $fs_options;

                switch (field.type) {
                    case 'select':
                    case 'radio':
                    case 'imageselect':
                    case 'imageswatches':
                    case 'colorswatches':
                    case 'radiogroup':
                    case 'checkboxgroup':
                        field.options_name = [];
                        field.options_title = [];
                        if (field.type === "imageselect" || field.type === "imageswatches") {
                            field.options_image = [];
                        }
                        if (field.type === "checkboxgroup") {
                            field.select_limit = $row.find("input.fcbg-limit").val();
                        }
                        $fs_options = $row.find(".fs-option");
                        $fs_options.each(function () {
                            var fs_title = $(this).find("input.fs-title");
                            var fs_name = $(this).find("input.fs-name");

                            var f_default_value = $(this).find('input.f-default-value').is(":checked");
                            //alert("State: " + f_default_value);
                            if (typeof f_default_value !== "undefined" && f_default_value === true) {
                                field.default_value = fs_name.val() + ":" + fs_title.val();
                            }
                            field.options_name.push(fs_name.val() + ":" + fs_title.val());
                            field.options_title.push(fs_title.val());

                            if (field.type === "imageselect" || field.type === "imageswatches") {
                                var fs_image = $(this).find("input.fs-image");
                                field.options_image.push(fs_image.val());
                            }
                        });

                        break;
                    case 'dropdown':
                        field.options_title = [];
                        $fs_options = $row.find(".fs-option");
                        $fs_options.each(function () {
                            var fs_title = $(this).find("input.fs-title");
                            /*if (fs_title.val() === "" || typeof fs_title === "undefined") {
                                fs_title.addClass("form-required");
                                error = true;
                            }*/
                            var f_default_value = $(this).find('input.f-default-value').is(":checked");
                            if (typeof f_default_value !== "undefined" && f_default_value === true) {
                                field.default_value = fs_title.val();
                                //alert("Value: " + field.default_value);
                            }
                            field.options_title.push(fs_title.val());
                        });
                        break;

                    case 'number':
                        var input_fnmin = $row.find('input.fn-min-value');
                        field.min = input_fnmin.val();
                        /*if (field.min === "") {
                            input_fnmin.addClass("form-required");
                            error = true;
                        }*/
                        var input_fnmax = $row.find('input.fn-max-value');
                        field.max = input_fnmax.val();
                        /*if (field.max === "") {
                            input_fnmax.addClass("form-required");
                            error = true;
                        }*/

                        break;
                    case 'checkbox':
                        var input_fcb_default_state = $row.find('select.fcb-default-state');
                        field.default_state = input_fcb_default_state.val() === "on";
                        break;
                    case 'text':
                    case 'textarea':
                    case 'email':
                        var input_ftminlen = $row.find('input.ft-min-length');
                        var input_ftmaxlen = $row.find('input.ft-max-length');
                        var input_ftpattern = $row.find('input.ft-pattern');
                        field.min = input_ftminlen.val();
                        field.max = input_ftmaxlen.val();
                        if (input_ftpattern.length > 0) {
                            field.pattern = input_ftpattern.val();
                        }
                        break;
                    case 'colorpicker':
                    case 'datepicker':
                    case 'rangedatepicker':
                        var input_fdpdisallow_past_date = $row.find('input.fdp-disallow-past-date');
                        field.disallow_past_date = input_fdpdisallow_past_date.is(':checked');
                        break;
                    case 'imageupload':
                    case 'fileupload':
                        //field.max_file_count = $row.find('input.fu-max-file-count').val();
                        field.max_file_size = $row.find('input.fu-max-file-size').val();
                        field.allowed_extensions = $row.find('input.fu-allowed-extensions').val();
                        break;
                    case 'html':
                    case 'paragraph':
                    case 'hidden':
                    case 'attachment':
                        field.content = $row.find('.fst-content').val();
                        break;
                    case 'formula':
                        field.content = $row.find('.fst-content').val();
                        field.display_on_user_cart = $row.find('.fst-display-on-user-cart').val() === "on";
                        break;
                    case 'link':
                        field.content = $row.find('.fst-content').val();
                        field.target = $row.find('.fst-target').val();
                        break;
                    case 'heading':
                        field.content = $row.find('.fst-content').val();
                        field.level = $row.find('.fst-level').val();
                        break;
                    /*default:
                        error = true;
                        alert("Error! Unrecognized field type!");
                        */
                }

                if (field.name !== "" && field.name in $WK.fields) {
                    error = true;
                    alert("Field names must be unique! The '" + field.name + "' name is used more than once.");
                }

                if (!error) {
                    $WK.appendFormulaVars(field);
                }

            });

            var wkform = $("form#post");
            if (!wkform[0].checkValidity()) {
                error = true;
                $("input:invalid").closest('.field').find('.action-toggle.dashicons-arrow-down-alt2').trigger('click');
                $("input:invalid").each(function () {
                    $('<label class="error"><span class="dashicons dashicons-warning"></span> ' + $(this)[0].validationMessage + "</label>").insertBefore($(this));
                });
            }

            if (error && $("label.error").length) {
                $('html, body').animate({scrollTop: $("label.error").first().offset().top - 160}, 1000);
            }

            $WK.saveJSONdata();
            $WK.saved = !error;

        };

        $WK.appendGlobalParameters = function () {
            if (typeof wck_global_parameters !== undefined) {
                $("#wck-parameters .global-parameters").html("");
                $.each(wck_global_parameters, function (name, value) {
                    var name = "global:" + name;
                    var appendChildren = "";
                    suggest.push(name);
                    //$("#formula_fields").append('<span class="formula-field">{' + name + '}</span> ');
                    if (typeof value === 'object') {
                        $.each(value, function (k, v) {
                            var suffix = "['" + k + "']";
                            appendChildren = appendChildren + '<option value="{' + name + '}' + suffix + '">' + name.replace('global:', '') + '[' + k + '] = ' + v + '</option>';
                        });
                        value = "";
                    } else {
                        value = " = " + value;
                    }
                    $("#wck-parameters .global-parameters").append('<option value="{' + name + '}">' + name.replace('global:', '') + value + '</option>');
                    $("#wck-parameters .global-parameters").append(appendChildren);
                });
            }
        }

        $WK.appendFormulaVars = function (field) {
            $WK.fields[field.name] = field;
            if (field.use_expression) {
                if (field.type !== 'checkboxgroup') {
                    suggest.push(field.name);
                    //$("#formula_fields").append('<span class="formula-field">{' + field.name + '}</span> ');
                    $("#wck-parameters .defined-fields").append('<option value="{' + field.name + '}">"' + field.title + '" value/price {' + field.name + '}' + '</option>');
                } else {
                    suggest.push(field.name);
                    //$("#formula_fields").append('<span class="formula-field">{' + field.name + '}</span> ');
                    $("#wck-parameters .defined-fields").append('<option value="{' + field.name + '}">Array of selected values {' + field.name + '}' + '</option>');
                    suggest.push(field.name + ":sum");
                    //$("#formula_fields").append('<span class="formula-field">{' + field.name + ':sum}</span> ');
                    $("#wck-parameters .defined-fields").append('<option value="{' + field.name + ':sum}">Sum of "' + field.title + '" {' + field.name + ':sum}' + '</option>');
                    suggest.push(field.name + ":min");
                    //$("#formula_fields").append('<span class="formula-field">{' + field.name + ':min}</span> ');
                    $("#wck-parameters .defined-fields").append('<option value="{' + field.name + ':min}">Min. value of "' + field.title + '" {' + field.name + ':min}' + '</option>');
                    suggest.push(field.name + ":max");
                    //$("#formula_fields").append('<span class="formula-field">{' + field.name + ':max}</span> ');
                    $("#wck-parameters .defined-fields").append('<option value="{' + field.name + ':max}">Max. value of "' + field.title + '" {' + field.name + ':max}' + '</option>');
                }
                if (field.type === "text" || field.type === "textarea") {
                    suggest.push(field.name + ":text");
                    //$("#formula_fields").append('<span class="formula-field">{' + field.name + ':text}</span> ');
                    $("#wck-parameters .defined-fields").append('<option value="{' + field.name + ':text}">Text value of "' + field.title + '" {' + field.name + ':text}' + '</option>');
                }
                if (field.type === "rangedatepicker") {
                    suggest.push(field.name + ":date_from");
                    //$("#formula_fields").append('<span class="formula-field">{' + field.name + ':date_from}</span> ');
                    $("#wck-parameters .defined-fields").append('<option value="{' + field.name + ':date_from}">Date "from" of "' + field.title + '" {' + field.name + ':date_from}' + '</option>');
                    suggest.push(field.name + ":date_to");
                    // $("#formula_fields").append('<span class="formula-field">{' + field.name + ':date_to}</span> ');
                    $("#wck-parameters .defined-fields").append('<option value="{' + field.name + ':date_to}">Date "to" of "' + field.title + '" {' + field.name + ':date_to}' + '</option>');
                    suggest.push(field.name + ":days");
                    // $("#formula_fields").append('<span class="formula-field">{' + field.name + ':days}</span> ');
                    $("#wck-parameters .defined-fields").append('<option value="{' + field.name + ':days}">Days between dates of "' + field.title + '" {' + field.name + ':days}' + '</option>');
                } else if (field.type === "datepicker") {
                    suggest.push(field.name + ":date");
                    // $("#formula_fields").append('<span class="formula-field">{' + field.name + ':date}</span> ');
                    $("#wck-parameters .defined-fields").append('<option value="{' + field.name + ':date}">Timestamp of "' + field.title + '" {' + field.name + ':date}' + '</option>');
                } else if (field.type === "imageupload" || field.type === "fileupload") {
                    suggest.push(field.name + ":size");
                    //$("#formula_fields").append('<span class="formula-field">{' + field.name + ':size}</span> ');
                    $("#wck-parameters .defined-fields").append('<option value="{' + field.name + ':size}">File size of "' + field.title + '" {' + field.name + ':size}' + ' [MB]</option>');
                }
            }
        };

        $WK.saveJSONdata = function () {
            if (Object.keys($WK.fields).length > 0) {
                $("input[name=_wck_fieldset]").val(JSON.stringify($WK.fields));
            }
        };

        $WK.loadJSONdata = function () {
            if (typeof wck_load_fieldset === "object") {
                var layoutLoaded = false;
                $.each(wck_load_fieldset, function () {

                    if (!layoutLoaded) {
                        if (this.hasOwnProperty("layout") && this.layout === 'two-col') {
                            $(".wck-toggle-layout").trigger("click");
                        }
                        layoutLoaded = true;
                    }

                    var field_id = $WK.addField(this.type);
                    var $field = $("#" + field_id + " .field");
                    $("#" + field_id + " .f-name").val(this.name);
                    if (this.hasOwnProperty("colspan")) {
                        $("#" + field_id + " .f-colspan").val(this.colspan);
                        if (this.colspan === '2') {
                            $("#" + field_id + " .wck-toggle-colspan").trigger("click");
                        }
                    }
                    $("#" + field_id + " .f-title").val(this.title);
                    if (this.hasOwnProperty("before_title")) {
                        $("#" + field_id + " .f-before-title").val(this.before_title);
                    }
                    if (this.hasOwnProperty("after_title")) {
                        $("#" + field_id + " .f-after-title").val(this.after_title);
                    }
                    $("#" + field_id + " .f-hint").val(this.hint);
                    $("#" + field_id + " .f-css-class").val(this.css_class);
                    if ($("#" + field_id + " .f-required").length > 0) {
                        $("#" + field_id + " .f-required").val(this.required === "1" ? "on" : (this.required === "2" ? "if-visible" : "off"));
                    }

                    if (this.hasOwnProperty("visibility")) {
                        $("#" + field_id + " .f-visibility").val(this.visibility);
                        $("#" + field_id + " .f-visibility-readable").val(this.visibility_readable);
                        if (this.visibility_readable !== "") {
                            $("#" + field_id + " .visibility-readable").val("Rule preview: " + this.visibility_readable);
                        }
                    }

                    var fprice = $("#" + field_id + " .f-price");
                    if (fprice.length > 0) {
                        fprice.val(this.price);
                    }

                    var fimgwidth = $("#" + field_id + " .fimg-width");
                    if (fimgwidth.length > 0) {
                        fimgwidth.val(this.image_size);
                    }


                    var options_title, options_name, options_image, default_value, first, $checked;

                    if (this.type === "dropdown") {

                        options_title = this.options_title;
                        default_value = this.default_value;
                        first = true;
                        $checked = null;
                        $.each(options_title, function (i, option_title) {
                            if (first) {
                                var $first = $field.find(".fs-option");
                                $first.find("input.fs-title").val(option_title);
                                if (default_value === option_title) {
                                    $checked = $first.find("input.f-default-value");//.prop("checked", true);
                                }
                                first = false;
                            } else {
                                var $clone = $field.find(".fs-option").last().clone().insertBefore($("#" + field_id + " .fs-options .action-add"));
                                $clone.find("input.fs-title").val(option_title);
                                if (default_value === option_title) {
                                    $checked = $clone.find("input.f-default-value");//.prop("checked", true);
                                }
                            }
                            if ($checked) {
                                $checked.prop("checked", true);
                            }
                        });

                    } else if (['select', 'radio', 'imageselect', 'radiogroup', 'checkboxgroup', 'imageswatches', 'colorswatches'].indexOf(this.type) >= 0) {
                        options_name = this.options_name;
                        options_title = this.options_title;
                        default_value = this.default_value;
                        if (this.type === "imageselect" || this.type === "imageswatches") {
                            options_image = this.options_image;
                            wp.media.attachment(options_image).fetch();
                        }
                        if (this.type === "checkboxgroup") {
                            $("#" + field_id + " .fcbg-limit").val(this.select_limit);
                        }
                        //console.log("Dropdown: ", default_value);
                        first = true;
                        $checked = null;
                        $.each(options_name, function (i, option_name) {
                            //Clean option_name after ":"
                            if (option_name.indexOf(':') >= 0) {
                                option_name = option_name.slice(0, option_name.indexOf(':'));
                            }

                            if (first) {
                                var $first = $field.find(".fs-option");
                                $first.find("input.fs-name").val(option_name);
                                $first.find("input.fs-title").val(options_title[i]);
                                var fs_image = $first.find("input.fs-image");
                                if (fs_image.length > 0) {
                                    fs_image.val(options_image[i]);
                                    $first.find("a.action-add-image").hide();
                                    $first.find(".wp-media-image-preview").attr("src", $WK.iconPreloader);
                                    $WK.preloadMedia(options_image[i], function (attachment) {
                                        $first.find(".wp-media-image-preview").attr("src", attachment.get('url'));
                                    });
                                }
                                if (default_value === option_name) {
                                    $checked = $first.find("input.f-default-value");//.prop("checked", true);
                                }
                                first = false;
                            } else {
                                var $clone = $field.find(".fs-option").last().clone().insertBefore($("#" + field_id + " .fs-options .action-add"));
                                $clone.find("input.fs-name").val(option_name);
                                $clone.find("input.fs-title").val(options_title[i]);
                                fs_image = $clone.find("input.fs-image");
                                if (fs_image.length > 0) {
                                    fs_image.val(options_image[i]);
                                    $clone.find("a.action-add-image").hide();
                                    $clone.find(".wp-media-image-preview").attr("src", $WK.iconPreloader);
                                    $WK.preloadMedia(options_image[i], function (attachment) {
                                        $clone.find(".wp-media-image-preview").attr("src", attachment.get('url'));
                                    });
                                }
                                if (default_value === option_name) {
                                    $checked = $clone.find("input.f-default-value");//.prop("checked", true);
                                }
                            }
                            if ($checked) {
                                $checked.prop("checked", true);
                            }
                        });
                    } else if (this.type === "number") {
                        $("#" + field_id + " .fn-min-value").val(this.min);
                        $("#" + field_id + " .fn-max-value").val(this.max);
                        $("#" + field_id + " .f-default-value").val(this.default_value);
                    } else if (this.type === "checkbox") {
                        $("#" + field_id + " .fcb-default-state").val(this.default_state === "1" ? "on" : "off");

                    } else if (this.type === "text" || this.type === "textarea" || this.type === "email") {
                        $("#" + field_id + " .ft-min-length").val(this.min);
                        $("#" + field_id + " .ft-max-length").val(this.max);
                        $("#" + field_id + " .f-default-value").val(this.default_value);
                        if (this.type === "text") {
                            $("#" + field_id + " .ft-pattern").val(this.pattern);
                        }
                        //$("#" + field_id + " .ft-pattern").val(this.pattern);
                    } else if (this.type === "colorpicker" || this.type === "datepicker" || this.type === "rangedatepicker") {
                        $("#" + field_id + " .fdp-disallow-past-date").prop("checked", this.disallow_past_date);
                    } else if (this.type === "imageupload" || this.type === "fileupload") {
                        $("#" + field_id + " .fu-max-file-size").val(this.max_file_size);
                        $("#" + field_id + " .fu-allowed-extensions").val(this.allowed_extensions);
                        var ext = this.allowed_extensions.split("|");
                        ext.forEach(function (e) {
                            $("#" + field_id + " .allowed-extensions.ext-" + e).prop("checked", true);
                        });
                    } else if (['html', 'paragraph', 'heading', 'hidden', 'link', 'attachment', 'formula'].indexOf(this.type) >= 0) {
                        $("#" + field_id + " .fst-content").val(this.content);
                        if (this.type === 'heading') {
                            $("#" + field_id + " .fst-level").val(this.level);
                        } else if (this.type === 'link') {
                            $("#" + field_id + " .fst-target").val(this.target);
                        } else if (this.type === 'attachment') {
                            $WK.preloadMedia(this.content, function (attachment) {
                                //console.log(field_id, attachment.get('url'));
                                $("#" + field_id + " .wp-media-attachment-preview").attr("href", attachment.get('url')).text(attachment.get('url'));
                            });
                        } else if (this.type === 'formula') {
                            $("#" + field_id + " .fst-display-on-user-cart").val(this.display_on_user_cart === "1" ? "on" : "off");
                        }
                    }
                });
                $WK.saveFields();
            }
        };


        // -------- EXPRESSION EDITOR -----------

        $WK.showExpressionEditor = function () {
            $("div.expression_oneline, div.expression_conditional, div.expression_off, div.expression_addon").hide();
            $("div.expression_" + $WK.expression.mode).show();
            if ($WK.expression.mode === "off") {
                $(".off-hide").hide();
            } else {
                $(".off-hide").show();
            }
        };

        $("input.expression_type").on("change", function () {
            $WK.expression.mode = $(this).val();
            $WK.expressionLastFocusedInput = null;
            $WK.showExpressionEditor();
        });

        $WK.addCondition = function (if_value, then_value, addon = false) {
            var $html = $('<div class="input-group">' +
                '<div class="input-buttons">' +
                '<span class="action-drag dashicons dashicons-move"></span>' +
                '</div>' +
                '<div class="input-icon input-if">' +
                '<input type="text" placeholder="Logical expression..." value=""><i></i>' +
                '</div>' +
                '<div class="input-icon input-equation">' +
                '<input type="text" placeholder="Price formula..." value=""><i></i>' +
                '</div>' +
                '<div class="input-buttons">' +
                '<span class="action-delete dashicons dashicons-trash"></span>' +
                '</div>' +
                '<div class="clearfix"></div></div>');
            $(".input-if input", $html).val(if_value);
            $(".input-equation input", $html).val(then_value);
            if (addon === true) {
                $("div#addon-inputs").append($html);
            } else {
                $("div#extra-inputs").append($html);
            }
            $WK.autocomplete();
        };

        $("button.add-condition").on("click", function () {
            $WK.addCondition();
        });

        $("button.add-addon").on("click", function () {
            $WK.addCondition("", "", true);
        });

        $WK.autocomplete = function () {
            $(".input-icon input").bind("keydown", function (e) {
                if (e.keyCode === 16 || e.keyCode === 219) {
                    input_key_pressed[e.keyCode] = true;
                }
            }).autocomplete({
                minLength: 0,
                source: function (request, response) {
                    if (input_key_pressed[16] === true && input_key_pressed[219] === true) {
                        response(suggest);
                    }
                },
                focus: function () {
                    return false;
                },
                select: function (event, ui) {
                    delete input_key_pressed[16];
                    delete input_key_pressed[219];
                    this.value += ui.item.value + "}";
                    return false;
                }
            });
        };

        $WK.saveExpression = function () {
            var mode = $WK.expression.mode;
            $WK.expr_saved = false;
            if (mode === "oneline") {
                $WK.expression.expr = $(".expression_oneline input").val();
                if ($WK.expression.expr !== "") {
                    $WK.expr_saved = true;
                }
            }
            if (mode === "conditional") {
                var data = [];
                $(".expression_conditional .input-group").each(function () {
                    var input_if = $(this).find(".input-if input").val();
                    var input_eq = $(this).find(".input-equation input").val();
                    data.push({
                        "type": "condition",
                        "if": input_if,
                        "then": input_eq
                    });
                });
                var input_else = $(".expression_conditional .input-else input").val();
                data.push({
                    "type": "else",
                    "if": "true",
                    "then": input_else
                });
                $WK.expression.expr = data;
                if ($WK.expression.expr.length > 0) {
                    $WK.expr_saved = true;
                }
            }

            if (mode === "addon") {
                var data = [];
                $(".expression_addon .input-group").each(function () {
                    var input_if = $(this).find(".input-if input").val();
                    var input_eq = $(this).find(".input-equation input").val();
                    data.push({
                        "type": "condition",
                        "if": input_if,
                        "then": input_eq
                    });
                });
                $WK.expression.expr = data;
                if ($WK.expression.expr.length > 0) {
                    $WK.expr_saved = true;
                }
            }
            if (mode === "off") {
                $("input[name=_wck_expression]").val("off");
            } else {
                $("input[name=_wck_expression]").val(JSON.stringify($WK.expression));
            }
        };

        $WK.loadExpression = function () {
            if (typeof wck_load_expression === "object") {
                $WK.expression.mode = wck_load_expression.mode;
                $WK.expression.expr = wck_load_expression.expr;
                if ($WK.expression.mode === "oneline") {
                    $(".expression_oneline input").val($WK.expression.expr);
                    $("input[name=_wck_choose_expression_type].expression_oneline").prop("checked", true);
                    $WK.showExpressionEditor();
                    $WK.addCondition();
                } else if ($WK.expression.mode === "conditional") {
                    $.each($WK.expression.expr, function () {
                        if (this.type === "condition") {
                            $WK.addCondition(this.if, this.then);
                        } else if (this.type === "else") {
                            $(".input-else input").val(this.then);
                        }
                    });
                    $("input[name=_wck_choose_expression_type].expression_conditional").prop("checked", true);
                    $WK.showExpressionEditor();
                } else if ($WK.expression.mode === "addon") {
                    $.each($WK.expression.expr, function () {
                        $WK.addCondition(this.if, this.then, true);
                    });
                    $("input[name=_wck_choose_expression_type].expression_addon").prop("checked", true);
                    $WK.showExpressionEditor();
                }
            } else {
                //console.log("Expression off");
                $WK.expression.mode = "off";
                $("input[name=_wck_choose_expression_type].expression_off").prop("checked", true);
                $WK.showExpressionEditor();
                $WK.addCondition();
            }
        };

        $("button.test-expression").on("click", function () {
            $WK.saveExpression();
        });

        $WK.toolbarOnTop = false;

        $(window).scroll(function () {
            if ($(this).scrollTop() > 100) {
                if (!$WK.toolbarOnTop) {
                    $("#wck-toolbar").fadeOut(function () {
                        $WK.toolbarOnTop = true;
                        $("#wck-toolbar").css({"top": "0px", "bottom": "auto"}).fadeIn();
                    });
                }
            } else if (!$("#postbox-container-2").hasClass("fullscreen")) {
                if ($WK.toolbarOnTop) {
                    $("#wck-toolbar").fadeOut(function () {
                        $WK.toolbarOnTop = false;
                        $("#wck-toolbar").css({"top": "auto", "bottom": "0px"}).fadeIn();
                    });
                }
            }
        });

        // --------------- CONDITIONAL VISIBILITY BUILDER -------------

        $CV.window = $("#wck-cv-builder");
        $CV.builder = $CV.window.find(".builder");
        $CV.template = $CV.window.find(".template").children();
        $CV.context = null;

        // --- Methods ---

        $CV.open = function (context) {
            $CV.context = context;
            var fieldSelect = $CV.template.find("select.p-field");
            fieldSelect.empty();
            $WK.saveFields();
            fieldSelect.append('<option value="" selected disabled>Choose field...</option>');
            $.each($WK.fieldList.find(".field"), function () {
                if ($(this).data("group") !== "static" && !$(this).is(context)) {
                    var name = $(this).find(".f-name").val();
                    fieldSelect.append('<option value="' + name + '">' + name + '</option>');
                }
            });

            $CV.builder.append($CV.template.clone());
            $CV.builder.find('.or-condition').remove();

            var loadData = $CV.context.find(".f-visibility").val();
            try {
                loadData = JSON.parse(loadData);
            } catch (e) {
                loadData = false;
            }

            if (typeof loadData === "object") {
                $.each(loadData, function (i, or_condition) {
                    var first = true;
                    $.each(or_condition, function (j, and_condition) {
                        $CV.add(first ? "or" : "and", null, and_condition);
                        first = false;
                    });
                });
            } else {
                $CV.add("or", null, null);
            }

            $CV.window.find("span.self-name").text(context.find(".f-name").val());
            $CV.window.show().css({"display": "flex"});
        };

        $CV.close = function () {
            $CV.window.hide();
            $CV.builder.empty();
        };

        $CV.add = function (type, destination, values) {
            if (destination === null) {
                destination = $CV.builder.find("." + type + "-group").last();
            }
            destination.append($CV.window.find(".template ." + type + "-condition").clone());
            var newCondition = destination.find(".and-condition").last();

            if (values !== null && typeof values === "object") {
                newCondition.find(".p-field").val(values.field);
                newCondition.find(".p-comparison").val(values.comp).trigger("change");
                newCondition.find(".p-value").val(values.value);
            } else {
                newCondition.find(".p-value").val("");
            }
        };

        $CV.validate = function () {
            var isValid = true;
            var conditions = $CV.builder.find(".and-condition");

            conditions.find(".validation-error").removeClass("validation-error");
            conditions.each(function () {
                var field = $(this).find(".p-field");
                var comp = $(this).find(".p-comparison").val();
                var value = $(this).find(".p-value");

                if ([null, ""].includes(field.val())) {
                    field.addClass("validation-error");
                    isValid = false;
                }

                if (!value.prop("disabled") && value.val() === "") {
                    value.addClass("validation-error");
                    isValid = false;
                }

                if (["5", "6", "7", "8"].includes(comp) && !value.val().match(/^-?\d*[\.,]?\d+$/)) {
                    value.addClass("validation-error");
                    isValid = false;
                }

                if (comp === "9" && value.val() === "") {
                    value.addClass("validation-error");
                    isValid = false;
                }

            });
            return isValid;
        };

        // --- Events ---

        $CV.window.on("click", ".cv-action-and", function (e) {
            e.preventDefault();
            $CV.add("and", $(this).prev(), null);
        });

        $CV.window.on("click", ".cv-action-or", function (e) {
            e.preventDefault();
            $CV.add("or", $(this).prev(), null);
        });

        $CV.window.on("click", ".cv-remove", function (e) {
            e.preventDefault();
            var count = $(this).closest(".and-group").children().length;
            if (count === 1) {
                $(this).closest(".or-condition").remove();
            } else {
                $(this).parent().remove();
            }
        });

        $CV.window.on("change", "select.p-comparison", function (e) {
            var disabled = ["1", "2"].includes($(this).val());
            var pVal = $(this).next(".p-value");
            pVal.prop("disabled", disabled);
            if (disabled) {
                pVal.val("");
            }
        });

        $CV.window.on("click", ".cv-close", function (e) {
            e.preventDefault();
            $CV.close();
        });

        $CV.window.on("click", ".cv-save", function (e) {
            e.preventDefault();
            if (!$CV.validate()) {
                alert("Please correct the form!");
                return false;
            }
            var or_conditions = [];
            var or_readable = [];
            $.each($CV.builder.find(".or-condition"), function () {
                var and_conditions = [];
                var and_readable = [];
                $.each($(this).find(".and-condition"), function () {
                    var item = {
                        field: $(this).find(".p-field").val(),
                        comp: $(this).find(".p-comparison").val(),
                        comp_text: $(this).find(".p-comparison option:selected").text(),
                        value: $(this).find(".p-value").val()
                    };
                    and_conditions.push(item);
                    and_readable.push("{" + item.field + "} " + item.comp_text + " " + item.value);
                });
                or_conditions.push(and_conditions);
                or_readable.push(" ( " + and_readable.join(" and ") + " ) ");
            });
            var readable = or_readable.join(" or ").replaceAll("  ", " ");

            $CV.context.find(".f-visibility").val(or_conditions.length === 0 ? "" : JSON.stringify(or_conditions));
            $CV.context.find(".f-visibility-readable").val(readable);
            if (readable !== "") {
                $CV.context.find(".visibility-readable").val("Rule preview: " + readable);
            } else {
                $CV.context.find(".visibility-readable").val("");
            }
            $CV.close();
        });

        $("body").on("click", ".action-field-visibility", function () {
            var context = $(this).closest(".field");
            $CV.open(context);
        });

        // ------------------------------------------------------------

        $WK.init();
        $WK.loadExpression();
        $WK.autocomplete();

    });
})(jQuery);