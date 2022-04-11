<?php

if (!defined('ABSPATH')) {
    exit;
}

use WCKalkulator\Helper;
use WCKalkulator\Cache;

?>
<p class="post-attributes-label-wrapper">
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
    <?php echo Helper::html_help_tip(__('Choose a field type from the list and click "Add field".', 'wc-kalkulator')); ?>
</p>
<p class="post-attributes-label-wrapper">
    <button type="button" class="button" id="add-field-button">
        <?php _e('Add field', 'wc-kalkulator'); ?>
    </button>
</p>
