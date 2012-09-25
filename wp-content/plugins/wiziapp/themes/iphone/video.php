<?php
/**
 * Created by JetBrains PhpStorm.
 * User: fabian
 * Date: 12/05/11
 * Time: 10:56
 * To change this template use File | Settings | File Templates.
 */
    global $video_row;

    wiziapp_get_header();

    $video = json_decode($video_row['attachment_info'], TRUE);
?>

<div class="page_content">
    <div class="post">
        <h2 class="pageitem">
            <?php echo $video['title']; ?>
        </h2>
        <div class="pageitem">
            <div class="video" id="video-<?php echo $video_row['id']; ?>">
                <div id="singlentry">
                    <?php
                        $ve = new WiziappVideoEmbed();
                        echo $ve->getCode($video['actionURL'], $video_row['id'], $video['bigThumb']);
                    ?>
                    <div id="video_description">
                        <?php echo $video['description']; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <br />
    <?php
        /**
         * The link below is for handing video in the simulator, the application shows the video itself
         * while the simulator only shows an image.
         */
    ?>
    <a href="cmd://open/video" id="dummy_video_opener"></a>
</div>
<script type="text/javascript">
    <?php
        /**
         * This class handle all the web view events and provides an external interface for the application
         * and the simulator. The simulator is getting some special treatment to help capture links and such
         */
    ?>
    window.wiziappDebug = <?php echo (WP_WIZIAPP_DEBUG) ? "true" : "false"; ?>;
    window.simMode = <?php echo (isset($_GET['sim']) && $_GET['sim']) ? 'true' : 'false'; ?>;
    window.wiziappCdn = "<?php echo WiziappConfig::getInstance()->getCdnServer(); ?>";
    window.wiziappPostHeaders = <?php echo json_encode(wiziapp_content_get_video_headers(get_bloginfo('name'))); ?>;
</script>
<?php
    $videoScript = WiziappConfig::getInstance()->getCdnServer() . '/scripts/api/1/apps/video.js?v=' . WIZIAPP_VERSION;
    echo '<script type="text/javascript" src="' . $videoScript . '"></script>';
    wiziapp_get_footer();
?>