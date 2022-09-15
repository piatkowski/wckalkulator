(function ($) {
    "use strict";
    $(document).ready(function ($) {
        var userTimeout, updateTimeout;
        var _form = wck_ajax_object.form;

        var shouldCalculatePrice = wck_ajax_object._wck_has_expression === "1";
        var CV = {};

        if (wck_ajax_object.hasOwnProperty("_wck_visibility_rules") && wck_ajax_object._wck_visibility_rules !== null) {
            $.each(wck_ajax_object._wck_visibility_rules, function (fieldName, options) {
                findFieldAndToggle(fieldName, false);
                CV[fieldName] = options;
            });
        }

        function findFieldAndToggle(fieldName, show) {
            var inputField = $("[name*='wck[" + fieldName + "]']");
            if(inputField.length > 0) {
                if(show) {
                    inputField.prop("disabled", false).show().closest('tr').show();
                } else {
                    inputField.prop("disabled", true).hide().closest('tr').hide();
                }
            } else {
                var staticField = $("[data-wck-static-name='" + fieldName + "']");
                if(staticField.length > 0) {
                    staticField.toggle(show);
                }
            }
        }

        function updateUI() {
            //wck-dynamic support, conditional visibility support
            var formFields = {};
            jQuery(_form + " [data-wck-static-name] , " + _form + " [name^=wck").each(function () {
                var fieldName = false;
                if($(this)[0].hasAttribute("name")) {
                    var fieldName = $(this).attr("name").replace("wck[", "").replace("]", "").replace("[]", "");
                    formFields["{" + fieldName + "}"] = $(this).val();
                    var type = $(this).prop("type");
                    switch(type) {
                        case 'file':
                            formFields["{" + fieldName + ":size}"] = (($(this)[0].files.length === 1) ? Math.round(($(this)[0].files[0].size / 1000000 + Number.EPSILON) * 100) / 100 : 0 );
                            break;
                    }
                } else {
                    fieldName = $(this).data("wckStaticName");
                }

                if (fieldName && CV.hasOwnProperty(fieldName)) {
                    toggleField(fieldName, CV[fieldName]);
                }
            });

            $.each(wck_ajax_object._wck_additional_parameters, function (name, value) {
                formFields["{" + name + "}"] = value;
            });

            $("span.wck-dynamic").each(function () {
                var expr = $(this).data('expr');
                var vars = expr.match(/{[^}]+}/gm);
                vars.forEach(function (v, i) {
                    expr = expr.replaceAll(v, formFields[v]);
                });
                try {
                    $(this).text(Math.round((Mexp.eval(expr) + Number.EPSILON) * 100) / 100);
                } catch (error) {
                    console.log("[Mexp]", error);
                }
            });
        }

        function getFieldValue(field) {
            if (field.prop("type") === "checkbox" || field.prop("type") === "radio") {
                var value = field.is(":checked") ? field.val() : "";
                var n = value.indexOf(':');
                if (n !== -1) {
                    value = value.substring(0, n);
                }
                return value;
            } else if(field.prop("type") === "file") {
                return  ((field[0].files.length === 1) ? (Math.round((field[0].files[0].size / 1000000 + Number.EPSILON) * 100) / 100) : 0);
            }
            return field.val();
        }

        function toggleField(fieldName, rules) {
            var state = null;
            $.each(rules, function (i, or_rule) {
                $.each(or_rule, function (j, and_rule) {
                    var fields = $("[name*='wck[" + and_rule.field + "]']");
                    if (fields.length) {
                        fields.each(function () {
                            var s = compare(getFieldValue($(this)), and_rule.comp, and_rule.value);
                            if (s === true) {
                                state = true;
                            }
                            return state !== false;
                        });
                    }
                    return state !== false;
                });
                if (state === true) {
                    findFieldAndToggle(fieldName, true);
                    return false;
                }
            });
            if (state !== true) {
                findFieldAndToggle(fieldName, false);
                return false;
            }
        }

        function compare(valueA, comp, valueB) {
            var numA, numB;
            if(["5", "6", "7", "8"].includes(comp)) {
                numA = Math.round(parseFloat(valueA) * 1000) / 1000;
                numB = Math.round(parseFloat(valueB) * 1000) / 1000;
            }
            try {
                switch (comp) {
                    case "1":
                        return valueA === "";
                    case "2":
                        return valueA !== "";
                    case "3":
                        return valueA === valueB;
                    case "4":
                        return valueA !== valueB;
                    case "5":
                        return numA < numB;
                    case "6":
                        return numA <= numB;
                    case "7":
                        return numA > numB;
                    case "8":
                        return numA >= numB;
                    case "9":
                        return valueA.includes(valueB);
                }
            } catch (e) {
                return false;
            }
            return false;
        }

        function calculatePrice() {
            if (shouldCalculatePrice) {
                var data = $(_form).serialize().replace('add-to-cart', 'atc') + '&action=wckalkulator_calculate_price' + '&_wck_ajax_nonce=' + wck_ajax_object._wck_ajax_nonce;
                $.each($(_form + " input[type=file].wck_imageupload:enabled"), function () {
                    data += "&" + $(this).attr("name") + "=" + (($(this)[0].files.length === 1) ? $(this)[0].files[0].size : 0);
                });
                $("form.cart [name^=wck]:disabled").each(function(){
                    data += "&" + $(this).attr("name") + "=0";
                });
                $.post(wck_ajax_object.ajax_url, data, function (response) {
                    if (response) {
                        $("#wckalkulator-price").html(response);
                    }
                });
            }
        }

        $('input.wck_imageupload').on('change', function (e) {
            var size = $(this)[0].files[0].size;
            if (size > $(this).data("maxfilesize") * 1000000) {
                alert(wck_ajax_object._wck_i18n_maxfilesize + " Max. " + $(this).data("maxfilesize") + "MB");
                $(this).val('');
            }
        });

        $(document).on('change keyup', _form + ' input, ' + _form + ' select, ' + _form + ' textarea', function () {
            clearTimeout(userTimeout);
            clearTimeout(updateTimeout);
            userTimeout = setTimeout(function () {
                $("#wckalkulator-price").html('<img style="display:inline" src="data:image/gif;base64,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" alt="...">');
                calculatePrice();
            }, 500);
            updateTimeout = setTimeout(function(){
                updateUI();
            }, 300);
        });

        updateUI();
        calculatePrice();

        $("span.wck-field-tip").tipTip({
            attribute: 'title',
            defaultPosition: 'left'
        });

        $("input[type=checkbox][data-type='checkboxgroup']").change(function (e) {
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
                    if ($(this).data("required") === 1 && $("input[type=checkbox][data-group=" + group + "]:checked").length === 0) {
                        $("input[type=checkbox][data-group=" + group + "]").get(0).setCustomValidity(wck_ajax_object._wck_i18n_required);
                        _form.reportValidity();
                        e.preventDefault();
                        return false;
                    }
                }
                lastGroup = group;
            });
            $("form.cart [name^=wck]:disabled").each(function(){
                var input = $("<input>").attr("type", "hidden").attr("name", $(this).attr("name")).val("");
                $(_form).append($(input));
            });
        });

    });

})(jQuery);

