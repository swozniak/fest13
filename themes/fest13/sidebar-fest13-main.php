<?php
/**
 * The sidebar containing the footer widget area
 *
 * If no active widgets in this sidebar, hide it completely.
 *
 */

if ( is_active_sidebar( 'fest13-main' ) ) : ?>
	<div class="col-xs-12 col-md-4" id="sidebar-column">
		<div class="widget-area">
			<?php dynamic_sidebar( 'fest13-main' ); ?>
		</div>
	</div>
<?php endif; ?>