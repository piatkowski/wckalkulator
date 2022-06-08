<?php
if (!defined('ABSPATH')) {
    exit;
}
use WCKalkulator\Helper;
?>

<label>* <?php _e('File Attachment', 'wc-kalkulator'); ?>
    <?php echo Helper::html_help_tip( __('Select a file to attach', 'wc-kalkulator')); ?></label>
<a href="#" class="button action-add-attachment"><?php _e("Select File", "wc-kalkulator"); ?></a>
<input type="hidden" class="param fst-content wp-media-attachment-id" value="">
<a href="#" class="wp-media-attachment-preview" target="_blank"></a>