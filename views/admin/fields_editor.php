<?php
if (!defined('ABSPATH')) {
    exit;
}

?>
<div id="wck-cv-builder" role="dialog" aria-modal="true" aria-labelledby="wck-cv-title" aria-hidden="true" tabindex="-1">
    <div class="template">
        <div class="or-group">
            <div class="or-condition">
                <div class="and-group">
                    <div class="and-condition">
                        <button type="button" class="cv-remove" aria-label="<?php esc_attr_e('Remove condition', 'wc-kalkulator'); ?>"><span class="dashicons dashicons-trash" aria-hidden="true"></span></button>
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
    <h1 id="wck-cv-title">{<span class="self-name"></span>} <?php _e(' is visible if', 'wc-kalkulator'); ?> </h1>
    <div class="builder" data-field=""></div>
    <p class="save">
        <button type="button" class="button cv-close"><?php _e('Close without saving', 'wc-kalkulator'); ?></button> <button type="button" class="button button-primary cv-save"><?php _e('Save & Close', 'wc-kalkulator'); ?></button>
    </p>
</div>


<ul id="f-field-list">
    <li class="welcome">
        <?php _e('Add your first field. Select the field type from the dropdown list on the toolbar and click "Add field".', 'wc-kalkulator'); ?>
    </li>
</ul>
<div class="clearfix"></div>
<input type="hidden" name="_wck_fieldset" value="">

