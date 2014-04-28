<?php
/**
 * Template Name: Fest 13 - VIDEOS
 *
 */

get_header();

wp_enqueue_script( 'youtube-friend', get_template_directory_uri() . '/js/videos.js', array(), '1.0.0', true );
?>


<div class="col-xs-12 col-md-8 video-content" id="content-column">
	<header class="entry-header">
		<div class="page-header">
			<h1><?php the_title() ?></h1>			
		</div>
	</header>
	<div class="video-container">
		<iframe id="ytplayer" type="text/html" width="560" height="315" src="//www.youtube.com/embed/Th9i8ODfkG8?fs=1" frameborder="0"></iframe>
	</div>

	<div class="video-meta">
		<h3 class="video-title">The Fest 12 Highlights</h3>
		Uploaded by <a target="_blank" href="//youtube.com/user/TheFestFLVideos/" class="video-author">@TheFestFLVideos</a>
	</div>
</div>

<div class="col-xs-12 col-md-4 video-sidebar" id="sidebar-column">
	<div class="video-search">
		<h4>Search Videos</h4>
		<input id="video-search" />
	</div>
	<div class="playlist-container">
		<h4>FEST 12</h4>
		<ul class="fest12"></ul>

		<h4>FEST 11</h4>
		<ul class="fest11"></ul>

		<h4>FEST 10</h4>
		<ul class="fest10"></ul>
		
		<h4>FEST 9</h4>
		<ul class="fest9"></ul>
		
		<h4>FEST 8</h4>
		<ul class="fest8"></ul>
		
		<h4>FEST 7</h4>
		<ul class="fest7"></ul>
		
		<h4>FEST 6</h4>
		<ul class="fest6"></ul>
		
		<h4>FEST 5</h4>
		<ul class="fest5"></ul>
		
		<h4>FEST 4</h4>
		<ul class="fest4"></ul>
		
		<h4>FEST 3</h4>
		<ul class="fest3"></ul>
		
		<h4>FEST 2</h4>
		<ul class="fest2"></ul>
		
		<h4>FEST 1</h4>
		<ul class="fest1"></ul>
	</div>
</div>

<?php get_footer();