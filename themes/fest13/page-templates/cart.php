<?php
/**
 * Template Name: Fest 13 - Cart
 *
 */
?>

<?php check_store_visibility(); ?>
<?php get_header(); ?>

<div class="col-xs-12 col-md-8" id="content-column">
	<?php echo do_shortcode( '[woocommerce_cart]' ); ?>
</div>

<div class="hidden-xs col-md-4" id="content-column">
	<div class="sidebar-backtostore">
		<a href="<?php echo home_url( 'store/'); ?>">&#171; Back To Store</a>
	</div>
	<?php dynamic_sidebar( 'fest13-store' ); ?>
</div>

<?php get_footer(); ?>
