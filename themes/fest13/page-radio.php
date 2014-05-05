<?php
/**
 * Template Name: Fest 13 - Radio
 *
 */

get_header( 'radio' );
?>

<div class="col-xs-12 radio-content" id="content-column">
<?php if ( have_posts() ) : ?>
	<?php while ( have_posts() ) : the_post(); ?>
		<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
			<header class="entry-header">
			</header>

			<div class="entry-content">
				<?php the_content(); ?>
				<?php wp_link_pages( array( 'before' => '<div class="page-links"><span class="page-links-title">' . __( 'Pages:', 'twentythirteen' ) . '</span>', 'after' => '</div>', 'link_before' => '<span>', 'link_after' => '</span>' ) ); ?>
			</div><!-- .entry-content -->

		</article><!-- #post -->
		<?php // comments_template(); ?>
	<?php endwhile; ?>
<?php endif; ?>
</div>
<div class="fest13-fixed-scrolling-fix"></div>

<style>
/* new styles */
.wp-playlist-current-item {
	font-family: 'Oswald', sans-serif;
	text-transform: uppercase;
}
.wp-playlist-tracks {
	font-family: 'Open Sans', sans-serif;
}
.wp-playlist-current-item img {
	background: #fff;
}
.wp-playlist-current-item .wp-playlist-item-title {
	font-size: 18px !important;
}
.wp-playlist-item-title {
	font-size: 14px !important;
}

.wp-playlist-item-artist {
	font-size: 14px !important;
}
.wp-playlist-item-meta {
	line-height: 1.3 !important;
}
.wp-playlist-item-length {
	top: 7px !important;
}

.wp-playlist-item {
	padding: 8px 3px!important;
	border-bottom: 1px dashed #fff !important;
	color: #fff !important;
}
.wp-playlist-playing, .wp-playlist-item:hover {
	background: #3ea9b3 !important;
}
.wp-playlist-item .wp-playlist-caption {
	display: block;
}

/* overall mediaelement overrides */
.wp-playlist {
	background: inherit !important;
	color: inherit !important;
	border: inherit !important;
}

.radio-content {
	padding: inherit !important;
}
/* fixing now playing image */
.wp-playlist-current-item > img {
	max-width: 40% !important;
}

/* scrolling tracks */
.fest13-fixed-scrolling-fix {
	position: fixed;
	top: 0;
	left: 0;
	right: 0;
	height: 8em;
	z-index: 3;
	background: #307f87;
}

@media only screen 
and (min-width : 960px) {
	.fest13-fixed-scrolling-fix {
		max-width: 960px;
		margin: 0 auto;
	}
}

.wp-playlist-current-item {
	position: fixed !important;
	top: 1em;
	z-index: 5;
}
.mejs-container {
	position: fixed !important;
	top: 6em;
	z-index: 5;
}
.wp-playlist-tracks {
	overflow: hidden;
	overflow-y: scroll;
	margin-top: 7.5em !important;
	z-index: 1;
}
</style>
<?php get_footer( 'radio' ); ?>