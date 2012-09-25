<?php
/**
 * @property integer $categories_list_limit
 * @property integer $posts_list_limit
 * @property integer $comments_list_limit
 * @property integer $links_list_limit
 * @property integer $pages_list_limit
 * @property integer $tags_list_limit
 * @property integer $videos_list_limit
 * @property integer $audios_list_limit
 * @property integer $comments_avatar_height // Replace the comments_avatar_size
 * @property integer $post_processing_batch_size
 * @property integer $search_limit
 * @property string $sep_color
 * @property integer $main_tab_index
 * @property string $api_server
 * @property boolean $configured
 * @property integer $app_id
 * @property string $app_token
 * @property string $app_name
 * @property string $version
 * @property string $appstore_url
 * @property boolean $app_live
 * @property integer $appstore_url_timeout
 * @property boolean $allow_grouped_lists
 * @property boolean $zebra_lists
 * @property string $wiziapp_theme_name
 * @property integer $count_minimum_for_appear_in_albums
 * @property integer $multi_image_height
 * @property integer $multi_image_width
 * @property integer $max_thumb_check
 * @property boolean $settings_done
 * @property boolean $finished_processing
 * @property integer $last_version_checked_at
 * @property string $wiziapp_avail_version
 * @property boolean $show_need_upgrade_msg
 * @property string $last_version_shown
 * @property boolean $wiziapp_showed_config_once
 * @property boolean $email_verified
 * @property boolean $show_email_verified_msg
 * @property integer $last_recorded_save
 */
// As long as we are supporting php < 5.3 we shouldn't extent the singleton class
//class WiziappConfig extends WiziappSingleton implements WiziappIInstallable{
class WiziappConfig implements WiziappIInstallable{
    private $options = array();

    private $saveAsBulk = FALSE;

    private $name = 'wiziapp_settings';
    private $internalVersion = 0.3;

    private static $_instance = null;

    public static function getInstance() {
        if( is_null(self::$_instance) ) {
            self::$_instance = new WiziappConfig();
        }

        return self::$_instance;
    }

    private function  __clone() {
        // Prevent cloning
    }

    private function __construct(){
        $this->load();
    }

    private function load(){
        $this->options = get_option($this->name);
    }

    public function upgrade(){
        /**
         * This is depended per version, each version might remove or add values...
         */
        $resetOptions = array(); // Add here the keys to reset to the default value;
        $removeOptions = array('test1'); // Add here the keys to remove from the options array;

        $newDefaults = wiziapp_getDefaultConfig();
        foreach($resetOptions as $optionName){
            $this->options[$optionName] = $newDefaults[$optionName];
        }

        foreach($removeOptions as $optionName){
           unset($this->options[$optionName]);
        }

        // save the updated options
        $this->options['options_version'] = $this->internalVersion;
        return $this->save();
    }

    public function needUpgrade(){
        return ( $this->internalVersion != $this->options['options_version'] );
    }

    public function uninstall(){
        delete_option( $this->name );
    }

    public function install(){
        if ( ! $this->isInstalled() ){
            $this->loadDefaultOptions();
            $this->options['options_version'] = $this->internalVersion;
            $this->save();
        }

        return $this->isInstalled();
    }

    public function isInstalled(){
        // Make sure we are loaded
        $this->load();
        return ( ! empty($this->options) );
    }

    private function loadDefaultOptions(){
        $this->options =  wiziapp_getDefaultConfig();
    }

    public function startBulkUpdate(){
        $this->saveAsBulk = TRUE;
    }

    public function bulkSave(){
        $this->saveAsBulk = FALSE;
        return $this->save();
    }

    private function save(){
        return update_option($this->name, $this->options, '', 'no');
    }

    public function __get($option){
        $value = null;

        if ( isset($this->options[$option]) ){
            $value = $this->options[$option];
        }

        return $value;
    }

    public function saveUpdate($option, $value){
        $saved = FALSE;

        if ( isset($this->options[$option]) ){
            $this->options[$option] = $value;
            $saved = $this->save();
        }

        return $saved;
    }

    public function __isset($option){
        return isset($this->options[$option]);
    }
    
    public function __set($option, $value){
        $saved = FALSE;
        
        //if ( isset($this->options[$option]) ){
            $this->options[$option] = $value;
            if ( !$this->saveAsBulk ){
                $saved = $this->save();
            }
        //}

        return $saved;
    }

    public function usePostsPreloading(){
        if ( isset($_GET['ap']) && $_GET['ap']==1 ){
            return FALSE;
        } else {
            return $this->options['use_post_preloading'];
        }
    }

   public function getImageSize($type){
        if ( !isset($this->options[$type . '_width']) || !isset($this->options[$type . '_height']) ){
            throw new WiziappUnknownType('Clone is not allowed.');
       }

        $size = array(
            'width' => $this->options[$type . '_width'],
            'height' => $this->options[$type . '_height'],
        );
        return $size;
   }

    public function getScreenTitle($screen){
        $title = '';
        if ( isset($this->options[$screen.'_title']) ){
            $title = stripslashes($this->options[$screen.'_title']);
        }
        return $title;
    }

    public function getCdnServer(){
        $cdn = $this->options['cdn_server'];
        $protocol = 'http://';

        if ( isset($_GET['secure']) && $_GET['secure']==1 ){
            $cdn = $this->options['secure_cdn_server'];
            $protocol = 'https://';
        }
        return $protocol.$cdn;
    }

    public function getCommonApiHeaders(){
        $app_token = $this->options['app_token'];

        $headers = array(
            'Application' => $app_token,
            'wiziapp_version' => WIZIAPP_P_VERSION
        );

        if ( !empty($this->options['api_key']) ){
            $headers['Authorization'] = 'Basic '.$this->options['api_key'];
        }

        return $headers;
    }

    public function getAppIcon(){
        $url = $this->options['app_icon'];

        if ( strpos($url, 'http') !== 0){
            $url = 'https://'.$this->options['api_server'].$url;
        }
        return $url;
    }

    public function getAppDescription(){
        $patterns = array("/(<br>|<br \/>|<br\/>)\s*/i","/(\r\n|\r|\n)/");
        $replacements = array(PHP_EOL, PHP_EOL);
        return preg_replace($patterns, $replacements, stripslashes($this->options['app_description']));
    }
}