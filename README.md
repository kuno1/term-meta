# term-meta
A WordPress utility to handle term meta easily.

## Installation

```
composer require kunoichi/term-meta
```

## Usage

In your theme's `functions.php`:

```
if ( class_exists( 'Kunoichi\TermMeta\ColorMeta' ) ) {
	new Kunoichi\TermMeta\ColorMeta( 'category_color', 'category', [
		'label'        => __( 'Color', 'your-domain' ),
		'admin_column' => true,
	] );
}
```

To get this color, use WordPress builtin function `get_term_meta( $term_id, $meta_key, $single )` .

```php
<?php
// For example, display category title
// with specified background color
// in category archive.
$color = get_term_meta( get_queried_object_id(), 'category_color', true ) ?: '#000';
?>
<h1 style="background-color: <?php echo esc_attr( $color ) ?>">
	<?php the_archive_title(); ?>
</h1>
```
