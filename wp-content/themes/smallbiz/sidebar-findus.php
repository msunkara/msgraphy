</div>
	<div id="sidebar">
		<ul>
			<?php 	/* Widgetized sidebar, if you have the plugin installed. */
					if ( !function_exists('dynamic_sidebar') || !dynamic_sidebar('Sidebar Find Us Page') ) : ?>
			<li class="box">
				<h3>Connect With Us: </h3>
				
				<p class="center"><a href="#" target="_blank"><img src="http://members.expand2web.com/E2W-theme-images/TW_icon2.png" class="frame" alt="Expand2Web Twitter Feed"/></a></p>
				
				<p class="center"><a href="#" target="_blank"><img src="http://members.expand2web.com/E2W-theme-images/YT_icon2.png" class="frame" alt="Expand2Web YouTube Link"/></a></p>
				<p class="center"><a href="#" target="_blank"><img src="http://members.expand2web.com/E2W-theme-images/FB_icon2.png" class="frame" alt="Expand2Web Facebook Link"/></a></p>
							<p>This demo sidebar widget will be replaced as soon as you add your own widget.</p>
				<p><strong><a href="http://userguide.expand2web.com/how-can-i-remove-the-default-social-media-widgets/" target="_blank">Click to Read the Tutorial</a><br /></strong></p>
			</li>
			<?php endif; ?>
		</ul>
	</div>
