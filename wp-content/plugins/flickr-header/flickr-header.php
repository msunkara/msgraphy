<?php
/*
    Plugin Name: Flickr Header
    Plugin URI: http://wordpress.org/extend/plugins/flickr-header/
    Description: Lets you pick a Creative Commons picture (or pictures) from Flickr to use as your WordPress header image(s).
    Version: 0.3
    Author: OwnLocal
    Author URI: http://www.ownlocal.com
    License: GPLv2 or any later version
*/

/*  Copyright 2012  OwnLocal

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/* License URI: http://www.gnu.org/licenses/gpl-2.0.html
 *
 * Attributions: This plugin is based on some of the work in the custom-header code of
 *               WordPress Core and the TwentyEleven theme (some of the cropping code
 *               and data storage code was coppied and modified)
 * */

class OwnlocalFlickrHeader {

  var $page;
  private $flickr_api_key;
  private $api_key_scope;

  const MIN_HEADER_WIDTH = 1000;

  function __construct() {
    add_action('init', array($this, 'init'), 10);
    add_action('init', array($this, 'capture_search_requests'), 20);
    add_action('admin_menu', array($this, 'setup_admin_ui'));
    add_action('network_admin_menu', array($this, 'setup_network_admin_ui'));
    add_action('network_admin_edit_flickr_header_settings', array($this, 'save_network_settings'));
    add_action('custom_header_options', array($this, 'header_options_form'));
    add_action('wp_enqueue_scripts', array($this, 'add_stylesheet') );
  }

  function init() {
    $this->api_key_scope = 'network';
    $this->flickr_api_key = get_site_option('flickr-header-api-key');
    if (!$this->flickr_api_key) {
      $this->api_key_scope = 'blog';
      $this->flickr_api_key = get_option('flickr-header-api-key');
    }
  }

  function setup_network_admin_ui() {
    add_submenu_page('settings.php', 'Flickr Header', 'Flickr Header', 'manage_network_options', 'flickr-header-settings', array($this, 'network_options_form'));
  }

  function network_options_form() {
    if (!current_user_can('manage_network_options')) return false;
    $api_key = get_site_option('flickr-header-api-key');
    echo "<h2>Flickr Header - Network Settings</h2>";
    echo "Use this page to set the API key across all sites in this network, to eliminate the ened for individual sites to enter an API key:<br/>\n";
    echo "(You can <a href='http://www.flickr.com/services/apps/create/apply' target='_blank'>get a Flickr API key here</a>)<br/><br/>\n";
    echo "<form action='edit.php?action=flickr_header_settings' method='post'>\n";
    wp_nonce_field('flickr-header-network-settings');
    echo "Network-wide API-key: <input type='text' name='flickr_header_api_key' value='$api_key'>\n";
    submit_button('Update Key');
    echo "</form>";
  }

  function save_network_settings() {
    // Verify authenticity
    check_admin_referer('flickr-header-network-settings');
    if (!current_user_can('manage_network_options')) return false;

    //Save the settings
    $api_key = preg_replace('/[^a-zA-Z0-9]/', '', $_POST['flickr_header_api_key']); //Strip out non alpha-numberic chars
    update_site_option('flickr-header-api-key', $api_key);

    //Redirect back to the settings page
    wp_redirect(add_query_arg(array('page' => 'flickr-header-settings', 'updated' => 'true'), network_admin_url('settings.php')));
    exit();
  }

  function setup_admin_ui() {
    if ( ! current_user_can('edit_theme_options') )
      return;

    $this->page = $page = add_theme_page(__('Flickr Header'), __('Flickr Header'), 'edit_theme_options', 'flickr-header', array(&$this, 'admin_page'));
    add_action("admin_print_styles-$page", array(&$this, 'admin_css_includes'));
    add_action("admin_print_scripts-$page", array(&$this, 'admin_js_includes'));
    add_action("admin_head-$page", array($this, 'process_form'));
    add_action("admin_head-$page", array(&$this, 'js'), 50);
  }

  function add_stylesheet() {
    wp_register_style('flickr-header', plugins_url('header-styles.css', __FILE__));
    wp_enqueue_style('flickr-header');
  }

  function admin_css_includes() {
    wp_register_style('flickr-header-admin', plugins_url('admin-styles.css', __FILE__));
    wp_enqueue_style('flickr-header-admin');
    wp_enqueue_style('imgareaselect'); //From standard WP Header functionality
  }

