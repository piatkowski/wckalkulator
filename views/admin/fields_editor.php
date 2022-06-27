<?php
if (!defined('ABSPATH')) {
    exit;
}

use WCKalkulator\Helper;
use WCKalkulator\Cache;

?>
<ul id="f-field-list">
    <li class="welcome">
        <?php _e('Add your first field. Select a field type from the dropdown at the right box and click "Add field".', 'wc-kalkulator'); ?>
    </li>
</ul>
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
            <?php _e('Add field', 'wc-kalkulator'); ?>
        </button>

</div>
<input type="hidden" name="_wck_fieldset" value="">
