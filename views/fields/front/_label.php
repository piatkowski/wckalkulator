<?php
if (!defined('ABSPATH')) {
    exit;
}
?>
<?php if (!empty($view->before_title)): ?>
    <span class="before-title"><?php echo esc_html($view->before_title); ?></span>
<?php endif; ?>

<?php echo esc_html($view->title); ?>

<?php if (isset($view->select_limit) && absint($view->select_limit) > 0): ?>
    <small class="multicheckbox-limit-info"><?php echo esc_html(sprintf(__('(max. %s)', 'wc-kalkulator'), $view->select_limit)); ?></small>
<?php endif; ?>

<?php if (!empty($view->after_title)): ?>
    <span class="after-title"><?php echo esc_html($view->after_title); ?></span>
<?php endif; ?>

<?php if (isset($view->show_required_asterisk) && $view->show_required_asterisk) : ?>
    <span class="required-asterisk">*</span>
<?php endif; ?>