  function admin_js_includes() {
    wp_register_script('flickr-header-admin', plugins_url('search_crop.js', __FILE__));
    wp_enqueue_script('flickr-header-admin');
    wp_enqueue_script('imgareaselect'); //From standard WP Header functionality
  }

  function js() {
    ?>
    <script type="text/javascript">
      var fh_width = <?php echo absint( get_theme_support( 'custom-header', 'width')); ?>;
      var fh_height = <?php echo absint( get_theme_support( 'custom-header', 'height')); ?>;
      var fh_flex_height = <?php echo 
        (current_theme_supports( 'custom-header', 'flex-height') ? 'true' : 'false'); ?>;
      var fh_flex_width = <?php echo 
        (current_theme_supports( 'custom-header', 'flex-width') ? 'true' : 'false'); ?>;
    </script>
    <?php
  }

  function admin_page() {
    ?>
    <h3>Flickr Header:</h3>
    <table class="form-table flickrHeader">
      <tr>
        <th scope="row">Current Image:</th>
        <td><div>
          <?php
            if ($data = get_theme_mod('flickr_header_data')) {
              $width = $data['width'];
              $height = $data['height'];
              $url = $data['url'];
              $crop = $data['crop'];
              $crop_x1 = intval($crop['x1']);
              $crop_y1 = intval($crop['y1']);
              $crop_width = intval($crop['width']);
              $crop_height = intval($crop['height']);

              $max_width = 0;
              // For flex, limit size of image displayed to 1500px unless theme says otherwise
              if ( current_theme_supports( 'custom-header', 'flex-width' ) )
                $max_width = 1500;
              if ( current_theme_supports( 'custom-header', 'max-width' ) )
                $max_width = max( $max_width, get_theme_support( 'custom-header', 'max-width' ) );
              $max_width = max( $max_width, get_theme_support( 'custom-header', 'width' ) );
              if ( $width > $max_width ) {
                $oitar = $width / $max_width;
                $width /= $oitar;
                $height /= $oitar;
                $crop_x1 /= $oitar;
                $crop_y1 /= $oitar;
                $crop_width /= $oitar;
                $crop_height /= $oitar;
              } else {
                $oitar = 1;
              }
              if (0 == $crop_width || 0 == $crop_height) {
                $crop_width = get_theme_support('custom-header', 'width');
                $crop_height = get_theme_support('custom-header', 'height');
                $aspect = $crop_width / $crop_height;
                if ($width < $crop_width || $height < $crop_height) {
                  if ($width / $height > $aspect) {
                    $crop_height = $height;
                    $crop_width = $crop_height * $aspect;
                  } else {
                    $crop_width = $width;
                    $crop_height = $crop_width / $aspect;
                  }
                }
                $crop_width /= $oitar;
                $crop_height /= $oitar;
              }
              $crop_x1 = round($crop_x1);
              $crop_y1 = round($crop_y1);
              $crop_width = round($crop_width);
              $crop_height = round($crop_height);
              ?>
              <div class="wrap">
              <?php screen_icon(); ?>
              <h2><?php _e( 'Crop Header Image' ); ?></h2>
              <form method="post">
                <p class="hide-if-no-js"><?php _e('Choose the part of the image you want to use as your header.'); ?></p>
                <p class="hide-if-js"><strong><?php _e( 'You need Javascript to crop the image.'); ?></strong></p>
                <div id="crop_image" style="position: relative">
                        <img src="<?php echo esc_url( $url ); ?>" id="upload" width="<?php echo $width; ?>" height="<?php echo $height; ?>" />
                </div>
                <input type="hidden" name="action" value="crop"/>
                <input type="hidden" name="x1" id="x1" value="<?php echo $crop_x1; ?>"/>
                <input type="hidden" name="y1" id="y1" value="<?php echo $crop_y1; ?>"/>
                <input type="hidden" name="width" id="width" value="<?php echo $crop_width; ?>"/>
                <input type="hidden" name="height" id="height" value="<?php echo $crop_height; ?>"/>
                <input type="hidden" name="oitar" id="oitar" value="<?php echo $oitar; ?>" />
                <p class="submit">
                  <?php submit_button( __( 'Update Crop' ), 'secondary', 'submit', false ); ?>
                </p>
              </form>
              </div>
              <?php

            } else {
              echo "no photo selected";
            }
          ?>
        </div></td>
      </tr>
      <?php if ('blog' == $this->api_key_scope) { ?>
        <tr>
          <th scope="row">
            API Key<br/>
            (<a href='http://www.flickr.com/services/apps/create/apply' taget='_blank'>Get one here</a>)</th>
          <td>
            <form method="post" action="<?php echo esc_attr(add_query_arg('action', 'update-key')); ?>">
            <?php
              $key = $this->flickr_api_key;
              if (!empty($key)) { 
                $masked_key = substr($key,0,2)."**********".substr($key,-4);
                echo "Current: <b>$masked_key</b><br/>";
              }
            ?>
            Update: <input type="text" name="flickrHeader[api_key]">
            <?php submit_button('Update API key', 'secondary'); ?>
            </form>
          </td>
        </tr>
      <?php } //end API key scope check ?>
      <tr>
        <th scope="row">Search For Images:</th>
        <td>
          <form method="post" action="<?php echo esc_attr(add_query_arg('action', 'select-image')); ?>">
          <div>
           <?php if (empty($this->flickr_api_key)) { ?>
              <p class="warning">You must enter a Flickr API key before flickr-header will work</p>
            <?php } else { ?>
            <label>Enter keyword:</label>
            <input type="text" name="flickrHeader[search]" id="search" value="" />
            <input type="button" name="flickrHeader[submit]" id="searchBtn" value="Search" />
            <?php } ?>
          </div>          
          <div id="results"></div>
          <?php submit_button('Select Image', 'primary'); ?>
          </form>
        </td>
      </tr>
    </table>
    <?php
  }

