(function ($) {
    "use strict";
    var $WK = {};
    $(document).ready(function ($) {
        var suggest = [];
        var input_key_pressed = {};

        $("#f-field-list").sortable({
            "handle": ".action-drag"
        });
        $("#extra-inputs, #addon-inputs").sortable({
            "handle": ".action-drag"
        });

        $("a.savefields").on("click", function (e) {
            e.preventDefault();
            $WK.saveFields();
        });
        $('<input type="hidden" class="wck-global-color-picker" />').insertBefore("#wpwrap");
        $WK.colorpicker = $("input.wck-global-color-picker");
        $WK.colorpicker.iris();

        $WK.expressionLastFocusedInput = null;
        $WK.wpMediaFrame = null;
        $WK.wpMediaTarget = null;
        $WK.iconPreloader = "data:image/gif;base64,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";

        $("body").on("focus", "#wck_expression input", function () {
            $WK.expressionLastFocusedInput = $(this);
        }).on("click", "span.formula-field", function () {
            var $focused = $WK.expressionLastFocusedInput;
            if ($focused && $focused.length) {
                var cursorPos = $focused[0].selectionStart;
                var x = $focused.val();
                $focused.val(x.slice(0, cursorPos) + $(this).text() + x.slice(cursorPos));
                $focused.focus();
                cursorPos += $(this).text().length;
                $focused[0].setSelectionRange(cursorPos, cursorPos);
            }
        }).on("click", ".field .pairs .action-add", function () {
            var $clone = $(this).prev(".pair").clone().insertBefore($(this));
            $clone.children("input").val("");
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
                $target.hide('slow', function () {
                    $target.remove();
                });
            }
        }).on("click", ".field .header .action-toggle", function () {
            $(this).closest(".field").find(".body").slideToggle(300);
            $(this).toggleClass("dashicons-arrow-up-alt2");
            $(this).toggleClass("dashicons-arrow-down-alt2");

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
                console.log($(this).data('extension'));
                ext.push($(this).data('extension'));
            });
            $(field_id + " input.fu-allowed-extensions").val(ext.join('|'));
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
            //$WK.appendGlobalParameters();
        };

        $WK.buildTooltips = function (selector) {
            $(selector + "span.wck-field-tip").tipTip({
                attribute: 'title',
                defaultPosition: 'left'
            });
        };

        $WK.changeAssignType = function () {
            var disabled = $("#assign_type").val() === "1";
            $("#assign_products, #assign_categories, #assign_tags").prop("disabled", disabled);
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
            $("#formula_fields").html(" &dash; ");
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
                    "hint": input_fhint.val(),
                    "default_value": input_default_value.val(),
                    "css_class": input_css_class.val(),
                    "required": (($row.find('input.f-required').length > 0) ? $row.find('input.f-required').is(':checked') : true)
                };

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
                        var input_fcb_default_state = $row.find('input.fcb-default-state');
                        field.default_state = input_fcb_default_state.is(':checked');
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
                $("input:invalid").parent().show();
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
                $.each(wck_global_parameters, function (name, value) {
                    var name = "global:" + name;
                    suggest.push(name);
                    $("#formula_fields").append('<span class="formula-field">{' + name + '}</span> ');
                });
            }
        }

        $WK.appendFormulaVars = function (field) {
            $WK.fields[field.name] = field;
            if (field.use_expression) {
                if (field.type !== 'checkboxgroup') {
                    suggest.push(field.name);
                    $("#formula_fields").append('<span class="formula-field">{' + field.name + '}</span> ');
                } else {
                    suggest.push(field.name + ":sum");
                    $("#formula_fields").append('<span class="formula-field">{' + field.name + ':sum}</span> ');
                    suggest.push(field.name + ":min");
                    $("#formula_fields").append('<span class="formula-field">{' + field.name + ':min}</span> ');
                    suggest.push(field.name + ":max");
                    $("#formula_fields").append('<span class="formula-field">{' + field.name + ':max}</span> ');
                }
                if (field.type === "text" || field.type === "textarea") {
                    suggest.push(field.name + ":text");
                    $("#formula_fields").append('<span class="formula-field">{' + field.name + ':text}</span> ');
                }
                if (field.type === "rangedatepicker") {
                    suggest.push(field.name + ":date_from");
                    $("#formula_fields").append('<span class="formula-field">{' + field.name + ':date_from}</span> ');
                    suggest.push(field.name + ":date_to");
                    $("#formula_fields").append('<span class="formula-field">{' + field.name + ':date_to}</span> ');
                    suggest.push(field.name + ":days");
                    $("#formula_fields").append('<span class="formula-field">{' + field.name + ':days}</span> ');
                } else if (field.type === "datepicker") {
                    suggest.push(field.name + ":date");
                    $("#formula_fields").append('<span class="formula-field">{' + field.name + ':date}</span> ');
                } else if (field.type === "imageupload") {
                    suggest.push(field.name + ":size");
                    $("#formula_fields").append('<span class="formula-field">{' + field.name + ':size}</span> ');
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
                $.each(wck_load_fieldset, function () {
                    var field_id = $WK.addField(this.type);
                    var $field = $("#" + field_id + " .field");
                    $("#" + field_id + " .f-name").val(this.name);
                    $("#" + field_id + " .f-title").val(this.title);
                    $("#" + field_id + " .f-hint").val(this.hint);
                    $("#" + field_id + " .f-css-class").val(this.css_class);
                    if ($("#" + field_id + " .f-required").length > 0) {
                        $("#" + field_id + " .f-required").prop("checked", this.required);
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
                        $("#" + field_id + " .fcb-default-state").prop("checked", this.default_state);
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
                    } else if (this.type === "imageupload") {
                        $("#" + field_id + " .fu-max-file-size").val(this.max_file_size);
                        $("#" + field_id + " .fu-allowed-extensions").val(this.allowed_extensions);
                        var ext = this.allowed_extensions.split("|");
                        ext.forEach(function (e) {
                            $("#" + field_id + " .allowed-extensions.ext-" + e).prop("checked", true);
                        });
                    } else if (['html', 'paragraph', 'heading', 'hidden', 'link', 'attachment'].indexOf(this.type) >= 0) {
                        $("#" + field_id + " .fst-content").val(this.content);
                        if (this.type === 'heading') {
                            $("#" + field_id + " .fst-level").val(this.level);
                        } else if (this.type === 'link') {
                            $("#" + field_id + " .fst-target").val(this.target);
                        } else if (this.type === 'attachment') {
                            $WK.preloadMedia(this.content, function (attachment) {
                                console.log(field_id, attachment.get('url'));
                                $("#" + field_id + " .wp-media-attachment-preview").attr("href", attachment.get('url')).text(attachment.get('url'));
                            });
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
            $WK.showExpressionEditor();
        });

        $WK.addCondition = function (if_value, then_value, addon = false) {
            var $html = $('<div class="input-group">' +
                '<span class="action-drag dashicons left dashicons-move"></span>' +
                '<span class="action-delete dashicons right dashicons-no-alt"></span>' +
                '<div class="clearfix"></div>' +
                '<div class="input-icon input-if">' +
                '<input type="text" placeholder="" value=""><i></i>' +
                '</div>' +
                '<div class="input-icon input-equation">' +
                '<input type="text" placeholder="" value=""><i></i>' +
                '</div></div>');
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
                $WK.expression = wck_load_expression;
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

        // --------------------------------------

        $WK.init();
        $WK.loadExpression();
        $WK.autocomplete();

    });
})(jQuery);