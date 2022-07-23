<?php

namespace WCKalkulator;

/**
 * Class Helper
 *
 * General helper functions
 *
 * @package WCKalkulator
 * @author Krzysztof Piątkowski
 * @license GPLv2
 * @since 1.0.0
 */
class Helper
{
    /**
     * Return HTML code for tooltip
     *
     * @param string $message
     * @return string
     * @since 1.1.0
     */
    public static function html_help_tip($message)
    {
        return '<span class="dashicons dashicons-editor-help wck-field-tip" title="' . esc_html($message) . '"></span>';
    }
    
    /**
     * Return "id" parameter from the $tag shortcode tag in the $content string
     *
     * @param $content
     * @param $tag
     * @return int
     * @since 1.1.0
     */
    public static function get_id_from_shortcode_tag( $content, $tag ) {
        if ( shortcode_exists( $tag ) ) {
            preg_match_all( '/' . get_shortcode_regex(array($tag)) . '/', $content, $matches, PREG_SET_ORDER );
            if ( empty( $matches ) ) {
                return 0;
            }
            foreach ( $matches as $shortcode ) {
                if ( $tag === $shortcode[2] ) {
                    preg_match('/id="?([0-9]+)"?/', $shortcode[3], $match_id);
                    if(isset($match_id[1]) && (int)$match_id[1] > 0)
                        return $match_id[1];
                }
            }
        }
        return 0;
    }
    
    /**
     * Echo $message only for woocommerce manager. Used in Ajax response.
     *
     * @param $message
     * @since 1.0.0
     */
    public static function message_for_manager($message)
    {
        if (current_user_can( 'manage_woocommerce' ) && Settings::get('display_errors') === 'yes') {
            echo esc_html($message);
        }
    }
}