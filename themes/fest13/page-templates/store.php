<?php
/**
 * Template Name: Fest 13 - Store
 *
 */
?>

<?php get_header(); ?>

<?php
	$product_categories = array(
		'Tickets' => 'ticket',
		'Shirts' => 'shirt',
		'Hoodies' => 'hoodie',
		'Accessories' => 'accessory',
		'Hotels' => 'hotel-room',
		'Bus Tickets' => 'bus-ticket',
	);
?>

<div class="col-xs-12 col-md-8" id="content-column">
	<ul class="nav nav-tabs">
		<?php foreach( $product_categories as $product_name => $product_slug ) : ?>
			<?php $active = $product_slug == 'ticket' ? 'active' : ''; ?>
			<li class="<?php echo $active; ?>"><a href="#<?php echo $product_slug; ?>" data-toggle="tab"><?php echo $product_name; ?></a></li>
		<?php endforeach; ?>
	</ul>
	<div class="tab-content">
		<?php foreach( $product_categories as $product_name => $product_slug ) : ?>
			<?php $class = $product_slug == 'ticket' ? 'in active' : ''; ?>
			<div class="tab-pane fade <?php echo $class; ?>" id="<?php echo $product_slug; ?>">
				<?php include( locate_template( 'template-parts/store/store-product-group.php' ) ); ?>
			</div>
		<?php endforeach; ?>
	</div>
</div>

<div class="hidden-xs col-md-4" id="content-column">
	<?php dynamic_sidebar( 'fest13-store' ); ?>
</div>

<?php get_footer(); ?>