var wck = function(fieldName) {
    return jQuery("[name*='wck[" + fieldName + "]']");
}

/*
https://github.com/bugwheels94/math-expression-evaluator/
 */
!function (t, e) {
    "object" == typeof exports && "object" == typeof module ? module.exports = e() : "function" == typeof define && define.amd ? define([], e) : "object" == typeof exports ? exports.Mexp = e() : t.Mexp = e()
}(self, (function () {
    return t = {
        28: (t, e, a) => {
            var n = a(733);
            n.prototype.formulaEval = function () {
                "use strict";
                for (var t, e, a, n = [], o = this.value, p = 0; p < o.length; p++) 1 === o[p].type || 3 === o[p].type ? n.push({
                    value: 3 === o[p].type ? o[p].show : o[p].value,
                    type: 1
                }) : 13 === o[p].type ? n.push({
                    value: o[p].show,
                    type: 1
                }) : 0 === o[p].type ? n[n.length - 1] = {
                    value: o[p].show + ("-" != o[p].show ? "(" : "") + n[n.length - 1].value + ("-" != o[p].show ? ")" : ""),
                    type: 0
                } : 7 === o[p].type ? n[n.length - 1] = {
                    value: (1 != n[n.length - 1].type ? "(" : "") + n[n.length - 1].value + (1 != n[n.length - 1].type ? ")" : "") + o[p].show,
                    type: 7
                } : 10 === o[p].type ? (t = n.pop(), e = n.pop(), "P" === o[p].show || "C" === o[p].show ? n.push({
                    value: "<sup>" + e.value + "</sup>" + o[p].show + "<sub>" + t.value + "</sub>",
                    type: 10
                }) : n.push({
                    value: (1 != e.type ? "(" : "") + e.value + (1 != e.type ? ")" : "") + "<sup>" + t.value + "</sup>",
                    type: 1
                })) : 2 === o[p].type || 9 === o[p].type ? (t = n.pop(), e = n.pop(), n.push({
                    value: (1 != e.type ? "(" : "") + e.value + (1 != e.type ? ")" : "") + o[p].show + (1 != t.type ? "(" : "") + t.value + (1 != t.type ? ")" : ""),
                    type: o[p].type
                })) : 12 === o[p].type && (t = n.pop(), e = n.pop(), a = n.pop(), n.push({
                    value: o[p].show + "(" + a.value + "," + e.value + "," + t.value + ")",
                    type: 12
                }));
                return n[0].value
            }, t.exports = n
        }, 618: (t, e, a) => {
            "use strict";
            var n = a(178);

            function o(t, e) {
                for (var a = 0; a < t.length; a++) t[a] += e;
                return t
            }

            var p = ["sin", "cos", "tan", "pi", "(", ")", "P", "C", " ", "asin", "acos", "atan", "7", "8", "9", "int", "cosh", "acosh", "ln", "^", "root", "4", "5", "6", "/", "!", "tanh", "atanh", "Mod", "1", "2", "3", "*", "sinh", "asinh", "e", "log", "0", ".", "+", "-", ",", "Sigma", "n", "Pi", "pow", "&"],
                h = ["sin", "cos", "tan", "&pi;", "(", ")", "P", "C", " ", "asin", "acos", "atan", "7", "8", "9", "Int", "cosh", "acosh", " ln", "^", "root", "4", "5", "6", "&divide;", "!", "tanh", "atanh", " Mod ", "1", "2", "3", "&times;", "sinh", "asinh", "e", " log", "0", ".", "+", "-", ",", "&Sigma;", "n", "&Pi;", "pow", "&"],
                u = [n.math.sin, n.math.cos, n.math.tan, "PI", "(", ")", n.math.P, n.math.C, " ".anchor, n.math.asin, n.math.acos, n.math.atan, "7", "8", "9", Math.floor, n.math.cosh, n.math.acosh, Math.log, Math.pow, Math.sqrt, "4", "5", "6", n.math.div, n.math.fact, n.math.tanh, n.math.atanh, n.math.mod, "1", "2", "3", n.math.mul, n.math.sinh, n.math.asinh, "E", n.math.log, "0", ".", n.math.add, n.math.sub, ",", n.math.sigma, "n", n.math.Pi, Math.pow, n.math.and],
                s = {
                    0: 11,
                    1: 0,
                    2: 3,
                    3: 0,
                    4: 0,
                    5: 0,
                    6: 0,
                    7: 11,
                    8: 11,
                    9: 1,
                    10: 10,
                    11: 0,
                    12: 11,
                    13: 0,
                    14: -1
                },
                r = [0, 0, 0, 3, 4, 5, 10, 10, 14, 0, 0, 0, 1, 1, 1, 0, 0, 0, 0, 10, 0, 1, 1, 1, 2, 7, 0, 0, 2, 1, 1, 1, 2, 0, 0, 3, 0, 1, 6, 9, 9, 11, 12, 13, 12, 8, 9],
                i = {0: !0, 1: !0, 3: !0, 4: !0, 6: !0, 8: !0, 9: !0, 12: !0, 13: !0, 14: !0}, l = {
                    0: !0,
                    1: !0,
                    2: !0,
                    3: !0,
                    4: !0,
                    5: !0,
                    6: !0,
                    7: !0,
                    8: !0,
                    9: !0,
                    10: !0,
                    11: !0,
                    12: !0,
                    13: !0
                }, v = {0: !0, 3: !0, 4: !0, 8: !0, 12: !0, 13: !0}, f = {},
                c = {0: !0, 1: !0, 3: !0, 4: !0, 6: !0, 8: !0, 12: !0, 13: !0}, y = {1: !0},
                g = [[], ["1", "2", "3", "7", "8", "9", "4", "5", "6", "+", "-", "*", "/", "(", ")", "^", "!", "P", "C", "e", "0", ".", ",", "n", " ", "&"], ["pi", "ln", "Pi"], ["sin", "cos", "tan", "Del", "int", "Mod", "log", "pow"], ["asin", "acos", "atan", "cosh", "root", "tanh", "sinh"], ["acosh", "atanh", "asinh", "Sigma"]];

            function m(t, e, a, n) {
                for (var o = 0; o < n; o++) if (t[a + o] !== e[o]) return !1;
                return !0
            }

            n.addToken = function (t) {
                for (var e = 0; e < t.length; e++) {
                    var a = t[e].token.length, n = -1;
                    g[a] = g[a] || [];
                    for (var o = 0; o < g[a].length; o++) if (t[e].token === g[a][o]) {
                        n = p.indexOf(g[a][o]);
                        break
                    }
                    -1 === n ? (p.push(t[e].token), r.push(t[e].type), g.length <= t[e].token.length && (g[t[e].token.length] = []), g[t[e].token.length].push(t[e].token), u.push(t[e].value), h.push(t[e].show)) : (p[n] = t[e].token, r[n] = t[e].type, u[n] = t[e].value, h[n] = t[e].show)
                }
            }, n.lex = function (t, e) {
                var a, d = {value: n.math.changeSign, type: 0, pre: 21, show: "-"},
                    w = {value: ")", show: ")", type: 5, pre: 0}, x = {value: "(", type: 4, pre: 0, show: "("}, M = [x],
                    E = [], P = t, b = i, k = 0, D = f, I = "";
                void 0 !== e && n.addToken(e);
                var C = {}, S = function (t) {
                    for (var e, a, o, i = [], l = t.length, v = 0; v < l; v++) if (!(v < l - 1 && " " === t[v] && " " === t[v + 1])) {
                        for (e = "", a = t.length - v > g.length - 2 ? g.length - 1 : t.length - v; a > 0; a--) if (void 0 !== g[a]) for (o = 0; o < g[a].length; o++) m(t, g[a][o], v, a) && (e = g[a][o], o = g[a].length, a = 0);
                        if (v += e.length - 1, "" === e) throw new n.Exception("Can't understand after " + t.slice(v));
                        var f = p.indexOf(e);
                        i.push({index: f, token: e, type: r[f], eval: u[f], precedence: s[r[f]], show: h[f]})
                    }
                    return i
                }(P);
                for (a = 0; a < S.length; a++) {
                    var N = S[a];
                    if (14 !== N.type) {
                        var R, j = N.token, q = N.type, O = N.eval, T = N.precedence, F = N.show, U = M[M.length - 1];
                        for (R = E.length; R-- && 0 === E[R];) if (-1 !== [0, 2, 3, 4, 5, 9, 11, 12, 13].indexOf(q)) {
                            if (!0 !== b[q]) throw new n.Exception(j + " is not allowed after " + I);
                            M.push(w), b = l, D = c, E.pop()
                        }
                        if (!0 !== b[q]) throw new n.Exception(j + " is not allowed after " + I);
                        if (!0 === D[q] && (q = 2, O = n.math.mul, F = "&times;", T = 3, a -= 1), C = {
                            value: O,
                            type: q,
                            pre: T,
                            show: F
                        }, 0 === q) b = i, D = f, o(E, 2), M.push(C), 4 !== S[a + 1].type && (M.push(x), E.push(2)); else if (1 === q) 1 === U.type ? (U.value += O, o(E, 1)) : M.push(C), b = l, D = v; else if (2 === q) b = i, D = f, o(E, 2), M.push(C); else if (3 === q) M.push(C), b = l, D = c; else if (4 === q) o(E, 1), k++, b = i, D = f, M.push(C); else if (5 === q) {
                            if (!k) throw new n.Exception("Closing parenthesis are more than opening one, wait What!!!");
                            k--, b = l, D = c, M.push(C), o(E, 1)
                        } else if (6 === q) {
                            if (U.hasDec) throw new n.Exception("Two decimals are not allowed in one number");
                            1 !== U.type && (U = {
                                value: 0,
                                type: 1,
                                pre: 0
                            }, M.push(U)), b = y, o(E, 1), D = f, U.value += O, U.hasDec = !0
                        } else 7 === q && (b = l, D = c, o(E, 1), M.push(C));
                        8 === q ? (b = i, D = f, o(E, 4), M.push(C), 4 !== S[a + 1].type && (M.push(x), E.push(4))) : 9 === q ? (9 === U.type ? U.value === n.math.add ? (U.value = O, U.show = F, o(E, 1)) : U.value === n.math.sub && "-" === F && (U.value = n.math.add, U.show = "+", o(E, 1)) : 5 !== U.type && 7 !== U.type && 1 !== U.type && 3 !== U.type && 13 !== U.type ? "-" === j && (b = i, D = f, o(E, 2).push(2), M.push(d), M.push(x)) : (M.push(C), o(E, 2)), b = i, D = f) : 10 === q ? (b = i, D = f, o(E, 2), M.push(C)) : 11 === q ? (b = i, D = f, M.push(C)) : 12 === q ? (b = i, D = f, o(E, 6), M.push(C), 4 !== S[a + 1].type && (M.push(x), E.push(6))) : 13 === q && (b = l, D = c, M.push(C)), o(E, -1), I = j
                    } else if (a > 0 && a < S.length - 1 && 1 === S[a + 1].type && (1 === S[a - 1].type || 6 === S[a - 1].type)) throw new n.Exception("Unexpected Space")
                }
                for (R = E.length; R--;) M.push(w);
                if (!0 !== b[5]) throw new n.Exception("complete the expression");
                for (; k--;) M.push(w);
                return M.push(w), new n(M)
            }, t.exports = n
        }, 178: t => {
            "use strict";
            var e = function (t) {
                this.value = t
            };
            e.math = {
                isDegree: !0, acos: function (t) {
                    return e.math.isDegree ? 180 / Math.PI * Math.acos(t) : Math.acos(t)
                }, add: function (t, e) {
                    return t + e
                }, asin: function (t) {
                    return e.math.isDegree ? 180 / Math.PI * Math.asin(t) : Math.asin(t)
                }, atan: function (t) {
                    return e.math.isDegree ? 180 / Math.PI * Math.atan(t) : Math.atan(t)
                }, acosh: function (t) {
                    return Math.log(t + Math.sqrt(t * t - 1))
                }, asinh: function (t) {
                    return Math.log(t + Math.sqrt(t * t + 1))
                }, atanh: function (t) {
                    return Math.log((1 + t) / (1 - t))
                }, C: function (t, a) {
                    var n = 1, o = t - a, p = a;
                    p < o && (p = o, o = a);
                    for (var h = p + 1; h <= t; h++) n *= h;
                    return n / e.math.fact(o)
                }, changeSign: function (t) {
                    return -t
                }, cos: function (t) {
                    return e.math.isDegree && (t = e.math.toRadian(t)), Math.cos(t)
                }, cosh: function (t) {
                    return (Math.pow(Math.E, t) + Math.pow(Math.E, -1 * t)) / 2
                }, div: function (t, e) {
                    return t / e
                }, fact: function (t) {
                    if (t % 1 != 0) return "NaN";
                    for (var e = 1, a = 2; a <= t; a++) e *= a;
                    return e
                }, inverse: function (t) {
                    return 1 / t
                }, log: function (t) {
                    return Math.log(t) / Math.log(10)
                }, mod: function (t, e) {
                    return t % e
                }, mul: function (t, e) {
                    return t * e
                }, P: function (t, e) {
                    for (var a = 1, n = Math.floor(t) - Math.floor(e) + 1; n <= Math.floor(t); n++) a *= n;
                    return a
                }, Pi: function (t, e, a) {
                    for (var n = 1, o = t; o <= e; o++) n *= Number(a.postfixEval({n: o}));
                    return n
                }, pow10x: function (t) {
                    for (var e = 1; t--;) e *= 10;
                    return e
                }, sigma: function (t, e, a) {
                    for (var n = 0, o = t; o <= e; o++) n += Number(a.postfixEval({n: o}));
                    return n
                }, sin: function (t) {
                    return e.math.isDegree && (t = e.math.toRadian(t)), Math.sin(t)
                }, sinh: function (t) {
                    return (Math.pow(Math.E, t) - Math.pow(Math.E, -1 * t)) / 2
                }, sub: function (t, e) {
                    return t - e
                }, tan: function (t) {
                    return e.math.isDegree && (t = e.math.toRadian(t)), Math.tan(t)
                }, tanh: function (t) {
                    return e.sinha(t) / e.cosha(t)
                }, toRadian: function (t) {
                    return t * Math.PI / 180
                }, and: function (t, e) {
                    return t & e
                }
            }, e.Exception = function (t) {
                this.message = t
            }, t.exports = e
        }, 477: (t, e, a) => {
            var n = a(618);
            n.prototype.toPostfix = function () {
                "use strict";
                for (var t, e, a, o, p, h = [], u = [{
                    value: "(",
                    type: 4,
                    pre: 0
                }], s = this.value, r = 1; r < s.length; r++) if (1 === s[r].type || 3 === s[r].type || 13 === s[r].type) 1 === s[r].type && (s[r].value = Number(s[r].value)), h.push(s[r]); else if (4 === s[r].type) u.push(s[r]); else if (5 === s[r].type) for (; 4 !== (e = u.pop()).type;) h.push(e); else if (11 === s[r].type) {
                    for (; 4 !== (e = u.pop()).type;) h.push(e);
                    u.push(e)
                } else {
                    o = (t = s[r]).pre, a = (p = u[u.length - 1]).pre;
                    var i = "Math.pow" == p.value && "Math.pow" == t.value;
                    if (o > a) u.push(t); else {
                        for (; a >= o && !i || i && o < a;) e = u.pop(), p = u[u.length - 1], h.push(e), a = p.pre, i = "Math.pow" == t.value && "Math.pow" == p.value;
                        u.push(t)
                    }
                }
                return new n(h)
            }, t.exports = n
        }, 733: (t, e, a) => {
            var n = a(477);
            n.prototype.postfixEval = function (t) {
                "use strict";
                (t = t || {}).PI = Math.PI, t.E = Math.E;
                for (var e, a, o, p = [], h = this.value, u = void 0 !== t.n, s = 0; s < h.length; s++) 1 === h[s].type ? p.push({
                    value: h[s].value,
                    type: 1
                }) : 3 === h[s].type ? p.push({
                    value: t[h[s].value],
                    type: 1
                }) : 0 === h[s].type || 7 === h[s].type ? void 0 === p[p.length - 1].type ? p[p.length - 1].value.push(h[s]) : p[p.length - 1].value = h[s].value(p[p.length - 1].value) : 8 === h[s].type ? (e = p.pop(), a = p.pop(), p.push({
                    type: 1,
                    value: h[s].value(a.value, e.value)
                })) : 10 === h[s].type ? (e = p.pop(), void 0 === (a = p.pop()).type ? (a.value = a.concat(e), a.value.push(h[s]), p.push(a)) : void 0 === e.type ? (e.unshift(a), e.push(h[s]), p.push(e)) : p.push({
                    type: 1,
                    value: h[s].value(a.value, e.value)
                })) : 2 === h[s].type || 9 === h[s].type ? (e = p.pop(), void 0 === (a = p.pop()).type ? ((a = a.concat(e)).push(h[s]), p.push(a)) : void 0 === e.type ? (e.unshift(a), e.push(h[s]), p.push(e)) : p.push({
                    type: 1,
                    value: h[s].value(a.value, e.value)
                })) : 12 === h[s].type ? (void 0 !== (e = p.pop()).type && (e = [e]), a = p.pop(), o = p.pop(), p.push({
                    type: 1,
                    value: h[s].value(o.value, a.value, new n(e))
                })) : 13 === h[s].type && (u ? p.push({value: t[h[s].value], type: 3}) : p.push([h[s]]));
                if (p.length > 1) throw new n.Exception("Uncaught Syntax error");
                return p[0].value > 1e15 ? "Infinity" : parseFloat(p[0].value.toFixed(15))
            }, n.eval = function (t, e, a) {
                return void 0 === e ? this.lex(t).toPostfix().postfixEval() : void 0 === a ? void 0 !== e.length ? this.lex(t, e).toPostfix().postfixEval() : this.lex(t).toPostfix().postfixEval(e) : this.lex(t, e).toPostfix().postfixEval(a)
            }, t.exports = n
        }
    }, e = {}, a = function a(n) {
        var o = e[n];
        if (void 0 !== o) return o.exports;
        var p = e[n] = {exports: {}};
        return t[n](p, p.exports, a), p.exports
    }(28), a;
    var t, e, a
}));