<?php
if (!defined('ABSPATH')) { exit; }
?>
<label><?php esc_html_e('Section display', 'wc-kalkulator'); ?></label>
<select class="param" data-wck-param="section_mode"><option value="section"><?php esc_html_e('Section', 'wc-kalkulator'); ?></option><option value="step"><?php esc_html_e('Configurator step', 'wc-kalkulator'); ?></option></select>
<p><?php esc_html_e('Includes subsequent fields until the next section. Steps have Back/Next navigation. Ordinary sections stay visible.', 'wc-kalkulator'); ?></p>
