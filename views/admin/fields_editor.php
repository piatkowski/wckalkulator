<?php
if (!defined('ABSPATH')) {
    exit;
}

use WCKalkulator\Helper;
use WCKalkulator\Cache;

?>
<p class="align-right">
    <span class="wck-toggle-layout woocommerce-input-toggle woocommerce-input-toggle--disabled"> </span>
    <?php _e('Two columns layout', 'wc-kalkulator'); ?>
    <span class="wck-toggle-expand woocommerce-input-toggle woocommerce-input-toggle--enabled"> </span>
    <?php _e('Toggle expansion', 'wc-kalkulator'); ?>
</p>
<ul id="f-field-list">
</ul>
<div class="clearfix"></div>
<div class="fields-section">

        <select id="select-field">
            <?php foreach (Cache::get_once('FieldsetPostType_fields_dropdown') as $group => $fields): ?>
                <optgroup label="<?php echo esc_html($group); ?>">
                    <?php foreach ($fields as $type => $options): ?>
                        <option value="<?php echo esc_html($type); ?>">
                            <?php echo esc_html($options['title']); ?>
                        </option>
                    <?php endforeach; ?>
                </optgroup>
            <?php endforeach; ?>
        </select>
        <button type="button" class="button" id="add-field-button">
            <span class="dashicons dashicons-plus"></span>
        </button>

</div>
<input type="hidden" name="_wck_fieldset" value="">
