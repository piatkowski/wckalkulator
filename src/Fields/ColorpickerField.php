<?php

namespace WCKalkulator\Fields;

use WCKalkulator\Plugin;
use WCKalkulator\View;

/**
 * Class ColorpickerField
 * @package WCKalkulator
 */

class ColorpickerField extends TextField {
	protected $type = "colorpicker";
	protected $group = "picker";

	/**
	 * Called in enqueue_scripts action
	 */
	public function enqueue_scripts() {
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_script(
			'iris',
			admin_url( 'js/iris.min.js' ),
			array( 'jquery-ui-draggable', 'jquery-ui-slider', 'jquery-touch-punch' ),
			false,
			1
		);
		wp_enqueue_script(
			'wp-color-picker',
			admin_url( 'js/color-picker.min.js' ),
			array( 'iris', 'wp-i18n' ),
			false,
			1
		);
		wp_enqueue_script(
			'wck-color-picker',
			Plugin::url() . '/assets/js/colorpicker.min.js',
			array( 'wp-color-picker' ),
			Plugin::VERSION,
			1
		);
	}

	/**
	 * Output HTML for fields at backend.
	 *
	 * @param $value
	 *
	 * @return string
	 */
	public function admin_fields( $value = '' ) {
		$this->admin_title = __( "Color Picker", "wc-kalkulator" );

		return View::render( 'fields/admin/colorpicker' );
	}

	/**
	 * Output HTML for product page
	 *
	 * @param $value
	 *
	 * @return string
	 */
	public function render_for_product( $value = "" ) {
		$args          = $args = $this->prepared_data();
		$args['value'] = $value;

		return View::render( 'fields/front/colorpicker', $args );
	}

	/**
	 * Run validation tests
	 *
	 * @param $value
	 *
	 * @return bool
	 */
	public function validate( $value ) {
		if ( ! is_string( $value ) ) {
			return false;
		}
		if ( ! $this->is_required() && $value === '' ) {
			return true;
		}
		if ( strlen( $value ) === 7 ) {
			if ( $value[0] === '#' ) {
				$hex = substr( $value, 1 );

				return preg_match( '/\A[0-9A-Fa-f]+\z/', $hex ) === 1;
			}
		}

		return false;
	}

}
