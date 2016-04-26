<?php
/**
 * Untappd Shortcodes
 * @author kraftbj
 *
 * [untappd-menu location="123" menu="1234-1234-1234"]
 * @since  4.1.0
 * @param location        int    Location ID for the Untappd venue. Required.
 * @param menu            string Menu ID for the venue's menu. Required.
 * @param headerbg        string Header background color, hex value. Opitonal.
 * @param menubg          string Menu backgroung color, hex value. Optional.
 * @param sectionheaderbg string Section Header background color, hex value. Optional.
 * @param footerfontcolor string Footer font color, hex value. Optional.
 * @param linkfontcolor   string Link font color, hex value. Optional.
 * @param font_family     string Font family name. Optional.
 * @param font_size       int    Font size in pixels. Optional.
 */

class Jetpack_Untappd {

	private $scripts_included = false;

	function __construct() {
		add_action( 'init', array( $this, 'action_init' ) );
	}

	function action_init() {
		add_action( 'wp_head', array( $this, 'add_scripts' ), 1 );
		add_shortcode( 'untappd-menu', array( $this, 'menu_shortcode' ) );
	}

	/**
	 * Enqueue scripts and styles
	 */
	function add_scripts() {
		global $posts;
		if ( empty( $posts ) || ! is_array( $posts ) ) {
			return;
		}

		foreach ( $posts as $p ) {
			if ( has_shortcode( $p->post_content, 'untappd-menu' ) ) {
				$this->scripts_included = true;
				break;
			}
		}

		if ( ! $this->scripts_included ) {
			return;
		}

		wp_enqueue_script( 'zepto', 'https://cdnjs.cloudflare.com/ajax/libs/zepto/1.1.6/zepto.min.js', null, '1.1.6' );

	}

	/**
	 * [untappd-menu] shortcode.
	 *
	 */
	static function menu_shortcode( $atts, $content = '' ) {
		// Let's bail if we don't have location or menu.
		if ( ! isset( $atts['location'] ) || ! isset( $atts['menu'] ) ) {
			if ( current_user_can( 'edit_posts') ){
				return __( 'No location or menu ID provided in the untappd-menu shortcode.', 'jetpack' );
			}
			return;
		}
		// We're going to clean the user input.
		$atts = self::santize_atts( $atts );

		// Let's apply some defaults.
		$atts = shortcode_atts( array(
			'location'        => '',
			'menu'            => '',
			'headerbg'        => '#FFFFFF',
			'menubg'          => '#F6F6F6',
			'sectionheaderbg' => '#282828',
			'footerfontcolor' => '#4A4A4A',
			'linkfontcolor'   => '#055CFF',
			'font_family'     => 'Helvetica Neue',
			'font_size'       => '14',
		), $atts, 'untappd-menu' );

		if ( ! wp_script_is( 'zepto', 'done' ) ){
			return;
		}

		static $untappd_menu = 1;

		$html  = '<div id="menus-container-untappd-' . $untappd_menu . '"></div>';
		$html .= '<script type="text/javascript" src="https://business.untappd.com/locations/' . $atts['location'] . '/add_menu_to_website/js?menu_ids[]=' . $atts['menu'] . '"></script>' . PHP_EOL;
		$html .= '<script type="text/javascript">var options = {' . PHP_EOL;
		$html .= '"HeaderBackgroundColor": "'. $atts['headerbg'] .'",' . PHP_EOL;
		$html .= '"MenuBackgroundColor": "'. $atts['menubg'] .'",' . PHP_EOL;
		$html .= '"SectionHeaderBackgroundColor": "'. $atts['sectionheaderbg'] .'",' . PHP_EOL;
		$html .= '"FooterFontColor": "'. $atts['footerfontcolor'] .'",' . PHP_EOL;
		$html .= '"LinkFontColor": "'. $atts['linkfontcolor'] .'",' . PHP_EOL;
		$html .= '"FontFamily": "'. $atts['font_family'] .'",' . PHP_EOL;
		$html .= '"BaseFontSize": "'. $atts['font_size'] .'px"' . PHP_EOL;
		$html .= '}' . PHP_EOL;

		$html .= 'new MenuView(' . $atts['location'] . ', "menus-container-untappd-' . $untappd_menu . '", options);</script>';

		$untappd_menu++;

		return $html;
	}

	/**
	 * Santize the atts
	 *
	 * @return array
	 */
	static function santize_atts( $atts = null ) {
		if ( ! is_array( $atts ) ){
			return;
		}
		$intkeys    = array( 'location', 'font_size' );
		$hexkeys    = array( 'headerbg', 'menubg', 'sectionheaderbg', 'footerfontcolor', 'linkfontcolor' );

		foreach ( $atts as $k => $v ){
			if ( 'menu' == $k ){
				$atts[ $k ] = esc_attr( $v );
				break;
			}
			if ( 'font_family' == $k ){
				$atts['k'] = esc_js( $v );
				break;
			}
			if ( in_array( $k, $intkeys) ){
				$atts['k'] = intval( $v );
				break;
			}
			if ( in_array( $k, $hexkeys) ){
				$atts[ $k ] = santize_hex_color( $v );
				break;
			}
		} // end foreach

		return $atts;
	}
}

new Jetpack_Untappd();