  function capture_search_requests() {
    if (isset($_GET['flickrHeader_ajaxRequest'])) {

      if (!empty($_GET['search'])) {
        $data = $this->search(stripslashes($_GET['search'])); 
        $response = array();
        $response['status'] = $data['stat'];
        if (!empty($data['photos']['total'])) {
          $html = '<p>Total '.$data['photos']['total'].' photo(s) for this keyword.</p>'; 
          $response['total'] = $data['photos']['total'];
          $response['photos'] = array();
          foreach($data['photos']['photo'] as $photo) {
            //Pick the smallest version large enough to cover the header. 
            if (isset($photo['width_l']) && $photo['width_l'] > self::MIN_HEADER_WIDTH) {
              $width = (int) $photo['width_l'];
              $height = (int) $photo['height_l'];
              $url = $photo['url_l'];
            } elseif (isset($photo['width_o']) && $photo['width_o'] > self::MIN_HEADER_WIDTH) {
              $width = (int) $photo['width_o'];
              $height = (int) $photo['height_o'];
              $url = $photo['url_o'];
            } else continue; //Skip photos that are too small for the header:

            //Append the good ones to the response
            $response['photos'][] = array(
              'id' =>       $photo['id'],
              'secret' =>   $photo['secret'],
              'thumb_url'=> $photo['url_t'],
              'url' =>      $url,
              'width' =>    $width,
              'height' =>   $height
            );

          }
        } else {
          $response['total'] = 0;
          if ($data['stat'] == 'fail') $response['message'] = $data['message'];
        }
        echo json_encode($response);
      }
      exit; //Skip the remaining WordPress output - this is all we needed.
    }
  }

  function search($query = null) { 
    $args = array(
      'text' => $query,
      'per_page' => 25,
      'format' => 'php_serial',
      'safe_search' => 1, //PG-13 content
      //Only allow commercially-usable photos for now
      //See http://www.flickr.com/services/api/flickr.photos.licenses.getInfo.html
      //Todo: Add a checkbox for whether your site is commercial or not.
      'license' => '4,5,6,7', 
      'extras' => 'url_l,url_o,url_t'
    );
    return $this->flickr_method('photos.search', $args);
  }  

  function process_form() {
    if (isset($_POST['flickrHeader']['image'])) {
      $data = $_POST['flickrHeader']['image'];
      $data['status'] = 'publish'; //This will change to "revoked" if Flickr user revokes public permissions
      $image_info = $this->get_image_info($data['id']);
      $data['photopage'] = $image_info['photo']['urls']['url'][0]['_content'];
      $data['owner_info'] = $image_info['photo']['owner'];
      set_theme_mod('flickr_header_data', $data);

      set_theme_mod('header_image', $data['url']);
    }
    if (!empty($_POST['flickrHeader']['api_key'])) {
      $key = preg_replace('/[^a-zA-Z0-9]/', '', $_POST['flickrHeader']['api_key']);
      update_option('flickr-header-api-key', $key);
    }
    if ('crop' == $_POST['action']) {
      $data = get_theme_mod('flickr_header_data');
      $ratio = floatval($_POST['oitar']);
      $data['crop'] = array(
        'x1' => intval($_POST['x1'])*$ratio,
        'y1' => intval($_POST['y1'])*$ratio,
        'width' => intval($_POST['width'])*$ratio,
        'height' => intval($_POST['height'])*$ratio
      );
      set_theme_mod('flickr_header_data', $data);
    }
  }

