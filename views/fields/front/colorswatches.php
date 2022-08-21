<?php
if (!defined('ABSPATH')) {
    exit;
}
/*
 * CSS style for thumbnail
 */
$style = ';';
if (isset($view->size) && absint($view->size) > 0) {
    $style .= 'width:' . $view->size . 'px;';
    $style .= 'height:' . $view->size . 'px;';
}

?>
<tr class="<?php echo esc_html($view->css_class); ?> wck-colorswatches">
    <?php echo wp_kses_post(apply_filters('wck_field_td_label', '<td class="label">', $view->field_type)); ?>
        <?php echo wp_kses($view->hint, \WCKalkulator\Sanitizer::allowed_html()); ?>
        <label for="<?php echo esc_html($view->id); ?>">
            <?php include '_label.php'; ?>
        </label>
    </td>
    <?php echo wp_kses_post(apply_filters('wck_field_td', '<td class="value">', $view->field_type)); ?>
        <?php foreach ($view->options_name as $i => $opt_name) : ?>
            <?php $id = $view->id . '-' . $i; ?>
            <label for="<?php echo esc_html($id); ?>">
                <input type="radio" name="<?php echo esc_html($view->name); ?>"
                       id="<?php echo esc_html($id); ?>" <?php echo esc_html($view->required); ?>
                       class="attached enabled"
                       value="<?php echo esc_html($opt_name); ?>" <?php checked(is_array($view->value) ? in_array($opt_name, $view->value) : ($view->value === $opt_name)); ?>>
                <span class="colorswatch" style="background-color:<?php echo esc_html($view->options_title[$i] . $style); ?>"></span>
            </label>
        <?php endforeach; ?>
    </td>
</tr>