<?php
    wiziapp_get_header();

    // Before handing the content, make sure this post is scanned
    $processed = get_post_meta($post->ID, 'wiziapp_processed');
    if (empty($processed)){
        wiziapp_save_post($post);
    }
?>
        <div class="page_content">
            <div class="post">
                <?php
                    $pLink = wiziapp_buildPostLink($post->ID);
                ?>
                <h2 class="pageitem">
                    <a id="post_title" href="<?php echo $pLink ?>" rel="bookmark" title="<?php the_title(); ?>">
                        <?php the_title(); ?>
                    </a>
                </h2>
                <div class="pageitem">
                    <div class="single-post-meta-top">
                        <div id="author_and_date"><span class="postDescriptionCellItem_author">By
                            <a href="<?php echo wiziapp_buildAuthorLink($post->post_author); ?>">
                            <?php the_author() ?>
                            </a></span>&nbsp;<span class="postDescriptionCellItem_date"><?php echo wiziapp_formatDate($post->post_date); ?></span>
                        </div>
                        <?php wiziapp_the_rating() ?>
                    </div>

                    <div class="clear"></div>
                    <div class="post" id="post-<?php the_ID(); ?>">
                        <div id="singlentry">
                            <?php
                                $GLOBALS['WiziappProfiler']->write('Before the thumb inside the post ' . $post->ID, 'theme._content');
                            ?>
                            <?php 
                                set_time_limit(60); 
                                $size = WiziappConfig::getInstance()->getImageSize('posts_thumb');
                                $limitSize = WiziappConfig::getInstance()->getImageSize('limit_post_thumb');
                                wiziapp_getPostThumbnail($post, $size, $limitSize);
                                //wiziapp_getPostThumbnail($post, array('width'=>100, height=>100), $limitSize); 
                            ?>
                            <?php
                                $GLOBALS['WiziappProfiler']->write('after the thumb inside the post ' . $post->ID, 'theme._content');
                            ?>
                            <?php
                                $GLOBALS['WiziappProfiler']->write('Before the content inside the post ' . $post->ID, 'theme._content');
                            ?>
                            <?php global $more; $more = 1; the_content(); ?>
                            <?php
                                $GLOBALS['WiziappProfiler']->write('After the content inside the post ' . $post->ID, 'theme._content');
                            ?>
                        </div>
                    </div>
                </div>
                <?php if (!is_page()) { ?>
                <div class="clear"></div>
                <ul class="wiziapp_bottom_nav">
                    <?php
                        wiziapp_get_categories_nav();
                        wiziapp_get_tags_nav();
                    ?>
                </ul>
                <div class="clear"></div>
                <?php } ?>
            </div>
            <br />  
            <div id="debug" style="background-color: #c0c0c0;">
                ####AREA 51####
                <div id="swipeme" style="height: 50px; background-color: #ccc;">
                    PLACE HOLDER
                </div>
                <a id="reload" href="#" onclick="top.location.reload(true)">RELOAD</a><br />
                <a id="swipeLeft" href="cmd://event/swipeRight"></a>
                <a id="swipeRight" href="cmd://event/swipeLeft"></a>
            </div>
            <?php
                /**
                 * The link below is for handing video in the simulator, the application shows the video itself
                 * while the simulator only shows an image.
                 */
            ?>
            <a href="cmd://open/video" id="dummy_video_opener"></a>
        </div>
        <?php                                                    
            //wp_footer(); - no need for this
            /**
             * @todo once the major part of the development is over move the script to the cdn, everything but the variables
             * scripts in the cdn in production mode are combined and minimized.
             * Beside... this code might be needed in other CMS
             */
        ?>
        <div id="templates" class="hidden"> 
            <div id="album_template">   
                <ul class="wiziapp_bottom_nav albums_list">
                    <li>
                        <a class="albumURL" href="javascript:void(0);">
                            <div class="imagesAlbumCellItem album_item">  
                                <div class="attribute imageURL album_item_image"></div>
                                <div class="album_item_decor"></div>
                                <p class="attribute text_attribute title album_item_title"></p>
                                <div class="numOfImages attribute text_attribute album_item_numOfImages"></div>
                                <span class="rowCellIndicator"></span>
                            </div>
                        </a> 
                    </li>    
                </ul>       
            </div>
        </div>  
        <script type="text/javascript">
            <?php
                /**
                 * This class handle all the webview events and provides an external interface for the application
                 * and the simulator. The simulator is getting some special treatment to help capture links and such
                 */
            ?>
    
            window.galleryPrefix = "<?php echo wiziapp_buildPostImagesGalleryLink($post->ID); ?>%2F";
            window.wiziappDebug = <?php echo (WP_WIZIAPP_DEBUG) ? "true" : "false"; ?>;
            window.wiziappPostHeaders = <?php echo json_encode(wiziapp_content_get_post_headers(FALSE)); ?>;
            window.wiziappRatingUrl = '<?php echo get_bloginfo('url'); ?>/?wiziapp/getrate/post/<?php echo $post->ID ?>';
            window.wiziappCommentsCountUrl = '<?php echo get_bloginfo('url'); ?>/?wiziapp/post/<?php echo $post->ID?>/comments';
            window.multiImageWidthLimit = "<?php echo WiziappConfig::getInstance()->multi_image_width; ?>";
            window.multiImageHeightLimit = "<?php echo WiziappConfig::getInstance()->multi_image_height; ?>";
            window.simMode = <?php echo (isset($_GET['sim']) && $_GET['sim']) ? 'true' : 'false'; ?>; 
            window.wiziappCdn = "<?php echo WiziappConfig::getInstance()->getCdnServer(); ?>";
        </script>
        <?php
            $contentScript = WiziappConfig::getInstance()->getCdnServer() . '/scripts/api/1/apps/content_'.WIZIAPP_VERSION.'.js';
            echo '<script type="text/javascript" src="' . $contentScript . '"></script>';
            wiziapp_get_footer();
        ?>