  function get_image_perms($id) {
    $args = array('photo_id' => $id);
    return $this->flickr_method('photos.getPerms', $args);
  }

  function get_image_info($id) {
    $args = array('photo_id' => $id);
    return $this->flickr_method('photos.getInfo', $args);
  }

  function get_user_info($nsid) {
    $args = array('user_id' => $nsid);
    return $this->flickr_method('people.getInfo', $args);
  }

  function flickr_method($method, $args) {
    $uri = 'http://flickr.com/services/rest/?';
    $data = array(
      'method' => "flickr.$method",
      'api_key' => $this->flickr_api_key,
      'format' => 'php_serial'
    );
    $data = array_merge($data, $args);
    $uri .= http_build_query($data);
    $result = file_get_contents($uri);
    $result = unserialize($result);
    return $result;
  }

}

$ol_flickr_header = new OwnlocalFlickrHeader();

/* 
 * Returns the url of the currently selected Flickr header for this theme,
 * or false if no flickr image is selected.
 */
function get_flickr_header_url() {
  $data = get_theme_mod('flickr_header_data');
  $url = $data['url'];
  
  //Return nothing if permission has been revoked on Flickr.
  if ('revoked' == $data['status']) 
    return false;

  return esc_url_raw( set_url_scheme( $url ) );
}

function get_flickr_header_attribution() {
  $data = get_theme_mod('flickr_header_data');
  $owner_info = $data['owner_info'];
  $attribution_link = $data['photopage'];
  $attribution_text = "Photo by ";
  if ($owner_info['realname']) {
    $attribution_text .= $owner_info['realname'];
    if ($owner_info['username'] != $owner_info['realname'])
      $attribution_text .= ' ('.$owner_info['username'].')';
  } else $attribution_text .= $owner_info['username'];
  $link = "<a class='attribution' target='_blank' href='$attribution_link'>$attribution_text</a>";
  return $link;
}

/*
 * Echoes the HTML to display the currently selected Flickr Header for this theme
 */
function flickr_header_html() {
  $data = get_theme_mod('flickr_header_data');
  $url = $data['url'];

  $crop = $data['crop'] ? $data['crop'] : array();
  $o_width = $data['width'];
  $o_height = $data['height'];
  $theme_width = get_theme_support('custom-header', 'width');
  $theme_height = get_theme_support('custom-header', 'height');
  $flex_width = current_theme_supports('custom-header', 'flex-width');
  $flex_height = current_theme_supports('custom-header', 'flex-height');
  $max_width = 1500;
  if (current_theme_supports('custom-header', 'max-width'))
    $max_width = get_theme_support('custom-header', 'max-width');

  # Make Some Calumalations
  $crop_x1 = intval($crop['x1']);
  $crop_y1 = intval($crop['y1']);
  $crop_width = intval($crop['width']);
  $crop_height = intval($crop['height']);
  if (!$crop_width) $crop_width = $o_width;
  if (!$crop_height) $crop_height = $o_height;

  # Calculate $scale, $target_height and $target_width
  if (!$flex_height && !$flex_width) { // Fixed size
    $target_width = $theme_width;
    $target_height = $theme_height;
    $scale = $target_width / $crop_width;
  } elseif ($flex_height && $flex_width) { // Flexible size
    $target_width = min($max_width, $crop_width);
    $scale = $target_width / $crop_width;
    $target_height = round($crop_height * $scale);
  } elseif($flex_height) { // Flex height only
    $target_width = $theme_width;
    $scale = $target_width / $crop_width;
    $target_height = round($crop_height * $scale);
  } else { // Flex width only
    $target_height = $theme_height;
    $scale = $target_height / $crop_height;
    $target_width = round($crop_width * $scale);
    // In case we end up with something too wide, just clip it:
    $target_width = min($max_width, $target_width); 
  }

  # Calculate image positioning based on values above
  $image_width = round($scale * $o_width);
  $image_height = round($scale * $o_height);
  $image_left = round($crop_x1 * $scale);
  $image_top = round($crop_y1 * $scale);

  echo "<div class='flickr-header' style='width:${target_width}px;height:${target_height}px;'>";
  echo "<img src='$url' style='width:${image_width}px;height:${image_height}px;top:-${image_top}px;left:-${image_left}px;'/>";
  echo get_flickr_header_attribution();
  echo "</div>";
}
