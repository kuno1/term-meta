<?php

namespace Kunoichi\TermMeta;


/**
 * Color meta setting.
 *
 * @package tax-meta
 */
class ColorMeta extends AbstractMeta {
	
	private static $called = false;
	
	public function enqueue_scripts( $page ) {
		$screen = get_current_screen();
		if ( 'term' === $screen->base && $this->taxonomy_supported( $screen->taxonomy ) && ! self::$called ) {
			self::$called = true;
			wp_enqueue_style( 'wp-color-picker' );
			wp_enqueue_script( 'wp-color-picker' );
			$js = <<<JS
jQuery( document ).ready( function( $ ) {
    $('.term-meta-color-picker').wpColorPicker();
});
JS;

			wp_add_inline_script( 'wp-color-picker', $js );
		}
	}
	
	public function render_input( $term ) {
		?>
		<input class="term-meta-color-picker" type="text" name="<?php echo esc_attr( $this->key ) ?>" value="<?php echo esc_attr( $this->get_value( $term->term_id ) ) ?>" />
		<?php
	}
	
	protected function render_column_content( $term_id, $value ) {
		if ( $value ) {
			return sprintf( '<span style="color:%1$s" title="%1$s">%2$s</span>', esc_attr( $value ),esc_html( $value ) );
		} else {
			return '&nbsp;';
		}
	}
	
	
}
