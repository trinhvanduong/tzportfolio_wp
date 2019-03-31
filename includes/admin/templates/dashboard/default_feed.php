<div class="rss-widget">
	<?php wp_widget_rss_output(array(
		'url' 			=> 'http://feeds.feedburner.com/tzportfolio/blog',
		'title' 		=> __( 'Latest From TZ Portfolio', 'tz-portfolio' ),
		'items'        	=> 4,
		'show_summary' 	=> 0,
		'show_author'  	=> 0,
		'show_date'    	=> 1,
	)); ?>
</div>

<style type='text/css'>
	#tp-metaboxes-mainbox-1 a.rsswidget {
		font-weight: 400
	}
	#tp-metaboxes-mainbox-1 .rss-widget span.rss-date{
		color: #777;
		margin-left: 12px;
	}
</style>