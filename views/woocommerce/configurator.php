<?php
if (!defined('ABSPATH')) { exit; }
use WCKalkulator\View;
use WCKalkulator\Sanitizer;
$sections = array(array('title' => '', 'mode' => 'section', 'fields' => array()));
foreach ($view->fields as $field) {
    if ($field['type'] === 'section') {
        $sections[] = array('title' => $field['title'], 'mode' => $field['section_mode'], 'fields' => array());
    } else {
        $sections[count($sections) - 1]['fields'][] = $field;
    }
}
?>
<div class="wck-configurator">
<input type="hidden" name="_wck_product_id" value="<?php echo absint($view->product_id); ?>">
<input type="hidden" name="_wck_hash" value="<?php echo esc_attr(wp_hash($view->product_id)); ?>">
<?php echo wp_kses($view->hidden, Sanitizer::allowed_html()); ?>
<ol class="wck-step-progress" aria-label="<?php esc_attr_e('Configuration progress', 'wc-kalkulator'); ?>" hidden>
<?php foreach ($sections as $section) : if ($section['mode'] === 'step') : ?><li><?php echo esc_html($section['title']); ?></li><?php endif; endforeach; ?>
</ol>
<?php foreach ($sections as $section) : ?>
<section class="wck-section<?php echo $section['mode'] === 'step' ? ' wck-step' : ''; ?>">
<?php if ($section['title'] !== '') : ?><h3 tabindex="-1"><?php echo esc_html($section['title']); ?></h3><?php endif; ?>
<table class="variations<?php echo $view->layout === 2 ? ' wck-layout-two-col' : ''; ?>">
<?php echo View::render('woocommerce/field_rows', array('fields' => $section['fields'], 'layout' => $view->layout)); ?>
</table>
</section>
<?php endforeach; ?>
<nav class="wck-step-navigation" aria-label="<?php esc_attr_e('Configuration steps', 'wc-kalkulator'); ?>" hidden>
<button type="button" class="button wck-step-back"><?php esc_html_e('Back', 'wc-kalkulator'); ?></button>
<p class="wck-step-status" aria-live="polite"></p>
<button type="button" class="button wck-step-next"><?php esc_html_e('Next', 'wc-kalkulator'); ?></button>
</nav>
</div>
