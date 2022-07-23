<?php
if (!defined('ABSPATH')) {
    exit;
}

?>
<div id="wck-cv-builder">
    <div class="template">
        <div class="or-group">
            <div class="or-condition">
                <div class="and-group">
                    <div class="and-condition">
                        <a href="#" class="cv-remove"><span class="dashicons dashicons-trash"></span></a>
                        <select class="p-field">
                            <option value="" selected disabled><?php _e('Choose field...', 'wc-kalkulator'); ?></option>
                        </select>
                        <select class="p-comparison">
                            <option value="1"><?php _e('is empty', 'wc-kalkulator'); ?></option>
                            <option value="2"><?php _e('has any value', 'wc-kalkulator'); ?></option>
                            <option value="3">==</option>
                            <option value="4">!=</option>
                            <option value="5">&lt;</option>
                            <option value="6">&lt;=</option>
                            <option value="7">&gt;</option>
                            <option value="8">&gt;=</option>
                            <option value="9"><?php _e('contains', 'wc-kalkulator'); ?></option>
                        </select>
                        <input type="text" class="p-value" disabled placeholder="Value..." autocomplete="off">
                    </div>
                </div>
                <button type="button" class="button cv-action-and"><?php _e('AND', 'wc-kalkulator'); ?></button>
            </div>
        </div>
        <button type="button" class="button cv-action-or"><?php _e('OR', 'wc-kalkulator'); ?></button>
    </div>
    <h1>{<span class="self-name"></span>} <?php _e(' is visible if', 'wc-kalkulator'); ?> </h1>
    <div class="builder" data-field=""></div>
    <p class="save">
        <a href="#" class="button cv-close">Close without saving</a> <a href="#" class="button button-primary cv-save">Save & Close</a>
    </p>
</div>


<ul id="f-field-list">
    <li class="welcome">
        <?php _e('Add your first field. Select the field type from the dropdown list on the toolbar and click "Add field".', 'wc-kalkulator'); ?>
    </li>
</ul>
<div class="clearfix"></div>
<input type="hidden" name="_wck_fieldset" value="">

