<?php

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

if ( ! class_exists( 'WP_CUJS_Shortcode' ) ) {
	/**
	 * Execute the shortcode functionality.
	 *
	 * @since  4.0.0
	 */
	class WP_CUJS_Shortcode {
		/**
		 * Set the plugin settings and add the
		 * shortcode functionality.
		 *
		 * @since  4.0.0
		 */
		public function __construct() {
			add_filter( 'script_loader_tag', array( $this, 'add_module_attribute' ), 10, 2 );
			add_shortcode( 'countup', array( $this, 'show_counter' ) );
		}

		/**
		 * The countUp.min.js file (2.0.4) is exported as a ES6 module
		 * that's why we need to add the module attribute in the script and
		 * the same for our own script.
		 *
		 * @since  4.1.0
		 *
		 * @param  string   $tag      The current script tag to render in HTML.
		 * @param  string   $handle   The current handle script name.
		 * @return string             The script tag with module type.
		 */
		public function add_module_attribute( $tag, $handle ) {
			$valid_handles = array(
				'countUp.min.js',
				'wp-countup-js-plugin',
			);

			if ( ! in_array( $handle, $valid_handles, true ) ) {
				return $tag;
			}

			return str_replace( ' src', ' type="module" src', $tag );
		}

		/**
		 * Return the HTML counter.
		 *
		 * @since  4.0.0
		 * @since  4.0.2   Now we can execute shortcodes inside the count up shortcode.
		 * @since  4.1.0   Add only the needed attributes instead of all of them.
		 *
		 * @param  array     $raw_atts   The input user's attributes.
		 * @param  string    $content    The content to return.
		 * @return string                The rendered counter.
		 */
		public function show_counter( $raw_atts, $content = '' ) {
			$end_value = $this->get_end_value( $raw_atts, $content );

			if ( 0 === $end_value ) {
				return '';
			}

			$valid_attributes = array(
				'decimals',
				'delay',
				'duration',
				'easing',
				'grouping',
				'prefix',
				'reset',
				'scroll',
				'separator',
				'start',
				'suffix',
			);

			// The HTML counter.
			$counter = '<div class="counter" ';

			foreach ( $raw_atts as $attribute => $value ) {
				if ( ! in_array( $attribute, $valid_attributes, true ) ) {
					continue;
				}

				$counter .= 'data-' . esc_attr( $attribute ) . '="' . esc_attr( $value ) . '" ';
			}

			$counter .= 'data-end="' . $end_value . '">';
			$counter .= '</div>';

			return $counter;
		}

		/**
		 * Get the end target value to use in the counter.
		 *
		 * @since  4.1.0
		 * @access private
		 *
		 * @param  array    $raw_atts   The input user's attributes.
		 * @param  string   $content    The content to return.
		 * @return int                  The counter end value.
		 */
		private function get_end_value( $raw_atts, $content ) {
			$settings  = WP_CUJS::get_instance()->settings;
			$end_value = (int) isset( $settings['end_inside_shortcode'] ) ? do_shortcode( $content ) : $raw_atts['end'];
			$end_value = ! is_numeric( $end_value ) ? 0 : $end_value;

			return apply_filters( 'wp_cujs_get_end_value', $end_value );
		}
	}
}
