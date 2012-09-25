<?php
$navigation = array(
	"next" => null,
	"prev" => null,
);
global $wp_query;
$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
$max_page = $wp_query->max_num_pages;
if (!is_single()) {
	if (empty($paged) || ($paged+1 <= $max_page)) {
				apply_filters( 'next_posts_link_attributes', '' );
		$navigation['next'] = next_posts( $max_page, false);		
	}
	if ($paged > 1) {
				apply_filters( 'previous_posts_link_attributes', '' );
		$navigation['prev'] = previous_posts( false );
	}
}
foreach ($navigation as $direction=>$url):
?>
<navigation type="<?php echo $direction ?>" show="<?php echo ($url != null ? "true" : "false") ?>">
	<url><![CDATA[<?php echo $url ?>]]></url>
</navigation>
<?php endforeach; ?>
</mysiteapp>
