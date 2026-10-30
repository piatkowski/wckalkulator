<?php

namespace WCKalkulator\Fields;

use WCKalkulator\View;

/** A section starts here and includes following fields until the next section. */
class SectionField extends HtmlField
{
    protected $type = 'section';
    protected $group = 'special';
    protected $show_title = true;
    protected $default_data = array('required' => false, 'section_mode' => 'section');

    public function admin_fields($value = '')
    {
        $this->admin_title = __('Section / configurator step', 'wc-kalkulator');
        return View::render('fields/admin/section');
    }

    public function is_required() { return false; }
    public function is_required_when_visible() { return false; }
    public function render_for_product($value = '') { return ''; }
    public function render_for_cart($value = '') { return ''; }
    public function order_item_value($value) { return ''; }
}
