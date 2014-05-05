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
		<?php
			$playlists = json_decode('[{
				"id": "fest12",
				"title": "Fest 12"
			}, {
				"id": "fest11",
				"title": "Fest 11"
			}, {
				"id": "fest10",
				"title": "Fest 10"
			}, {
				"id": "fest9",
				"title": "Fest 9"
			}, {
				"id": "fest8",
				"title": "Fest 8"
			}, {
				"id": "fest7",
				"title": "Fest 7"
			}, {
				"id": "fest6",
				"title": "Fest 6"
			}, {
				"id": "fest5",
				"title": "Fest 5"
			}, {
				"id": "fest4",
				"title": "Fest 4"
			}, {
				"id": "fest3",
				"title": "Fest 3"
			}, {
				"id": "fest2",
				"title": "Fest 2"
			}, {
				"id": "fest1",
				"title": "Fest 1"
			}]', true );

		foreach( $playlists as $playlist ) {
			$data = get_transient( implode( '-', array( 'videos', $playlist['id'] ) ) );
			echo implode( '', array( '<h4>', $playlist['title'], '</h4>' ) );
			echo implode( '"', array( '<ul class=', $playlist['id'], '>' ) );

			foreach ( $data as $item ) {
				$title = $item->title->{'$t'};
				$author = $item->{'media$group'}->{'media$credit'}[0]->{'$t'};
				$href = $item->link[0]->href;
				$fragments = explode( '/', $item->link[1]->href );
				$videoID = $fragments[count( $fragments ) - 2];
				$videoURL = '//www.youtube.com/embed/';
				$url =  $videoURL . $videoID;
				$thumb = '//img.youtube.com/vi/' . $videoID . '/default.jpg'; ?>
				

				<li class="video-thumb" 
					data-href="<?php echo $href; ?>" 
					data-title="<?php echo $title; ?>" 
					data-author="<?php echo $author; ?>" 
					data-url="<?php echo $url; ?>">
					<h5><?php echo $title; ?></h5>
					<img alt="<?php echo $title; ?>" src="<?php echo $thumb; ?>">
				</li>
				<?
			}
			echo '</ul>';
		} ?>
	</div>
</div>

<?php get_footer();