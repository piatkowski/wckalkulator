<?php
if (!defined('ABSPATH')) {
    exit;
}

use WCKalkulator\Helper;

?>
<label>* <?php _e('HTML code', 'wc-kalkulator'); ?>
    <?php echo Helper::html_help_tip(__('Write HTML code which will be displayed on product page.', 'wc-kalkulator')); ?></label>
<textarea rows="8" class="param fst-content"></textarea>
<p><b>Hint:</b> use <code>{= }</code> to write dynamic content calculated from field's values. For example:
    <code>{={width}*{height}}</code> will display dynamically calculated area. <a href="https://www.youtube.com/watch?v=Jrc1dXof_pw&amp;t=75s" target="_blank">Watch video</a><br /></p>