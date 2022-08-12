<?php
if (!defined('ABSPATH')) {
    exit;
}
global $post;
$javascript = get_post_meta($post->ID, '_wck_javascript', true);

?>
<div class="javascript">
    <p>
        <?php _e('Use JavaScript or jQuery syntax. Use <code>wck("field_name")</code> to access jQuery object of the field by its name. You can use <code>jQuery()</code> or <code>$()</code>', 'wc-kalkulator'); ?> <br />Need help? <a href="https://www.youtube.com/watch?v=sX5mFks7WD0" target="_blank"><?php _e('Watch video', 'wc-kalkulator'); ?></a>
    </p>
    <code class="lh-5">jQuery(document).ready(function ($) {</code>
    <textarea id="wck_js_editor" name="_wck_javascript"><?php esc_html_e($javascript); ?></textarea>
    <code class="lh-5">});</code>
</div>
