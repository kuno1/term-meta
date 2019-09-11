<?php

namespace Kunoichi\TermMeta;

/**
 * Base class for meta.
 *
 * @package tax-meta
 * @property-read string $label
 * @property-read string $description
 * @property-read string $required
 * @property-read string $default
 * @property-read string $type
 * @property-read bool   $admin_column
 */
abstract class AbstractMeta {
	
	protected $key = '';
	
	/**
	 * @var string[]
	 */
	protected $taxonomy = [];
	
	protected $options = [];
	
	/**
	 * AbstractSetting constructor.
	 *
	 * @param string $key
	 * @param string|string[] $taxonomy
	 * @param array  $options
	 * * label => Title of row
	 * * priority => Number. Default 10.
	 * * description => Description of this field.
	 * * required    => Boolean. Default false.
	 * * default     => Default value. Default, empty.
	 * @
	 */
	public function __construct( $key, $taxonomy, $options = [] ) {
		$this->key      = $key;
		$this->taxonomy = (array) $taxonomy;
		$this->options = wp_parse_args( $options, [
			'label'        => $key,
			'description'  => '',
			'required'     => false,
			'default'      => '',
			'type'         => '',
			'admin_column' => false,
		] );
		// Save term meta.
		add_action( 'edited_terms', [ $this, 'save_term_meta' ], 10, 2 );
		foreach ( $this->taxonomy as $taxonomy ) {
			// Render nonce.
			add_action( "{$taxonomy}_term_edit_form_top", [ $this, 'render_nonce' ] );
			// Render fields.
			add_action( "{$taxonomy}_edit_form_fields", [ $this, 'form_fields' ], 10, 2 );
			if ( $this->admin_column ) {
				add_filter( 'manage_edit-' . $taxonomy . '_columns', [ $this, 'add_column' ] );
				add_filter( "manage_{$taxonomy}_custom_column", [ $this, 'render_column'], 10, 3 );
			}
		}
		// Enqueue scripts.
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
		
	}
	
	/**
	 * Enqueue assets.
	 *
	 * @param string $page
	 */
	public function enqueue_scripts( $page ) {
		// Enqueue something.
	}
	
	/**
	 * Detect if taxonomy supported.
	 *
	 * @param string $taxonomy
	 * @return bool
	 */
	protected function  taxonomy_supported( $taxonomy ) {
		return in_array( $taxonomy, $this->taxonomy );
	}
	
	/**
	 * Save term meta.
	 *
	 * @param int $term_id
	 * @param string $taxonomy
	 */
	public function save_term_meta( $term_id, $taxonomy ) {
		if ( ! $this->taxonomy_supported( $taxonomy ) ) {
			return;
		}
		if ( ! wp_verify_nonce( filter_input( INPUT_POST, '_taxnonce_' . $this->key ), $this->key ) ) {
			return;
		}
		update_term_meta( $term_id, $this->key, isset( $_POST[ $this->key ] ) ? $_POST[ $this->key ] : '' );
	}
	
	/**
	 * Render nonce field.
	 */
	public function render_nonce() {
		wp_nonce_field( $this->key, '_taxnonce_' . $this->key, false );
	}
	
	/**
	 * Render input field.
	 *
	 * @param \WP_Term $term
	 * @return void
	 */
	abstract public function render_input( $term );
	
	/**
	 * Render form fields.
	 *
	 * @param \WP_Term $term
	 * @param string $taxonomy
	 */
	public function form_fields( $term, $taxonomy ) {
		?>
		<tr>
			<th><label for="<?php echo esc_attr( $this->key ) ?>"><?php echo esc_html( $this->label ) ?></label></th>
			<td>
				<?php $this->render_input( $term ); ?>
				<?php if ( $this->description ) : ?>
					<p class="description"><?php echo wp_kses_post( $this->description ) ?></p>
				<?php endif; ?>
			</td>
		</tr>
		<?php
	}
	
	/**
	 * Get meta value.
	 *
	 * @param int $term_id
	 *
	 * @return mixed
	 */
	public function get_value( $term_id ) {
		return get_term_meta( $term_id, $this->key, true );
	}
	
	/**
	 * Add column
	 *
	 * @param array $columns
	 * @return array
	 */
	public function add_column( $columns ) {
		$columns[ 'meta-' . $this->key ] = $this->label;
		return $columns;
	}
	
	/**
	 * Render content.
	 *
	 * @param string $content
	 * @param string $column
	 * @param int    $term_id
	 * @return string
	 */
	public function render_column( $content, $column, $term_id ) {
		if ( 'meta-' . $this->key !== $column ) {
			return $content;
		}
		$content = $this->render_column_content( $term_id, $this->get_value( $term_id ) );
		return $content;
	}
	
	/**
	 * Render column content.
	 *
	 * @param int   $term_id
	 * @param mixed $value
	 * @return string
	 */
	protected function render_column_content( $term_id, $value ) {
		return esc_html( $value );
	}
	
	/**
	 * Getter
	 *
	 * @param string $name
	 * @return mixed
	 */
	public function __get( $name ) {
		if ( isset( $this->options[ $name ] ) ) {
			return $this->options[ $name ];
		} else {
			return null;
		}
	}
}
