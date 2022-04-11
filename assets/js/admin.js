(function ($) {
    "use strict";
    var $WK = {};
    $(document).ready(function ($) {
        var suggest = [];
        var input_key_pressed = {};

        $("#f-field-list").sortable({
            "handle": ".action-drag"
        });
        $("#extra-inputs").sortable({
            "handle": ".action-drag"
        });

        $("a.savefields").on("click", function (e) {
            e.preventDefault();
            $WK.saveFields();
        });

        $WK.expressionLastFocusedInput = null;

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
        };

        $WK.buildTooltips = function (selector) {
            $(selector + "span.wck-field-tip").tipTip({
                attribute: 'title',
                defaultPosition: 'left'
            });
        };

        $WK.changeAssignType = function () {
            var disabled = $("#assign_type").val() === "1";
            $("#assign_products, #assign_categories").prop("disabled", disabled);
        };
        $("#assign_type").on("change", $WK.changeAssignType);


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
                    "css_class": input_css_class.val()
                };

                var input_fprice = $row.find('input.f-price');
                if (input_fprice.length > 0) {
                    field.price = input_fprice.val();
                    if (field.price !== "") {
                        field.use_expression = true;
                    }
                }

                var input_frequired = false;
                var $fs_options;

                switch (field.type) {
                    case 'select':
                        field.options_name = [];
                        field.options_title = [];
                        $fs_options = $row.find(".fs-option");
                        $fs_options.each(function () {
                            var fs_name = $(this).find("input.fs-name");
                            /*if (fs_name.val() === "" || typeof fs_name == "undefined") {
                                fs_name.addClass("form-required");
                                error = true;
                            }*/
                            var fs_title = $(this).find("input.fs-title");
                            /*if (fs_title.val() === "" || typeof fs_title === "undefined") {
                                fs_title.addClass("form-required");
                                error = true;
                            }*/
                            var f_default_value = $(this).find('input.f-default-value').is(":checked");
                            //alert("State: " + f_default_value);
                            if (typeof f_default_value !== "undefined" && f_default_value === true) {
                                field.default_value = fs_name.val();
                            }
                            field.options_name.push(fs_name.val());
                            field.options_title.push(fs_title.val());
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

                        input_frequired = $row.find('input.f-required');
                        field.required = input_frequired.is(':checked');

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
                        input_frequired = $row.find('input.f-required');
                        var input_ftminlen = $row.find('input.ft-min-length');
                        var input_ftmaxlen = $row.find('input.ft-max-length');
                        //var input_ftpattern = $row.find('input.ft-pattern');
                        field.min = input_ftminlen.val();
                        field.max = input_ftmaxlen.val();
                        //field.pattern = input_ftpattern.val();
                        field.required = input_frequired.is(':checked');
                        break;

                    case 'colorpicker':
                    case 'datepicker':
                    case 'rangedatepicker':
                        input_frequired = $row.find('input.f-required');
                        field.required = input_frequired.is(':checked');
                        var input_fdpdisallow_past_date = $row.find('input.fdp-disallow-past-date');
                        field.disallow_past_date = input_fdpdisallow_past_date.is(':checked');
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

        $WK.appendFormulaVars = function (field) {
            $WK.fields[field.name] = field;
            if (field.use_expression) {
                suggest.push(field.name);
                $("#formula_fields").append('<span class="formula-field">{' + field.name + '}</span> ');
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

                    var fprice = $("#" + field_id + " .f-price");
                    if (fprice.length > 0) {
                        fprice.val(this.price);
                    }
                    var options_title, options_name, default_value, first, $checked;

                    if (this.type === "dropdown") {
                        $("#" + field_id + " .f-required").prop("checked", this.required);
                        options_title = this.options_title;
                        default_value = this.default_value;
                        first = true;
                        $checked = null;
                        $.each(options_title, function (i, option_title) {
                            if (first) {
                                var $first = $field.find(".fs-option");
                                $first.children("input.fs-title").val(option_title);
                                if (default_value === option_title) {
                                    $checked = $first.children("input.f-default-value");//.prop("checked", true);
                                }
                                first = false;
                            } else {
                                var $clone = $field.find(".fs-option").last().clone().insertBefore($("#" + field_id + " .fs-options .action-add"));
                                $clone.children("input.fs-title").val(option_title);
                                if (default_value === option_title) {
                                    $checked = $clone.children("input.f-default-value");//.prop("checked", true);
                                }
                            }
                            if ($checked) {
                                $checked.prop("checked", true);
                            }
                        });

                    } else if (this.type === "select") {
                        options_name = this.options_name;
                        options_title = this.options_title;
                        default_value = this.default_value;
                        console.log("Dropdown: ", default_value);
                        first = true;
                        $checked = null;
                        $.each(options_name, function (i, option_name) {
                            console.log(option_name);
                            if (first) {
                                var $first = $field.find(".fs-option");
                                $first.children("input.fs-name").val(option_name);
                                $first.children("input.fs-title").val(options_title[i]);
                                if (default_value === option_name) {
                                    $checked = $first.children("input.f-default-value");//.prop("checked", true);
                                }
                                first = false;
                            } else {
                                var $clone = $field.find(".fs-option").last().clone().insertBefore($("#" + field_id + " .fs-options .action-add"));
                                $clone.children("input.fs-name").val(option_name);
                                $clone.children("input.fs-title").val(options_title[i]);
                                if (default_value === option_name) {
                                    $checked = $clone.children("input.f-default-value");//.prop("checked", true);
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
                    } else if (this.type === "text" || this.type === "textarea") {
                        $("#" + field_id + " .ft-min-length").val(this.min);
                        $("#" + field_id + " .ft-max-length").val(this.max);
                        $("#" + field_id + " .f-default-value").val(this.default_value);
                        //$("#" + field_id + " .ft-pattern").val(this.pattern);
                        $("#" + field_id + " .f-required").prop("checked", this.required);
                    } else if (this.type === "colorpicker" || this.type === "datepicker" || this.type === "rangedatepicker") {
                        $("#" + field_id + " .f-required").prop("checked", this.required);
                        $("#" + field_id + " .fdp-disallow-past-date").prop("checked", this.disallow_past_date);
                    }
                });
                $WK.saveFields();
            }
        };


        // -------- EXPRESSION EDITOR -----------

        $WK.showExpressionEditor = function () {
            $("div.expression_oneline, div.expression_conditional, div.expression_off").hide();
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

        $WK.addCondition = function (if_value, then_value) {
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
            $("div#extra-inputs").append($html);
            $WK.autocomplete();
        };

        $("button.add-condition").on("click", function () {
            $WK.addCondition();
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