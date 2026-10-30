(function (root, $) {
    'use strict';
    function measurementValue(value, factor, config) {
        if (value === '') { return null; }
        var raw = Number(value), number = raw * Number(factor), precision = Number(config.precision);
        if (!isFinite(raw) || raw < 0 || !isFinite(number)) { return NaN; }
        // Decimal shifting avoids toFixed's 1.005 -> 1.00 tie, matching PHP round.
        var parts = number.toString().split('e');
        var shifted = Number(parts[0] + 'e' + (Number(parts[1] || 0) + precision));
        if (isFinite(shifted)) {
            parts = Math.round(shifted).toString().split('e');
            number = Number(parts[0] + 'e' + (Number(parts[1] || 0) - precision));
        }
        var steps = (number - Number(config.min)) / Number(config.step);
        return isFinite(number) && number >= Number(config.min) && number <= Number(config.max) &&
            Math.abs(steps - Math.round(steps)) <= 0.000001 ? number : NaN;
    }
    function compare(values, comp, target) {
        var present = values.filter(function (value) { return value !== '' && value !== null && value !== undefined; });
        if (comp === '1') { return !present.length; }
        if (comp === '2') { return !!present.length; }
        if (comp === '3') { return present.some(function (v) { return String(v) === String(target); }); }
        if (comp === '4') { return !!present.length && present.every(function (v) { return String(v) !== String(target); }); }
        if (comp === '9') { return present.some(function (v) { return String(v).indexOf(String(target)) !== -1; }); }
        return present.some(function (v) {
            var a = Math.floor(Number(String(v).replace(',', '.')) * 1000 + 0.5) / 1000;
            var b = Math.floor(Number(String(target).replace(',', '.')) * 1000 + 0.5) / 1000;
            if (!isFinite(a) || !isFinite(b)) { return false; }
            return comp === '5' ? a < b : comp === '6' ? a <= b : comp === '7' ? a > b : comp === '8' && a >= b;
        });
    }
    root.WCKConfigurator = {measurementValue: measurementValue, compare: compare};
    $(function () {
        var words = root.wck_configurator_i18n;
        function updateMeasurements(scope) {
            scope.find('.wck-measurement').each(function () {
                var box = $(this), input = box.find('input')[0], config = JSON.parse(box.attr('data-wck-config'));
                var value = measurementValue(input.value, box.find('option:selected').attr('data-wck-factor'), config);
                input.setCustomValidity(Number.isNaN(value) ? words.measurement : '');
            });
        }
        function renumber(group) {
            var name = group.attr('data-wck-name'), prefix = group.attr('data-wck-prefix') || 'wck_' + name;
            group.find('.wck-repeat-row').each(function (index) {
                $(this).find('[name]').each(function () {
                    this.name = this.name.replace(/^(wck\[[^\]]+\])\[[^\]]+\]/, '$1[' + index + ']');
                });
                $(this).find('[id], [for]').each(function () {
                    var element = $(this);
                    ['id', 'for'].forEach(function (attr) {
                        var value = element.attr(attr);
                        if (value) { element.attr(attr, value.replace(new RegExp('^wck_(?:[0-9]+_)?' + name + '_(?:[0-9]+|__row__)_'), prefix + '_' + index + '_')); }
                    });
                });
                $(this).find('[data-group]').each(function () {
                    var input = $(this), child = input.attr('name').match(/^wck\[[^\]]+\]\[[^\]]+\]\[([^\]]+)\]/);
                    if (child) { input.attr('data-group', name + '_' + index + '_' + child[1]).data('group', name + '_' + index + '_' + child[1]); }
                });
            });
            var count = group.find('.wck-repeat-row').length;
            var quantityMode = group.attr('data-wck-mode') === 'quantity';
            group.find('.wck-remove-row').prop('disabled', count <= Number(group.attr('data-wck-min'))).prop('hidden', quantityMode);
            group.find('.wck-add-row').prop('disabled', count >= Number(group.attr('data-wck-max'))).prop('hidden', quantityMode);
            group.find('.wck-repeat-status').text(words.rows.replace('%s', count));
        }
        function addRow(group) {
            var row = $(group.find('.wck-repeat-template').html());
            group.children('.wck-repeat-rows').append(row);
            renumber(group);
            return row;
        }
        function rowVisibility(group) {
            var definitions = JSON.parse(group.attr('data-wck-children'));
            group.find('.wck-repeat-row').each(function () {
                var row = $(this), fields = {}, states = {}, visiting = {};
                Object.keys(definitions).forEach(function (name) {
                    fields[name] = row.find('[name]').filter(function () {
                        var match = this.name.match(/^wck\[[^\]]+\]\[[^\]]+\]\[([^\]]+)\]/);
                        return match && match[1] === name;
                    });
                });
                function values(name) {
                    var inputs = fields[name], box = inputs.closest('.wck-measurement');
                    if (box.length) {
                        var config = JSON.parse(box.attr('data-wck-config'));
                        return [measurementValue(box.find('input').val(), box.find('option:selected').attr('data-wck-factor'), config)];
                    }
                    var type = inputs.first().prop('type');
                    if (type === 'checkbox' || type === 'radio' || inputs.filter(':checkbox, :radio').length) { inputs = inputs.filter(':checked'); }
                    return inputs.map(function () { return $(this).val(); }).get();
                }
                function visible(name) {
                    if (states[name] !== undefined) { return states[name]; }
                    if (!definitions[name] || visiting[name]) { throw new Error('visibility'); }
                    visiting[name] = true;
                    var rules = definitions[name].visibility || '';
                    rules = rules ? JSON.parse(rules) : [];
                    var show = !rules.length || rules.some(function (conditions) {
                        return conditions.every(function (condition) {
                            return visible(condition.field) && compare(values(condition.field), String(condition.comp), condition.value || '');
                        });
                    });
                    delete visiting[name];
                    states[name] = show;
                    return show;
                }
                try { Object.keys(definitions).forEach(visible); }
                catch (error) { Object.keys(definitions).forEach(function (name) { states[name] = false; }); }
                Object.keys(definitions).forEach(function (name) {
                    var inputs = fields[name], enabled = states[name];
                    inputs.prop('disabled', !enabled).closest('tr').toggle(enabled);
                    if (definitions[name].type === 'checkboxgroup') {
                        var checks = inputs.filter(':checkbox');
                        if (checks.length) {
                            checks[0].setCustomValidity(enabled && definitions[name].required && !checks.filter(':checked').length ? words.required : '');
                        }
                    }
                });
            });
        }
        function sync(form) {
            var quantityInput = form.find('[name="quantity"]')[0], quantityValid = true;
            form.find('.wck-repeater').each(function () {
                var group = $(this);
                if (group.attr('data-wck-mode') === 'quantity') {
                    var quantity = quantityInput ? Number(quantityInput.value) : 1;
                    var min = Number(group.attr('data-wck-min')), max = Number(group.attr('data-wck-max'));
                    var valid = Number.isInteger(quantity) && quantity >= min && quantity <= max;
                    quantityValid = quantityValid && valid;
                    if (valid) {
                        while (group.find('.wck-repeat-row').length < quantity) { addRow(group); }
                        group.find('.wck-repeat-row').slice(quantity).remove();
                    }
                }
                renumber(group);
                rowVisibility(group);
            });
            if (quantityInput) { quantityInput.setCustomValidity(quantityValid ? '' : words.limit); }
            updateMeasurements(form);
        }
        $('form').has('.wck-measurement, .wck-repeater, .wck-configurator').each(function (formIndex) {
            var form = $(this);
            if (form.data('wck-configured')) { return; }
            form.data('wck-configured', true);
            form.find('[id^="wck_"], [for^="wck_"]').each(function () {
                var element = $(this);
                ['id', 'for'].forEach(function (attr) {
                    var value = element.attr(attr);
                    if (value) { element.attr(attr, value.replace(/^wck_/, 'wck_' + formIndex + '_')); }
                });
            });
            form.find('.wck-repeater').each(function () { $(this).attr('data-wck-prefix', 'wck_' + formIndex + '_' + $(this).attr('data-wck-name')); });
            form.on('click', '.wck-add-row', function () {
                var group = $(this).closest('.wck-repeater');
                if (group.find('.wck-repeat-row').length >= Number(group.attr('data-wck-max'))) { return; }
                var row = addRow(group);
                sync(form);
                row.find('input, select, textarea').first().trigger('focus').trigger('change');
            }).on('click', '.wck-remove-row', function () {
                var group = $(this).closest('.wck-repeater'), index = $(this).closest('.wck-repeat-row').index();
                if (group.find('.wck-repeat-row').length <= Number(group.attr('data-wck-min'))) { return; }
                $(this).closest('.wck-repeat-row').remove();
                sync(form);
                var target = group.find('.wck-repeat-row').eq(Math.max(0, index - 1)).find('input, select, textarea').first();
                (target.length ? target : group.find('.wck-add-row')).trigger('focus');
                form.trigger('wck:configuration-changed');
            }).on('input change', 'input, select, textarea', function () { sync(form); });
            sync(form);
            form.find('.wck-configurator').each(function () {
                var configurator = $(this), steps = configurator.children('.wck-step'), current = 0;
                if (!steps.length) { return; }
                var navigation = configurator.children('.wck-step-navigation');
                var progress = configurator.children('.wck-step-progress');
                function show(index, focus) {
                    current = index;
                    steps.prop('hidden', true).eq(current).prop('hidden', false);
                    navigation.prop('hidden', false);
                    progress.prop('hidden', false).children().removeAttr('aria-current').eq(current).attr('aria-current', 'step');
                    navigation.find('.wck-step-back').prop('disabled', current === 0);
                    navigation.find('.wck-step-next').prop('hidden', current === steps.length - 1);
                    navigation.find('.wck-step-status').text(words.step.replace('%1$s', current + 1).replace('%2$s', steps.length));
                    if (focus) { steps.eq(current).find('h3').first().trigger('focus'); }
                }
                function next() {
                    var invalid = steps.eq(current).find('input, select, textarea').filter(function () { return !this.disabled && !this.checkValidity(); }).first();
                    if (invalid.length) { invalid[0].reportValidity(); return; }
                    show(Math.min(current + 1, steps.length - 1), true);
                }
                navigation.on('click', '.wck-step-next', next).on('click', '.wck-step-back', function () { show(Math.max(0, current - 1), true); });
                var invalidBurst = false;
                form[0].addEventListener('invalid', function (event) {
                    if (invalidBurst) { return; }
                    invalidBurst = true;
                    setTimeout(function () { invalidBurst = false; }, 0);
                    var index = steps.index($(event.target).closest('.wck-step'));
                    if (index >= 0 && steps.eq(index).prop('hidden')) { show(index, false); }
                }, true);
                configurator.on('keydown', 'input, select', function (event) {
                    if (event.key === 'Enter' && current < steps.length - 1) { event.preventDefault(); next(); }
                });
                show(0, false);
            });
        });
    });
})(window, jQuery);
