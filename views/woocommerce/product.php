<?php
if (!defined('ABSPATH')) {
    exit;
}

use WCKalkulator\FieldsetProduct, WCKalkulator\Sanitizer;

?>
<table class="variations<?php echo $view->layout === 2 ? ' wck-layout-two-col' : '' ?>">
    <input type="hidden" name="_wck_product_id" value="<?php echo absint($view->product_id); ?>">
    <input type="hidden" name="_wck_hash" value="<?php echo wp_hash($view->product_id); ?>">
    <?php
    if ($view->layout === 1) {
        echo isset($view->html) ? wp_kses($view->html, Sanitizer::allowed_html()) : '';
    } elseif ($view->layout === 2) {
        $col = 1;
        $row_opened = false;
        foreach ($view->fields as $field) {

            if($field['type'] === 'hidden' || $field['type'] === 'formula') {
                echo wp_kses($field['html'], Sanitizer::allowed_html());
                continue;
            }

            if ($field['colspan'] === 2) {
                echo $row_opened ? '</tr>' : '';
                $col = 1;
                echo '<tr><td colspan="2" class="table-cell">';
                echo '<table class="wck-inner-table">' . wp_kses($field['html'], Sanitizer::allowed_html()) . '</table>';
                echo '</td></tr>';
            } else {
                if ($col == 1) {
                    echo '<tr>';
                    $row_opened = true;
                }
                echo '<td class="table-cell col-' . $col . '"><table class="wck-inner-table">' . wp_kses($field['html'], Sanitizer::allowed_html()) . '</table></td>';
                if ($col == 2) {
                    echo '</tr>';
                    $row_opened = false;
                }
            }
            $col = ($col === 1 && $field['colspan'] === 1) ? 2 : 1;
        }
        echo $row_opened ? '</tr>' : '';
    } else {
        if (current_user_can('manage_woocommerce')) {
            _e('Unsupported layout!', 'wc-kalkulator');
        }
    }
    ?>
</table>