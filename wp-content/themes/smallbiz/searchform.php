<form method="get" id="searchform" action="<?php bloginfo('url'); ?>/">
<label class="hidden" for="s" style="padding-left:15px;"><?php _e('Search for:'); ?></label>
<div style="padding-left:15px;"><input type="text" value="<?php the_search_query(); ?>" name="s" id="s" />
<input type="submit" id="searchsubmit" value="Search" />
</div>
</form>